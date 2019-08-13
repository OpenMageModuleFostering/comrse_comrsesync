<?php
/** 
* Comrse Shipping Handler
* Hide Comrse Shipping Method outside of SOAP API
*
* @version 1.1.3
* @since 1.1.0
*/
class Comrse_ComrseSync_Model_Sales_Quote_Address extends Mage_Sales_Model_Quote_Address
{
    public function getShippingRatesCollection()
    {
        try{
            error_reporting(0);
            ini_set('display_errors',0);
            parent::getShippingRatesCollection();
            $removeRates = array();
            $soap_connection = false;

            // if SOAP is detected
            if((@isset($_SERVER["PATH_INFO"]) && @$_SERVER["PATH_INFO"] == '/api/soap/index/') || (@isset($_SERVER['HTTP_USER_AGENT']) && stripos(@$_SERVER['HTTP_USER_AGENT'], 'SOAP') !== FALSE) || @isset($_SERVER['HTTP_SOAPACTION'])){
                $soap_connection = true;
            }

            // if not a SOAP connection remove comrseshipping method
            if(!$soap_connection){
                foreach($this->_rates as $key => $rate) {
                    if($rate->getCarrier() == 'comrseship') {
                        $removeRates[] = $key;
                    }
                }
                foreach($removeRates as $key){
                    $this->_rates->removeItemByKey($key);
                }
            }
            return $this->_rates;
        }
        catch(Exception $e){

            error_reporting(0);
            ini_set('display_errors',0);

            // ensure comrseship is removed
            parent::getShippingRatesCollection();
            $removeRates = array();
            foreach($this->_rates as $key => $rate) {
                if($rate->getCarrier() == 'comrseship') {
                    $removeRates[] = $key;
                }
            }
            foreach($removeRates as $key){
                $this->_rates->removeItemByKey($key);
            }
            return $this->_rates;

            // log error
            Mage::log('Comrse Shipping Method: '.$e->getMessage());
            
        }
    }
}
?>