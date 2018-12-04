<?php

namespace Cocote\Feed\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Getcoordsonline extends Field
{

    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $errorMessage=addslashes(__("temporary geolocalisation issue , please retry after or insert coord manually"));

        $html =$this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )
        ->setData(['id' => $element->getHtmlId(),'label' => __('Get coordinates')])
        ->setOnClick("getCoordsOnline();")
        ->toHtml();

        $html .= "
<script >
 function getCoordsOnline() {
 url='https://fr.cocote.com/api/connector/geocoding';
 jQuery.ajax({
    type: 'POST',
    url: url,
    data: {
        shopId: jQuery('#cocote_general_shop_id').val(),
        privateKey: jQuery('#cocote_general_shop_key').val(),
        query: jQuery('#cocote_location_place_online_road').val()+' '+jQuery('#cocote_location_place_online_city').val()+' '+jQuery('#cocote_location_place_online_zipcode').val()
    }
})
.done(function(data) {
    if(data.Response.status==false) {
        alert(data.Response.errors[0]);
    }
    else {
        lat=data.Response.result.lat;
        jQuery('#cocote_location_place_online_latitude').val(lat);
        lng=data.Response.result.lng;
        jQuery('#cocote_location_place_online_longitude').val(lng);
    }
})
.fail(function() {
    alert('".$errorMessage."');
})
;
}
</script>";
        return $html;
    }
}
