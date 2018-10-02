<?php

namespace Cocote\Feed\Ui\Component\Listing\Column;

class Distance extends \Magento\Ui\Component\Listing\Columns\Column
{
    public $attribute='cocote_allowed_distance';

    protected $backendUrl;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->backendUrl = $backendUrl;
    }

    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->attribute]=$this->getSelect($item);
            }
        }
        return $dataSource;
    }

    public function getSelect($item) {
        $id=$item['entity_id'];
        $ajaxUrl = $this->backendUrl->getUrl('cocote/cocote/syncprods');
        $value='';
        if (isset($item[$this->attribute])) {
          $value=$item[$this->attribute];
        }

        $html='<input value="'.$value.'" type="text" data-attr_name="'.$this->attribute.'" data-id="'.$id.'" id="'.$this->attribute.'_select_'.$id.'" style="width:150px;"  onchange="changeVal(this,\''.$ajaxUrl.'\')" />';

        return $html;
    }
}
