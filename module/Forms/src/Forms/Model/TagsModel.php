<?php
// filename : module/Forms/src/Forms/Model/forms.php
namespace Forms\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

// use Zend\Filter\File\RenameUpload;
// use Zend\Validator\File\Size;

class TagsModel implements InputFilterAwareInterface
{   
    // public $fileUpload;
    public $id;
    public $value;
    protected $inputFilter;
    
    public function exchangeArray($data)
    {        
        $this->id     = (isset($data['id'])) ? $data['id'] : null;
        $this->value = (isset($data['value'])) ? $data['value'] : null;       
    } 
    
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }
    
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();
             
            $inputFilter->add(
                $factory->createInput(array(
                    'name'     => 'value',
                    'required' => true,
                    'filters'  => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name'    => 'StringLength',
                            'options' => array(
                                'encoding' => 'UTF-8',
                                'min'      => 1,
                                'max'      => 100,
                            ),
                        ),
                    ),
                ))
            );
                       
            $this->inputFilter = $inputFilter;
        }        
        return $this->inputFilter;
    }
}
