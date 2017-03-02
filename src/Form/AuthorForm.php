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
                'class' => 'col-sm-1 col-md-1 col-lg-1',
                'readonly' => true,
            ],
            'options' => [
                'label' => 'Author ID (will be auto-generated): ',
                'label_attributes' => [
                    'class' => 'col-sm-3 col-md-3 col-lg-3'
                ]
            ],

        ]);
        $this->add([
            'name' => 'author_name',
            'type' => 'text',
            'attributes' => [
                'id' => 'name',
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
