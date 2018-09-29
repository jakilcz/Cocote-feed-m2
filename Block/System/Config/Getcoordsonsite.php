<?php

namespace Cocote\Feed\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Getcoordsonsite extends Field
{

    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }


    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element) {

        $alertMessage=addslashes(__("Can't get coordinates"));
        $errorMessage=addslashes(__("temporary geolocalisation issue , please retry after or insert coord manually"));

        $key='AIzaSyBzNYZwGM07VrBKgH_xbuRwsOYm8IJfKyk';

        $html =$this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
                [
                    'id' => $element->getHtmlId(),
                    'label' => __('Get coordinates'),
                ]
            )
            ->setOnClick("getCoordsOnsite();")
            ->toHtml();

        $html .= "

<script >
 function getCoordsOnsite() {
 url='https://maps.googleapis.com/maps/api/geocode/json';
 jQuery.ajax({
    url: url,
    method: 'GET',
    dataType:'json',
    data: {
        key: '".$key."',
        address: jQuery('#cocote_location_place_onsite_road').val()+' '+jQuery('#cocote_location_place_onsite_city').val()+' '+jQuery('#cocote_location_place_onsite_zipcode').val()
    }
})
.done(function(data) {
    if(data['status']!='OK') {
        alert(data.error_message);
    }
    else {
        lat=data.results[0].geometry.location.lat;
        jQuery('#cocote_location_place_onsite_latitude').val(lat);
        lng=data.results[0].geometry.location.lng;
        jQuery('#cocote_location_place_onsite_longitude').val(lng);
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

?>