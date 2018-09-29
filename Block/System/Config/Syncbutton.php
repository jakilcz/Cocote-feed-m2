<?php

namespace Cocote\Feed\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Syncbutton extends Field
{

    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }


    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $attribute=str_replace(['cocote_general_','_sync'],'',$element->getHtmlId());

        $alertMessage=addslashes(__("This can remove the values you've already set for this attribute, are you sure?"));

        $url = $this->getUrl('cocote/cocote/syncvalues',['_current' => true, '_use_rewrite' => true, '_query' => ['attribute'=>$attribute]]); //

        $html =$this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
                [
                    'id' => $element->getHtmlId(),
                    'label' => __('Start Now !'),
                ]
            )
            ->setOnClick("if (confirm('$alertMessage')) { setLocation('$url'); }")
            ->toHtml();

        $html .= '<h1 style="display:none" id="'.$element->getHtmlId().'_message">'.__('Please save configuration first')
            . '</h1>';
        return $html;
    }

}

?>