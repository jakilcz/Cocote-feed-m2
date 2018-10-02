<?php

namespace Cocote\Feed\Model\Config\Source;

class Attributes extends SourceAbstract
{
    protected $attributeFactory;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory
    ) {
        $this->attributeFactory=$attributeFactory;
    }

    public function getValues()
    {
        $values=[];
        $values['---']='';

        $attributeInfo = $this->attributeFactory->getCollection()->addFieldToFilter('entity_type_id', 4);

        foreach ($attributeInfo as $items) {
            $values[$items->getFrontendLabel()]=$items->getAttributeCode();
        }

        return $values;
    }
}
