<?php
class Comrse_ComrseSync_Helper_Order extends Mage_Core_Helper_Abstract {

  /**
  * Normalize Order Data
  * Maps mixed Magento Order data to Comrse Order Object
  * Maps mixed Magento Order data to Comrse Metric Event Object
  * @access public
  * @return Object normalizedOrder
  * @param Object mageOrder
  * @param Bool sendMetrics
  * @todo Re-implement send metrics
  */
  public function normalizeOrderData($mageOrder, $sendMetrics = false, $orderStatus = "IN_PROCESS")
  {
    //------------------------------------------------------------
    // Setup Data Objects
    //------------------------------------------------------------
    $orgData = Mage::getModel('comrsesync/comrseconnect')->load(Comrse_ComrseSync_Model_Config::COMRSE_RECORD_ROW_ID);
    $mageOrderData = $mageOrder->getData();
    $comrseOrder = Mage::getModel('comrsesync/Comrse_Order');
    $comrseCustomer = Mage::getModel('comrsesync/Comrse_Customer');

    //------------------------------------------------------------
    // If Passing Metric Event
    //------------------------------------------------------------
    if ($sendMetrics)
    {
      $metricEvent = Mage::getModel('comrsesync/Comrse_MetricEvent');
      $metricEvent
      ->setBrandId($orgData->getOrg())
      ->setEmail($mageOrderData['customer_email'])
      ->setIpAddress($mageOrderData['x_forwarded_for'])
      ->setActivityTimestamp(strtotime($mageOrderData['created_at']) * 1000)
      ->setActivityGroupId($mageOrderData['increment_id']);
    }

    $comrseOrder
    ->setSubmitDate($mageOrderData['created_at'])
    ->setExternalId($mageOrderData['entity_id'])
    ->setIpAddress($mageOrderData['x_forwarded_for'])
    ->setStatus($orderStatus);

    //------------------------------------------------------------
    // Set Pricing
    //------------------------------------------------------------
    $comrseOrder
    ->setTotal($mageOrderData['grand_total'])
    ->setSubTotal($mageOrderData['subtotal'])
    ->setTotalShipping($mageOrderData['shipping_amount'])
    ->setTotalTax($mageOrderData['tax_amount']);

    //------------------------------------------------------------
    // Set Customer & Billing Address Details
    //------------------------------------------------------------
    $comrseCustomer->setEmailAddress($mageOrderData['customer_email']);
    if ($mageOrder->getBillingAddress()) 
    {
      $billingAddress = $mageOrder->getBillingAddress()->getData();

      // create customer
      $comrseCustomer
      ->setFirstName($billingAddress['firstname'])
      ->setLastName($billingAddress['lastname']);

      // if metrics
      if ($sendMetrics)
        $metricEvent->setCustomerName($billingAddress['firstname'] . " " . $billingAddress['lastname']);

      // create customer address
      $regionData = Mage::getModel('directory/region')->load($billingAddress['region_id'])->getOrigData();
      $customerAddress = Mage::getModel('comrsesync/Comrse_CustomerAddress');
      $customerAddress
      ->setFirstName($billingAddress['firstname'])
      ->setLastName($billingAddress['lastname'])
      ->setAddressLine1($billingAddress['street'])
      ->setCity($billingAddress['city'])
      ->setPostalCode($billingAddress['postcode'])
      ->setCountry($regionData['country_id'])
      ->setState($regionData['code'], $regionData['name'])
      ->setPhonePrimary($billingAddress['telephone'])
      ->setAddressType("billing");
      $comrseOrder->addAddress($customerAddress->toArray());
    }
    $comrseOrder->setCustomer($comrseCustomer->toArray());

    //------------------------------------------------------------
    // Handle Order Items
    //------------------------------------------------------------
    $mageOrderItems = $mageOrder->getAllItems();

    foreach ($mageOrderItems as $mageOrderItem)
    {
      $mageProduct = $mageOrderItem->getData();
      if (empty($mageProduct['parent_item_id']))
      {
        $comrseOrderItem = Mage::getModel('comrsesync/Comrse_OrderItem');
        $comrseOrderItem
        ->setExternalId($mageProduct['product_id'])
        ->setProductId($mageProduct['product_id'])
        ->setName($mageProduct['name'])
        ->setQuantity($mageProduct['qty_ordered'])
        ->setRetailPrice($mageProduct['original_price'])
        ->setSalePrice($mageProduct['price']);

        $mageProductOptions = $mageOrderItem->getProductOptions();
        $mageProductOptionsList = $mageProductOptions['attributes_info'];

        if (is_array($mageProductOptionsList))
        {
          foreach ($mageProductOptionsList as $mageProductOption)
          {
            $comrseOrderItem->addOrderItemAttribute($mageProductOption);

            // if metrics
            if ($sendMetrics)
              $metricEvent->addProductOption($mageProductOption);
          }
        }
        $comrseOrder->addOrderItem($comrseOrderItem->toArray());

        // if metrics
        if ($sendMetrics)
        {
          $metricEvent
          ->setExternalProductId($mageProduct['product_id'])
          ->setProductPrice($mageProduct['original_price'])
          ->setProductSalePrice($mageProduct['price'])
          ->setProductQuantity($mageProduct['qty_ordered'])
          ->setProductName($mageProduct['name'])
          ->send($orgData);
        }
      }
    }
    return $comrseOrder->toArray();
  }


