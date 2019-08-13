<?php

class Comrse_ComrseSync_Helper_Product extends Mage_Core_Helper_Abstract {

  private $productModel;
  private $linksModel;
  private $mediaPath;
  private $basePath;
  private $normalizedProducts = array();
  private $syncedProductIds = array();
  private $orgId;
  private $productTypes = array("configurable", "simple", "downloadable"); // do not modify w/o modifying db schema
  private $categorizedProductIds = array();

  /**
  * Init
  * - Define the Product Model
  * - Define Downloadble Product Link Model
  * - Define Base Media Path
  * - Define Base Web Path
  */
  function __construct()
  {
    $this->productModel = Mage::getModel('catalog/product');
    $this->linksModel =  Mage::getModel('downloadable/link');
    $this->mediaPath = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product';
    $this->basePath = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
  }

  /**
  * Map Product Categories
  * recursively maps product categories and sub categories
  * @param object $categories
  * @return array $subCategories
  */
  private function mapCategories($categories) {
    try
    {
      $orgData = Mage::getModel('comrsesync/comrseconnect')->load(Comrse_ComrseSync_Model_Config::COMRSE_RECORD_ROW_ID);

      $categorizedProductIds = json_decode(json_encode($this->categorizedProductIds));

      if ($categories && !empty($categories))
      {
        $subCategories = array();
        foreach ($categories as $category)
        {

          $_cat = Mage::getModel('catalog/category')->load($category->getId());

          $comrseCategory = Mage::getModel('comrsesync/Comrse_ProductCategory');
          $comrseCategory
          ->setExternalId($_cat->getId())
          ->setName($_cat->getName())
          ->setDescription($_cat->getDescription())
          ->setUrl($_cat->getUrlPath())
          ->setUrlKey($_cat->getUrlKey())
          ->setOrganizationId($orgData->getOrg())
          ->setActiveStartDate(date("Y-m-d\TH:i:sO", strtotime($_cat->getCreatedAt())))
          ->setActiveEndDate(date("Y-m-d\TH:i:sO", strtotime("+ 10 years", time())));

          if (isset($categorizedProductIds->{$_cat->getId()}) && !empty($categorizedProductIds->{$_cat->getId()}))
          {
            foreach ($categorizedProductIds->{$_cat->getId()} as $categorizedProductId)
            {
              $comrseCategory->addProduct($categorizedProductId);
            }
          }

          if ((int)$_cat->getChildrenCount() > 0)
          {
            $childCategories = $_cat->getChildrenCategories();
            $subCats = $this->mapCategories($childCategories);
            $comrseCategory->setSubcategories($subCats);
          }
          $subCategories[] = $comrseCategory->toArray();
        }
        return $subCategories;
      }
    }
    catch (Exception $e){
      Mage::log('Category Sync Error: '.$e->getMessage());
    }
  }


