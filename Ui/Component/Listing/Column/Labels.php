<?php

namespace Cocote\Feed\Ui\Component\Listing\Column;

class Labels extends AbstractColumn
{
    public $attribute='cocote_labels';

    protected function getOptions()
    {
        $valuesSource = new \Cocote\Feed\Model\Config\Source\Labels($this->cacheType);
        $options=$valuesSource->toOptionArray();
        return $options;
    }
}
