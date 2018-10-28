<?php
namespace Cocote\Feed\Model\Config\Source;

class State extends SourceAbstract
{
    public function getValues()
    {
        $values=[
            '---'=>'',
            'Nouveau'=>'new',
            'Occasion'=>'second_hand',
        ];

        return $values;
    }
}
