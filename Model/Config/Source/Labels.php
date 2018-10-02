<?php
 
namespace Cocote\Feed\Model\Config\Source;

class Labels extends SourceAbstract
{

    public function getValues()
    {
        return $this->getValuesFromCache('labels');
    }
}
