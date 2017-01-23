<?php
/**
 * Created by PhpStorm.
 * User: vjvan
 * Date: 12/1/2016
 * Time: 12:01
 */
namespace Biblio\Controller;

use Biblio\Model\Biblio;
use Biblio\Model\Author;
use Biblio\Model\Reference;
use Biblio\Model\BiblioTable;
use Biblio\Model\AuthorTable;
use Biblio\Model\ReferencesTable;
use Biblio\Form\BiblioForm;
use Biblio\Form\AuthorForm;
use Biblio\Form\ReferenceForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ArrayUtils;

class BiblioController extends AbstractActionController
{
    private $biblioTable;
    private $authorTable;
    private $referencesTable;

    public function __construct(BiblioTable $biblioTable, AuthorTable $authorTable, ReferencesTable $referencesTable)
    {
        $this->biblioTable = $biblioTable;
        $this->authorTable = $authorTable;
        $this->referencesTable = $referencesTable;
    }

    public function onDispatch(MvcEvent $e)
    {
        // Call the base class' onDispatch() first and grab the response
        $response = parent::onDispatch($e);

        // Set alternative layout
        $this->layout()->setTemplate('biblio/layout');

        // Return the response
        return $response;
    }

    public function indexAction() {
        $fullBibliography = $this->biblioTable->getBibliography();

        return array(
            'bibliography' => $fullBibliography
        );
    }

    public function addAction()
    {
        $form = new BiblioForm();
        $form->get('submit')->setValue('Add');
        $request = $this->getRequest();
        if (! $request->isPost()) {
            return ['form' => $form];
        }
        $newBiblio = new Biblio();
        $form->setInputFilter($newBiblio->getInputFilter());
        $data = $request->getPost();
        $newBiblio->exchangeArray($data->toArray());
        $form->bind($newBiblio);
        $form->setData($data);

        if (! $form->isValid()) {
            return ['form' => $form];
        }

        $this->biblioTable->saveBibliography($newBiblio);
        return $this->redirect()->toRoute('biblio', ['action' => 'index']);
    }
    
    public function addAuthorAction()
    {
        $authors = $this->authorTable->getAuthors();

        $form = new AuthorForm();
        $form->get('submit')->setValue('Add');
        $request = $this->getRequest();
        if (! $request->isPost()) {
            return ['form' => $form];
        }
        $author = new Author();
        $form->setInputFilter($author->getInputFilter());
        $data = $request->getPost();
        $data = ArrayUtils::iteratorToArray($data);
        $author->exchangeArray($data);
        $form->bind($author);
        $form->setData($data);

        if (! $form->isValid()) {
            return [
                'form' => $form,
                'authors' => $authors
            ];
        }

        $this->authorTable->saveAuthor($author);
        return $this->redirect()->toRoute('biblio', ['action' => 'index']);
    }
    
    public function authorsAction()
    {
        $authors = $this->authorTable->getAuthors();
        return [
            'authors' => $authors
        ];
    }
    
    public function editAuthorAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        if (0 === $id) {
            return $this->redirect()->toRoute('biblio', ['action' => 'addAuthor']);
        }
        try {
            $authors = $this->authorTable->getAuthors($id);
            foreach ($authors as $authorResult) {
                $author = $authorResult;
            }
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('biblio', ['action' => 'authors']);
        }
        $form = new AuthorForm();
        $form->bind($author);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        $viewData = ['id' => $id, 'form' => $form];

        if (! $request->isPost()) {
            return $viewData;
        }

        $form->setInputFilter($author->getInputFilter());
        $form->setData($request->getPost());

        if (! $form->isValid()) {
            return $viewData;
        }
        $this->authorTable->saveAuthor($author);
        return $this->redirect()->toRoute('biblio', ['action' => 'authors']);
    }
    
    public function editAction()
    {
        // $id = (int) $this->params()->fromRoute('id', 0);
        $id = $this->params()->fromRoute('id', 0);

        if (0 === $id) {
            return $this->redirect()->toRoute('site', ['action' => 'addBiblio']);
        }
        try {
            $biblioRow = $this->biblioTable->getBibliography($id);
            $biblio = $biblioRow[$id]['object'];
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('biblio', ['action' => 'index']);
        }
        $form = new BiblioForm();
        $form->bind($biblio);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        $viewData = ['id' => $id, 'form' => $form];

        if (! $request->isPost()) {
            return $viewData;
        }

        $form->setInputFilter($biblio->getInputFilter());
        $form->setData($request->getPost());

        if (! $form->isValid()) {
            return $viewData;
        }
        $this->biblioTable->saveBibliography($biblio);
        return $this->redirect()->toRoute('biblio', ['action' => 'index']);
    }

}