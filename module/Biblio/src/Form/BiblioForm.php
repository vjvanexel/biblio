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
use Zend\Hydrator\ClassMethods as ClassMethodsHydrator;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;

class BiblioForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct($name = null);

        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        $this->setInputFilter(new InputFilter());

        $this->add([
            'name' => 'bibliog_id',
            'type' => 'text',
            'attributes' => [
                'id' => 'bib_id',
                'class' => 'col-sm-9 col-md-9 col-lg-9',],
            'options' => [
                'label' => 'Bibliography ID',
                'label_attributes' => [
                    'class' => 'col-sm-1 col-md-2 col-lg-2'
                ]
            ]
        ]);
        $this->add([
            'name' => 'edited',
            'type' => \Zend\Form\Element\Checkbox::class,
            'attributes' => [
                'id' => '',
                'class' => 'col-sm-1 col-md-1 col-lg-1'
            ],
            'options' => [
                'label' => 'Edited / journal: ',
                'label_attributes' => [
                    'class' => 'col-sm-1 col-md-2 col-lg-2'
                ]
            ],
        ]);
        $this->add([
            'name' => 'year',
            'type' => 'text',
            'attributes' => [
                'id' => '',
                'class' => 'col-sm-2 col-md-4 col-lg-4'
            ],
            'options' => [
                'label' => 'Year: ',
                'label_attributes' => [
                    'class' => 'col-sm-1 col-md-2 col-lg-2'
                ],
            ]
        ]);
        $this->add([
            'name' => 'title',
            'type' => 'text',
            'attributes' => [
                'id' => '',
                'class' => 'col-sm-3 col-md-5 col-lg-5'
            ],
            'options' => [
                'label' => 'Title: ',
                'label_attributes' => [
                    'class' => 'col-sm-1 col-md-2 col-lg-2'
                ],
            ]
        ]);
        $this->add([
            'name' => 'journal',
            'type' => 'text',
            'attributes' => [
                'id' => 'journal',
                'class' => 'combobox-get col-sm-2 col-md-4 col-lg-4'
            ],
            'options' => [
                'label' => 'in: ',
                'label_attributes' => [
                    'class' => 'col-sm-1 col-md-2 col-lg-2'
                ],
            ]
        ]);
        $this->add([
            'name' => 'issue',
            'type' => 'text',
            'attributes' => [
                'id' => '',
                'class' => 'col-sm-2 col-md-4 col-lg-4'
            ],
            'options' => [
                'label' => 'Issue: ',
                'label_attributes' => [
                    'class' => 'col-sm-1 col-md-2 col-lg-2'
                ],
            ]
        ]);
        $this->add([
            'name' => 'series',
            'type' => 'text',
            'attributes' => [
                'id' => '',
                'class' => 'col-sm-1 col-md-4 col-lg-4'
            ],
            'options' => [
                'label' => 'Series: ',
                'label_attributes' => [
                    'class' => 'col-sm-1 col-md-2 col-lg-2'
                ],
            ]
        ]);
        $this->add([
            'name' => 'series_vol',
            'type' => 'text',
            'attributes' => [
                'id' => '',
                'class' => 'col-sm-2 col-md-4 col-lg-4'
            ],
            'options' => [
                'label' => 'Series vol. nr.: ',
                'label_attributes' => [
                    'class' => 'col-sm-1 col-md-2 col-lg-2'
                ],
            ]
        ]);
        $this->add([
            'name' => 'pages',
            'type' => 'text',
            'attributes' => [
                'id' => '',
                'class' => 'col-sm-2 col-md-4 col-lg-4'
            ],
            'options' => [
                'label' => 'Pages: ',
                'label_attributes' => [
                    'class' => 'col-sm-1 col-md-2 col-lg-2'
                ],
            ]
        ]);
        $this->add([
            'name' => 'publish_info',
            'type' => 'text',
            'attributes' => [
                'id' => '',
                'class' => 'col-sm-3 col-md-6 col-lg-6'
            ],
            'options' => [
                'label' => 'Publishing Info',
                'label_attributes' => [
                    'class' => 'col-sm-1 col-md-2 col-lg-2'
                ],
            ]
        ]);
        $this->add([
            'name' => 'authors',
            'type' => Element\Collection::class,
            'attributes' => [
                'id' => '',
                'class' => 'col-sm-9 col-md-9 col-lg-9'
            ],
            'options' => [
                'label' => 'Author(s)',
                'label_attributes' => [
                    'class' => 'col-sm-3 col-md-3 col-lg-3'
                ],
                'count' => 1,
                'should_create_template' => true,
                'allow_add' => true,
                'target_element' => [
                    'type' =>AuthorsFieldset::class,
                ],
            ],
        ]);
        $this->add([
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => [
                'value' => 'Go',
                'id'    => 'submitbutton',
                'class' => 'btn btn-primary',
            ],
        ]);
    }
}
