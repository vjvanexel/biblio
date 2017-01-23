<?php
/**
 * Created by PhpStorm.
 * User: vjvan
 * Date: 1/4/2017
 * 
 * Time: 10:00
 */
namespace Biblio\Model;

use RuntimeException;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Biblio\Model\Biblio;
use Biblio\Model\Author;
use Biblio\Model\AuthorTable;

class BiblioTable
{
    private $bibEntriesGateway;
    private $bibAuthCrossGateway;
    private $authorTable;

    public function __construct(TableGateway $bibEnriesTableGateway, TableGateway $bibAuthCrTableGateway, AuthorTable $authorTable)
    {
        $this->bibEntriesGateway = $bibEnriesTableGateway;
        $this->bibAuthCrossGateway = $bibAuthCrTableGateway;
        $this->authorTable = $authorTable;
    }

    public function fetchAll()
    {
        return $this->bibAuthCrossGateway->select();
    }

    public function getBibliography($biblioId = null)
    {
        $biblioSql = $this->bibAuthCrossGateway->getSql();
        $resultSet = $this->bibAuthCrossGateway->select(
            function(Select $select) use ($biblioId) {
                $select
                    ->join('bibliog_entries', 'bibliog_entries.bibliog_id = biblio_author_cross.bibliog_id',
                    array('bibliog_id', 'edited', 'year', 'title', 'journal', 'issue', 'pages', 'publish_info', 'series', 'series_vol'), Select::JOIN_RIGHT)
                    ->group('bibliog_entries.bibliog_id')
                    ->join('authors', 'biblio_author_cross.author_id = authors.author_id', array('author_name'))
                    ->columns(array('author_id', 'author_names' => new Expression('GROUP_CONCAT(authors.author_name ORDER BY biblio_author_cross.position)'),
                        'author_ids' => new Expression('GROUP_CONCAT(authors.author_id ORDER BY biblio_author_cross.position)')));
                if ($biblioId) {
                    $select->where(array('bibliog_entries.bibliog_id' => $biblioId));
                }
            }
        );
        $biblioCollection = array();
        $biblioReferences = array();
        foreach ($resultSet as $biblio) {
            $biblioCollection[$biblio->bibliog_id] = $biblio;
        }
        foreach ($biblioCollection as $bibliographicId => $bibliographicItem) {
            $biblioReferences[$bibliographicId]['object'] = $bibliographicItem;
            if ($bibliographicItem->journal) {
                try {
                    $journal = $biblioCollection[$bibliographicItem->journal];
                    $bibliographicItem->journal = $journal->title;
                } catch (\Exception $e) {
                    continue;
                }
            }
            $authorNumber = count($bibliographicItem->authors);
            if ($authorNumber > 1) {
                if ($authorNumber === 2) {
                    $fullAuthors = trim($bibliographicItem->authors[0]->author_name) . ' & ' . $bibliographicItem->authors[1]->author_name;
                    $abbrevReference = $fullAuthors . ', ' . $bibliographicItem->year;
                } else {
                    $i = 0;
                    foreach ($bibliographicItem->authors as $author) {
                        $fullAuthorsString = '';
                        if ($i == 0) {
                            $fullAuthorsString .= trim($author->author_name) . ', ';
                        } else {
                            $authorNameElements = explode(',', trim($author->author_name));
                            $fullAuthorsString .= $authorNameElements[1] . ' ' . $authorNameElements[0];
                        }
                        $i++;
                    }
                    $fullAuthors = $fullAuthorsString;
                    $abbrevReference = $bibliographicItem->authors[0]->author_name . '<i> et al.</i>, ' . $bibliographicItem->year;
                }
            } else {
                $fullAuthors = $bibliographicItem->authors[0]->author_name;
                $abbrevReference = $bibliographicItem->authors[0]->author_name . ', ' . $bibliographicItem->year;
            }
            $biblioReferences[$bibliographicId]['abbrev'] = $abbrevReference;

            if ($bibliographicItem->edited) {
                $fullReference = $fullAuthors;
                if ($authorNumber > 1) {
                    $fullReference .= '(eds.) ' . $bibliographicItem->year . '. ';
                } else {
                    $fullReference .= '(ed.) ' . $bibliographicItem->year . '. ';
                }
            } else {
                $fullReference = $fullAuthors . ' ' . $bibliographicItem->year . '. ';
            }
            if ($bibliographicItem->journal) {
                $fullReference .= '"' . $bibliographicItem->year . '", <i>' . $bibliographicItem->journal . '</i>: ';
                $fullReference .= $bibliographicItem->issue . ': ' . $bibliographicItem->pages;
            } else {
                $fullReference .= '<i>' . $bibliographicItem->title . '</i> ';
            }
            if ($bibliographicItem->series) {
                $fullReference .= '(' . $bibliographicItem->series . ' ' . $bibliographicItem->series_vol . ') ';
            }
            if ($bibliographicItem->publish_info) {
                $fullReference .= $bibliographicItem->publish_info . '. ';
            }
            $biblioReferences[$bibliographicId]['full'] = $fullReference;
        }

        return $biblioReferences;
    }


