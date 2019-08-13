<?php
class Comrse_ComrseSync_Helper_Product extends Mage_Core_Helper_Abstract {

	public function syncProducts() {

		try 
    {
      error_reporting(0);
      ini_set('display_errors',0);

      $currency_code = Mage::app()->getStore()->getCurrentCurrencyCode();
      if (empty($currency_code)) {
        $currency_code = "USD";
      }

      // synced products array
      $_synced_products = array();

      $max_config_pid = 0;
      $max_simple_pid = 0;
      $max_grouped_pid = 0;
      $max_dl_pid = 0;

      // org data
      $org_data = Mage::getModel('comrsesync/comrseconnect')->load(1);
      $org_id = $org_data->getOrg();
      $api_token = $org_data->getToken();


      $last_config_product_synced = 0;
      $last_simple_product_synced = 0;
      $last_grouped_product_synced = 0;
      $last_dl_product_synced = 0;


      $product_model = Mage::getModel('catalog/product'); // product collection model
      $links_model =  Mage::getModel('downloadable/link');
      $media_path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product';
      $base_path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
      $options = array();
      $attributes = "";
      $child_products = array();
      $attr_ignored = array("entity_id", "attribute_set_id", "type_id", "entity_type_id", "name", "description", "short_description", "sample_test", "sku", "old_id", "weight", "news_from_date", "news_to_date", "status", "url_key", "url_path", "visibility", "country_of_manufacture", "category_ids", "required_options", "has_options", "image_label", "small_image_label", "thumbnail_label", "created_at", "updated_at", "price", "group_price", "special_price", "special_from_date", "special_to_date", "tier_price", "msrp_enabled", "minimal_price", "msrp_display_actual_price_type", "msrp", "enable_googlecheckout", "tax_class_id", "meta_title", "meta_keyword", "meta_description", "image", "small_image", "thumbnail", "media_gallery", "gallery", "is_recurring", "recurring_profile", "custom_design", "custom_design_from", "custom_design_to", "custom_layout_update", "page_layout", "options_container", "gift_message_available");

      $_stores = Mage::app()->getStores(); // get all stores

      // handle single store installation
      if (!is_array($_stores)) 
      {
        $stores = array("stores" => false);
        $_stores = array($_stores);
      }

      // loop stores
      $disabled = $multi_store = false;
      $_store_id = 0;
      if (is_array($_stores)) 
      {
        foreach ($_stores as $_store) 
        {
          $_store_id = $_store->getId();
          
          $_disabled = Mage::getStoreConfig('advanced/modules_disable_output/Comrse_ComrseSync', $_store_id); // check if plugin is disabled on store
          $multi_store = true;

          // if store is enabled
          if (!$_disabled)
          {
            // HANDLE CONFIGURABLE PRODUCTS //
            try
            {
              if ($multi_store)
                $product_count = $product_model->getCollection()->addStoreFilter($_store_id)->addAttributeToFilter('type_id','configurable')->count(); // configurable products collection count
              else
                $product_count = $product_model->getCollection()->addAttributeToFilter('type_id','configurable')->count(); // configurable products collection count

              $math = ceil($product_count / 100);

              // iterate product batch
              for ($i = 1; $i <= $math; $i++)
              {
                $_products = array();

                if ($multi_store)
                  $products = $product_model->getCollection()->addStoreFilter($_store_id)->addAttributeToFilter('type_id','configurable')->setPageSize(100)->setCurPage($i); // configurable products collection
                else
                  $products = $product_model->getCollection()->addAttributeToFilter('type_id','configurable')->setPageSize(100)->setCurPage($i); // configurable products collection

                $product_data = Mage::getModel('comrsesync/comrseconnect')->load(1); // retreive last product synced data for configurable type products

                $last_products_synced = json_decode($product_data->getLastProductsSynced(), true);

                if (isset($last_products_synced[$_store_id]['config_pid']))
                  $last_config_product_synced = $last_products_synced[$_store_id]['config_pid'];

                // loop product collection
                foreach ($products as $product)
                {
                  #$product = Mage::getModel('catalog/product')->load(220);
                  $pid = $product->getId();
                  #$pid = 220;

                  if ($pid) 
                  {
                    if (!in_array($_synced_products, $pid)) 
                    {
                      $_item = $product_model->load($pid);
                     
                      $product_status = $_item->getStatus();
                      $product_visibility = $_item->getVisibility();
                      $attributes = $_item->getAttributes();
                      $_attributes = array();
                      $product_options = array();
                      $attr_codes = array();
                      $product_name = $_item->getName();

                      // sync option positions w/ defined ordering
                      $productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);

                      foreach ($productAttributeOptions as $productAttribute) 
                      {
                        $attr_codes[$productAttribute['attribute_id']] = $productAttribute['attribute_code'];

                        $attribute = Mage::getModel('eav/entity_attribute')->load( $productAttribute['attribute_code'], 'attribute_code');

                        $option_col = Mage::getResourceModel( 'eav/entity_attribute_option_collection')
                        ->setAttributeFilter($attribute->getId())
                        ->setStoreFilter()
                        ->setPositionOrder('ASC');
                      }

                      // loop through product attributes
                      $optionIterator = 1;
                      foreach ($productAttributeOptions as $option)
                      {
                        #print_r($option);
                        if (is_null($option) || empty($option) || !isset($option['label']) || is_null($option['label']))
                          continue;

                        $attribute = Mage::getModel('eav/entity_attribute')->load($option['attribute_code'], 'attribute_code');

                        $attr_type = $attribute->getFrontendInput();
                        $allowedValues = array();
                        $optionValueIterator = 1;
                        foreach ($option['values'] as $value)
                        {
                          $adjustmentAmount = (is_numeric($value['pricing_value'])) ? $value['pricing_value'] : 0.00;
                          $allowedValues[] = Mage::getModel('comrsesync/Comrse_AllowedValue')
                          ->setExternalId($value['value_index'])
                          ->setAttributeValue($value['label'])
                          ->setPriceAdjustment(Mage::getModel('comrsesync/Comrse_Price')->setAmount($adjustmentAmount)->toArray())
                          ->setProductOptionId("1")
                          ->setDisplayOrder($optionValueIterator)
                          ->toArray();
                          
                          $optionValueIterator++;
                        }


                        $product_options[] = Mage::getModel('comrsesync/Comrse_ProductOption')
                        ->setAttributeName($option['label'])
                        ->setLabel($option['store_label'])
                        ->setExternalId($option['attribute_id'])
                        ->setRequired("true")
                        ->setProductOptionType(strtoupper($attr_type))
                        ->setAllowedValues(array("allowedValue" => $allowedValues))
                        ->setDisplayOrder($optionIterator)
                        ->toArray();

                        $optionIterator++;
                        $allowedValues = null;
                      }
                  

                      // get product media
                      $media = $_item->getData('media_gallery');
                      $images = $media['images'];
                      $_images = array();
                      $used_images = array();
                      $has_thumb = false;

                      // check for primary image
                      if ($_item->hasThumbnail())
                      {
                        $thumb_file = $_item->getThumbnail();
                        $has_thumb = true;
                      }

                      $primary_set = false;
                      if (is_array($images))
                      {
                        foreach ($images as $image)
                        {
                          if ($image['file']) {
                            $altText = "";
                            if ($has_thumb && $image['file'] == $thumb_file) 
                            {
                              $altText = "primary";
                              $primary_set = true;
                            }
                            elseif ($image['position_default'] == 1) 
                            {
                              $altText = "primary";
                              $primary_set = true;
                            }
                            $used_images[] = $image['file'];

                            $_images[] = Mage::getModel('comrsesync/Comrse_Image')
                            ->setId("")
                            ->setTitle($image['value_id'])
                            ->setUrl($media_path . $image['file'])
                            ->setAltText($altText)
                            ->setOrgId($org_id)
                            ->toArray();
                          }
                        }
                      }

                      $children = array();
                      // children of configurable products
                      $product_weight = 0.5;
                      $conf = Mage::getModel('catalog/product_type_configurable')->setProduct($product);
                      $simple_collection = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
                      $a_i = 0;
                      // loop children
                      $simple_product_attributes = array();
                      foreach ($simple_collection as $simple_product)
                      {
                        $simple_product_data = $simple_product->getData();

                        foreach ($attr_codes as $key => $val) 
                        {
                          if (isset($simple_product_data[$val]))
                            $simple_product_attributes[$simple_product->getId()][$key] = $simple_product_data[$val]; // map child ID's to attribute ID's for writeback inventory lookup
                        }

                        $child_products[] = $simple_product->getId();
                        if ($simple_product->getWeight() > $product_weight)
                          $product_weight = $simple_product->getWeight();

                        $qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($simple_product)->getQty();
                        $s_product_data = $simple_product->getData();
                        $s_product = $product_model->load($simple_product->getId());
                        $s_media = $s_product->getData('media_gallery');

                        if (empty($used_images) && $s_product->hasThumbnail())
                        {
                          $thumb_file = $s_product->getThumbnail();
                          $has_thumb = true;
                        }

                        $s_images = $s_media['images'];

                        if (is_array($s_images))
                        {
                          foreach ($s_images as $s_image) 
                          {
                            if ($s_image['file'] && !in_array($s_image['file'], $used_images)) 
                            {
                              $altText = "";
                              if ($has_thumb && !$primary_set)
                              {
                                if ($s_image['file'] == $thumb_file)
                                {
                                  $altText = "primary";
                                  $primary_set = true;
                                }
                              }
                              elseif ($s_image['position_default'] == 1 && !$primary_set) 
                              {
                                $altText = "primary";
                                $primary_set = true;
                              }

                              $_images[] = Mage::getModel('comrsesync/Comrse_Image')
                              ->setId("")
                              ->setTitle($s_image['value_id'])
                              ->setUrl($media_path . $s_image['file'])
                              ->setAltText($altText)
                              ->setOrgId($org_id)
                              ->toArray();

                            }
                          }
                        }
                        foreach ($attr_codes as $key => $val) 
                        {
                          $qty_array[$a_i]["qty"] = $qty;
                          $qty_array[$a_i][$key] = $s_product_data[$val];
                        }
                        
                        $a_i++;

                        // clear memory
                        $simple_product = $s_product_data = $s_product = $s_media = $s_images = null;
                      }

                      $_item_type = $_item->getTypeID();

                      $product_attributes = array(
                        "productAttribute" => array(
                          array("id" => "", "attributeName" => "inventory", "attributeValue" => json_encode($qty_array)),
                          array("id" => "", "attributeName" => "url", "attributeValue" => $product->getProductUrl()),
                          array("id" => "", "attributeName" => "mapping", "attributeValue" => json_encode($simple_product_attributes))
                        )
                      );
                    
                      // if sale price does not exist use base price
                      $sale_price = ($_item->getSpecialPrice() > 0 ? $_item->getSpecialPrice() : $_item->getPrice());

                      // extra check to make sure sale price is NOT 0
                      if ($sale_price == 0 || is_null($sale_price))
                        $sale_price = $_item->getPrice();

                      // if no images use placeholder
                      if (empty($_images))
                        $_images[0] = array("id" => "", "title" => "No Image", "url" => "https://comr.se/assets/img/404_img.png", "altText" => "No Image");

                      // setup description
                      $description = $_item->getDescription();
                      $short_description = $_item->getShortDescription();

                      if (strlen($short_description) > strlen($description))
                        $description = $short_description;

                      // is product active
                      $active = ($product_status == 1 ? "true" : "false");
                      $activeStartDate = ($active == "true" ? date("Y-m-d\TH:i:s.000O", strtotime("- 1 day", time())) : date("Y-m-d\TH:i:s.000O", strtotime("+ 10 years", time())));
                      $activeEndDate = ($active == "true" ? date("Y-m-d\TH:i:s.000O",strtotime("+ 10 years", time())) : date("Y-m-d\TH:i:s.000O",strtotime("- 1 day", time())));

                      // if product options exist (configurables must have product options!)
                      if (is_array($product_options) && !empty($product_options) && !is_null($pid) && $product_visibility != 1)
                      {

                        $_products[] = Mage::getModel('comrsesync/Comrse_Product')
                        ->setId("")
                        ->setExternalId((string)$pid)
                        ->setName($product_name)
                        ->setLongDescription(trim(str_replace(array("\r\n", "\r", "<br />", "<br>"), "", $description)))
                        ->setDimension(Mage::getModel('comrsesync/Comrse_Dimension')->toArray())
                        ->setWeight(
                          Mage::getModel('comrsesync/Comrse_Weight')
                          ->setWeight($product_weight)
                          ->toArray()
                        )
                        ->setRetailPrice(
                          Mage::getModel('comrsesync/Comrse_Price')
                          ->setAmount($_item->getPrice())
                          ->toArray()
                        )
                        ->setSalePrice(
                          Mage::getModel('comrsesync/Comrse_Price')
                          ->setAmount($sale_price)
                          ->toArray()
                        )
                        ->setPrimaryMedia($_images[0])
                        ->setActive($active)
                        ->setActiveStartDate($activeStartDate)
                        ->setActiveEndDate($activeEndDate)
                        ->setManufacturer("")
                        ->setDefaultCategoryId("")
                        ->setProductAttributes($product_attributes)
                        ->setProductOptions(array("productOption" => $product_options))
                        ->setMediaItems(array("media" => $_images))
                        ->toArray();

                      }
                      $_synced_products[] = $pid;
                    }
                  }
                  else {
                    $conf = Mage::getModel('catalog/product_type_configurable')->setProduct($product);
                    $simple_collection = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
                    if (is_array($simple_collection)) {
                      foreach ($simple_collection as $simple_product) {
                        $child_products[] = $simple_product->getId();
                      }
                    }
                  }
                } // END LOOP
                if (!empty($_products)) 
                {

                  // push configurable products
                  $data = json_encode(array("products" => $_products));
                  $postProducts = Mage::helper('comrsesync')->comrseRequest("POST", Comrse_ComrseSync_Model_Config::API_PATH . "organizations/" . $org_id . "/products", $org_data, $data);

                  // record max product id
                  $last_products_synced[$_store_id]['config_pid'] = $pid;
                  $new_save = $product_data->setLastProductsSynced(json_encode($last_products_synced))->save();

                }

                // clear memory
                $products = $_products = $data = $media = $images = $_images = $attributes = $result = $product_options = $allowedValues = $qty_array = $_child = $_children = $_product_options = $links = null;

              }
            }
            catch (Exception $e) 
            {
              Mage::log("Comrse configurable product sync failure: " . $e->getMessage());
            }

            
           // HANDLE GROUPED PRODUCTS //
            try 
            {
              $_all_links = array();

              if ($multi_store) 
                $product_count = $product_model->getCollection()->addStoreFilter($_store_id)->addAttributeToFilter('status',1)->addAttributeToFilter('type_id', 'grouped')->count(); // grouped products collection count
              else 
                $product_count = $product_model->getCollection()->addAttributeToFilter('status',1)->addAttributeToFilter('type_id', 'grouped')->count(); // grouped products collection count

              if ($product_count > 0) 
              {
                $core_resource = Mage::getSingleton('core/resource');
                $conn = $core_resource->getConnection('core_read');
              }
              $math = ceil($product_count / 100);

              // loop product loads
              for ($i = 1; $i <= $math; $i++) 
              {
                $_products = array();

                if ($multi_store) 
                  $products = $product_model->getCollection()->addStoreFilter($_store_id)->addAttributeToFilter('status',1)->addAttributeToFilter('type_id', 'grouped')->setPageSize(100)->setCurPage($i); // grouped products collection
                else 
                  $products = $product_model->getCollection()->addAttributeToFilter('status',1)->addAttributeToFilter('type_id', 'grouped')->setPageSize(100)->setCurPage($i); // grouped products collection

                // retreive last product synced data for grouped type products
                $product_data = Mage::getModel('comrsesync/comrseconnect')->load(1);
                $last_products_synced = json_decode($product_data->getLastProductsSynced(), true);
                if (isset($last_products_synced[$_store_id]['grouped_pid'])) 
                {
                  $last_grouped_product_synced = $last_products_synced[$_store_id]['grouped_pid'];
                }

                // loop grouped products
                foreach ($products as $product) 
                {
                  $pid = $product->getId();

                  if ($pid)
                  {
                    if (!in_array($_synced_products, $pid)) 
                    {
                      $_images = array();
                      $select = $conn->select()->from($core_resource->getTableName('catalog/product_relation'), array('child_id'))->where('parent_id = ?', $pid);
                      $_children = $conn->fetchCol($select);
                      if (is_array($_children)) 
                      {
                        $_product = $product->load($product->getId());
                        $media = $_product->getData('media_gallery');
                        $images = $media['images'];
                        $has_thumb = false;
                        $primary_set = false;
                        if ($_product->hasThumbnail()) 
                        {
                          $thumb_file = $_product->getThumbnail();
                          $has_thumb = true;
                        }
                        if (is_array($images)) 
                        {
                          foreach ($images as $image) 
                          {
                            if ($image['file'] && !in_array($image['file'], $used_images)) 
                            {
                              $altText = "";
                              if ($has_thumb && !$primary_set) 
                              {
                                if ($image['file'] == $thumb_file) 
                                {
                                  $altText = "primary";
                                  $primary_set = true;
                                }
                              }
                              elseif ($image['position_default'] == 1 && !$primary_set) 
                              {
                                $altText = "primary";
                                $primary_set = true;
                              }

                              $_images[] = Mage::getModel('comrsesync/Comrse_Image')
                              ->setId("")
                              ->setTitle($s_image['value_id'])
                              ->setUrl($media_path . $image['file'])
                              ->setAltText($altText)
                              ->setOrgId($org_id)
                              ->toArray();

                            }
                          }
                        }
                        // loop children
                        foreach ($_children as $_child)
                        {
                          // add child ID to used children array
                          $child_products[] = $_child;
                          // load the simple product
                          $_item = $product_model->load($_child);
                          $product_status = $_item->getStatus();
                          $product_visibility = $_item->getVisibility();
                          $_attributes = array();
                          $media = $_item->getData('media_gallery');
                          $images = $media['images'];
                          $c_images = array();
                          $altText = "";
                          if (is_array($images)) 
                          {
                            $c_i = 0;
                            foreach ($images as $image) 
                            {
                              if ($c_i == 0 && empty($_images)) 
                              {
                                $altText = "primary";
                              }
                              if ($image['file']) 
                              {
                                $c_images[] = array("id" => "", "title" => $image['value_id'], "url" => $media_path.$image['file'], "altText" => $altText, "orgId" => $org_id);
                              }
                              $c_i++;
                            }
                          }

                          if (is_array($c_images)) 
                          {
                            $_c_images = array_merge($c_images, $_images);
                          }

                          $qtyStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_item)->getQty();
                          $base_path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

                          $qty_array[0]["qty"] = $qtyStock;
                          $_item_type = $_item->getTypeID();
                          $product_attributes = array(
                            "productAttribute" => array(
                              array("id" => "", "attributeName" => "inventory", "attributeValue" => json_encode($qty_array)),
                              array("id" => "", "attributeName" => "url", "attributeValue" => $product->getProductUrl())
                              )
                            );

                          // if sale price not exists use base price
                          $sale_price = ($_item->getSpecialPrice() > 0 ? $_item->getSpecialPrice() : $_item->getPrice());

                          // extra check to make sure sale price is NOT 0
                          if ($sale_price == 0 || is_null($sale_price)) 
                          {
                            $sale_price = $_item->getPrice();
                          }

                          // setup description
                          $description = $_item->getDescription();
                          $short_description = $_item->getShortDescription();
                          if (strlen($short_description) > strlen($description))
                          {
                            $description = $short_description;
                          }

                          if (empty($_images))
                          {
                            $_images[0] = array("id" => "", "title" => "No Image", "url" => "https://comr.se/assets/img/404_img.png", "altText" => "No Image");
                          }

                          $allowedValues = array();
                          $product_options = array();
                          if ($_item_type == "downloadable") 
                          {
                            @$_links =  $links_model->getCollection()->addProductToFilter($_child)->addTitleToResult()->addPriceToResult();
                            $optionValueIterator = 1;
                            foreach ($_links as $_link) 
                            {

                              $allowedValues[] = Mage::getModel('comrsesync/Comrse_AllowedValue')
                              ->setExternalId($_link->getLinkId())
                              ->setAttributeValue($_link->getTitle())
                              ->setPriceAdjustment(Mage::getModel('comrsesync/Comrse_Price')->setAmount($_link->getPrice())->toArray())
                              ->setProductOptionId("1")
                              ->setDisplayOrder($optionValueIterator)
                              ->toArray();

                              $optionValueIterator++;
                            }

                            $product_options[] = Mage::getModel('comrsesync/Comrse_ProductOption')
                            ->setAttributeName("Link")
                            ->setLabel("Link")
                            ->setExternalId("99999")
                            ->setRequired("true")
                            ->setProductOptionType("Select")
                            ->setAllowedValues(array("allowedValue" => $allowedValues))
                            ->setDisplayOrder(1)
                            ->toArray();

                          }

                          $_product_options = (!empty($product_options)) ? array("productOption" => $product_options) : "";

                          // is product active
                          $active = ($product_status == 1 ? "true" : "false");
                          $activeStartDate = ($active == "true" ? date("Y-m-d\TH:i:s.000O", strtotime("- 1 day", time())) : date("Y-m-d\TH:i:s.000O", strtotime("+ 10 years", time())));
                          $activeEndDate = ($active == "true" ? date("Y-m-d\TH:i:s.000O",strtotime("+ 10 years", time())) : date("Y-m-d\TH:i:s.000O",strtotime("- 1 day", time())));

                          if (!is_null($_child) && $product_visibility != 1)
                          {

                            $_products[] = Mage::getModel('comrsesync/Comrse_Product')
                            ->setId("")
                            ->setExternalId((string)$_child)
                            ->setName($_item->getName())
                            ->setLongDescription(trim(str_replace(array("\r\n", "\r", "<br />", "<br>"), "", $description)))
                            ->setDimension(Mage::getModel('comrsesync/Comrse_Dimension')->toArray())
                            ->setWeight(
                              Mage::getModel('comrsesync/Comrse_Weight')
                              ->setWeight(($_item->getWeight() > 0) ? $_item->getWeight() : 0.5)
                              ->toArray()
                            )
                            ->setRetailPrice(
                              Mage::getModel('comrsesync/Comrse_Price')
                              ->setAmount($_item->getPrice())
                              ->toArray()
                            )
                            ->setSalePrice(
                              Mage::getModel('comrsesync/Comrse_Price')
                              ->setAmount($sale_price)
                              ->toArray()
                            )
                            ->setPrimaryMedia(@$_c_images[0])
                            ->setActive($active)
                            ->setActiveStartDate($activeStartDate)
                            ->setActiveEndDate($activeEndDate)
                            ->setManufacturer("")
                            ->setDefaultCategoryId("")
                            ->setProductAttributes($product_attributes)
                            ->setProductOptions(array("productOption" => $product_options))
                            ->setMediaItems(array("media" => $_c_images))
                            ->toArray();
                          }
                          $_product_options = null;
                        }
                      }
                      $_synced_products[] = $pid;
                    }
                  }
                  else {
                  // add child ids to used children array
                    $select = $conn->select()->from($core_resource->getTableName('catalog/product_relation'), array('child_id'))->where('parent_id = ?', $pid);
                    $_children = $conn->fetchCol($select);
                    if (is_array($_children)) 
                    {
                      foreach ($_children as $_child) 
                      {
                        $child_products[] = $_child;
                      }
                    }
                  }
                }

                if (!empty($_products)) 
                {
                  // push the configurable products to CPS
                  $data = json_encode(array("products" => $_products));
                  $postProducts = Mage::helper('comrsesync')->comrseRequest("POST", Comrse_ComrseSync_Model_Config::API_PATH . "organizations/" . $org_id . "/products", $org_data, $data);

                  // store max grouped product id
                  $last_products_synced[$_store_id]['grouped_pid'] = $pid;
                  $new_save = $product_data->setLastProductsSynced(json_encode($last_products_synced))->save();
                }
                // clear the memory
                $products = $_products = $data = $media = $images = $_images = $attributes = $result = $product_options = $allowedValues = $qty_array = $_child = $_children = $_product_options = $links = null;
              }
            }
            catch (Exception $e) {
              Mage::log("Comrse grouped product sync failure: " . $e->getMessage());
            }


            // HANDLE SIMPLE PRODUCTS //
            try 
            {
              if ($multi_store)
                $product_count = $product_model->getCollection()->addStoreFilter($_store_id)->addAttributeToFilter('status',1)->addAttributeToFilter('type_id', 'simple')->count(); // products collection count

              else
                $product_count = $product_model->getCollection()->addAttributeToFilter('status',1)->addAttributeToFilter('type_id', 'simple')->count(); // products collection count

              $math = ceil($product_count / 100);

              for ($i = 1; $i <= $math; $i++) 
              {
                $_products = array();
                
                // filter by store if multi store
                if ($multi_store)
                  $products = $product_model->getCollection()->addStoreFilter($_store_id)->addAttributeToFilter('status',1)->addAttributeToFilter('type_id', 'simple')->setPageSize(100)->setCurPage($i); // products collection

                else
                  $products = $product_model->getCollection()->addAttributeToFilter('status',1)->addAttributeToFilter('type_id', 'simple')->setPageSize(100)->setCurPage($i); // products collection

                // retreive last product synced data for grouped type products
                $product_data = Mage::getModel('comrsesync/comrseconnect')->load(1);
                $last_products_synced = json_decode($product_data->getLastProductsSynced(), true);

                if (isset($last_products_synced[$_store_id]['simple_pid']))
                  $last_simple_product_synced = $last_products_synced[$_store_id]['simple_pid'];

                // loop the products
                foreach ($products as $product) 
                {
                  $pid = $product->getId();
                  if ($pid) 
                  {
                    if (!in_array($pid, $child_products) && !in_array($pid, $_synced_products)) 
                    {

                      $_item = $product_model->load($pid);

                      $product_status = $_item->getStatus();
                      $product_visibility = $_item->getVisibility();
                      $_attributes = array();
                      $media = $_item->getData('media_gallery');
                      $images = $media['images'];
                      $_images = array();

                      foreach ($images as $image) 
                      {
                        if ($image['file'])
                          $_images[] = array("id" => "", "title" => $image['value_id'], "url" => $media_path.$image['file'], "altText" => $image['label'], "orgId" => $org_id);
                      }

                      $qtyStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_item)->getQty();
                      $base_path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

                      $qty_array[0]["qty"] = $qtyStock;
                      $_item_type = $_item->getTypeID();
                      $product_attributes = array(
                        "productAttribute" => array(
                          array("id" => "", "attributeName" => "inventory", "attributeValue" => json_encode($qty_array)),
                          array("id" => "", "attributeName" => "url", "attributeValue" => $product->getProductUrl())
                        )
                      );

                      // if sale price not exists use base price
                      $sale_price = ($_item->getSpecialPrice() > 0 ? $_item->getSpecialPrice() : $_item->getPrice());

                      // extra check to make sure sale price is NOT 0
                      if ($sale_price == 0 || is_null($sale_price))
                        $sale_price = $_item->getPrice();

                      // setup description
                      $description = $_item->getDescription();
                      $short_description = $_item->getShortDescription();

                      if (strlen($short_description) > strlen($description))
                        $description = $short_description;

                      if (empty($_images))
                        $_images[0] = array("id" => "", "title" => "No Image", "url" => "https://comr.se/assets/img/404_img.png", "altText" => "No Image");

                      // is product active
                      $active = ($product_status == 1 ? "true" : "false");
                      $activeStartDate = ($active == "true" ? date("Y-m-d\TH:i:s.000O", strtotime("- 1 day", time())) : date("Y-m-d\TH:i:s.000O", strtotime("+ 10 years", time())));
                      $activeEndDate = ($active == "true" ? date("Y-m-d\TH:i:s.000O",strtotime("+ 10 years", time())) : date("Y-m-d\TH:i:s.000O",strtotime("- 1 day", time())));

                      if (!is_null($pid) && $product_visibility != 1) 
                      {
                        $_products[] = Mage::getModel('comrsesync/Comrse_Product')
                        ->setId("")
                        ->setExternalId((string)$pid)
                        ->setName($_item->getName())
                        ->setLongDescription(trim(str_replace(array("\r\n", "\r", "<br />", "<br>"), "", $description)))
                        ->setDimension(Mage::getModel('comrsesync/Comrse_Dimension')->toArray())
                        ->setWeight(
                          Mage::getModel('comrsesync/Comrse_Weight')
                          ->setWeight(($_item->getWeight() > 0) ? $_item->getWeight() : 0.5)
                          ->toArray()
                        )
                        ->setRetailPrice(
                          Mage::getModel('comrsesync/Comrse_Price')
                          ->setAmount($_item->getPrice())
                          ->toArray()
                        )
                        ->setSalePrice(
                          Mage::getModel('comrsesync/Comrse_Price')
                          ->setAmount($sale_price)
                          ->toArray()
                        )
                        ->setPrimaryMedia($_images[0])
                        ->setActive($active)
                        ->setActiveStartDate($activeStartDate)
                        ->setActiveEndDate($activeEndDate)
                        ->setManufacturer("")
                        ->setDefaultCategoryId("")
                        ->setProductAttributes($product_attributes)
                        ->setProductOptions("")
                        ->setMediaItems(array("media" => $_images))
                        ->toArray();

                      }
                      $_synced_products[] = $pid;
                    }
                  }
                }
                if (!empty($_products)) {

                  // push the configurable products to CPS
                  $data = json_encode(array("products" => $_products));
                  $postProducts = Mage::helper('comrsesync')->comrseRequest("POST", Comrse_ComrseSync_Model_Config::API_PATH . "organizations/" . $org_id . "/products", $org_data, $data);

                  // store max simple product id
                  $last_products_synced[$_store_id]['simple_pid'] = $pid;
                  $new_save = $product_data->setLastProductsSynced(json_encode($last_products_synced))->save();
                }

                // clear the memory
                $products = $_products = $data = $media = $images = $_images = $attributes = $result = $product_options = $allowedValues = $qty_array = null;
              }
            }
            catch (Exception $e) {
              Mage::log("Comrse simple product sync failure: ".$e->getMessage());
            }


            try 
            {

              if ($multi_store)
                $products = $product_model->getCollection()->addStoreFilter($_store_id)->addAttributeToFilter('status',1)->addAttributeToFilter('type_id', 'downloadable'); // products collection
              else
                $products = $product_model->getCollection()->addAttributeToFilter('status',1)->addAttributeToFilter('type_id', 'downloadable'); // products collection


              $_products = array();

              // retreive last product synced data for grouped type products
              $product_data = Mage::getModel('comrsesync/comrseconnect')->load(1);
              $last_products_synced = json_decode($product_data->getLastProductsSynced(), true);

              if (isset($last_products_synced[$_store_id]['dl_pid']))
                $last_dl_product_synced = $last_products_synced[$_store_id]['dl_pid'];

              // loop products
              foreach ($products as $product) 
              {
                $pid = $product->getId();
                if ($pid) 
                {
                  if (!in_array($pid, $child_products) && !in_array($pid, $_synced_products)) 
                  {
                    $_item = $product_model->load($pid);
                    $product_status = $_item->getStatus();
                    $product_visibility = $_item->getVisibility();
                    $attributes = $_item->getAttributes();
                    $_attributes = array();
                    $media = $_item->getData('media_gallery');
                    $images = $media['images'];
                    $_images = array();
                    $has_thumb = false;
                    if ($_item->hasThumbnail()) 
                    {
                      $thumb_file = $_item->getThumbnail();
                      $has_thumb = true;
                    }

                    $primary_set = false;

                    // loop media
                    foreach ($images as $image) 
                    {
                      if ($image['file']) 
                      {
                        $altText = "";
                        if ($has_thumb)
                        {
                          if ($image['file'] == $thumb_file)
                          {
                            $altText = "primary";
                            $primary_set = true;
                          }
                        }
                        elseif ($image['position_default'] == 1)
                        {
                          $altText = "primary";
                          $primary_set = true;
                        }

                        $_images[] = Mage::getModel('comrsesync/Comrse_Image')
                        ->setId("")
                        ->setTitle($s_image['value_id'])
                        ->setUrl($media_path . $image['file'])
                        ->setAltText($altText)
                        ->setOrgId($org_id)
                        ->toArray();

                      }
                    }

                    $qtyStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_item)->getQty();
                    $base_path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

                    $allowedValues = array();
                    @$links = $links_model->getCollection()->addProductToFilter($pid)->addTitleToResult()->addPriceToResult();

                    $optionValueIterator = 1;
                    $product_options = array();
                    foreach ($links as $link) 
                    {
                      $allowedValues[] = Mage::getModel('comrsesync/Comrse_AllowedValue')
                      ->setExternalId($link->getLinkId())
                      ->setAttributeValue($link->getTitle())
                      ->setPriceAdjustment(Mage::getModel('comrsesync/Comrse_Price')->setAmount($link->getPrice())->toArray())
                      ->setProductOptionId("1")
                      ->setDisplayOrder($optionValueIterator)
                      ->toArray();

                      $optionValueIterator++;
                    }

                    $product_options[] = Mage::getModel('comrsesync/Comrse_ProductOption')
                    ->setAttributeName("Link")
                    ->setLabel("Link")
                    ->setExternalId("99999")
                    ->setRequired("true")
                    ->setProductOptionType("Select")
                    ->setAllowedValues(array("allowedValue" => $allowedValues))
                    ->setDisplayOrder(1)
                    ->toArray();

                    $allowedValues = null;

                    $qty_array[0]["qty"] = $qtyStock;
                    $_item_type = $_item->getTypeID();
                    $product_attributes = array(
                      "productAttribute" => array(
                        array("id" => "", "attributeName" => "inventory", "attributeValue" => json_encode($qty_array)),
                        array("id" => "", "attributeName" => "url", "attributeValue" => $product->getProductUrl())
                      )
                    );

                    // if sale price not exists use base price
                    $sale_price = ($_item->getSpecialPrice() > 0 ? $_item->getSpecialPrice() : $_item->getPrice());


                    // extra check to make sure sale price IS NOT 0
                    if ($sale_price == 0 || is_null($sale_price))
                      $sale_price = $_item->getPrice();


                    // setup description
                    $description = $_item->getDescription();
                    $short_description = $_item->getShortDescription();
                    if (strlen($short_description) > strlen($description))
                      $description = $short_description;

                    if (empty($_images))
                      $_images[0] = array("id" => "", "title" => "No Image", "url" => "https://comr.se/assets/img/404_img.png", "altText" => "No Image");

                    // is product active
                    $active = ($product_status == 1 ? "true" : "false");
                    $activeStartDate = ($active == "true" ? date("Y-m-d\TH:i:s.000O", strtotime("- 1 day", time())) : date("Y-m-d\TH:i:s.000O", strtotime("+ 10 years", time())));
                    $activeEndDate = ($active == "true" ? date("Y-m-d\TH:i:s.000O",strtotime("+ 10 years", time())) : date("Y-m-d\TH:i:s.000O",strtotime("- 1 day", time())));


                    if (!is_null($pid) && $product_visibility != 1)
                    {

                      $_products[] = Mage::getModel('comrsesync/Comrse_Product')
                      ->setId("")
                      ->setExternalId((string)$pid)
                      ->setName($_item->getName())
                      ->setLongDescription(trim(str_replace(array("\r\n", "\r", "<br />", "<br>"), "", $description)))
                      ->setDimension(Mage::getModel('comrsesync/Comrse_Dimension')->toArray())
                      ->setWeight(
                        Mage::getModel('comrsesync/Comrse_Weight')
                        ->setWeight(($_item->getWeight() > 0) ? $_item->getWeight() : 0.5)
                        ->toArray()
                      )
                      ->setRetailPrice(
                        Mage::getModel('comrsesync/Comrse_Price')
                        ->setAmount($_item->getPrice())
                        ->toArray()
                      )
                      ->setSalePrice(
                        Mage::getModel('comrsesync/Comrse_Price')
                        ->setAmount($sale_price)
                        ->toArray()
                      )
                      ->setPrimaryMedia($_images[0])
                      ->setActive($active)
                      ->setActiveStartDate($activeStartDate)
                      ->setActiveEndDate($activeEndDate)
                      ->setManufacturer("")
                      ->setDefaultCategoryId("")
                      ->setProductAttributes($product_attributes)
                      ->setProductOptions(array("productOption" => $product_options))
                      ->setMediaItems(array("media" => $_images))
                      ->toArray();

                    }
                    $_synced_products[] = $pid;
                  }
                }
              }

              if (!empty($_products)) 
              {
                // push the configurable products to CPS
                $data = json_encode(array("products" => $_products));

                $postProducts = Mage::helper('comrsesync')->comrseRequest("POST", Comrse_ComrseSync_Model_Config::API_PATH . "organizations/" . $org_id . "/products", $org_data, $data);
        
                // store max dl product id
                $last_products_synced[$_store_id]['dl_pid'] = $pid;
                $new_save = $product_data->setLastProductsSynced(json_encode($last_products_synced))->save();
              }

              // clear the memory
              $products = null;
              $_products = null;
              $data = null;
              $media = null;
              $images = null;
              $_images = null;
              $attributes = null;
              $result = null;
              $product_options = null;
              $allowedValues = null;
              $qty_array = null;
              $_stores = null;
              $_time = null;

            }
            catch (Exception $e) 
            {
              Mage::log("Comrse downloadable product sync failure: ".$e->getMessage());
            }
          }
        }
      }

    }
    catch (Exception $e) 
    {
      Mage::log('Product Sync Error: '.$e->getMessage());
    }
  

	}

}