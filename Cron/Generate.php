<?php

namespace Cocote\Feed\Cron;

class Generate {
    
    protected $helper;

    public function __construct(
        \Magento\Framework\App\State $appState,
        \Cocote\Feed\Helper\Data $helper
    ) {
        $this->helper=$helper;
        //$appState->setAreaCode('adminhtml'); // or 'frontend', depending on your needs
    
    }

    public function execute() {
        $this->helper->generateFeed();
    }
}