  /**
  * Sync Orders
  * @access public
  * @todo handle last synced
  */
  public function syncOrders() 
  {
    //------------------------------------------------------------
    // Retreive and Handle Org Data
    //------------------------------------------------------------
    $orgData = Mage::getModel('comrsesync/comrseconnect')->load(Comrse_ComrseSync_Model_Config::COMRSE_RECORD_ROW_ID);
    $lastSynced = $orgData->getLastOrderSynced();

    $stores = Mage::app()->getStores();

    if (!is_array($stores))
      $stores = array("stores" => false);

    foreach ($stores as $store)
    {
      if (isset($stores[0]['stores']) && $stores[0]['stores'] === false) 
      {
        $storeDisabled = $multiStore = false;
        $storeId = 0;
      }
      else 
      {
        $storeId = $store->getId();
        $multiStore = true;
      }

      // check if plugin is disabled on store
      $storeDisabled = Mage::getStoreConfig('advanced/modules_disable_output/Comrse_ComrseSync', $storeId);
      if (!$storeDisabled)
      {
        if ($multiStore)
          $orderCount = Mage::getModel('sales/order')->getCollection()->addFieldToFilter("store_id", array('eq' => $storeId))->getSize();
        else
          $orderCount = Mage::getModel('sales/order')->getCollection()->getSize();

        // paginate and loop
        $batchMath = ceil($orderCount / Comrse_ComrseSync_Model_Config::ORDER_SYNC_BATCH_SIZE);

        for ($i = 1; $i <= $batchMath; $i++)
        {
          if ($multiStore)
            $mageOrders = Mage::getModel('sales/order')->getCollection()->setPageSize(Comrse_ComrseSync_Model_Config::ORDER_SYNC_BATCH_SIZE)->setCurPage($i)->addFieldToFilter("store_id", array('eq' => $storeId));
          else
            $mageOrders = Mage::getModel('sales/order')->getCollection()->setPageSize(Comrse_ComrseSync_Model_Config::ORDER_SYNC_BATCH_SIZE)->setCurPage($i);

          foreach ($mageOrders as $mageOrder)
            $ordersPayload[] = $this->normalizeOrderData($mageOrder, false);

          $postOrders = json_decode(Mage::helper('comrsesync')->comrseRequest("POST", Comrse_ComrseSync_Model_Config::API_PATH . "organizations/" . $orgData->getOrg() . "/orders?metrics_only=false", $orgData, json_encode(array("orders" => $ordersPayload))));
          if (isset($postOrders->message)){
            Mage::log("ORDER SYNC ERROR: " . json_encode($postOrders));
            return false;
          }
        }
      }
    }
  }
}