<?php

namespace Cocote\Feed\Block\Adminhtml\Form\Field;

class ShippingType extends \Magento\Framework\View\Element\Html\Select {

    protected $shippingTypeSource;

    public function __construct(
    \Magento\Framework\View\Element\Context $context, 
    \Cocote\Feed\Model\Config\Source\Shippingtypes $shippingTypeSource, 
    array $data = []
    ) {
        parent::__construct($context, $data);
        $this->shippingTypeSource = $shippingTypeSource;
    }


    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml() {
        if (!$this->getOptions()) {
            $this->setOptions($this->shippingTypeSource->toOptionArray());
        }
        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     * 
     * @param string $value
     * @return $this
     */
    public function setInputName($value) {
        return $this->setName($value);
    }

}
