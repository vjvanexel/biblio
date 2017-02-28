<?php
/**
 * Created by PhpStorm.
 * User: vjvan
 * Date: 1/4/2017
 * Time: 10:00
 */
namespace Biblio\Model;

use RuntimeException;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Update;
use Zend\Db\TableGateway\TableGateway;
use Biblio\Model\Biblio;
use Biblio\Model\BiblioTable;
use Biblio\Model\Author;
use Biblio\Model\Reference;

class ReferencesTable
{
    private $referencesGateway;
    private $biblioTable;

    public function __construct(TableGateway $referencesTableGateway, BiblioTable $biblioTable)
    {
        $this->referencesGateway = $referencesTableGateway;
        $this->biblioTable = $biblioTable;
    }

    public function fetchAll()
    {
        return $this->referencesGateway->select();
    }

    public function getReference($referenceId = null)
    {
        if ($referenceId) {
            $referenceResults = $this->referencesGateway->select(function (Select $refSelect) use ($referenceId) {
                $refSelect->join('reference_types', 'reference_types.ref_id = references.ref_type', Select::SQL_STAR, Select::JOIN_LEFT);
                $refSelect->where(array('references.ref_id' => $referenceId));
            });
        } else {
            $referenceResults = $this->referencesGateway->select(function(Select $refSelect) {
                $refSelect->join('reference_types', 'reference_types.ref_id = references.ref_type',
                    ['ref_type', 'type_option_table', 'select_name'], Select::JOIN_LEFT);
            });
        }
        $referenceCollection = [];
        foreach ($referenceResults as $reference) {
            $biblioItem = $this->biblioTable->getBibliography($reference->bibliog_id);
            $reference->bibliog_item = $biblioItem[$reference->bibliog_id];
            $referenceCollection[] = $reference;
        }
        return $referenceCollection;
    }

    /**
     * Get reference information based on reference type and type option
     *
     * @param $siteId
     * @return array
     */
    public function getOptionReferences($referenceType, $referenceOptionId)
    {
        $referenceResults = $this->referencesGateway->select(function (Select $select) use ($referenceType, $referenceOptionId) {
            $select->where(array('ref_type' => $referenceType, 'ref_type_id' => $referenceOptionId));
        });
        
        $biblioCollection = [];
        foreach ($referenceResults as $reference) {
            $biblioItem = $this->biblioTable->getBibliography($reference->bibliog_id);
            $biblioCollection[$reference->bibliog_id] = $biblioItem[$reference->bibliog_id];
            $biblioCollection[$reference->bibliog_id]['ref_note'] = $reference->bibliog_note;
        }
        return $biblioCollection;
    }

    /**
     * Save reference information
     */
    public function saveReference(Reference $reference)
    {
        $data = $reference->getArrayCopy();
        $refId = $data['ref_id'];
        unset($data['bibliog_item']);
        unset($data['ref_id']);
        if ($refId) {
            $this->referencesGateway->update($data, ['ref_id' => $refId]);
        } else {
            $this->referencesGateway->insert($data);
        }
    }
}