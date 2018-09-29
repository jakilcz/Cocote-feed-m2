<?php
 
namespace Cocote\Feed\Model\Config\Source;
 
use Magento\Framework\DB\Ddl\Table;
 
class SourceAbstract extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    protected $feedUrl='https://fr.cocote.com/api/connector/fields';
    protected $cacheType;

    public function __construct(\Cocote\Feed\Model\Cache\Type $cacheType)
    {
        $this->cacheType = $cacheType;
    }


    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $values=$this->getValues();

        $options=array();

        foreach($values as $group=>$labels) {
            if(is_array($labels)) {
                $data=array();
                foreach($labels as $labelName=>$labelCode) {
                    $data[]=array('label'=>$labelName,'value'=>$labelCode);      //here we can add translation
                }
                $options[]=array('label'=>$group,'value'=>$data);
            }
            else {
                $options[]=array(
                    'value'=>$labels,
                    'label'=>$group
                );
            }
        }

        return $options;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => Table::TYPE_VARCHAR,
                'nullable' => true,
                'comment' => 'Cocote' . $attributeCode . ' column',
            ],
        ];
    }

    public function getValuesFromApi($fieldId) {

        $json = file_get_contents($this->feedUrl);
        $obj = json_decode($json);
        $fields=$obj->Response->fields;

        $valuesArray=array();

        foreach($fields as $field) {
            if($field->identifier==$fieldId) {
                foreach($field->availableValues as $value) {
                    if(isset($value->value)) {
                        $valuesArray[$value->label]=$value->value;
                    }
                    else {
                        foreach($value->children as $child) {
                            $valuesArray[$value->label][$child->label]=$child->value;
                        }
                    }
                }
            }
        }
        return $valuesArray;
    }

    public function getValuesFromCache($fieldId) {

        $lifetime=3600*24;

        $cachedValues=$this->cacheType->load('cocote_values_'.$fieldId);
        if($cachedValues) {
            return unserialize(stripslashes($cachedValues));
        }

        $apiValues= $this->getValuesFromApi($fieldId);
        $cachedValues=addslashes(serialize($apiValues));
        $this->cacheType->save($cachedValues,'cocote_values_'.$fieldId,[\Cocote\Feed\Model\Cache\Type::CACHE_TAG],$lifetime);

        return $apiValues;
    }



}