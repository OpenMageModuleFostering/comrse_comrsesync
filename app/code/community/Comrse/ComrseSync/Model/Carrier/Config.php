<?php
/** 
* Comrse Shipping Handler
* Hide Comr.se Shipping Method from front end calls
*
* @version 1.2.1
* @since 11
*/
class Comrse_ComrseSync_Model_Shipping_Config extends Mage_Shipping_Model_Config
{
    public function getActiveCarriers($store = null)
    {
        try{
            error_reporting(0);
            $carriers = parent::getActiveCarriers($store);
            if(Mage::getDesign()->getArea() === Mage_Core_Model_App_Area::AREA_FRONTEND){
                $carriersCodes = array_keys($carriers);
                foreach($carriersCodes as $carriersCode){
                    if($carriersCode == 'comrseship'){
                        unset($carriers[$carriersCode]);
                    }
                }
            }
            return $carriers;
        }
        catch(Exception $e){
            Mage::log('Comrse Shipping Method: '.$e->getMessage());
        }
    }
}