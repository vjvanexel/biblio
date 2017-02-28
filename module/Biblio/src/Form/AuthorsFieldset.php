<?php
/**
 * Created by PhpStorm.
 * User: vjvan
 * Date: 12/12/2016
 * Time: 04:56
 */
namespace Biblio\Form;

use Biblio\Model\Author;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Biblio\Model\Biblio;

class AuthorsFieldset extends Fieldset implements \Zend\InputFilter\InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('biblio');

        $this->setObject(new Author());
        $this->add([
            'name' => 'author_name',
            'type' => Element\Select::class,
            'options' => [
                'label' => 'Author: ',
                'label_attributes' => [
                    'class' => 'col-sm-1 col-md-2 col-lg-2'
                ]
            ],
            'attributes' => [
                'id' => 'authors',
                'class' => 'combobox col-sm-2 col-md-4 col-lg-4',
            ],
        ]);
    }

    function getInputFilterSpecification()
    {
        return [
            'author_name' => [
                'required' => true,
            ]
        ];
    }
}