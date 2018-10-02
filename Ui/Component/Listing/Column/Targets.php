<?php

namespace Cocote\Feed\Ui\Component\Listing\Column;

class Targets extends AbstractColumn
{
    public $attribute='cocote_targets';

    protected function getOptions()
    {
        $valuesSource = new \Cocote\Feed\Model\Config\Source\Targets($this->cacheType);
        $options=$valuesSource->toOptionArray();
        return $options;
    }
}
