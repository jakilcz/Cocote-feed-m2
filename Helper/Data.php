<?php

namespace Cocote\Feed\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\App\ObjectManager;
use \Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
use \Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    public $mapping=[];

    protected $productFactory;
    protected $output;
    protected $scopeConfig;
    protected $productCollectionFactory;
    protected $productVisibility;
    protected $galleryReadHandler;
    protected $storeManager;
    protected $priceHelper;
    protected $configInterface;
    protected $stockHelper;
    protected $cacheTypeList;
    protected $resource;
    protected $timeZone;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configInterface,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Framework\App\State $appState,
        \Magento\CatalogInventory\Helper\Stock $stockHelper,
        GalleryReadHandler $galleryReadHandler,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        Context $context
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configInterface = $configInterface;
        $this->productVisibility = $productVisibility;
        $this->productCollectionFactory=$productCollectionFactory;
        $this->galleryReadHandler = $galleryReadHandler;
        $this->storeManager=$storeManager;
        $this->priceHelper=$priceHelper;
        $this->stockHelper=$stockHelper;
        $this->timeZone=$timezone;
        $this->cacheTypeList = $cacheTypeList;
        $this->resource = $resource;

        parent::__construct($context);
    }

    public function getFileLink()
    {
        $path=$this->getConfigValue('cocote/generate/path');
        $baseUrl=$this->storeManager->getStore()->getBaseUrl();
        return $baseUrl.'pub/'.$path.'/'.$this->getFileName();
    }

    public function getFilePath()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');

        $path=$this->getConfigValue('cocote/generate/path');

        $dirPath=$directory->getPath('pub').'/'.$path;

        if (!file_exists($dirPath)) {
            mkdir($dirPath, 0777, true);
        }

        return $dirPath.'/'.$this->getFileName();
    }

    public function getFileName()
    {
        $fileName=$this->getConfigValue('cocote/generate/filename');

        if (!$fileName) {
            $fileName=$this->generateRandomString().'.xml';
            $this->configInterface
                ->saveConfig('cocote/generate/filename', $fileName, 'default', 0);
            $this->cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
            $this->cacheTypeList->cleanType(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER);
        }
        return $fileName;
    }

    public function generateRandomString($length = 8)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function getProductCollection()
    {
        $storeCode=$this->getConfigValue('cocote/general/store');
        if (!$storeCode) {
            $storeCode='default';
        }

        $this->storeManager->setCurrentStore($storeCode);

        $collection = $this->productCollectionFactory->create();

        $collection->setVisibility($this->productVisibility->getVisibleInSiteIds());

        $collection->addAttributeToSelect('price');
        $collection->addAttributeToSelect('image');
        $collection->addAttributeToSelect('meta_keyword');
        $collection->addAttributeToSelect('cocote_labels');
        $collection->addAttributeToSelect('cocote_categories');
        $collection->addAttributeToSelect('cocote_tags');
        $collection->addAttributeToSelect('cocote_targets');
        $collection->addAttributeToSelect('cocote_producer');
        $collection->addAttributeToSelect('cocote_state');
        $collection->addAttributeToSelect('cocote_salestypes');
        $collection->addAttributeToSelect('cocote_payment_online');
        $collection->addAttributeToSelect('cocote_allowed_distance');
        $collection->addAttributeToSelect('cocote_types');
        $collection->addFieldToFilter('status', ['eq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED]);

        $inStockOnly=$this->getConfigValue('cocote/generate/in_stock_only');
        if ($inStockOnly) {
            $this->stockHelper->addInStockFilterToCollection($collection);
        }

        foreach ($this->mapping as $attribute) {
            $collection->addAttributeToSelect($attribute);
        }
        return $collection;
    }

    public function generateFeed()
    {
        $validate=[];

        $filePath=$this->getFilePath();
        $store = $this->storeManager->getStore();

        $mapName=$this->getConfigValue('cocote/general/map_name');
        $mapMpn=$this->getConfigValue('cocote/general/map_mpn');
        $mapGtin=$this->getConfigValue('cocote/general/map_gtin');
        $mapDescription=$this->getConfigValue('cocote/general/map_description');
        $mapManufacturer=$this->getConfigValue('cocote/general/map_manufacturer');

        if ($mapName) {
            $this->mapping['title']=$mapName;
        }
        if ($mapMpn) {
            $this->mapping['mpn']=$mapMpn;
        }
        if ($mapGtin) {
            $this->mapping['gtin']=$mapGtin;
        }
        if ($mapDescription) {
            $this->mapping['description']=$mapDescription;
        }
        if ($mapManufacturer) {
            $this->mapping['brand']=$mapManufacturer;
        }

        $productCollection = $this->getProductCollection();

        $domtree = new \DOMDocument('1.0', 'UTF-8');

        $xmlRoot = $domtree->createElement("shop");
        $xmlRoot = $domtree->appendChild($xmlRoot);

        $generated = $domtree->createElement('generated',$this->timeZone->date()->format('Y-m-d H:i:s'));
        $generated->setAttribute('cms', 'magento');
        $xmlRoot->appendChild($generated);

        $sponsorship=$domtree->createElement('sponsorship');
        $sponsorship->setAttribute('godfather_advantage', $this->getConfigValue('cocote/general/godfather_advantage'));
        $sponsorship->setAttribute('godson_advantage', $this->getConfigValue('cocote/general/godson_advantage'));
        $sponsorship->setAttribute('details_url', $this->getConfigValue('cocote/general/sponsorship_url'));
        $xmlRoot->appendChild($sponsorship);

        $offers = $domtree->createElement("offers");
        $offers = $xmlRoot->appendChild($offers);

        foreach ($productCollection as $product) {
            $imageLink='';
            $imageSecondaryLink='';

            if ($product->getImage()) {
                $imageLink = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
                $this->addGallery($product);
                $images = $product->getMediaGalleryImages();
                foreach ($images as $image) {
                    if ($image->getFile()==$product->getImage()) {
                        continue;
                    }
                    if ($image->getUrl() && $image->getMediaType()=='image') {
                        $imageSecondaryLink=$image->getUrl();
                        break;
                    }
                }
            }

            $currentprod = $domtree->createElement("item");
            $currentprod = $offers->appendChild($currentprod);

            $url=$product->getProductUrl();

            $currentprod->appendChild($domtree->createElement('identifier', $product->getId()));
            $currentprod->appendChild($domtree->createElement('link', $url));
            $currentprod->appendChild($domtree->createElement('keywords', $product->getData('meta_keyword')));


            if($product->getTypeId()=='bundle') {
                $rawPrice=$product->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue();
                $price=$this->priceHelper->currency($rawPrice, true, false);
            }
            else {
                $price=$this->priceHelper->currency($product->getFinalPrice(), true, false);
            }
            $currentprod->appendChild($domtree->createElement('price', $price));

            $labels=explode(',', $product->getData('cocote_labels'));
            $labelsString=implode('|', array_unique($labels));
            $currentprod->appendChild($domtree->createElement('labels', $labelsString));

            $categories=explode(',', $product->getData('cocote_categories'));
            $categoriesString=implode('|', array_unique($categories));
            $currentprod->appendChild($domtree->createElement('category', $categoriesString));

            $tags=explode(',', $product->getData('cocote_tags'));
            $tagsString=implode('|', array_unique($tags));
            $currentprod->appendChild($domtree->createElement('tags', $tagsString));

            foreach ($this->mapping as $nodeName => $attrName) {
                if($nodeName=='description') {
                    $descTag=$domtree->createElement('description');
                    $descTag->appendChild($domtree->createCDATASection($product->getData($attrName)));
                    $currentprod->appendChild($descTag);
                }
                else {
                    if ($product->getData($attrName)) {
                        $value=$product->getResource()->getAttribute($attrName)->getFrontend()->getValue($product);
                        $currentprod->appendChild($domtree->createElement($nodeName, htmlspecialchars($value)));
                    }
                }
            }

            if ($imageLink) {
                $currentprod->appendChild($domtree->createElement('image_link', $imageLink));
            }

            if ($imageSecondaryLink) {
                $currentprod->appendChild($domtree->createElement('image_link2', $imageSecondaryLink));
            }

            $salesTypes = explode(',', $product->getData('cocote_salestype'));
            $salesTypesString = implode('|', array_unique($salesTypes));
            $currentprod->appendChild($domtree->createElement('sale_type', $salesTypesString));

            $types=str_replace(',', '|', $product->getData('cocote_types'));
            $currentprod->appendChild($domtree->createElement('type', $types));

            $paymentOnline=str_replace(',', '|', $product->getData('cocote_payment_online'));
            $currentprod->appendChild($domtree->createElement('payment_online', $paymentOnline));

            $currentprod->appendChild($domtree->createElement('producer', $product->getData('cocote_producer')));
            $currentprod->appendChild($domtree->createElement('state', $product->getData('cocote_state')));
            $currentprod->appendChild($domtree->createElement('distance', $product->getData('cocote_allowed_distance')));
            $currentprod->appendChild($domtree->createElement('targets', $this->mergeText($product->getData('cocote_targets'))));

            $placesOnline=$domtree->createElement('places_online');
            $placeOnline=$domtree->createElement('place_online');

            $placeOnlineLat=$this->getConfigValue('cocote/location/place_online_latitude');
            $placeOnlineLon=$this->getConfigValue('cocote/location/place_online_longitude');
            $placeOnlineRoad=$this->getConfigValue('cocote/location/place_online_road');
            $placeOnlineZipcode=$this->getConfigValue('cocote/location/place_online_zipcode');
            $placeOnlineCity=$this->getConfigValue('cocote/location/place_online_city');


            $placeOnline->setAttribute('lat', $placeOnlineLat);
            $placeOnline->setAttribute('lon', $placeOnlineLon);
            $placeOnline->setAttribute('road', $placeOnlineRoad);
            $placeOnline->setAttribute('zipcode', $placeOnlineZipcode);
            $placeOnline->setAttribute('city', $placeOnlineCity);

            $currentprod->appendChild($placesOnline);
            $placesOnline->appendChild($placeOnline);

            $placesOnsite=$domtree->createElement('places_onsite');
            $place=$domtree->createElement('place_onsite');

            if ($this->getConfigValue('cocote/location/place_online_the_same')) {
                $place->setAttribute('lat', $placeOnlineLat);
                $place->setAttribute('lon', $placeOnlineLon);
                $place->setAttribute('road', $placeOnlineRoad);
                $place->setAttribute('zipcode', $placeOnlineZipcode);
                $place->setAttribute('city', $placeOnlineCity);
            } else {
                $place->setAttribute('lat', $this->getConfigValue('cocote/location/place_onsite_latitude'));
                $place->setAttribute('lon', $this->getConfigValue('cocote/location/place_onsite_longitude'));
                $place->setAttribute('road', $this->getConfigValue('cocote/location/place_onsite_road'));
                $place->setAttribute('zipcode', $this->getConfigValue('cocote/location/place_onsite_zipcode'));
                $place->setAttribute('city', $this->getConfigValue('cocote/location/place_onsite_city'));
            }

            $place->setAttribute('phone', $this->getConfigValue('cocote/location/place_onsite_phone'));
            $place->setAttribute('mobile', $this->getConfigValue('cocote/location/place_onsite_mobile'));
            $place->setAttribute('email', $this->getConfigValue('cocote/location/place_onsite_email'));
            $currentprod->appendChild($placesOnsite);
            $placesOnsite->appendChild($place);

            $paymentOnsite=str_replace(',', '|', $this->getConfigValue('cocote/general/payment_onsite'));
            $place->appendChild($domtree->createElement('payment_onsite', $paymentOnsite));

            $openingHours=$domtree->createElement('opening_hours');
            $openingHours->setAttribute('monday', $this->getOpeningHours(0));
            $openingHours->setAttribute('tuesday', $this->getOpeningHours(1));
            $openingHours->setAttribute('wednesday', $this->getOpeningHours(2));
            $openingHours->setAttribute('thursday', $this->getOpeningHours(3));
            $openingHours->setAttribute('friday', $this->getOpeningHours(4));
            $openingHours->setAttribute('saturday', $this->getOpeningHours(5));
            $openingHours->setAttribute('sunday', $this->getOpeningHours(6));
            $openingHours->setAttribute('additional_info', $this->getConfigValue('cocote/location/opening_hours_additional'));
            $place->appendChild($openingHours);

            $shippingCostTag=$domtree->createElement('shipping_costs');

            $shippingCosts = $this->getConfigValue('cocote/general/shipping');
            if ($shippingCosts) {
                $shippingCosts = json_decode($shippingCosts);
                foreach ($shippingCosts as $shippingCostsRow) {
                    $shippingChoice=$domtree->createElement('shipping_choice');
                    $shippingChoice->setAttribute('type', $shippingCostsRow->type);
                    $shippingChoice->setAttribute('delay', $shippingCostsRow->delay);
                    $shippingChoice->setAttribute('value_from', $shippingCostsRow->value_from);
                    $shippingChoice->setAttribute('free_after', $shippingCostsRow->free_after);
                    $shippingCostTag->appendChild($shippingChoice);
                }
            }
            $currentprod->appendChild($shippingCostTag);

            $discountTag=$domtree->createElement('offer_list');
            $discount = $this->getConfigValue('cocote/general/discount');
            if ($discount) {
                $discount = json_decode($discount);
                foreach ($discount as $discountRow) {
                    $discountChoice=$domtree->createElement('offer');
                    $discountChoice->setAttribute('description', $discountRow->description);
                    $discountChoice->setAttribute('conditions', $discountRow->conditions);
                    $discountChoice->setAttribute('start', strtotime($discountRow->from_date.' '.$discountRow->from_time));
                    $discountChoice->setAttribute('end', strtotime($discountRow->to_date.' '.$discountRow->to_time));
                    $discountTag->appendChild($discountChoice);
                }
            }
            $currentprod->appendChild($discountTag);

            if ($product->getData('cocote_labels')) {
                $validate['labels']=1;
            }
            if ($product->getData('cocote_categories')) {
                $validate['categories']=1;
            }
            if ($product->getData('cocote_targets')) {
                $validate['targets']=1;
            }
            if ($product->getData('cocote_types')) {
                $validate['types']=1;
            }
            if($product->getData('cocote_state')) {
                $validate['state']=1;
            }
            if($product->getData('cocote_producer')) {
                $validate['producer']=1;
            }
            if ($product->getData('cocote_salestypes')) {
                $validate['sales_types']=1;
                if(in_array('online',$salesTypes)) {
                    $validate['sales_type_online_present']=1;
                    if (!$product->getData('cocote_payment_online')) {
                        $validate['payment_online_error']=1;
                    }
                }
                if(in_array('onsite',$salesTypes)) {
                    $validate['sales_type_onsite_present']=1;
                }
            }
        }

        $validateErrors=array();
        $showStar=0;
        if (!isset ($validate['categories'])) {
            $validateErrors[]='le champ category n\'est pas renseigné *';
            $showStar=1;
        }
        if (!isset ($validate['state'])) {
            $validateErrors[]='le champ \'état des produits (neuf/occasion)\' n\'est pas renseigné *';
            $showStar=1;
        }
        if (!isset ($validate['producer'])) {
            $validateErrors[]='le champ \'producteur / revendeur\' n\'est pas renseigné *';
            $showStar=1;
        }

        if (!isset ($validate['sales_types'])) {
            $validateErrors[]='le champ \'Type de vente (en ligne/ sur place) n\'est pas renseigné';
        }
        if (isset ($validate['payment_online_error'])) {
            $validateErrors[]='le champ \'Paiement en ligne\' est vide alors que des produits sont vendus en ligne';
        }

        if (isset ($validate['sales_type_online_present']) && !$shippingCosts) {
            $validateErrors[]='le champ \'Livraison\' n\'est pas renseigné alors que des produits sont vendus sur place';
        }

        if (!$placeOnlineCity || !$placeOnlineZipcode || !$placeOnlineLat || !$placeOnlineLon || !$placeOnlineRoad) {
            $validateErrors[]='Votre adresse est vide, merci de la completer';
        }

        if (isset($validate['sales_type_onsite_present'])) {
            if (!$paymentOnsite) {
                $validateErrors[]='le champ \'Paiement sur place\' n\'est pas renseigné alors que des produits sont vendus sur place';
            }

            if(!$this->getConfigValue('cocote/location/place_onsite_phone')) {
                $validateErrors[]='Vous vendez au moins un produit sur place or le champ Telephone est vide';
            }
            if(!$this->getConfigValue('cocote/location/place_onsite_mobile')) {
                $validateErrors[]='Vous vendez au moins un produit sur place or le champ Mobile est vide';
            }
            if(!$this->getConfigValue('cocote/location/place_onsite_email')) {
                $validateErrors[]='Vous vendez au moins un produit sur place or le champ Mail est vide';
            }
            if(!$this->getOpeningHours(0) && !$this->getOpeningHours(1) && !$this->getOpeningHours(2) &&
                !$this->getOpeningHours(3) && !$this->getOpeningHours(4) && !$this->getOpeningHours(5) && !$this->getOpeningHours(6))
            {
                $validateErrors[]='Vous vendez au moins un produit sur place or Vos horaires d\'ouvertures ne sont pas indiqués';
            }
        }

        if(sizeof($validateErrors)) {
            $errorText='Les erreurs suivantes ont été détectes et empechent la generation de votre flux, merci de les corriger.<br />';
            foreach($validateErrors as $error) {
                $errorText.=$error.'</br>';
            }

            if($showStar) {
                $errorText.='*: Avez vous bien renseigné / enregistré une valeur puis cliqué sur le bouton correspondant \'Commencez des maintenant\' en dessous du champ?';
            }

            $this->storeManager->setCurrentStore('admin');
            throw new \Exception($errorText);
        }

        $domtree->save($filePath);
        return $validate;

    }

    public function mergeText($string)
    {
        $retString=str_replace(',', '|', $string);
        return $retString;
    }

    /** Add image gallery to $product */
    public function addGallery($product)
    {
        $this->galleryReadHandler->execute($product);
    }

    public function getConfigValue($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    private function getOpeningHours($i)
    {
        $start1=$this->getConfigValue('cocote/location/openinghours'.$i.'_1');
        $end1=$this->getConfigValue('cocote/location/openinghours'.$i.'_2');
        $start2=$this->getConfigValue('cocote/location/openinghours'.$i.'_3');
        $end2=$this->getConfigValue('cocote/location/openinghours'.$i.'_4');
        if (!$start1) {
            return '';
        }
        $ret=$start1.'-'.$end1;
        if ($start2) {
            $ret.=';'.$start2.'-'.$end2;
        }
        return $ret;
    }

    public function updateFlat($productId, $attribute, $value)
    {
        if(!$this->getConfigValue('catalog/frontend/flat_catalog_product')) { //flat is not enabled, no need to update
            return true;
        }
        //get default store

        $storeCode=$this->getConfigValue('cocote/general/store');
        if (!$storeCode) {
            $storeCode='default';
        }

        $stores = $this->storeManager->getStores(true, false);
        foreach($stores as $store){
            if($store->getCode() === $storeCode){
                $storeId = $store->getId();
            }
        }

        $tableName=$this->getFlatTableName($storeId);

        $connection = $this->resource->getConnection();

        $sql='UPDATE '.$tableName.' SET '.$attribute.'="'.$value.'"';

        if($productId) {
            $sql.=' WHERE entity_id='.$productId;
        }
        $connection->query($sql);

    }

    public function getTable($name)
    {
        return $this->resource->getTableName($name);
    }

    /**
     * Retrieve Catalog Product Flat Table name
     *
     * @param int $storeId
     * @return string
     */
    public function getFlatTableName($storeId)
    {
        return sprintf('%s_%s', $this->getTable('catalog_product_flat'), $storeId);
    }

}
