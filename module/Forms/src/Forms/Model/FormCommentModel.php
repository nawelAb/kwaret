<?php
// filename : module/Forms/src/Forms/Model/forms.php
namespace Forms\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

use Zend\Filter\File\RenameUpload;
use Zend\Validator\File\Size;

class FormCommentModel implements InputFilterAwareInterface
{   
    public $id;
    public $comment_id;
    public $form_id;
    protected $inputFilter;
    
    public function exchangeArray($data)
    {        
        $this->id               = (isset($data['id'])) ? $data['id'] : null;
        $this->comment_id       = (isset($data['comment_id'])) ? $data['comment_id'] : null;
        $this->form_id          = (isset($data['form_id'])) ? $data['form_id'] : null;
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
            //     $factory->createInput(array(
            //         'name'     => 'form_name',
            //         'required' => true,
            //         'filters'  => array(
            //             array('name' => 'StripTags'),
            //             array('name' => 'StringTrim'),
            //         ),
            //         'validators' => array(
            //             array(
            //                 'name'    => 'StringLength',
            //                 'options' => array(
            //                     'encoding' => 'UTF-8',
            //                     'min'      => 1,
            //                     'max'      => 100,
            //                 ),
            //             ),
            //         ),
            //     ))
            // );
            
            // $inputFilter->add(
            //     $factory->createInput(array(
            //         'name'     => 'fileUpload',
            //         'required' => true,
            //         'validators' => array(
            //         //     [
            //         //     'name'  => 'Zend\Validator\File\Size',
            //         //     'options' =>
            //         //         [
            //         //             'max'      => '30MB',
            //         //         ],
            //         // ],
            //         // 
                        
                        
            //         )
            //     ))
            // );

            // $inputFilter->add(
            //     $factory->createInput(array(
            //         'name'     => 'category',
            //         'required' => true,
            //         'validators' => array(
            //         )
            //     ))
            );
            
            $this->inputFilter = $inputFilter;
        }        
        return $this->inputFilter;
    }
}
