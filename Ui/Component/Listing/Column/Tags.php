<?php

namespace Cocote\Feed\Ui\Component\Listing\Column;

class Tags extends AbstractColumn {
    public $attribute='cocote_tags';

    protected function getOptions() {
        $valuesSource = new \Cocote\Feed\Model\Config\Source\Tags($this->cacheType);
        $options=$valuesSource->toOptionArray();
        return $options;
    }

}


