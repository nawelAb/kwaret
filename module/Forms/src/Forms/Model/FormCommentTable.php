<?php
namespace Forms\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;


class FormCommentTable
{
    protected $tableGateway;


    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
       
    }
	
    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getFormComment($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveFormComment(FormCommentModel $form)
    {
        // pour Zend\Db\TableGateway\TableGateway les donnes doivent etre dans un tableau non un objet 
        $data = array( 
            'id'      => $form->id,    
            'form_id'   => $form->form_id,
            'comment_id' => $form->comment_id             
        );
       
        $id = (int)$form->id;
        if ($id == 0) {
            $this->tableGateway->insert($data); 
            return $id = $this->tableGateway->lastInsertValue;         
        }
    }
    
    public function deleteFormComment($id)
    {
        $this->tableGateway->delete(array('id' => $id));
    }  

   
}