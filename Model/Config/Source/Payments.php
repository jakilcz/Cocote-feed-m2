<?php

namespace Cocote\Feed\Model\Config\Source;

class Payments extends SourceAbstract
{

    public function getValues() {
        return $this->getValuesFromCache('payment_online');
    }


}