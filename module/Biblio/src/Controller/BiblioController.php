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

    /**
     * Show a list of the entire bibliography. 
     * 
     * @return array
     */
    public function indexAction() {
        $fullBibliography = $this->biblioTable->getBibliography();

        return array(
            'bibliography' => $fullBibliography
        );
    }

    /**
     * Form to add a bibliographic item. 
     * 
     * @return array|\Zend\Http\Response
     */
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
        $data = $data->toArray();
        // Process form data to be loaded into objects.
        $data['journal'] = $data['journal'][0];
        if (isset($data['authors'])) {
            for ($i=0, $size=count($data['authors']); $i < $size; $i++) {
                $data['authors'][$i] = [
                    'author_id' => $data['authors'][$i],
                    'position' => $i+1
                ];
            }
        } else {
            $data['authors'] = [];
        }
        $newBiblio->exchangeArray($data);
        $form->bind($newBiblio);
        $form->setData($data);

        if (! $form->isValid()) {
            return ['form' => $form];
        }

        $this->biblioTable->saveBibliography($newBiblio);
        return $this->redirect()->toRoute('biblio', ['action' => 'index']);
    }

    /**
     * Form to add a new author.
     *
     * @return array|\Zend\Http\Response
     */
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
        return $this->redirect()->toRoute('biblio', ['action' => 'authors']);
    }

    /**
     * Show all authors.
     *
     * @return array
     */
    public function authorsAction()
    {
        $authors = $this->authorTable->getAuthors();
        return [
            'authors' => $authors
        ];
    }

    /**
     * Edit information of an existing author
     *
     * @return array|\Zend\Http\Response
     */
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

    /**
     * Update the information of an existing bibliographic item.
     *
     * @return array|\Zend\Http\Response
     */
    public function editAction()
    {
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

    /**
     * Show list of all references.
     */
    public function referencesAction() {
        $break = 'halt';
        $referenceCollection = $this->referencesTable->getReference();

        return array(
            'references' => $referenceCollection
        );
    }

    /**
     * Form to add a new reference.
     *
     * @return array|\Zend\Http\Response
     */
    public function addReferenceAction() {
        $form = new ReferenceForm();
        $form->get('submit')->setValue('Add');
        $request = $this->getRequest();
        if (! $request->isPost()) {
            return ['form' => $form];
        }
        $reference = new Reference();
        $form->setInputFilter($reference->getInputFilter());
        $data = $request->getPost();
        $data = ArrayUtils::iteratorToArray($data);
        // Update form data and load into objects
        if (isset($data['bibliograpy'])) {
            $bibliogId = $data['bibliography'][0];
            $data['bibliography'] = $this->biblioTable->getBibliography(["bibliog_entries.bibliog_id = $bibliogId"]);
        }
        $reference->exchangeArray($data);
        $form->bind($reference);
        $form->setData($data);

        if (! $form->isValid()) {
            return ['form' => $form];
        }

        $this->referencesTable->saveReference($reference);
        return $this->redirect()->toRoute('biblio', ['action' => 'references']);
    }

    /**
     * Edit the information of an existing reference.
     *
     * @return array|\Zend\Http\Response
     */
    public function editReferenceAction() {
        $id = $this->params()->fromRoute('id', 0);
        if (0 === $id) {
            return $this->redirect()->toRoute('biblio', ['action' => 'addReference']);
        }
        try {
            $references = $this->referencesTable->getReference($id);
            foreach ($references as $referenceResult) {
                $reference = $referenceResult;
            }
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('biblio', ['action' => 'references']);
        }
        $form = new ReferenceForm();
        $form->bind($reference);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        $viewData = ['id' => $id, 'form' => $form];

        if (! $request->isPost()) {
            return $viewData;
        }

        $form->setInputFilter($reference->getInputFilter());
        $form->setData($request->getPost());

        if (! $form->isValid()) {
            return $viewData;
        }
        $this->referencesTable->saveReference($reference);
        return $this->redirect()->toRoute('biblio', ['action' => 'references']);
    }

    /**
     * Provide a list of options for select combobox.
     *
     * @return array $options
     */
    public function optionsAjaxAction()
    {
        $options = [];

        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine("Content-type: application/json");

        // get parameters 
        $request = $this->getRequest();
        if ($request->isPost()) {
            $rawParams = $request->getPost();
            $params = ArrayUtils::iteratorToArray($rawParams);

        }
        switch($params['option_type']) {
            case 'authors':
                $authorsResult = $this->authorTable->getAuthors();
                $authors = $authorsResult->toArray();
                foreach ($authors as $author) {
                    $options[] = [$author['author_id'], $author['author_name']];
                }
                break;
            case 'journal':
                $biblioResult = $this->biblioTable->getBibliography(['edited = 1']);
                foreach ($biblioResult as $biblioItem) {
                    $biblio = $biblioItem['object'];
                    $options[] = [$biblio->bibliog_id, $biblio->title];
                }
                break;
            case 'bibliography':
                $biblioResult = $this->biblioTable->getBibliography();
                foreach ($biblioResult as $biblioItem) {
                    $biblio = $biblioItem['object'];
                    $options[] = [$biblio->bibliog_id, $biblio->title];
                }
                break;
        }

        $response->setContent(json_encode($options));
        return $response;
    }

    /**
     * Add a new option to the database
     *
     * @return \Zend\Stdlib\ResponseInterface $content  The new id and value of the added database entry.
     */
    public function newOptionAjaxAction()
    {
        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine("Content-type: application/json");

        // get parameters 
        $request = $this->getRequest();
        if ($request->isPost()) {
            $rawParams = $request->getPost();
            $params = ArrayUtils::iteratorToArray($rawParams);
            if ($params['option_type'] == 'authors') {
                $newAuthorName = $params['new_option'];
                $newAuthorName = trim($newAuthorName) . ' '; // Make sure that author name ends with space 
                $newAuthor = new Author();
                $newAuthor->exchangeArray([
                    'author_name' => $newAuthorName
                ]);
                $newOptionId = $this->authorTable->saveAuthor($newAuthor);
            }
            $content = json_encode([
                'option_type' => $params['option_type'],
                'new_option_id' => $newOptionId]);
            $response->setContent($content);
            return $response;
        }
    }
}