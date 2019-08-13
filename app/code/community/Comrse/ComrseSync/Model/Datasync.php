<?php

/**
* Comr.se Ongoing Sync & Payment Method
*
* @author Zabel @ Comr.se
* @copyright 2014 Comr.se
* @version 1.1.4.1
* @since 0.0.1
* @todo replace order arrays with object model
*/

class Comrse_ComrseSync_Model_Datasync extends Mage_Payment_Model_Method_Abstract
{
    /*
    * COMRSE PAYMENT METHOD SETTINGS
    * - allows external payment methods
    * - does not appear during normal checkout in properly configured/current Magento installations
    */
    protected $_code = 'comrsesync';
    protected $_isGateway = false;
    protected $_canAuthorize = false;
    protected $_canCapture = false;
    protected $_canCapturePartial = false;
    protected $_canRefund = false;
    protected $_canVoid = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = false;
    protected $_canUseForMultishipping = false;
    protected $_canSaveCc = false;


    /**
    * Payment Refunded Handling
    */
    public function paymentRefund(Varien_Event_Observer $observer)
    {
        error_reporting(0);
        ini_set('display_errors',0);
        try 
        {
            $mageEventData = $observer->getEvent()->getData();
            $mageOrderPayment = $mageEventData['payment'];
            $mageOrder = $mageOrderPayment->getOrder();
            $normalizedOrder = Mage::helper('comrsesync/order')->normalizeOrderData($mageOrder, false, "RETURNED");
            $orgData = Mage::getModel('comrsesync/comrseconnect')->load(Comrse_ComrseSync_Model_Config::COMRSE_RECORD_ROW_ID);
            $postOrder = Mage::helper('comrsesync')->comrseRequest("POST", Comrse_ComrseSync_Model_Config::API_PATH . "organizations/" . $orgData->getOrg() . "/orders?metrics_only=false", $orgData, json_encode($normalizedOrder));
        }
        catch (Exception $e) 
        {
            Mage::log("Comrse - Error capturing payment refund: {$e->getMessage()}");
        }
    }


    /**
    * ORDER PRICE MANIPULATION
    * - uses price set in Comr.se for total amount
    * - single product usage
    * @todo secure stock sync
    */
    public function orderSave(Varien_Event_Observer $observer)
    {
        error_reporting(0);
        ini_set('display_errors',0);
        try 
        {

            // check if store has comrse disabled
            $storeDisabled = Mage::getStoreConfig('advanced/modules_disable_output/Comrse_ComrseSync', $observer->getOrder()->getStoreId());

            if (!$storeDisabled)
            {
                $mageOrder = $observer->getOrder();
                $orderData = $mageOrder->getData();

                $customerEmailAddress = (!empty($orderData['customer_email']) ? $orderData['customer_email'] : "");

                // comrse info
                $orgData = Mage::getModel('comrsesync/comrseconnect')->load(Comrse_ComrseSync_Model_Config::COMRSE_RECORD_ROW_ID);
                

                if (@is_object($mageOrder) && is_object($orgData)) 
                {
                    $orderPaymentMethod = $mageOrder->getPayment()->getMethodInstance()->getTitle();

                    // if this order came from Comr.se
                    if ($orderPaymentMethod == "Comrse" || $orderPaymentMethod == "comrsepay" || $orderPaymentMethod == "comrsesync") 
                    {
                        $mageOrderItems = $mageOrder->getAllItems();

                        $mageCollectionItems = Mage::getResourceModel('sales/order_item_collection')->addFieldToFilter('order_id', array('eq' => $mageOrder->getId()));

                        if (is_array($mageOrderItems)) 
                        {
                            $shippingAmount = $taxAmount = $subTotalAmount = $grandTotalAmount = 0;

                            foreach ($mageOrderItems as $mageOrderItem) {
                                $itemData = @$mageOrderItem->getData(); // one product limitation begins here
                                $itemId = $itemData['product_id'];

                                // loop collection
                                foreach ($mageCollectionItems as $mageCollectionItem) 
                                {
                                    if ($mageCollectionItem->getProductType() == 'simple')
                                    {
                                        $invoiceItems[$mageCollectionItem->getId()] = $mageCollectionItem->getQtyOrdered();

                                        $qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($mageCollectionItem->getProduct())->getQty();

                                        $mageProductOptions = $mageCollectionItem->getProductOptions();

                                        if (is_array($mageProductOptions) && is_array($mageProductOptions['info_buyRequest']) && is_array($mageProductOptions['info_buyRequest']['super_attribute']))
                                        {
                                            $mageProductOptions['info_buyRequest']['super_attribute']["qty"] = $qty;
                                            $payload = urlencode(json_encode($mageProductOptions['info_buyRequest']['super_attribute']));
                                            $stockSync = Mage::helper('comrsesync')->comrseRequest("GET", Comrse_ComrseSync_Model_Config::CART_API_PATH . "stock_sync?external_id=$itemId&org_id={$orgData->getOrg()}&stock=$payload", $orgData);
                                        }
                                    }
                                }
                            }

                            //------------------------------------------------------------
                            // retreive comr.se amounts
                            //------------------------------------------------------------
                            $comrseAmounts = Mage::helper('comrsesync')->comrseRequest("GET", Comrse_ComrseSync_Model_Config::CART_API_PATH . "price_sync?email_address=$customerEmailAddress&org_id=phpunit-test-framework", $orgData);

                            if ($comrseAmounts != '' && !is_null($comrseAmounts)) 
                            {
                                $comrseAmounts = json_decode($comrseAmounts);

                                $shippingAmount += $comrseAmounts->shipping;
                                $taxAmount += $comrseAmounts->tax;
                                $subTotalAmount += $comrseAmounts->subtotal;
                                $grandTotalAmount += $comrseAmounts->total;
                            }

                            // alter order amounts for Comr.se Order
                            if ($subTotalAmount > 0 && $grandTotalAmount > 0) 
                            {

                                // shipping totals
                                $mageOrder->setShippingAmount($shippingAmount);
                                $mageOrder->setBaseShippingAmount($shippingAmount);
                                // tax totals
                                $mageOrder->setTaxAmount($taxAmount);
                                $mageOrder->setBaseTaxAmount($taxAmount);
                                // subtotals
                                $mageOrder->setSubtotal($subTotalAmount);
                                $mageOrder->setBaseSubtotal($subTotalAmount);
                                // grand totals
                                $mageOrder->setGrandTotal($grandTotalAmount);
                                $mageOrder->setBaseGrandTotal($grandTotalAmount);
                                $mageOrder->setBaseTotalPaid($grandTotalAmount);
                                $mageOrder->setTotalPaid($grandTotalAmount);
                                $mageOrder->save();

                                // create order invoice
                                if (isset($invoiceItems) && is_array($invoiceItems))
                                    $createInvoice = Mage::getModel('sales/order_invoice_api')->create($mageOrder->getIncrementId(), $invoiceItems, null, false, true);

                            }
                        }
                    }
                    // else the order is from the dotcom
                    else 
                    {
                        //------------------------------------------------------------
                        // Normalize Order Data
                        //------------------------------------------------------------
                        $normalizedOrder = Mage::helper('comrsesync/order')->normalizeOrderData($mageOrder);
                        if (!empty($normalizedOrder))
                            $postOrder = Mage::helper('comrsesync')->comrseRequest("POST", Comrse_ComrseSync_Model_Config::API_PATH . "organizations/" . $orgData->getOrg() . "/orders?metrics_only=false", $orgData, json_encode(array("orders" => array($normalizedOrder))));
                    }
                }
            }
        }
        catch(Exception $e)
        {
            Mage::log("Comrse New Order: {$e->getMessage()}");
        }
    }


