<?php
/**
*
* Comr.se Sync Controller
*
* @author Z
* @copyright 2013 Comr.se
* @version 1.1.4
* @since 0.0.1
* @todo replace arrays with models
*/

class Comrse_ComrseSync_Adminhtml_ComrsesyncbackendController extends Mage_Adminhtml_Controller_Action
{

  private $_currency_code = "USD";

  /**
  * Render the Admin Frontend View
  * @access public
  */
  public function indexAction() {
    try
    {
      error_reporting(0);
      ini_set('display_errors',0);
  
      $orgData = Mage::getModel('comrsesync/comrseconnect')->load(1);
      $userData = array (
        'org_id' => $orgData->getOrg(),
        'api_token' => $orgData->getToken()
        );
      Mage::register('data', $userData); 
      $this->loadLayout();
      $this->_title($this->__("Comrse Settings"));
      $this->renderLayout();
    }
    catch (Exception $e)
    {
      Mage::log("Comr.se Index Screen: ".$e->getMessage());
    }
  }


  /**
  * Sync brand data
  * @access public
  */
  public function postAction()
  {
    try 
    {
      $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();

      if (empty($currencyCode))
        $this->_currency_code = $currencyCode;

      //------------------------------------------------
      // prevent surfacing of errors
      //------------------------------------------------
      error_reporting(0);
      ini_set('display_errors', 0);

      // update org data information
      $org_id = trim($_POST['org_id']);
      $api_token = trim($_POST['api_token']);


      //------------------------------------------------
      // if resetting orders sync
      //------------------------------------------------
      if (isset($_POST['resync_org']) && $_POST['resync_org'] == "true")
      {
        $data = array('last_order_synced'=>0, 'synced'=>0, 'last_orders_synced' => '', 'last_customers_synced' => '', 'last_products_synced' => '');
        $orgModel = Mage::getModel('comrsesync/comrseconnect')->load(Comrse_ComrseSync_Model_Config::COMRSE_RECORD_ROW_ID)->addData($data);
        $orgModel->save();
      }

      //------------------------------------------------
      // set Org Data collected in form fields
      //------------------------------------------------
      $orgData = array('org' => $org_id, 'token' => $api_token);
      $model = Mage::getModel('comrsesync/comrseconnect')->load(Comrse_ComrseSync_Model_Config::COMRSE_RECORD_ROW_ID)->addData($orgData);
      $model->save();
      $orgData = Mage::getModel('comrsesync/comrseconnect')->load(Comrse_ComrseSync_Model_Config::COMRSE_RECORD_ROW_ID);
      
      //------------------------------------------------
      // create API User
      //------------------------------------------------
      $createApiUser = Mage::helper('comrsesync/apiuser')->createApiUser($api_token);
      
      //------------------------------------------------
      // Sync Products
      //------------------------------------------------
      $productSync = Mage::helper('comrsesync/product')->syncProducts();

      //------------------------------------------------
      // Sync Customers
      //------------------------------------------------
      $customerSync = Mage::helper('comrsesync/customer')->syncCustomers();

      //------------------------------------------------
      // Sync Historical Orders if not synced
      //------------------------------------------------
      if ($orgData->getSynced() == "0")
        $orderSync = Mage::helper('comrsesync/order')->syncOrders();

    }
    catch (Exception $e) 
    {
      Mage::log("Commerce Sync: {$e->getMessage()}");
      @mail("sync@comr.se", "Sync Error: $org_id", $e->getMessage());
    }
  }


  /*
  * Validate Data
  */
  private function validateData($org_data) {
    $validate = Mage::helper('comrsesync')->comrseRequest("GET", Comrse_ComrseSync_Model_Config::API_PATH . "verify/org/products?sendEmail=true&email=ops@comr.se", $org_data);
    return true;
  }
}