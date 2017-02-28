<?php
/**
 * Created by PhpStorm.
 * User: vjvan
 * Date: 1/6/2017
 * Time: 10:23
 */
namespace Biblio\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;

class ReferenceForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct($name = null);

        $this->setAttribute('method', 'post');
        $this->setInputFilter(new InputFilter());

        $this->add([
            'name' => 'ref_id',
            'type' => 'text',
            'options' => [
                'label' => 'Reference ID (will be auto-generated): ',

            ],
            'attributes' => [
                'readonly' => true,
            ]
        ]);
        $this->add([
            'name' => 'ref_type',
            'type' => 'text',
            'options' => [
                'label' => 'Reference type: ',
            ]
        ]);
        $this->add([
            'name' => 'ref_type_id',
            'type' => 'text',
            'options' => [
                'label' => 'Reference Type Option: ',
            ]
        ]);
        $this->add([
            'name' => 'bibliog_id',
            'type' => Element\Select::class,
            'attributes' => [
                'id' => 'bibliography',
                'class' => 'combobox-get'
            ],
            'options' => [
                'label' => 'Bibliography: ',
            ]
        ]);
        $this->add([
            'name' => 'bibliog_note',
            'type' => 'text',
            'options' => [
                'label' => 'Reference note/details: ',
            ]
        ]);
        $this->add([
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => [
                'value' => 'Go',
                'id'    => 'submitbutton',
            ],
        ]);
    }
}
