<?php

namespace Cocote\Feed\Ui\Component\Listing\Column;

class Categories extends AbstractColumn
{
    public $attribute='cocote_categories';

    protected function getOptions()
    {
        $valuesSource = new \Cocote\Feed\Model\Config\Source\Categories($this->cacheType);
        $options=$valuesSource->toOptionArray();
        return $options;
    }
}