    /**
    * Update Order
    * Set order in processing state
    * @access public
    */
    public function orderUpdate(Varien_Event_Observer $observer)
    {
        try{
            error_reporting(0);
            ini_set('display_errors',0);

            $storeDisabled = Mage::getStoreConfig('advanced/modules_disable_output/Comrse_ComrseSync', $observer->getOrder()->getStoreId());
            if (!$storeDisabled)
            {
                if ($mageOrder = $observer->getOrder())
                {
                    $orderPaymentMethod = $mageOrder->getPayment()->getMethodInstance()->getTitle();
                    // if this order came from Comr.se
                    if (($orderPaymentMethod == "Comrse" || $orderPaymentMethod == "comrsepay" || $orderPaymentMethod == "comrsesync") && $mageOrder->getState() == Mage_Sales_Model_Order::STATE_NEW)
                    {
                        $orgData = Mage::getModel('comrsesync/comrseconnect')->load(Comrse_ComrseSync_Model_Config::COMRSE_RECORD_ROW_ID);
                        $mageOrder->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
                        $mageOrder->save();
                    }
                }
            }
        }
        catch(Exception $e)
        {
            Mage::log("Comrse Order Update: {$e->getMessage()}");
        }
    }
  

 
    /**
    * Order Cancelation Event Listener
    * @access public
    * @param Varien_Event_Observer $observer
    */
    public function orderCancelation(Varien_Event_Observer $observer)
    {
        try 
        {
            $storeDisabled = Mage::getStoreConfig('advanced/modules_disable_output/Comrse_ComrseSync', $observer->getOrder()->getStoreId());
            if (!$storeDisabled)
            {
                $orgData = Mage::getModel('comrsesync/comrseconnect')->load(Comrse_ComrseSync_Model_Config::COMRSE_RECORD_ROW_ID);

                //------------------------------------------------------------
                // Normalize Order Data & Send to Comr.se
                //------------------------------------------------------------
                $normalizedOrder = Mage::helper('comrsesync/order')->normalizeOrderData($observer->getOrder(), false, "CANCELLED");
                if (!empty($normalizedOrder))
                    $postOrder = Mage::helper('comrsesync')->comrseRequest("POST", Comrse_ComrseSync_Model_Config::API_PATH . "organizations/" . $orgData->getOrg() . "/orders?metrics_only=false", $orgData, json_encode($normalizedOrder));
            }
     
        }
        catch (Exception $e)
        {
            Mage::log("Comrse Order Cancelation: {$e->getMessage()}");
        }
    }


    /**
    * Product Update/Creation Event Listener
    * @access public
    * @param Varien_Event_Observer $observer
    */
    public function productUpdate(Varien_Event_Observer $observer)
    {
        try
        {
            //------------------------------------------------------------//
            // Retreive and Handle Org Data
            //------------------------------------------------------------//
            $orgData = Mage::getModel('comrsesync/comrseconnect')->load(Comrse_ComrseSync_Model_Config::COMRSE_RECORD_ROW_ID);

            //------------------------------------------------------------//
            // Normalize Product Data
            //------------------------------------------------------------//
            $normalizedProduct = Mage::helper('comrsesync/product')->normalizeProductData($observer->getProduct());

            //------------------------------------------------------------//
            // Sync Products to Comr.se
            //------------------------------------------------------------//
            $preparedData = json_encode(array("product_details_list" => array($normalizedProduct)));
            $postProducts = Mage::helper('comrsesync')->comrseRequest("POST", Comrse_ComrseSync_Model_Config::API_PATH . "organizations/" . $orgData->getOrg() . "/products", $orgData, $preparedData);
        }
        catch (Exception $e)
        {
            Mage::log("Comrse Product Update Sync: {$e->getMessage()}");
        }
    }
}
?>