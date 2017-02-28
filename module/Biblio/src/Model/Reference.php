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
use Zend\Filter\ToInt;
use Zend\I18n\Filter\NumberFormat;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator\StringLength;
use Zend\I18n\Validator\IsFloat;

class Reference implements InputFilterAwareInterface
{
    public $ref_id;
    public $bibliog_id;
    public $bibliog_item;
    public $bibliog_note;
    public $ref_type;
    public $ref_type_id;

    private $inputFilter;

    public function exchangeArray(array $data)
    {
        $this->ref_id       = $data['ref_id'] ? $data['ref_id'] : null;
        $this->bibliog_id   = isset($data['bibliog_id']) ? $data['bibliog_id'] : null;
        if (isset($data['bibliog_item'])) {
            $this->bibliog_item = $data['bibliog_item'] ? $data['bibliog_item'] : null;
        }
        $this->bibliog_note = $data['bibliog_note'] ? $data['bibliog_note'] : null;
        $this->ref_type     = isset($data['select_name']) ? $data['select_name'] : $data['ref_type'];
        $this->ref_type_id  = $data['ref_type_id'] ? $data['ref_type_id'] : null;

    }

    public function getArrayCopy()
    {
        return [
            'ref_id'        => $this->ref_id,
            'bibliog_id'    => $this->bibliog_id,
            'bibliog_item'  => $this->bibliog_item,
            'bibliog_note'  => $this->bibliog_note,
            'ref_type'      => $this->ref_type,
            'ref_type_id'   => $this->ref_type_id
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
            'name' => 'ref_id',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);
        $inputFilter->add([
            'name' => 'ref_type',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);
        $inputFilter->add([
            'name' => 'ref_type_id',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);
        $inputFilter->add([
            'name' => 'bibliog_id',
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
        $inputFilter->add([
            'name' => 'bibliog_note',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);
        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

}