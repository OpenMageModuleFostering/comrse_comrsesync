<?php

/**
* Comr.se Config
* - shared environment variables
* @author Z @ Comr.se
* @version 1.1.5
* @since 1.1.4.1
*/

class Comrse_ComrseSync_Model_Config
{
	const API_PATH = "http://omni.comr.se/1.0/";
	const CART_API_PATH = "http://cart-api.comr.se/index.php/";
  const STOREFRONT_API_PATH = "https://api.comr.se/index.php/endpoints/";
	const COMRSE_API_VERSION = "2015-02-01";
	const PRODUCT_SYNC_BATCH_SIZE = 25;
  const ORDER_SYNC_BATCH_SIZE = 25;
	const CUSTOMER_SYNC_BATCH_SIZE = 25;
	const COMRSE_RECORD_ROW_ID = 1;
	const DATE_FORMAT = "Y-m-d\TH:i:sO";
	const CDN_NO_IMG_URL = "https://comr.se/assets/img/404_img.png";
}