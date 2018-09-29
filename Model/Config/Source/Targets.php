<?php
namespace Cocote\Feed\Model\Config\Source;

class Targets extends SourceAbstract
{
    public function getValues() {
        return $this->getValuesFromCache('targets');
    }

}