<?php

namespace Cocote\Feed\Model\Config\Source;

class Salestypes extends SourceAbstract
{
    public function getValues() {
        return $this->getValuesFromCache('sale_type');
    }

}