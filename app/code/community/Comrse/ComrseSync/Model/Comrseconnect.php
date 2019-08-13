<?php
 
class Comrse_ComrseSync_Model_Comrseconnect extends Mage_Core_Model_Abstract
{
     
    public function _construct()
    {
    	try{
        parent::_construct();
        $this->_init('comrsesync/comrseconnect');
      }
      catch(Exception $e){
      	Mage::log('Comrse Connect Mode: '.$e->getMessage());
      }
    }
}