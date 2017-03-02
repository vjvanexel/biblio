<?php
/**
 * Created by PhpStorm.
 * User: vjvan
 * Date: 12/2/2016
 * Time: 13:20
 */
namespace Biblio\Form;

use Biblio\Form\AuthorsFieldset;
use Zend\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;

class SearchForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct($name = null);

        $this->setAttribute('method', 'post');

        $this->setInputFilter(new InputFilter());

        $this->add([
            'name' => 'general',
            'type' => 'text',
            'options' => [
                'label' => 'simple search: ',
            ]
        ]);
        $this->add([
            'name' => 'year',
            'type' => 'text',
            'options' => [
                'label' => 'Publication year',
            ]
        ]);
        $this->add([
            'name' => 'title',
            'type' => 'text',
            'options' => [
                'label' => 'Title: ',
            ]
        ]);
        $this->add([
            'name' => 'journal',
            'type' => 'text',
            'options' => [
                'label' => 'Journal: ',
            ]
        ]);
        $this->add([
            'name' => 'authors',
            'type' => 'text',
            'options' => [
                'label' => 'Author(s)',
                'allow_add' => true,
            ],
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
