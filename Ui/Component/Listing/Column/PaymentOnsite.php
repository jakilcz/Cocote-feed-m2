<?php

namespace Cocote\Feed\Ui\Component\Listing\Column;

class PaymentOnsite extends AbstractColumn {
    public $attribute='cocote_payment_onsite';

    protected function getOptions() {
        $valuesSource = new \Cocote\Feed\Model\Config\Source\Payments($this->cacheType);
        $options=$valuesSource->toOptionArray();
        return $options;
    }

}