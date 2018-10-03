<?php

namespace Cocote\Feed\Ui\Component\Listing\Column;

class State extends AbstractColumn
{
    public $attribute='cocote_state';

    protected function getOptions()
    {
        $valuesSource = new \Cocote\Feed\Model\Config\Source\State($this->cacheType);
        $options=$valuesSource->toOptionArray();
        return $options;
    }
}
