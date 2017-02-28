<?php
/**
 * Created by PhpStorm.
 * User: vjvan
 * Date: 1/4/2017
 * Time: 09:49
 */
namespace Biblio\Model;

use DomainException;
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\I18n\Filter\NumberFormat;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator\StringLength;

class Biblio implements InputFilterAwareInterface
{
    public $bibliog_id;
    public $edited;
    public $year;
    public $title;
    public $journal;
    public $issue;
    public $pages;
    public $publish_info;
    public $authors;
    public $position;

    private $inputFilter;

    public function exchangeArray(array $data)
    {
        $this->bibliog_id   = !empty($data['bibliog_id']) ? $data['bibliog_id'] : null;
        $this->edited       = !empty($data['edited']) ? $data['edited'] : 0;
        $this->year         = !empty($data['year']) ? $data['year'] : null;
        $this->title        = !empty($data['title']) ? $data['title'] : null;
        $this->journal      = !empty($data['journal']) ? trim($data['journal']) : null;
        $this->issue        = !empty($data['issue']) ? $data['issue'] : null;
        $this->pages        = !empty($data['pages']) ? $data['pages'] : null;
        $this->series       = !empty($data['series']) ? $data['series'] : null;
        $this->series_vol   = !empty($data['series_vol']) ? $data['series_vol'] : null;
        $this->publish_info = !empty($data['publish_info']) ? $data['publish_info'] : null;
        // Convert data into author objects 
        if (!empty($data['authors'])) {
            foreach ($data['authors'] as $key => $authorInfo) {
                if (is_array($authorInfo)) {
                    $author = new Author();
                    $author->exchangeArray($authorInfo);
                    $this->authors[$key] = $author;
                }
            }
        }
        if (empty($this->authors) && isset($data['author_names']) && isset($data['author_ids'])) {
            if (strpos($data['author_ids'], ',')) {
                $authorIds = explode(',', $data['author_ids']);
                $authorNames = explode('. ,', $data['author_names']);
                if (count($authorNames) === count($authorIds)) {
                    for ($i = 0; $i < count($authorIds); $i++) {
                        $author = new Author();
                        if ($i == (count($authorIds) - 1)) {
                            $authorName = $authorNames[$i];
                        } else {
                            $authorName = $authorNames[$i] . '.';
                        }
                        $author->exchangeArray([
                            'author_id' => $authorIds[$i],
                            'author_name' => $authorName
                        ]);
                        $authors[] = $author;
                    }
                    $this->authors = $authors;
                }
            } else {
                $singleAuthor = new Author();
                $singleAuthor->exchangeArray([
                    'author_id' => $data['author_ids'],
                    'author_name' => $data['author_names'],
                ]);
                $this->authors = [$singleAuthor];
            }
        }

        return $this;
    }

    public function getArrayCopy()
    {
        return [
            'bibliog_id' => $this->bibliog_id,
            'edited' => $this->edited,
            'year' => $this->year,
            'title' => $this->title,
            'issue' => $this->issue,
            'pages' => $this->pages,
            'series' => $this->series,
            'series_vol' => $this->series_vol,
            'publish_info' => $this->publish_info,
            'authors' => $this->authors,
        ];
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException(sprintf(
            '%s does not allow injection of an alternate input filter',
            __CLASS__
        ));
    }

    public function getInputFilter()
    {
        if ($this->inputFilter) {
            return $this->inputFilter;
        }

        $inputFilter = new InputFilter();

        $inputFilter->add([
            'name' => 'bibliog_id',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);
        $inputFilter->add([
            'name' => 'year',
            'required' => true,
            'filters' => [
                [
                    'name' => NumberFormat::class,
                    'option' => [
                        'locale' => 'en_US'
                    ]
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'title',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
            ],
        ]);
        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }
}