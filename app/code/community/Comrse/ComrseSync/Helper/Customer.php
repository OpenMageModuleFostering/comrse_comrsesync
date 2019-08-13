<?php
class Comrse_ComrseSync_Helper_Customer extends Mage_Core_Helper_Abstract {

  /**
  * Normalize Customer Data
  * Maps mixed Magento Customer data to Comrse Customer Object
  * @access public
  * @return Array normalizedCustomer
  * @param Object mageCustomer
  */
  public function normalizeCustomerData($mageCustomer, $orgId)
  {
    try
    {
      $commerceCustomer = Mage::getModel('comrsesync/Comrse_Customer');
      $commerceCustomer
      ->setFirstName($mageCustomer['firstname'])
      ->setLastName($mageCustomer['lastname'])
      ->setEmailAddress($mageCustomer['email'])
      ->setExternalId($mageCustomer['entity_id'])
      ->setOrgId($orgId);
      return $commerceCustomer->toArray();
    }
    catch (Exception $e)
    {
      Mage::log("Comrse Customer Data Normalization Error: {$e->getMessage()}");
    }
  }


  /**
  * Sync Orders
  * @access public
  */
  public function syncCustomers() 
  {
    try
    {
      //------------------------------------------------------------
      // Retreive and Handle Org Data
      //------------------------------------------------------------
      $orgData = Mage::getModel('comrsesync/comrseconnect')->load(Comrse_ComrseSync_Model_Config::COMRSE_RECORD_ROW_ID);
      $lastCustomersSynced = json_decode($orgData->getLastCustomersSynced(), true);

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
        $lastCustomersOfStoreSynced = (isset($lastCustomersSynced[$storeId])) ? $lastCustomersSynced[$storeId] : 0;

        // check if plugin is disabled on store
        $storeDisabled = Mage::getStoreConfig('advanced/modules_disable_output/Comrse_ComrseSync', $storeId);
        if (!$storeDisabled)
        {

          $customerCount = ($multiStore) ? Mage::getModel('customer/customer')->getCollection()->addFieldToFilter("store_id", array('eq' => $storeId))->addAttributeToFilter('entity_id', array('gt' => $lastCustomersOfStoreSynced))->getSize() : Mage::getModel('customer/customer')->getCollection()->getSize();

          // paginate and loop
          $batchMath = ceil($customerCount / Comrse_ComrseSync_Model_Config::CUSTOMER_SYNC_BATCH_SIZE) + 1;
          for ($i = 1; $i <= $batchMath; $i++)
          {
            $maxCustomerId = 0;
            $customersPayload = array();

            if ($multiStore)
              $mageCustomers = Mage::getModel('customer/customer')->getCollection()->setPageSize(Comrse_ComrseSync_Model_Config::ORDER_SYNC_BATCH_SIZE)->setCurPage($i)->addFieldToFilter("store_id", array('eq' => $storeId))->addAttributeToFilter('entity_id', array('gt' => $lastCustomersOfStoreSynced))->addAttributeToSelect('firstname')->addAttributeToSelect('lastname')->addAttributeToSelect('email');
            else
              $mageCustomers = Mage::getModel('customer/customer')->getCollection()->setPageSize(Comrse_ComrseSync_Model_Config::ORDER_SYNC_BATCH_SIZE)->setCurPage($i)->addAttributeToFilter('entity_id', array('gt' => $lastCustomersOfStoreSynced))->addAttributeToSelect('firstname')->addAttributeToSelect('lastname')->addAttributeToSelect('email');

            if ($mageCustomers)
            {
              foreach ($mageCustomers as $mageCustomer)
              {
                $customersPayload[] = $this->normalizeCustomerData($mageCustomer, $orgData->getOrg());
                if ($mageCustomer['entity_id'] > $maxCustomerId)
                  $maxCustomerId = $mageCustomer['entity_id'];
              }
            }

            if (!empty($customersPayload))
            {
              // submit customers to Commerce
              $postCustomers = Mage::helper('comrsesync')->comrseRequest("POST", Comrse_ComrseSync_Model_Config::API_PATH . "organizations/" . $orgData->getOrg() . "/customers?metrics_only=false", $orgData, json_encode(array("customers" => $customersPayload)));
              $lastCustomersSynced[$storeId] = $maxCustomerId;
              if (isset($postCustomers->message))
              {
                Mage::log("COMMERCE CUSTOMER SYNC ERROR: " . json_encode($postCustomers));
                return false;
              }
            }
          }
        }
      }
      //------------------------------------------------------------
      // Record Last Synced Product ID
      //------------------------------------------------------------
      $saveLastSynced = @$orgData->setLastCustomersSynced(json_encode($lastCustomersSynced))->save();
    }
    catch (Exception $e)
    {
      Mage::log("Comrse Customer Sync Error: {$e->getMessage()}");
    }
  }
}