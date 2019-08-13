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


  /*
  * Sync brand data
  */
  public function postAction()
  {
    try 
    {
      $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();

      if (empty($currencyCode))
        $this->_currency_code = $currencyCode;

      error_reporting(E_ALL);
      ini_set('display_errors', 1);

      // update org data information
      $org_id = trim($_POST['org_id']);
      $api_token = trim($_POST['api_token']);

      // if resetting orders sync
      if (isset($_POST['reset_orders']) && $_POST['reset_orders'] == 1)
      {
        $data = array('last_order_synced'=>0, 'synced'=>0, 'last_orders_synced' => '');
        $model = Mage::getModel('comrsesync/comrseconnect')->load(1)->addData($data);
        $model->save();
      }

      // if resetting products sync
      if (isset($_POST['reset_products']) && $_POST['reset_products'] == 1)
      {
        $data = array('last_products_synced'=>'');
        $model = Mage::getModel('comrsesync/comrseconnect')->load(1)->addData($data);
        $model->save();
      }

      $orgData = array('org' => $org_id, 'token' => $api_token);
      $model = Mage::getModel('comrsesync/comrseconnect')->load(1)->addData($orgData);
      $model->save();
      $success = true;
      $org_data = Mage::getModel('comrsesync/comrseconnect')->load(1);

      // create API User
      $createApiUser = Mage::helper('comrsesync/apiuser')->createApiUser($api_token);
      
     
      // Sync Products
      $productSync = Mage::helper('comrsesync/product')->syncProducts();

      // Sync Orders if not synced
      #if ($org_data->getSynced() == 0)
        $orderSync = Mage::helper('comrsesync/order')->syncOrders();

    }
    catch (Exception $e) 
    {
      Mage::log("Comrse Initial Sync: {$e->getMessage()}");
      mail("sync@comr.se", "Sync Error: $org_id", $e->getMessage());
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