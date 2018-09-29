<?php
 
namespace Cocote\Feed\Model\Config\Source;
 
class Categories extends SourceAbstract
{
    public function getValues() {
        return $this->getValuesFromCache('category');
    }

}