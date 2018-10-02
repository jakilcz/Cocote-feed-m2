<?php

namespace Cocote\Feed\Ui\Component\Listing\Column;

class Producer extends AbstractColumn
{
    public $attribute='cocote_producer';

    protected function getOptions()
    {
        $valuesSource = new \Cocote\Feed\Model\Config\Source\Producer($this->cacheType);
        $options=$valuesSource->toOptionArray();
        return $options;
    }
}
