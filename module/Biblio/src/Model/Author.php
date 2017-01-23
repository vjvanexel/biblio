<?php
/**
 * Created by PhpStorm.
 * User: vjvan
 * Date: 1/6/2017
 * Time: 10:27
 */
namespace Biblio\Model;

use DomainException;
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator\StringLength;

class Author implements InputFilterAwareInterface
{
    public $author_id;
    public $author_name;

    private $inputFilter;

    public function exchangeArray(array $data)
    {
        $this->author_id = $data['author_id'] ? $data['author_id'] : null;
        $this->author_name = $data['author_name'] ? $data['author_name'] : null;
    }

    public function getArrayCopy()
    {
        return [
            'author_id' => $this->author_id,
            'author_name' => $this->author_name,
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
            'name' => 'author_id',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);

        $inputFilter->add([
            'name' => 'author_name',
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