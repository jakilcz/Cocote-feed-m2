<?php

namespace Cocote\Feed\Ui\Component\Listing\Column;

use Cocote\Feed\Model\Config\Source\Types as ValuesSource;

class Types extends AbstractColumn
{
    public $attribute='cocote_types';

    protected function getOptions()
    {
        $valuesSource = new \Cocote\Feed\Model\Config\Source\Types($this->cacheType);
        $options=$valuesSource->toOptionArray();
        return $options;
    }
}
