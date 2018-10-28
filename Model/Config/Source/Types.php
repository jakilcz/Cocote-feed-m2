<?php

namespace Cocote\Feed\Model\Config\Source;

class Types extends SourceAbstract
{
    public function getValues()
    {
        return $this->getValuesFromCache('type');
    }
}
