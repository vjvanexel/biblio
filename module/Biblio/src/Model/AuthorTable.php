<?php
/**
 * Created by PhpStorm.
 * User: vjvan
 * Date: 1/6/2017
 * Time: 11:37
 */
namespace Biblio\Model;

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
        if (array_key_exists('position', $data)) {
            unset($data['position']);
        }
        if (!isset($data['author_id'])) {
            $this->authorsGateway->insert($data);
            $authorId = $this->authorsGateway->adapter->driver->getLastGeneratedValue();
        } else {
            $authorId = $author->author_id;
            $this->authorsGateway->update(['author_name' => $data['author_name']], ['author_id' => $data['author_id']]);
        }
        return $authorId;
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