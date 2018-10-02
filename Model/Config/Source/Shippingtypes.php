<?php
namespace Cocote\Feed\Model\Config\Source;

class Shippingtypes extends SourceAbstract
{
    public function getValues()
    {
        $types=[
            "<1 jour ouvré"=>"-1d",
            "<3 jours ouvré"=>"-3d",
            "<10 jours ouvré"=>"-10d",
            ">10 jours ouvré"=>"+10d"
        ];

        return $types;
    }
}
