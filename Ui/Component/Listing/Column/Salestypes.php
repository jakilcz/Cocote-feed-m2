<?php

namespace Cocote\Feed\Ui\Component\Listing\Column;

use Cocote\Feed\Model\Config\Source\Salestypes as ValuesSource;

class Salestypes extends AbstractColumn
{
    public $attribute='cocote_salestypes';

    protected function getOptions()
    {
        $valuesSource = new \Cocote\Feed\Model\Config\Source\Salestypes($this->cacheType);
        $options=$valuesSource->toOptionArray();
        return $options;
    }
}