    public function saveBibliography(Biblio $biblio)
    {
        $dataRaw = $biblio->getArrayCopy();
        $biblio_id = $dataRaw['bibliog_id'];
        $entriesData = array(
            'bibliog_id' => $biblio->bibliog_id,
            'edited' => $biblio->edited,
            'year' => $biblio->year,
            'title' => $biblio->title,
            'issue' => $biblio->issue,
            'pages' => $biblio->pages,
            'series' => $biblio->series,
            'series_vol' => $biblio->series_vol,
            'publish_info' => $biblio->publish_info,

        );
        $authorsData = array(
            'author_id' => $biblio->author_id,
            'author_ids' => $biblio->author_ids,
            'author_name' => $biblio->author_name,
            'authors' => $biblio->authors,
            'position' => $biblio->position
        );
        $authCrossData = array(
            'bibliog_id' => $biblio_id,
            'authors' => $biblio->authors
        );
        //$this->bibEntriesGateway->update($entriesData, ['bibliog_id' => $biblio->bibliog_id]);
        // $this->bibAuthCrossGateway->update($authCrossData, ['bibliog_id' => $biblio->bibliog_id]);
        // Add update authors info later: new authors, new order of authors, etc.
        //$this->authorsGateway->update($authorsData, ['author_id' => $biblio->author_id]);

        $entryResult = 1;
        if (! $this->getBibliography($biblio_id)) {
            //$entryResult = $this->bibEntriesGateway->insert($entriesData);
            //return;
        }
        $authCrossResult = [];
        if (! $entryResult == 0) {
            foreach ($authCrossData['authors'] as $authorObj) {
                if (count($this->authorTable->getAuthors($authorObj->author_id)) == 0) {
                    $this->authorTable->saveAuthor($authorObj);
                }
                $authCrossPartialResult = $this->bibAuthCrossGateway->insert([
                    'bibliog_id' => $biblio_id,
                    'author_id' => $authorObj->author_id
                ]);
                if (! $authCrossPartialResult == 0) {
                    $authCrossResult['success'][$biblio_id] = $authorObj->author_id;
                } else  {
                    $authCrossResult['failure'][$biblio_id] = $authorObj->author_id;
                }
            }
        }
        //$this->tableGateway->update($data, ['route_id' => $data['route_id']]);

        /*$update1 = new Update('biblio_author_cross');
        $update1->where(array('biblio_author_cross.bibliog_id' => $biblio_id))
            ->join('bibliog_entries', 'bibliog_entries.bibliog_id = biblio_author_cross.bibliog_id',
                array('bibliog_id', 'edited', 'year', 'title', 'journal', 'issue', 'pages', 'publish_info', 'series', 'series_vol'), Select::JOIN_RIGHT)
            ->join('authors', 'biblio_author_cross.author_id = authors.author_id', array('author_name'))
            ->set($data);
        $entriesJoin = array(
            'name' => 'bibliog_entries',
            'on' => 'bibliog_entries.bibliog_id = biblio_author_cross.bibliog_id',
            'type' => Select::JOIN_RIGHT
        );
        $authorsJoin = array(
            'name' => 'authors',
            'on' => 'biblio_author_cross.author_id = authors.author_id'
        );
        $this->bibAuthCrossGateway->update($data, array('biblio_author_cross.bibliog_id' => $biblio_id), array($entriesJoin, $authorsJoin));
        */$break = 'halt';
    }

}