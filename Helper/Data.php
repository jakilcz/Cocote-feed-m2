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

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configInterface,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Framework\App\State $appState,
        GalleryReadHandler $galleryReadHandler,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        Context $context
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configInterface = $configInterface;
        $this->productVisibility = $productVisibility;
        $this->productCollectionFactory=$productCollectionFactory;
        $this->galleryReadHandler = $galleryReadHandler;
        $this->storeManager=$storeManager;
        $this->priceHelper=$priceHelper;

        parent::__construct($context);
    }

    public function getFileLink()
    {
        $path=$this->scopeConfig->getValue('cocote/generate/path', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $baseUrl=$this->storeManager->getStore()->getBaseUrl();
        return $baseUrl.'pub/'.$path.'/'.$this->getFileName();
    }

    public function getFilePath()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');

        $path=$this->scopeConfig->getValue('cocote/generate/path', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $dirPath=$directory->getPath('pub').'/'.$path;

        if (!file_exists($dirPath)) {
            mkdir($dirPath, 0777, true);
        }

        return $dirPath.'/'.$this->getFileName();
    }

    public function getFileName()
    {
        $fileName=$this->scopeConfig->getValue('cocote/generate/filename', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if (!$fileName) {
            $fileName=$this->generateRandomString().'.xml';
            $this->configInterface
                ->saveConfig('cocote/generate/filename', $fileName, 'default', 0);
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
        $storeCode=$this->scopeConfig->getValue('cocote/general/store', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$storeCode) {
            $storeCode='default';
        }

        $this->storeManager->setCurrentStore($storeCode);

        $collection = $this->productCollectionFactory->create();

        $collection->setVisibility($this->productVisibility->getVisibleInSiteIds());

        $collection->addAttributeToSelect('price');
        $collection->addAttributeToSelect('image');
        $collection->addAttributeToSelect('cocote_labels');
        $collection->addAttributeToSelect('cocote_categories');
        $collection->addAttributeToSelect('cocote_tags');
        $collection->addAttributeToSelect('cocote_targets');
        $collection->addAttributeToSelect('cocote_producer');
        $collection->addAttributeToSelect('cocote_state');
        $collection->addAttributeToSelect('cocote_salestypes');
        $collection->addAttributeToSelect('cocote_payment_online');
        $collection->addAttributeToSelect('cocote_payment_onsite');
        $collection->addAttributeToSelect('cocote_allowed_distance');

        foreach ($this->mapping as $attribute) {
            $collection->addAttributeToSelect($attribute);
        }
        return $collection;
    }

    public function generateFeed()
    {
        $filePath=$this->getFilePath();
        $store = $this->storeManager->getStore();

        $mapName=$this->scopeConfig->getValue('cocote/general/map_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $mapMpn=$this->scopeConfig->getValue('cocote/general/map_mpn', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $mapGtin=$this->scopeConfig->getValue('cocote/general/map_gtin', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $mapDescription=$this->scopeConfig->getValue('cocote/general/map_description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $mapManufacturer=$this->scopeConfig->getValue('cocote/general/map_manufacturer', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

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
            $this->mapping['marque']=$mapManufacturer;
        }

        $productCollection = $this->getProductCollection();

        $domtree = new \DOMDocument('1.0', 'UTF-8');

        $xmlRoot = $domtree->createElement("offers");
        $xmlRoot = $domtree->appendChild($xmlRoot);

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
            $currentprod = $xmlRoot->appendChild($currentprod);

            $url=$product->getProductUrl();

            $currentprod->appendChild($domtree->createElement('id', $product->getId()));
            $currentprod->appendChild($domtree->createElement('price', $this->priceHelper->currency($product->getFinalPrice(), true, false)));
            $currentprod->appendChild($domtree->createElement('link', $url));

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
                if ($product->getData($attrName)) {
                    $value=$product->getResource()->getAttribute($attrName)->getFrontend()->getValue($product);
                    $currentprod->appendChild($domtree->createElement($nodeName, htmlspecialchars($value)));
                }
            }

            if ($imageLink) {
                $currentprod->appendChild($domtree->createElement('image_link', $imageLink));
            }

            if ($imageSecondaryLink) {
                $currentprod->appendChild($domtree->createElement('image_link2', $imageSecondaryLink));
            }

            $salesTypes=str_replace(',', '|', $product->getData('cocote_salestypes'));
            $currentprod->appendChild($domtree->createElement('sale_type', $salesTypes));

            $paymentOnline=str_replace(',', '|', $product->getData('cocote_payment_online'));
            $currentprod->appendChild($domtree->createElement('payment_online', $paymentOnline));

            $paymentOnsite=str_replace(',', '|', $product->getData('cocote_payment_onsite'));
            $currentprod->appendChild($domtree->createElement('payment_onsite', $paymentOnsite));

            $currentprod->appendChild($domtree->createElement('producer', $product->getData('cocote_producer')));
            $currentprod->appendChild($domtree->createElement('state', $product->getData('cocote_state')));
            $currentprod->appendChild($domtree->createElement('distance', $product->getData('cocote_allowed_distance')));
            $currentprod->appendChild($domtree->createElement('targets', $this->mergeText($product->getData('cocote_targets'))));

            $placeOnline=$domtree->createElement('place_online');
            $placeOnline->setAttribute('lat', $this->scopeConfig->getValue('cocote/location/place_online_latitude', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $placeOnline->setAttribute('lon', $this->scopeConfig->getValue('cocote/location/place_online_longitude', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $placeOnline->setAttribute('road', $this->scopeConfig->getValue('cocote/location/place_online_road', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $placeOnline->setAttribute('zipcode', $this->scopeConfig->getValue('cocote/location/place_online_zipcode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $placeOnline->setAttribute('city', $this->scopeConfig->getValue('cocote/location/place_online_city', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $currentprod->appendChild($placeOnline);

            $placeOnsite=$domtree->createElement('place_onsite');
            $place=$domtree->createElement('place');

            if ($this->scopeConfig->getValue('cocote/location/place_online_the_same', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $place->setAttribute('lat', $this->scopeConfig->getValue('cocote/location/place_online_latitude', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $place->setAttribute('lon', $this->scopeConfig->getValue('cocote/location/place_online_longitude', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $place->setAttribute('road', $this->scopeConfig->getValue('cocote/location/place_online_road', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $place->setAttribute('zipcode', $this->scopeConfig->getValue('cocote/location/place_online_zipcode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $place->setAttribute('city', $this->scopeConfig->getValue('cocote/location/place_online_city', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            } else {
                $place->setAttribute('lat', $this->scopeConfig->getValue('cocote/location/place_onsite_latitude', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $place->setAttribute('lon', $this->scopeConfig->getValue('cocote/location/place_onsite_longitude', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $place->setAttribute('road', $this->scopeConfig->getValue('cocote/location/place_onsite_road', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $place->setAttribute('zipcode', $this->scopeConfig->getValue('cocote/location/place_onsite_zipcode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $place->setAttribute('city', $this->scopeConfig->getValue('cocote/location/place_onsite_city', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            }

            $currentprod->appendChild($placeOnsite);
            $placeOnsite->appendChild($place);

            $shippingCostTag=$domtree->createElement('shiping_cost');

            $shippingCosts = $this->scopeConfig->getValue('cocote/general/shipping', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if ($shippingCosts) {
                $shippingCosts = json_decode($shippingCosts);
                foreach ($shippingCosts as $shippingCostsRow) {
                    $shippingChoice=$domtree->createElement('shiping_choice');
                    $shippingChoice->setAttribute('type', $shippingCostsRow->type);
                    $shippingChoice->setAttribute('delay', $shippingCostsRow->delay);
                    $shippingChoice->setAttribute('value_from', $shippingCostsRow->value_from);
                    $shippingChoice->setAttribute('free_after', $shippingCostsRow->free_after);
                    $shippingCostTag->appendChild($shippingChoice);
                }
            }
            $currentprod->appendChild($shippingCostTag);
        }

        $discountTag=$domtree->createElement('offer_list');
        $discount = $this->scopeConfig->getValue('cocote/general/discount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($discount) {
            $discount = json_decode($discount);
            foreach ($discount as $discountRow) {
                $discountChoice=$domtree->createElement('offer');
                $discountChoice->setAttribute('description', $discountRow->description);
                $discountChoice->setAttribute('conditions', $discountRow->conditions);
                $discountTag->appendChild($discountChoice);
            }
        }
        $currentprod->appendChild($discountTag);


        $domtree->save($filePath);
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
}
