<?php

namespace Cocote\Feed\Ui\Component\Listing\Column;

class AbstractColumn extends \Magento\Ui\Component\Listing\Columns\Column
{
    public $attribute;
    protected $backendUrl;
    protected $cacheType;

    public function __construct(
        \Cocote\Feed\Model\Cache\Type $cacheType,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->cacheType=$cacheType;
        $this->backendUrl = $backendUrl;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->attribute]=$this->getSelect($item);
            }
        }
        return $dataSource;
    }

    public function getSelect($item)
    {
        $options=$this->getOptions();
        $id=$item['entity_id'];
        $ajaxUrl = $this->backendUrl->getUrl('cocote/cocote/syncprods');
        $value=[];

        if (isset($item[$this->attribute])) {
            $value=explode(',', $item[$this->attribute]);
        }

        if ($this->attribute=='cocote_state' || $this->attribute=='cocote_producer') {
            $html='<select data-attr_name="'.$this->attribute.'" data-id="'.$id.'" id="'.$this->attribute.'_select_'.$id.'" style="width:150px;"  onchange="changeVal(this,\''.$ajaxUrl.'\')">';
        } else {
            $html='<select multiple="multiple" data-attr_name="'.$this->attribute.'" data-id="'.$id.'" id="'.$this->attribute.'_select_'.$id.'" class="chosen" style="width:150px;"  onchange="changeVal(this,\''.$ajaxUrl.'\')">';
        }

        foreach ($options as $option) {
            if (is_array($option['value'])) {
                $html.= '<optgroup label="' . $option['label'] . '">';
                foreach ($option['value'] as $groupItem) {
                    $selected='';
                    if (in_array($groupItem['value'], $value)) {
                        $selected='selected="selected"';
                    }
                    $html.='<option value="'.$groupItem['value'].'" '.$selected.' >'.$groupItem['label'].'</option>';
                }
                $html .= '</optgroup>';
            } else {
                $selected='';
                if (in_array($option['value'], $value)) {
                    $selected='selected="selected"';
                }
                $html.='<option value="'.$option['value'].'" '.$selected.' >'.$option['label'].'</option>';
            }
        }

        $html.='</select>';
        return $html;
    }
}
