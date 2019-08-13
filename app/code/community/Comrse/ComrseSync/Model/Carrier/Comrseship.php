<?php

/** 
* Comrse Shipping Handler
* Hidden shipping method only available to the SOAP API
*
* @version 1.1.3
* @since 1.1.2
*/
class Comrse_ComrseSync_Model_Carrier_Comrseship
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
    {

    protected $_code = 'comrseship';
    protected $_isFixed = true;

     public function collectRates(Mage_Shipping_Model_Rate_Request $request){
        try{
            error_reporting(0);
            ini_set('display_errors',0);
            if (!$this->getConfigFlag('active')) {
                return false;
            }

            $freeBoxes = 0;
            if ($request->getAllItems()) {
                foreach ($request->getAllItems() as $item) {

                    if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                        continue;
                    }

                    if ($item->getHasChildren() && $item->isShipSeparately()) {
                        foreach ($item->getChildren() as $child) {
                            if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                                $freeBoxes += $item->getQty() * $child->getQty();
                            }
                        }
                    } elseif ($item->getFreeShipping()) {
                        $freeBoxes += $item->getQty();
                    }
                }
            }
            $this->setFreeBoxes($freeBoxes);

            $result = Mage::getModel('shipping/rate_result');
            if ($this->getConfigData('type') == 'O') { // per order
                $shippingPrice = $this->getConfigData('price');
            } elseif ($this->getConfigData('type') == 'I') { // per item
                $shippingPrice = ($request->getPackageQty() * $this->getConfigData('price')) - ($this->getFreeBoxes() * $this->getConfigData('price'));
            } else {
                $shippingPrice = false;
            }

            $shippingPrice = $this->getFinalPriceWithHandlingFee($shippingPrice);

            if ($shippingPrice !== false) {
                $method = Mage::getModel('shipping/rate_result_method');

                $method->setCarrier('comrseship');
                $method->setCarrierTitle($this->getConfigData('title'));

                $method->setMethod('comrseship');
                $method->setMethodTitle($this->getConfigData('name'));

                if ($request->getFreeShipping() === true || $request->getPackageQty() == $this->getFreeBoxes()) {
                    $shippingPrice = '0.00';
                }

                $method->setPrice($shippingPrice);
                $method->setCost($shippingPrice);

                $result->append($method);
            }

            return $result;
        }
        catch(Exception $e){
            Mage::log('ComrseShip Model: '.$e->getMessage());
        }
    }


    // return comrse ship name
    public function getAllowedMethods()
    {
        try{
            return array('comrseship' => $this->getConfigData('name'));
        }
        catch(Exception $e) {
            Mage::log('Return ComrseShip Name: ' . $e->getMessage());
        }
    }

}
