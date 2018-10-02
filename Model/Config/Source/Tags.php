<?php
namespace Cocote\Feed\Model\Config\Source;

class Tags extends SourceAbstract
{
    public function getValues()
    {
        return $this->getValuesFromCache('tags');
    }
}
