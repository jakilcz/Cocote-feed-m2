<?php

namespace Cocote\Feed\Ui\Component\Listing\Column;

use Cocote\Feed\Model\Config\Source\Categories as ValuesSource;

class Categories extends \Magento\Ui\Component\Listing\Columns\Column {

    public $attribute='cocote_categories';

    protected $backendUrl;
    protected $cacheType;

    public function __construct(
        \Cocote\Feed\Model\Cache\Type $cacheType,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        array $components = [],
        array $data = []
    ){
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->backendUrl = $backendUrl;
        $this->cacheType=$cacheType;

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
        $valuesSource = new ValuesSource($this->cacheType);
        $options=$valuesSource->toOptionArray();

        $id=$item['entity_id'];

        $ajaxUrl = $this->backendUrl->getUrl('cocote/cocote/syncprods');

        $value=[];
        if(isset($item[$this->attribute])) {
            $value=explode(',',$item[$this->attribute]);
        }

       $html='<select multiple="multiple" data-attr_name="'.$this->attribute.'" data-id="'.$id.'" id="'.$this->attribute.'_select_'.$id.'" class="chosen" style="width:150px;"  onchange="changeVal(this,\''.$ajaxUrl.'\')">';


        foreach($options as $option) {

            if(is_array($option['value'])) {
                $html.= '<optgroup label="' . $option['label'] . '">';
                foreach ($option['value'] as $groupItem) {
                    $selected='';
                    if(in_array($groupItem['value'],$value))
                        $selected='selected="selected"';
                    $html.='<option value="'.$groupItem['value'].'" '.$selected.' >'.$groupItem['label'].'</option>';
                }
                $html .= '</optgroup>';
            }
            else {
                $selected='';
                if(in_array($option['value'],$value))
                    $selected='selected="selected"';
                $html.='<option value="'.$option['value'].'" '.$selected.' >'.$option['label'].'</option>';
            }
        }

        $html.='</select>';
//        $html.='<script>jQuery("#'.$this->attribute.'_select_'.$id.'").chosen();</script>';

        return $html;
    }
}