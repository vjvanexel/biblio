<?php
/**
 * Created by PhpStorm.
 * User: vjvan
 * Date: 1/4/2017
 * 
 * Time: 10:00
 */
namespace Biblio\Model;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Biblio\Model\Biblio;
use Biblio\Model\Author;
use Biblio\Model\AuthorTable;
use Zend\Stdlib\ArrayUtils;

class BiblioTable
{
    private $bibEntriesGateway;
    private $bibAuthCrossGateway;
    private $authorTable;
    private $authorCrossGateway;

    public function __construct(TableGateway $bibEnriesTableGateway, TableGateway $bibAuthCrTableGateway, AuthorTable $authorTable, TableGateway $authorCrossTG)
    {
        $this->bibEntriesGateway = $bibEnriesTableGateway;
        $this->bibAuthCrossGateway = $bibAuthCrTableGateway;
        $this->authorTable = $authorTable;
        $this->authorCrossGateway = $authorCrossTG;
    }

    public function fetchAll()
    {
        return $this->bibAuthCrossGateway->select();
    }

    /**
     * Get bibliographic item(s)
     *
     * @param null $biblioId
     * @return array $biblioReferences  Array containing Biblio object, abbreviated and full length bibliographic info.
     */
    public function getBibliography($biblioId = null)
    {
        // Get bibliographic item data from database.
        $mainResultSet = $this->bibEntriesGateway->select(
            function(Select $select) use ($biblioId) {
                $authorNamesSelect = new Select('biblio_author_cross');
                $authorNamesSelect->join('authors', 'authors.author_id = biblio_author_cross.author_id', [
                    'author_names' => new Expression('GROUP_CONCAT(authors.author_name ORDER BY biblio_author_cross.position)'),
                    'author_ids' => new Expression('GROUP_CONCAT(authors.author_id ORDER BY biblio_author_cross.position)')
                ], Select::JOIN_LEFT)
                    ->group('biblio_author_cross.bibliog_id');
                $select->join(
                    ['c' => $authorNamesSelect], 'c.bibliog_id = bibliog_entries.bibliog_id', ['author_names','author_ids'], Select::JOIN_LEFT
                );
                if (!empty($biblioId)) {
                    foreach ($biblioId as $condition) {
                        $select->where($condition);
                    }
                }
            }
        );

        // Convert database data to abbreviated and full length bibliographic description.
        // TODO: refactor & add possibility to use different templates to transform data to different bibliography standards
        $biblioCollection = array();
        $biblioReferences = array();
        foreach ($mainResultSet as $biblio) {
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
            } elseif ($authorNumber == 0 && $bibliographicItem->edited == 1) {
                $abbrevReference = $biblio->title;
                $fullAuthors = $biblio->title;
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

    /**
     * Save bibliographic item.
     *
     * @param \Biblio\Model\Biblio $biblio
     */
    public function saveBibliography(Biblio $biblio)
    {
        // Separate author and bibliographic item
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

        $entryResult = 0;
        if (empty($biblio->bibliog_id)) {
            $entryResult = $this->bibEntriesGateway->insert($entriesData);
            $biblio->bibliog_id = $this->bibEntriesGateway->adapter->driver->getLastGeneratedValue();
            if (! $entryResult == 0 && !empty($biblio->authors)) {
                $this->saveAuthors($biblio);
            }
        } else {
            unset($entriesData['bibliog_id']);
            $this->bibEntriesGateway->update($entriesData, ['bibliog_id' => $biblio->bibliog_id]);

            $storedAuthorsResult = $this->authorCrossGateway->select(['bibliog_id' => $biblio->bibliog_id]);
            $storedAuthors = $storedAuthorsResult->toArray();
            $this->updateAuthors($biblio, $storedAuthors);
        }
    }

    /**
     * Save new and updated author information
     */
    private function saveAuthors($biblio) {
        $i = 1;
        foreach ($biblio->authors as $authorObj) {
            if (empty($authorObj->author_id)) {
                $this->authorTable->saveAuthor($authorObj);
                $authorObj->author_id = $this->authorTable->driver->adapter->getLastGeneratedValue();
            }
            $authCrossPartialResult = $this->authorCrossGateway->insert([
                'bibliog_id' => $biblio->bibliog_id,
                'author_id' => $authorObj->author_id,
                'position' => $i,
            ]);
        }
    }

    /**
     * Compare authors information from form with the stored data  
     * and create/update/delete database entries.
     */
    private function updateAuthors($biblio, $storedAuthors) {
        $authorsPositions = [];
        foreach ($storedAuthors as $storedAuthor) {
            $authorsPositions[$storedAuthor['author_id']] = $storedAuthor['position'];
        }
        for ($i = 0, $size = count($biblio->authors); $i< $size; $i++) {
            $author = $biblio->authors[$i];
            if (array_key_exists($author->author_id, $authorsPositions)) {
                if ($i+1 != $authorsPositions[$author->author_id]) {
                    $this->authorCrossGateway->update(['position' => $i+1], [
                        'bibliog_id' => $biblio->bibliog_id,
                        'author_id' => $author->author_id
                    ]);
                }
                unset($authorsPositions[$author->author_id]);
            } else {
                $this->authorCrossGateway->insert([
                    'bibliog_id' => $biblio->bibliog_id,
                    'author_id' => $author->author_id,
                    'position' => $i+1,
                ]);
            }
            if (!empty($authorsPositions)) {
                foreach ($authorsPositions as $authorId => $position) {
                    $this->authorCrossGateway->delete([
                        'bibliog_id' => $biblio->bibliog_id,
                        'author_id' => $authorId
                    ]);
                }
            }
        }
    }
}