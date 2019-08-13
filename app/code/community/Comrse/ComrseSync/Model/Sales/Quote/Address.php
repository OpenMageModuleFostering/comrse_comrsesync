<?php
/** 
* Comrse Shipping Handler
* Hide Comrse Shipping Method outside of SOAP API
* @author Z
* @version 1.1.3
* @since 1.1.0
*/
class Comrse_ComrseSync_Model_Sales_Quote_Address extends Mage_Sales_Model_Quote_Address
{
    public function getShippingRatesCollection()
    {   
        error_reporting(0);
        ini_set('display_errors',0);
        try
        {
            parent::getShippingRatesCollection();
            $removeRates = array();
            $soapConnection = false;

            // check for SOAP Patterns
            $isSoapPath = @(isset($_SERVER["PATH_INFO"]) && $_SERVER["PATH_INFO"] == '/api/soap/index/');
            $isSoapUserAgent = @(isset($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'], 'SOAP') !== FALSE);
            $isSoapAction = @(isset($_SERVER['HTTP_SOAPACTION']));

            // if SOAP connection is detected
            if ($isSoapPath || $isSoapUserAgent || $isSoapAction)
                $soapConnection = true;

            // if not a SOAP connection remove comrseshipping method
            if (!$soapConnection) {
                foreach($this->_rates as $key => $rate) {
                    if($rate->getCarrier() == 'comrseship')
                        $removeRates[] = $key;
                }
                foreach($removeRates as $key)
                    $this->_rates->removeItemByKey($key);
            }
            return $this->_rates;
        }
        catch(Exception $e)
        {
            /* ENSURE COMRSE SHIP IS REMOVED FROM UI */
            parent::getShippingRatesCollection();
            $removeRates = array();
            foreach ($this->_rates as $key => $rate) {
                if($rate->getCarrier() == 'comrseship')
                    $removeRates[] = $key;
            }
            foreach($removeRates as $key)
                $this->_rates->removeItemByKey($key);

            return $this->_rates;

            Mage::log('Comrse Shipping Method: '.$e->getMessage());
        }
    }
}
?>