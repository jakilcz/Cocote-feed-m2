<?php
namespace Cocote\Feed\Model\Config\Source;

class State extends SourceAbstract
{
    public function getValues() {
        $types=array(
            '---'=>'',
            'Nouveau'=>'new',
            'Occasion'=>'second_hand',
        );

        return $types;
    }

}