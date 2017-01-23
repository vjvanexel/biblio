<?php
/**
 * Created by PhpStorm.
 * User: vjvan
 * Date: 1/6/2017
 * Time: 10:23
 */
namespace Biblio\Form;

use Biblio\Form\PointsFieldset;
use Zend\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter\InputFilter;
use Biblio\Form\RouteFieldset;

class AuthorForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct($name = null);

        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        $this->setInputFilter(new InputFilter());

        $this->add([
            'name' => 'author_id',
            'type' => 'text',
            'attributes' => [
                'id' => 'author_id',
                'class' => 'col-sm-9 col-md-9 col-lg-9',
                'readonly' => true,
            ],
            'options' => [
                'label' => 'Author ID (will be auto-generated): ',
                'label_attributes' => [
                    'class' => 'col-sm-3 col-md-3 col-lg-3'
                ]
            ],

        ]);$this->add([
            'name' => 'author_name',
            'type' => Element\Select::class,
            'attributes' => [
                'id' => 'combobox',
                'class' => 'col-sm-3 col-md-3 col-lg-3'
            ],
            'options' => [
                'label' => 'Author name: ',
                'label_attributes' => [
                    'class' => 'col-sm-3 col-md-3 col-lg-3'
                ],
            ],
        ]);
        $this->add([
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => [
                'value' => 'Go',
                'id'    => 'submitbutton',
                'class' => 'btn btn-primary'
            ],
        ]);
    }
}
