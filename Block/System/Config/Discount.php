<?php

namespace Cocote\Feed\Block\System\Config;

class Discount extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

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
    protected function _construct()
    {
        parent::_construct();
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('description', ['label' => __('Description')]);
        $this->addColumn('conditions', ['label' => __('Conditions')]);
        $this->addColumn('from_date', ['label' => __('From date'), 'class' => 'datepicker']);
        $this->addColumn('from_time', ['label' => __('From time'), 'class' => 'from_time']);
        $this->addColumn('to_date', ['label' => __('To date'), 'class' => 'datepicker']);
        $this->addColumn('to_time', ['label' => __('To time'), 'class' => 'to_time']);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
