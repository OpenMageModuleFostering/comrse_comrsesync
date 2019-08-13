<?php
 
class Comrse_ComrseSync_Model_Resource_Comrseconnect extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('comrsesync/comrseconnect', 'comrsesync_id');
    }
}