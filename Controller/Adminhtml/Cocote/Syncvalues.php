<?php
namespace Cocote\Feed\Controller\Adminhtml\Cocote;

use Magento\Framework\Controller\ResultFactory;

class Syncvalues extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    protected $scopeConfig;
    protected $productCollectionFactory;
    protected $productVisibility;
    protected $resultRedirect;
    protected $messageManager;
    protected $request;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Framework\Controller\ResultFactory $result,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Request\Http $request

    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeConfig;
        $this->productVisibility = $productVisibility;
        $this->productCollectionFactory=$productCollectionFactory;
        $this->resultRedirect = $result;
        $this->messageManager = $messageManager;
        $this->request = $request;
    }


    public function execute()
    {
        $attribute=$this->request->getParam('attribute');

        $value=$this->scopeConfig->getValue('cocote/general/'.$attribute, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        try {
            $prodctIds=$this->getProductCollectionIds();
            $this->updateAttributeValue($prodctIds,$attribute,$value);
            $this->messageManager->addSuccessMessage(__($attribute." have been updated"));
        }

        catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
        }


        $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    public function getProductCollectionIds() {

        $collection = $this->productCollectionFactory->create();
        //$collection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);
        $collection->setVisibility($this->productVisibility->getVisibleInSiteIds());
        $ids=$collection->getAllIds();
        return $ids;

    }

    public function updateAttributeValue($prodIds,$attribute,$value) {
        $attributeName='cocote_'.$attribute;
        set_time_limit(0); // unlimited max execution time

        $action = $this->_objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\Action');

        $storeId=0;

        foreach($prodIds as $productId) {
            if($productId) {
                $action->updateAttributes([$productId], [$attributeName => $value], $storeId);
            }
        }
    }


}
?>
