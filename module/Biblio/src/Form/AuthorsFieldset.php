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
use Zend\Hydrator\ClassMethods as ClassMethodsHydrator;

class AuthorsFieldset extends Fieldset implements \Zend\InputFilter\InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('biblio');

        $this->setObject(new Author()); // changed from Biblio()
        $this->setHydrator(new ClassMethodsHydrator(false));
        $this->add([
            'name' => 'author_id', 
            'options' => [
                'label' => 'Author ID',
            ],
            'attributes' => [
                'required' => 'required',
            ],
        ]);
        $this->add([
            'name' => 'author_name', 
            'options' => [
                'label' => 'Name of the Author',
            ],
            'attributes' => [
                'required' => 'required',
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

    /**
     * Recursively populate values of attached elements and fieldsets
     *
     * @param  array|Traversable $data
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public function p_o_pulateValues($data)
    {
        /*if (!is_array($data) && !$data instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Traversable set of data; received "%s"',
                __METHOD__,
                (is_object($data) ? get_class($data) : gettype($data))
            ));
        }*/

        foreach ($this->iterator as $name => $elementOrFieldset) {
            $valueExists = array_key_exists($name, $data);

            if ($elementOrFieldset instanceof FieldsetInterface) {
                if ($valueExists && (is_array($data[$name]) || $data[$name] instanceof Traversable)) {
                    $elementOrFieldset->populateValues($data[$name]);
                    continue;
                }

                if ($elementOrFieldset instanceof Element\Collection) {
                    if ($valueExists && null !== $data[$name]) {
                        $elementOrFieldset->populateValues($data[$name]);
                        continue;
                    }

                    /* This ensures that collections with allow_remove don't re-create child
                     * elements if they all were removed */
                    $elementOrFieldset->populateValues([]);
                    continue;
                }
            }

            if ($valueExists) {
                if (is_array($data)){
                    $elementOrFieldset->setValue($data[$name]); // instead of $data[$name]
                }
                if (is_object($data)){
                    $elementOrFieldset->setValue($data->$name); // instead of $data[$name]
                }
            }
        }
    }
}