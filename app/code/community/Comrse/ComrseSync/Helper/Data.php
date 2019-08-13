<?php
class Comrse_ComrseSync_Helper_Data extends Mage_Core_Helper_Abstract {

	// comrse request
  public function comrseRequest($method, $targetUrl, $orgData, $postData = NULL, $contentType = NULL, $time = NULL, $debug = 0) {
    try
    {
      // if content type not passed
      if (is_null($contentType))
        $contentType = "application/json";
   
      // prepare header
      $headerArray = array(
        "Content-Type: " . $contentType,
        "X-Comrse-Token: " . $orgData->getToken(),
        "X-Comrse-Version: " . Comrse_ComrseSync_Model_Config::COMRSE_API_VERSION,
        "Connection: close"
      );

      $ch = curl_init($targetUrl);

      // if POST Data is present
      if (!is_null($postData))
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

      // method
      if ($method == "POST")
        curl_setopt($ch, CURLOPT_POST, true); 
      else
        curl_setopt($ch,CURLOPT_CUSTOMREQUEST, $method);

      curl_setopt($ch,CURLOPT_HTTPHEADER, $headerArray);
      curl_setopt($ch,CURLOPT_HEADER, $debug);
      curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, FALSE);

      $result = curl_exec($ch);

      return $result;
    }
    catch (Exception $e)
    {
      Mage::log("Comrse Service Request Error: {$e->getMessage()}");
      return false;
    }
  }


  // basic request
  public function basicRequest($path, $method = "GET") {
    try {
      $ch = curl_init();
      curl_setopt($ch,CURLOPT_URL, $path);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Connection: close"
      ));
      curl_setopt($ch,CURLOPT_CUSTOMREQUEST, $method);
      curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, FALSE);
      $result = curl_exec($ch);
      curl_close($ch);
      return $result;
    }
    catch (Exception $e) {
      Mage::log("Comrse Basic HTTP Request Error: ".$e->getMessage());
      return false;
    }
  }




}