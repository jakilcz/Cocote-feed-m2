<?php
namespace Cocote\Feed\Controller\Adminhtml\Cocote;

use Magento\Framework\Controller\ResultFactory;

class Generate extends \Magento\Backend\App\Action
{

    protected $helper;
    protected $resultRedirect;
    protected $messageManager;


    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\ResultFactory $result,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Cocote\Feed\Helper\Data $helper
    ) {
        $this->helper=$helper;
        $this->resultRedirect = $result;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }


    public function execute()
    {
        try {
            $validate=$this->helper->generateFeed();
            $this->messageManager->addSuccessMessage(__("Generating done"));
            $validateWarnings=[];

            if (!isset($validate['targets'])) {
                $validateWarnings[]='le champ cible n\'est pas renseigné * . Si tout ou partie de vos produits
                correspondent a une cible définie, nous vous conseillons de remplir ce champ qui vous apportera
                une meilleure visibilité des offres correspondantes';
            }
            if (!isset($validate['types'])) {
                $validateWarnings[]='le champ type de produit n\'est pas renseigné * . Si tout ou partie de vos produits
                correspondent a un type bien précis, nous vous conseillons de remplir ce champ qui vous apportera une
                meilleure visibilité des offres correspondantes';
            }
            if (!isset($validate['labels'])) {
                $validateWarnings[]='le champ label n\'est pas renseigné * . Si tout ou partie de vos produits sont
                labellisés (ex: \'made in france\') ou peuvent porter des mentions, nous vous conseillons de remplir
                ce champ qui vous apportera une meilleure visibilité des offres correspondantes. Un label, une
                mention manque? n\'hésitez pas a nous le faire savoir que nous puissions l\'ajouter';
            }

            if(sizeof($validateWarnings)) {
                $warningText='Les suggestions suivantes ont été détectes:';
                $this->messageManager->addWarningMessage($warningText);
                foreach($validateWarnings as $warning) {
                    $this->messageManager->addWarningMessage($warning);
                }

                $warningText='*: Avez vous bien renseigné / enregistré une valeur puis cliqué sur le bouton correspondant \'Commencez des maintenant\' en dessous du champ?';
                //$this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($message)
                $this->messageManager->addWarningMessage($warningText);
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
