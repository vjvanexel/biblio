<?php
/**
 * Created by PhpStorm.
 * User: vjvan
 * Date: 1/6/2017
 * Time: 11:37
 */
namespace Biblio\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGateway;

class AuthorTable
{
    public $authorsGateway;
    
    public function __construct(TableGateway $authorsTableGateway)
    {
        $this->authorsGateway = $authorsTableGateway;
    }

    public function saveAuthor(Author $author)
    {
        $data = $author->getArrayCopy();
        if (!$data['author_id']) {
            $data['author_id'] = 'new_' . $data['author_name'];
            // TODO: Add auto-increment integer id field to authors. 
            // TODO: When adding new author check whether authors with similar name already exists.
            $this->authorsGateway->insert($data);
        } else {
            $this->authorsGateway->update(['author_name' => $data['author_name']], ['author_id' => $data['author_id']]);
        }
    }

    public function getAuthors($id = null)
    {
        if (!$id) {
            $authorCollection = $this->authorsGateway->select();
        } else {
            $authorCollection = $this->authorsGateway->select(['author_id' => $id]);
        }
        return $authorCollection;
    }

}