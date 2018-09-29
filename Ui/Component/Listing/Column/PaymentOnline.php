<?php

namespace Cocote\Feed\Ui\Component\Listing\Column;

class PaymentOnline extends AbstractColumn {
    public $attribute='cocote_payment_online';

    protected function getOptions() {
        $valuesSource = new \Cocote\Feed\Model\Config\Source\Payments($this->cacheType);
        $options=$valuesSource->toOptionArray();
        return $options;
    }

}