<?php

namespace Cocote\Feed\Model\Config\Source;

class Producer extends SourceAbstract
{
    public function getValues()
    {
        $types=[
            '---' => '',
            'Oui' => 'oui',
            'Non' => 'non',
        ];

        return $types;
    }
}
