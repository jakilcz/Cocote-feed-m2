<?php

namespace Cocote\Feed\Setup;

use Magento\Eav\Setup\EavSetup; 
use Magento\Eav\Setup\EavSetupFactory /* For Attribute create  */;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Catalog\Setup\CategorySetupFactory;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    private $categorySetupFactory;
    private $attributeSetFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        CategorySetupFactory $categorySetupFactory
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->categorySetupFactory = $categorySetupFactory;
        /* assign object to class global variable for use in other class methods */
    }
 
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {



        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $attributeSets = $objectManager->create(\Magento\Catalog\Model\Product\AttributeSet\Options::class);
        $attributeSetIds=[];
        foreach($attributeSets->toOptionArray() as $attrSet) {
            $attributeSetIds[]=$attrSet['value'];
        }

        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);

        $groupName = 'Cocote';


        foreach($attributeSetIds as $attributeSetId) {
            $categorySetup->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 60);
        }



        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $attributesToInstall=[
            ['code'=>'cocote_categories','label'=>'Cocote categories','source'=>'Cocote\Feed\Model\Config\Source\Categories','type'=>'select'],
            ['code'=>'cocote_labels','label'=>'Cocote labels','source'=>'Cocote\Feed\Model\Config\Source\Labels','type'=>'multiselect'],
            ['code'=>'cocote_targets','label'=>'Cocote targets','source'=>'Cocote\Feed\Model\Config\Source\Targets','type'=>'multiselect'],
            ['code'=>'cocote_tags','label'=>'Cocote tags','source'=>'Cocote\Feed\Model\Config\Source\Tags','type'=>'multiselect'],
            ['code'=>'cocote_producer','label'=>'Cocote producer','source'=>'Cocote\Feed\Model\Config\Source\Producer','type'=>'select'],
            ['code'=>'cocote_state','label'=>'Cocote state','source'=>'Cocote\Feed\Model\Config\Source\State','type'=>'select'],
            ['code'=>'cocote_salestypes','label'=>'Cocote salestypes','source'=>'Cocote\Feed\Model\Config\Source\Salestypes','type'=>'multiselect'],
            ['code'=>'cocote_payment_online','label'=>'Cocote payment online','source'=>'Cocote\Feed\Model\Config\Source\Payments','type'=>'multiselect'],
            ['code'=>'cocote_payment_onsite','label'=>'Cocote payment online','source'=>'Cocote\Feed\Model\Config\Source\Payments','type'=>'multiselect'],
        ];


        foreach($attributesToInstall as $attributeData) {
            $data=[
                'group' => 'Cocote',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => $attributeData['label'],
                'input' => $attributeData['type'],
                'class' => '',
                'source' => $attributeData['source'],
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false
            ];
            if($attributeData['type']=='multiselect') {
                $data['backend'] ='Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend';
            }

            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,$attributeData['code']);
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $attributeData['code'], $data
            );
        }

        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'cocote_allowed_distance');
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'cocote_allowed_distance',
            [
                'group' => 'Cocote',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Cocote allowed distance',
                'input' => 'text',
                'class' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false
            ]
        );
    }
}