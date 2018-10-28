<?php

namespace Cocote\Feed\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Gridlink extends Field
{

    protected $backendUrl;

    public function __construct(
        \Magento\Backend\Model\UrlInterface $backendUrl,
        Context $context,
        array $data = []
    ) {
        $this->backendUrl = $backendUrl;
        parent::__construct($context, $data);
    }


    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $url = $this->backendUrl->getUrl('cocote/cocote/productsgrid');

        $html='<button
        onclick="window.open(\''.$url.'\');return false;" />
        <span><span>'.__('Now, Customize your products for each').'</span></span></button>';
        return $html;
    }
}
