<?php

namespace Cocote\Feed\Block\System\Config;

class Shipping extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray {

    /**
     * Grid columns
     *
     * @var array
     */
    protected $_columns = [];
    protected $renderer;

    /**
     * Enable the "Add after" button or not
     *
     * @var bool
     */
    protected $_addAfter = true;

    /**
     * Label of add button
     *
     * @var string
     */
    protected $_addButtonLabel;

    /**
     * Check if columns are defined, set template
     *
     * @return void
     */
    protected function _construct() {
        parent::_construct();
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Returns renderer for country element
     * 
     * @return \Magento\Braintree\Block\Adminhtml\Form\Field\Countries
     */
    protected function getRenderer()
    {
        if (!$this->renderer) {
            $this->renderer = $this->getLayout()->createBlock(
                '\Cocote\Feed\Block\Adminhtml\Form\Field\ShippingType', '', ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->renderer;
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('type', ['label' => __('Type')]);
        $this->addColumn('delay', ['label' => __('Delay'),'renderer' => $this->getRenderer()]);
        $this->addColumn('value_from', ['label' => __('Value from')]);
        $this->addColumn('free_after', ['label' => __('Free after')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $delay = $row->getDelay();
        $options = [];
        if ($delay) {
            $options['option_' . $this->getRenderer()->calcOptionHash($delay)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
        }
    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     * @return string
     * @throws \Exception
     */
    public function renderCellTemplate($columnName)
    {
        if ($columnName == "delay") {
            $this->_columns[$columnName]['style'] = 'width:150px';
        }
        return parent::renderCellTemplate($columnName);
    }
}
