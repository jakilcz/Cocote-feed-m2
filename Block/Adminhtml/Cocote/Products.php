<?php
namespace Cocote\Feed\Block\Adminhtml;

class Post extends \Magento\Backend\Block\Widget\Grid\Container
{

    protected function _construct()
    {
        $this->_controller = 'adminhtml_cocote_productsgrid';
        $this->_blockGroup = 'Cocote_Feed';
        $this->_headerText = __('Prods');
        $this->_addButtonLabel = __('Create New Prod');
        parent::_construct();
    }
}
