<?php

namespace Cocote\Feed\Ui\Component\Listing\Column;

use Cocote\Feed\Model\Config\Source\Labels as LabelsSource;
use Cocote\Feed\Model\Cache\Type;

class Labels extends \Magento\Ui\Component\Listing\Columns\Column {

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
                $item['cocote_labels']=$this->getSelect($item);
            }
        }
        return $dataSource;
    }


    public function getSelect($item) {
        $id=$item['entity_id'];

        $ajaxUrl = $this->backendUrl->getUrl('cocote/cocote/syncprods');

        $value=[];
        if(isset($item['cocote_labels'])) {
            $value=explode(',',$item['cocote_labels']);
        }

       $html='<select multiple="multiple" data-attr_name="cocote_labels" data-id="'.$id.'" id="cat_select_'.$id.'" class="chosen" style="width:150px;"  onchange="changeVal(this,\''.$ajaxUrl.'\')">';

        $labelsSource = new LabelsSource($this->cacheType);
        $options=$labelsSource->toOptionArray();

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
//        $html.='<script>jQuery("#cat_select_'.$id.'").chosen();</script>';

        return $html;
    }
}