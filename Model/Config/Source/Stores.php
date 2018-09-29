<?php
namespace Cocote\Feed\Model\Config\Source;

class Stores extends SourceAbstract
{
    protected $storeManager;


    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager

    )
    {
        $this->storeManager=$storeManager;
    }

    public function getAllOptions()
    {
        $storeManagerDataList = $this->storeManager->getStores();
        $options = array();

        foreach ($storeManagerDataList as $key => $value) {
            $options[] = ['label' => $value['name'].' - '.$value['code'], 'value' => $value['code']];
        }
        return $options;
    }

}