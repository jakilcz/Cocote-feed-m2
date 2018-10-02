<?php
namespace Cocote\Feed\Controller\Adminhtml\Cocote;

use Magento\Framework\Controller\ResultFactory;

class Syncprods extends \Magento\Backend\App\Action
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
    protected $resultJsonFactory;

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
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory

    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeConfig;
        $this->productVisibility = $productVisibility;
        $this->productCollectionFactory=$productCollectionFactory;
        $this->resultRedirect = $result;
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->resultJsonFactory=$resultJsonFactory;
    }


    public function execute()
    {
        
       $attribute=$this->request->getParam('attr');
       $id=$this->request->getParam('id');
       $value=$this->request->getParam('value');
       if(is_array($value)) {
           $value=implode(',',$value);
       }

       $this->updateAttributeValue([$id],$attribute,$value);
       $message=__('Product updated');
       return  $this->resultJsonFactory->create()->setData(['msg' => $message]);
    }

    public function updateAttributeValue($prodIds,$attribute,$value) {
        set_time_limit(0); // unlimited max execution time

        $action = $this->_objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\Action');

        $storeId=0;

        foreach($prodIds as $productId) {
            if($productId) {
                $action->updateAttributes([$productId], [$attribute => $value], $storeId);
            }
        }
    }
}