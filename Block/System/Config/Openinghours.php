<?php

namespace Cocote\Feed\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Openinghours extends Field
{

    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Context $context,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;

        parent::__construct($context, $data);
    }


    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $days=['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];

        $html='<table class="opening-hours">';

        foreach ($days as $i => $day) {
            $html.='
            <tr>
            <th>'.$day.'</th>
            <td>de: <input placeholder="9:00" name="groups[location][fields][openinghours'.$i.'_1][value]" value="'.$this->scopeConfig->getValue('cocote/location/openinghours'.$i.'_1', \Magento\Store\Model\ScopeInterface::SCOPE_STORE).'"></td>
            <td>à: <input placeholder="15:00" name="groups[location][fields][openinghours'.$i.'_2][value]" value="'.$this->scopeConfig->getValue('cocote/location/openinghours'.$i.'_2', \Magento\Store\Model\ScopeInterface::SCOPE_STORE).'"></td>
            <td>de: <input placeholder="16:00" name="groups[location][fields][openinghours'.$i.'_3][value]" value="'.$this->scopeConfig->getValue('cocote/location/openinghours'.$i.'_3', \Magento\Store\Model\ScopeInterface::SCOPE_STORE).'"></td>
            <td>à: <input placeholder="18:00" name="groups[location][fields][openinghours'.$i.'_4][value]" value="'.$this->scopeConfig->getValue('cocote/location/openinghours'.$i.'_4', \Magento\Store\Model\ScopeInterface::SCOPE_STORE).'"></td>
            </tr>
            ';
        }
        $html.='</table>';

        return $html;
    }
}