  /**
  * Normalize Product Data
  * Maps mixed Magento Product data to Comrse Product Object
  * @access public
  * @return Object normalizedProduct
  * @param Object product
  * @todo Re-implement Last Synced functionality
  */
  public function normalizeProductData($product) {
    error_reporting(0);
    try
    {
      //------------------------------------------------------------
      // If Simple Product Look For Parent and Use Parent Instead
      //------------------------------------------------------------ 
      if ($product->getTypeId() == 'simple')
      {
        $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
        if ($parentIds && !empty($parentIds))
        {
          $product =  Mage::getModel('catalog/product')->load($parentIds[0]);
        }
      }

      if ($pid = $product->getId()) 
      {
        if (!in_array($pid, $this->syncedProductIds)) 
        {
          $_item = $this->productModel->load($pid); // load extended product data
          $productName = $_item->getName();
          $productOptions = array();
          $attrCodes = array();
          $productVisibility = $_item->getVisibility();
          $productType = $_item->getTypeID();
          $productWeight = ($_item->getWeight() > 0) ? $_item->getWeight() : 0.5;

          @$categories = $_item->getCategoryCollection();


          if (!empty($categories))
          {
            foreach ($categories as $category) 
            {
              $categoryId = $category->getEntityId();

              if (isset($this->categorizedProductIds[$categoryId]))
              {
                if (!in_array($_item->getId(), $this->categorizedProductIds[$categoryId]))
                {
                  $this->categorizedProductIds[$categoryId][] = $_item->getId();
                }
              }
              else
                $this->categorizedProductIds[$categoryId][] = $_item->getId();
            }
          }

          //------------------------------------------------------------
          // Handle Configurable Product Options
          //------------------------------------------------------------
          if ($productType == "configurable")
          {
             $productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
      
            foreach ($productAttributeOptions as $productAttribute) 
            {
              $attrCodes[$productAttribute['attribute_id']] = $productAttribute['attribute_code'];

              $attribute = Mage::getModel('eav/entity_attribute')->load($productAttribute['attribute_code'], 'attribute_code');

              $option_col = Mage::getResourceModel( 'eav/entity_attribute_option_collection')
              ->setAttributeFilter($attribute->getId())
              ->setStoreFilter()
              ->setPositionOrder('ASC');
            }
           
            // loop product attributes
            $optionIterator = 1;

            foreach ($productAttributeOptions as $option)
            {
              if (is_null($option) || empty($option) || !isset($option['label']) || is_null($option['label']))
                continue;

              $attribute = Mage::getModel('eav/entity_attribute')->load($option['attribute_code'], 'attribute_code');
              $allowedValues = array();
              
              // loop product attribute values
              $optionValueIterator = 1;
              foreach ($option['values'] as $value)
              {
                $adjustmentAmount = (is_numeric($value['pricing_value'])) ? $value['pricing_value'] : 0.00; // remove def
                // build Comr.se allowed value
                $allowedValues[] = Mage::getModel('comrsesync/Comrse_AllowedValue')
                ->setExternalId($value['value_index'])
                ->setAttributeValue($value['label'])
                ->setPriceAdjustment(Mage::getModel('comrsesync/Comrse_Price')->setAmount($adjustmentAmount)->toArray())
                ->setProductOptionId("1")
                ->setDisplayOrder($optionValueIterator)
                ->toArray();
                $optionValueIterator++;
              }

              // build Comr.se product option
              $productOptions[] = Mage::getModel('comrsesync/Comrse_ProductOption')
              ->setAttributeName($option['label'])
              ->setLabel($option['store_label'])
              ->setExternalId($option['attribute_id'])
              ->setRequired("true")
              ->setProductOptionType(strtoupper($attribute->getFrontendInput()))
              ->setAllowedValues($allowedValues)
              ->setDisplayOrder($optionIterator)
              ->toArray();

              $optionIterator++;
              $allowedValues = null;
            }
          }

          //------------------------------------------------------------
          // Handle Downloadable Product Options (Links)
          //------------------------------------------------------------
          elseif($productType == "downloadable")
          {
            $allowedValues = array();
            @$links = $this->linksModel->getCollection()->addProductToFilter($pid)->addTitleToResult()->addPriceToResult();

            $optionValueIterator = 1;
            $productOptions = array();
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

            $productOptions[] = Mage::getModel('comrsesync/Comrse_ProductOption')
            ->setAttributeName("Link")
            ->setLabel("Link")
            ->setExternalId("99999")
            ->setRequired("true")
            ->setProductOptionType("Select")
            ->setAllowedValues($allowedValues)
            ->setDisplayOrder(1)
            ->toArray();

            $allowedValues = null;
          }
          else
          {
            $productOptions = null;
          }


          //------------------------------------------------------------
          // Handle Product Media
          //------------------------------------------------------------
          $media = $_item->getData('media_gallery');
          $images = $media['images'];
          $_images = array();
          $usedImages = array();
          $hasThumb = false;

          if ($_item->hasThumbnail())
          {
            $thumbFile = $_item->getThumbnail();
            $hasThumb = true;
          }

          $primaryImageSet = false;
          if (is_array($images))
          {
            foreach ($images as $image)
            {
              if ($image['file']) 
              {
                $altText = "";
                if ($hasThumb && $image['file'] == $thumbFile && !$primaryImageSet) 
                {
                  $altText = "primary";
                  $primaryImageSet = true;
                }
                elseif ($image['position_default'] == 1 && !$primaryImageSet) 
                {
                  $altText = "primary";
                  $primaryImageSet = true;
                }
                $usedImages[] = $image['file'];

                $_images[] = Mage::getModel('comrsesync/Comrse_Image')
                ->setId(null)
                ->setTitle($image['value_id'])
                ->setUrl($this->mediaPath . $image['file'])
                ->setAltText($altText)
                ->setOrgId($this->orgId)
                ->toArray();
              }
            }
          }



          //------------------------------------------------------------
          // Handle Configurable Product Children
          //------------------------------------------------------------
          if ($productType == "configurable")
          {
            $children = array();
            $conf = Mage::getModel('catalog/product_type_configurable')->setProduct($product);
            $simpleCollection = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
            $attributeIterator = 0;

            $simpleProductAttributes = array();
            $skuMapping = array();
            foreach ($simpleCollection as $simpleProduct)
            {
              $simpleProductData = $simpleProduct->getData();
              $skuMapping[$simpleProduct->getId()][$attributeIterator]["sku"] = $simpleProduct->getSku(); // map child ID's to attribute ID's for writeback inventory lookup
              $skuMapping[$simpleProduct->getId()][$attributeIterator]["upc"] = null; // map child ID's to attribute ID's for writeback inventory lookup


              foreach ($attrCodes as $key => $val) 
              {
                if (isset($simpleProductData[$val]))
                  $simpleProductAttributes[$simpleProduct->getId()][$key] = $simpleProductData[$val]; // map child ID's to attribute ID's for writeback inventory lookup
              }

              $childProducts[] = $simpleProduct->getId();
              if ($simpleProduct->getWeight() > $productWeight)
                $productWeight = $simpleProduct->getWeight();

              $qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($simpleProduct)->getQty();
              $sProductData = $simpleProduct->getData();
              $sProduct = $this->productModel->load($simpleProduct->getId());
              $sMedia = $sProduct->getData('media_gallery');

              if (empty($usedImages) && $sProduct->hasThumbnail())
              {
                $thumbFile = $sProduct->getThumbnail();
                $hasThumb = true;
              }

              $sImages = $sMedia['images'];

              if (is_array($sImages))
              {
                foreach ($sImages as $sImage) 
                {
                  if ($sImage['file'] && !in_array($sImage['file'], $usedImages)) 
                  {
                    $altText = "";
                    if ($hasThumb && !$primaryImageSet)
                    {
                      if ($sImage['file'] == $thumbFile)
                      {
                        $altText = "primary";
                        $primaryImageSet = true;
                      }
                    }
                    elseif ($sImage['position_default'] == 1 && !$primaryImageSet) 
                    {
                      $altText = "primary";
                      $primaryImageSet = true;
                    }

                    $_images[] = Mage::getModel('comrsesync/Comrse_Image')
                    ->setId(null)
                    ->setTitle($sImage['value_id'])
                    ->setUrl($this->mediaPath . $sImage['file'])
                    ->setAltText($altText)
                    ->setOrgId($this->orgId)
                    ->toArray();
                  }
                }
              }
              foreach ($attrCodes as $key => $val) 
              {
                $qtyArray[$attributeIterator]["qty"] = $qty;
                $qtyArray[$attributeIterator][$key] = $sProductData[$val];
              }
              
              $attributeIterator++;
              $this->syncedProductIds[] = $simpleProduct->getId();
              $simpleProduct = $sProductData = $sProduct = $sProductMedia = $sProductImages = null; // memclear
            }
          }

          //------------------------------------------------------------
          // Handle Simple/Downloadable Product Quantities
          //------------------------------------------------------------
          elseif ($productType == "simple" || $productType == "downloadable")
          {
            $qtyStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_item)->getQty();
            $qtyArray[0]["qty"] = $qtyStock;
            $simpleProductAttributes = array();
          }

          //------------------------------------------------------------
          // Define Product Attributes
          //------------------------------------------------------------
          $productAttributes = array(
            array("id" => null, "attribute_name" => "inventory", "attribute_value" => json_encode($qtyArray)),
            array("id" => null, "attribute_name" => "url", "attribute_value" => $product->getProductUrl()),
            array("id" => null, "attribute_name" => "mapping", "attribute_value" => json_encode($simpleProductAttributes)),
            array("id" => null, "attribute_name" => "sku_mapping", "attribute_value" => json_encode($skuMapping))
          );
          
          //------------------------------------------------------------
          // if sale price does not exist use base price
          //------------------------------------------------------------
          $salePrice = ($_item->getSpecialPrice() > 0 ? $_item->getSpecialPrice() : $_item->getPrice());

          //------------------------------------------------------------
          // extra check to make sure sale price is NOT 0
          //------------------------------------------------------------
          if ($salePrice == 0 || is_null($salePrice))
            $salePrice = $_item->getPrice();

          //------------------------------------------------------------
          // if no images use placeholder
          //------------------------------------------------------------
          if (empty($_images))
            $_images[0] = array("id" => null, "title" => "No Image", "url" => Comrse_ComrseSync_Model_Config::CDN_NO_IMG_URL, "altText" => "No Image"); // @TODO

          //------------------------------------------------------------
          // if short description is longer than description use short description
          //------------------------------------------------------------
          $description = (strlen($_item->getShortDescription()) > strlen($_item->getDescription())) ? $_item->getShortDescription() : $_item->getDescription();

          //------------------------------------------------------------
          // Handle Product Status
          //------------------------------------------------------------
          $active = ($_item->getStatus() == 1 ? "true" : "false");
          $activeStartDate = ($active == "true" ? date("Y-m-d\TH:i:sO", strtotime("- 1 day", time())) : date("Y-m-d\TH:i:sO", strtotime("+ 10 years", time())));
          $activeEndDate = ($active == "true" ? date("Y-m-d\TH:i:sO",strtotime("+ 10 years", time())) : date("Y-m-d\TH:i:sO",strtotime("- 1 day", time())));

          //------------------------------------------------------------
          // Ensure Primary Image is Set
          //------------------------------------------------------------
          $primaryImage = $_images[0];
          foreach ($_images as $image)
          {
            if ($image['alt_text'] == "primary") 
            {
              $primaryImage = $image;
              break;
            }
          }

          //------------------------------------------------------------
          // Create Comr.se Product Object
          //------------------------------------------------------------
          if (!is_null($pid) && $productVisibility != 1)
          {
            return Mage::getModel('comrsesync/Comrse_Product')
            ->setId(null)
            ->setExternalId((string)$pid)
            ->setName($productName)
            ->setLongDescription(trim(str_replace(array("\r\n", "\r", "<br />", "<br>"), "", $description)))
            ->setDimension(Mage::getModel('comrsesync/Comrse_Dimension')->toArray())
            ->setWeight(
              Mage::getModel('comrsesync/Comrse_Weight')
              ->setWeight($productWeight)
              ->toArray()
            )
            ->setRetailPrice(
              Mage::getModel('comrsesync/Comrse_Price')
              ->setAmount($_item->getPrice())
              ->toArray()
            )
            ->setSalePrice(
              Mage::getModel('comrsesync/Comrse_Price')
              ->setAmount($salePrice)
              ->toArray()
            )
            ->setPrimaryMedia($primaryImage)
            ->setActive($active)
            ->setActiveStartDate($activeStartDate)
            ->setActiveEndDate($activeEndDate)
            ->setManufacturer("")
            ->setDefaultCategoryId(null)
            ->setProductAttributes($productAttributes)
            ->setProductOptions($productOptions)
            ->setMedia($_images)
            ->toArray();
          }
          $this->syncedProductIds[] = $pid;
        }
      }
    }
    catch (Exception $e){
      Mage::log('Product Sync Error: '.$e->getMessage());
    }
  }


  /**
  * Sync Products
  * @access public
  * @todo properly lookup multi-store
  */
	public function syncProducts() {
		try
    {
      //------------------------------------------------------------
      // Retreive and Handle Org Data
      //------------------------------------------------------------
      $orgData = Mage::getModel('comrsesync/comrseconnect')->load(Comrse_ComrseSync_Model_Config::COMRSE_RECORD_ROW_ID);
      $orgId = $orgData->getOrg();
      $apiToken = $orgData->getToken();

      $stores = Mage::app()->getStores(); // get all stores

      // handle single store installation
      if (!is_array($stores)) 
        $stores = array($stores);

      //------------------------------------------------------------
      // Loop Stores
      //------------------------------------------------------------
      $disabled = $multiStore = false;
      $storeId = 0;
      if (is_array($stores)) 
      {
        foreach ($stores as $store) 
        {
          
          $storeId = $store->getId();
          
          $storeDisabled = Mage::getStoreConfig('advanced/modules_disable_output/Comrse_ComrseSync', $storeId); // check if plugin is disabled on store
          $multiStore = true;

          if (!$storeDisabled) // limit sync to enabled stores
          {
            foreach ($this->productTypes as $productType)
            {

              switch ($productType)
              {
                case "downloadable" : $pidColumn = "dl_pid"; break; 
                case "configurable" : $pidColumn = "config_pid"; break; 
                case "simple" : $pidColumn = "simple_pid"; break;
                default: $pidColumn = "simple_pid"; break;
              }

              //------------------------------------------------------------
              // retreive last product synced data for product type
              //------------------------------------------------------------
              $orgData = Mage::getModel('comrsesync/comrseconnect')->load(Comrse_ComrseSync_Model_Config::COMRSE_RECORD_ROW_ID);
              $lastProductsSynced = json_decode($orgData->getLastProductsSynced(), true);
              $lastProductOfTypeSynced = 0;

              if (isset($lastProductsSynced[$storeId][$pidColumn]))
                $lastProductOfTypeSynced = $lastProductsSynced[$storeId][$pidColumn];

              if ($multi_store)
                $productCount = $this->productModel->getCollection()->addStoreFilter($storeId)->addAttributeToFilter('type_id', $productType)->addAttributeToFilter('entity_id', array('gt' => $lastProductOfTypeSynced))->count(); // configurable products collection count
              else
                $productCount = $this->productModel->getCollection()->addAttributeToFilter('type_id', $productType)->addAttributeToFilter('entity_id', array('gt' => $lastProductOfTypeSynced))->count(); // configurable products collection count

           
              $batchMath = ceil($productCount / Comrse_ComrseSync_Model_Config::PRODUCT_SYNC_BATCH_SIZE);


              // iterate product batch
              for ($i = 1; $i <= $batchMath; $i++)
              {
                if ($multiStore)
                  $mageProducts = $this->productModel->getCollection()->addStoreFilter($storeId)->addAttributeToFilter('type_id', $productType)->addAttributeToFilter('entity_id', array('gt' => $lastProductOfTypeSynced))->setPageSize(Comrse_ComrseSync_Model_Config::PRODUCT_SYNC_BATCH_SIZE)->setCurPage($i); // configurable products collection
                else
                  $mageProducts = $this->productModel->getCollection()->addAttributeToFilter('type_id', $productType)->addAttributeToFilter('entity_id', array('gt' => $lastProductOfTypeSynced))->setPageSize(Comrse_ComrseSync_Model_Config::PRODUCT_SYNC_BATCH_SIZE)->setCurPage($i);

                $productPayload = array();


                //------------------------------------------------------------
                // Loop Products
                //------------------------------------------------------------
                foreach ($mageProducts as $mageProduct) 
                {
                  $normalizedProduct = self::normalizeProductData($mageProduct, $productType);
                  if (!empty($normalizedProduct))
                    $productPayload[] = $normalizedProduct;
                }

                //------------------------------------------------------------
                // Sync Products to Comr.se
                //------------------------------------------------------------
                if (!empty($productPayload))
                {
                  $preparedData = json_encode(array("product_details_list" => $productPayload));
                  $postProducts = json_decode(Mage::helper('comrsesync')->comrseRequest("POST", Comrse_ComrseSync_Model_Config::API_PATH . "organizations/" . $orgId . "/products", $orgData, $preparedData));

                  if (isset($postProducts->message)){
                    Mage::log("PRODUCT SYNC ERROR: " . json_encode($postProducts));
                    #return false;
                  }
                }
             
                //------------------------------------------------------------
                // Record Last Synced Product ID
                //------------------------------------------------------------
                $lastProductsSynced[$storeId][$pidColumn] = @$productPayload[max(array_keys($productPayload))]["external_id"];
                $saveLastSynced = @$orgData->setLastProductsSynced(json_encode($lastProductsSynced))->save();
              }
            }
          }
        }

        //------------------------------------------------------------
        // Sync Categories to Comr.se
        //------------------------------------------------------------
        $categories = Mage::getModel('catalog/category')->getCollection()
        ->addAttributeToSelect('*')//or you can just add some attributes
        ->addAttributeToFilter('level', 2)//2 is actually the first level
        ->addAttributeToFilter('is_active', 1);//if you want only active categories
        $mappedCategories = $this->mapCategories($categories);

        if (!is_array($mappedCategories))
          $mappedCategories = array($mappedCategories);

          
        if (is_array($mappedCategories) && !empty($mappedCategories)) {
          foreach ($mappedCategories as $mappedCategory)
          {
            $postCategories = Mage::helper('comrsesync')->comrseRequest("POST", Comrse_ComrseSync_Model_Config::API_PATH . "organizations/" . $orgData->getOrg() . "/category", $orgData, json_encode($mappedCategory), null, null, 1);
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