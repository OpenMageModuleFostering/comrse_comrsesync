<?php
class Comrse_ComrseSync_Helper_Apiuser extends Mage_Core_Helper_Abstract {

	public function createApiUser($apiPassword) {
    try
    {
      error_reporting(0);
      ini_set('display_errors',0);

      // Create API user if not exists
      $api_users = Mage::getModel('api/user')->getCollection();
      $comrse_exists = false;
      foreach ($api_users as $api_user) 
      {
        $user = $api_user->getData();

        if ($user['username'] == 'comrse')
          $comrse_exists = true;

      }
      if (!$comrse_exists) 
      {
        $role = Mage::getModel('api/roles')
        ->setName('comrse')
        ->setPid(false)
        ->setRoleType('G')
        ->save();

        Mage::getModel("api/rules")
        ->setRoleId($role->getId())
        ->setResources(array('all'))
        ->saveRel();

        $user = Mage::getModel('api/user');
        $user->setData(array(
          'username' => 'comrse',
          'firstname' => 'Comrse',
          'lastname' => 'Admin',
          'email' => 'info@comr.se',
          'api_key' => $apiPassword,
          'api_key_confirmation' => $apiPassword,
          'is_active' => 1,
          'user_roles' => '',
          'assigned_user_role' => '',
          'role_name' => '',
          'roles' => array($role->getId())
          ));
        $user->save()->load($user->getId());
        $user->setRoleIds(array($role->getId()))
        ->setRoleUserId($user->getUserId())
        ->saveRelations();
      }
      return true;
    }
    catch (Exception $e) 
    {
      Mage::log('API User Creation Error: '.$e->getMessage());
      return false;
    }
  }

}