<?php 

namespace Forms\Controller;

use Zend\Mvc\Controller\AbstractActionController;

use Zend\Validator\File\Size; 
use Zend\Http\PhpEnvironment\Request;

use Zend\Db\TableGateway\TableGateway;
use Zend\View\Model\ViewModel;

use Forms\Form\FormsForm;

use Forms\Model\FormsTable;
use Forms\Model\FormsModel;

// comments
use Forms\Form\CommentsForm;
use Forms\Model\CommentsModel;
use Forms\Model\CommentsTable;

// tags
use Forms\Form\TagsForm;
use Forms\Model\TagsModel;
use Forms\Model\TagsTable;

use Forms\Model\FormCommentModel;
use Forms\Model\FormTagModel;

use Forms\Form\CategoryFilter;
use Forms\Model\CategoryModel;
use Forms\Model\CategoryTable;
use Forms\Form\CategoryForm;
use Forms\Form\CategoryFormUpdate;

use Forms\Form\FormFilter;
use Forms\Form\FormsFormUpdate;

use Forms\Model\Formulaires;
// use Comments\Form\CommentsForm;
// use Comments\Model\CommentsModel;
// use Comments\Model\CommentsTable;*
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;

use Zend\Db\Adapter\Adapter;

class IndexController extends AbstractActionController
{
    protected $formsTable;
    protected $commentsTable;
    protected $categoryTable;
    protected $formCommentTable;
    protected $tagsTable;
    protected $formTagTable;
    protected $CategoryTable;

    public function indexAction() 
    {   
        $formulaire = $this->getSelectFormsTable()->select();
        $categories = $this->getSelectCategoryTable()->select();
              // \Zend\Debug\Debug::dump($categories); die; 
        return new ViewModel(array('rowset' => $formulaire, 'categories' => $categories)); // pr afficher les data    
                                // return array(
        //     'iduser'    => $id,
        //     'user' => $this->getUserTable()->getUser($id)
        // );                  
    } 
////////////////////////////////////////Forms//////////////////////////////////////////////////////////  

     // UploadForm
    public function uploadFormAction() 
    {
        $form = new FormsForm();
        $request = $this->getRequest();    
        
        if ($request->isPost()) {
            
            $forms = new FormsModel();
            $form->setInputFilter($forms->getInputFilter());       
          
            $nonFile = $request->getPost()->toArray();
            $File    = $this->params()->fromFiles('fileUpload');
            $data = array_merge(
                 $nonFile,  
                 array('fileUpload'=> $File['name']) 
             );
            
            $form->setData($data);

            if ($form->isValid()) {
                
                $size = new Size(array('min'=>20000)); //min filesize                
                $adapter = new \Zend\File\Transfer\Adapter\Http();               
                $adapter->setValidators(array($size), $File['name']);

                // renomer le fichier en ajoutant un rand
                $destination = '.\data\UPLOADS';
                $ext = pathinfo($File['name'], PATHINFO_EXTENSION);

                $newName = md5(rand(). $File['name']) . '.' . $ext;
                $adapter->addFilter('File\Rename', array(
                     'target' => $destination . '/' . $File['name'].$newName,
                ));
                  
                
                if (!$adapter->isValid()){
                    $dataError = $adapter->getMessages();
                    $error = array();
                    foreach($dataError as $key=>$row)
                    {
                        $error[] = $row;
                    } //set formElementErrors
                    $form->setMessages(array('fileUpload'=>$error ));
                } else {
                      // renomer avant de sauvegarder 
                    // $adapter->setDestination($destination);
                    if ($adapter->receive($File['name'])) { 

                        $data = $form->getData();
                        // $data = $this->prepareData($data);
                        $forms->exchangeArray($data);                        
                        $this->getFormsTable()->saveForm($forms);                    

                        echo 'forms success ';                       
                    }
                }  
            }
        }         
        return array('form' => $form);
    }


    public function updateAction() // modifier les donnes d'un formulaire avec ajout de categorie  
    {        
        $id = $this->params()->fromRoute('id');
        if (!$id) return $this->redirect()->toRoute('forms/default', array('controller' => 'Index', 'action' => 'list-form'));
        
        $form = new FormsFormUpdate();
        $request = $this->getRequest();
     
        if ($request->isPost()) {
            $form->setInputFilter(new FormFilter());
            $form->setData($request->getPost());        
            if ($form->isValid()) {
                $data = $form->getData();
                $data['state'] = 1;                          
                unset($data['submit']);          
                $this->getSelectFormsTable()->update($data, array('id' => $id));
                var_dump( "success");          
                         
                return $this->redirect()->toRoute('forms/default', array('controller' => 'Index', 'action' => 'update'));                                                 
            }            
        } else {
            
            $form->setData($this->getSelectFormsTable()->select(array('id' => $id))->current());          
        }

        return new ViewModel(array('form'=> $form, 'id' => $id));
    }

    // liste des formulaires 
    public function listFormAction()
    {
        $list = $this->getSelectFormsTable()->select(array('state' => 1));
        $categories = $this->getSelectCategoryTable()->select();
        return new ViewModel(array('rowset' => $list, 'categories' => $categories, 'list'=>$list)); 
    }

    // les commentaires d'un formulaire  
    public function detailFormAction() 
    {
       
        $formId = $this->params()->fromRoute('id');
        
        // if (!$id) return $this->redirect()->toRoute('auth/default', array('controller' => 'admin', 'action' => 'index'));
        $unformulaire = $this->getSelectFormsTable()->select(array('id' => $formId));
var_dump($this->getFormsTable());
die;
        $this->getFormsTable()->getFormComment($formId);
// // il faut tester ca abvec la vue et tt 
// // 
//         $table = $this->getServiceLocator()->get('Forms\Model\FormsTable');
//         $joinedData = $table->JoinfetchAll($formId);

//         return $joinedData;

        // $table = $this->getServiceLocator()->get('Forms\Model\Formulaires');
          // $comments = $this->getFormulaires()->Leases($formId);
         // \Zend\Debug\Debug::dump($comments); die;
///////******************************************************
  
        // $comment = $this->getSelectCommentsTable()->select();
     
        // ajout d un commentauire 
        $form = new CommentsForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
        }
        
        return new ViewModel(array('form'=>$form,  'unformulaire' => $unformulaire,  'form_id'=>$formId)); // pr afficher les data    
        // \Zend\Debug\Debug::dump($formulaire) ; die;
          
         
    }

     public function getFormulaires() // test 
    {
        if (!$this->formsTable) {
            $sm = $this->getServiceLocator();
            $this->formsTable = $sm->get('Forms\Model\Formulaires');
        }
        return $this->formsTable;
    }

    public function getFormsTable()
    {
        if (!$this->formsTable) {
            $sm = $this->getServiceLocator();
            $this->formsTable = $sm->get('Forms\Model\FormsTable');
        }
        return $this->formsTable;
    }

    
    public function getSelectFormsTable()// pr l affichages des donnes 
    {        
        if (!$this->formsTable) {
            $this->formsTable = new TableGateway(
                'forms', 
                $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter')

            );
        }
        return $this->formsTable;    
    }   
   
////////////////////////////////////////Comments//////////////////////////////////////////////////////////

    //list form avec  une requete where state = 0 
    public function adminListFormAction()
    {
       $list = $this->getSelectFormsTable()->select(array('state' => 0));
       // $categories = $this->getSelectCategoryTable()->select();
        return new ViewModel(array('rowset' => $list)); 
    }

    public function adminDetailFormAction() 
    {
        $formId = $this->params()->fromRoute('id');        
        // if (!$id) return $this->redirect()->toRoute('auth/default', array('controller' => 'admin', 'action' => 'index'));
        $unformulaire = $this->getSelectFormsTable()->select(array('id' => $formId));

        // category 
        $categories = $this->getSelectCategoryTable()->select();

/* la requete sql permet de recuperer les id des tags mais je n arrive pas a les afficher 
        // $TAGS = $this->getFormsTable()->formTagGet();
        $select = new Select();
        $select->from('form_tag')->columns(array('tag_id'))
            ->join('forms', 'form_tag.form_id = forms.id', array(), Select::JOIN_LEFT)
            ->join('comments', 'form_tag.tag_id = comments.id', array(), Select::JOIN_LEFT)
            ->where('forms.id ='.$formId);
        $select->getSqlString();
        return $this->tableGateway->selectWith($select);
        // \Zend\Debug\Debug::dump($select->getSqlString()); die;
*/      
     
        // ajout d un tag
        $form = new TagsForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
        }        
        return new ViewModel(array('form'=>$form,'unformulaire' => $unformulaire, 'categories' => $categories, 'formId'=>$formId)); // pr afficher les data          
         
    }

    public function addCommentAction()  // avec sauvegarde des deux id dans form_comment
    {           
        $form = new CommentsForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            
            $comments = new CommentsModel();
            $form->setInputFilter($comments->getInputFilter());    
            $form->setData($request->getPost());
            if ($form->isValid()) {          
                $data = $form->getData();
                $comments->exchangeArray($data);
                            
                $comment_id = $this->getCommentsTable()->saveComment($comments);
                // $comment_id =$this->getCommentId();                
                $formComment = new FormCommentModel();
                $dataId['form_id'] = $data['form_id'];
                $dataId['comment_id'] = $comment_id;             

                $formComment->exchangeArray($dataId);
                $this->getFormCommentTable()->saveFormComment($formComment);

            }
             $this->redirect()->toRoute('forms/default', array('controller'=>'Index', 'action'=>'add-comment'));
        }
        return new ViewModel(array('form' => $form, 'comments'=>$comments));        
    }

    public function getCommentsTable()
    {
        if (!$this->commentsTable) {
            $sm = $this->getServiceLocator();
            $this->commentsTable = $sm->get('Forms\Model\CommentsTable');
        }
        return $this->commentsTable;
    }

    public function getFormCommentTable()
    {
        if (!$this->formCommentTable) {
            $sm = $this->getServiceLocator();
            $this->formCommentTable = $sm->get('Forms\Model\FormCommentTable');
        }
        return $this->formCommentTable;
    }
   
    public function getSelectCommentsTable()// pr l affichages des donnes 
    {        
        if (!$this->commentsTable) {
            $this->commentsTable = new TableGateway(
                'comments', 
                $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter')

            );
        }
        return $this->commentsTable;    
    }   
 
//////////////////////////////////////// ESPACE ADMIN::::: Tags  //////////////////////////////////////////////////////////
    public function addTagAction()  // avec sauvegarde des deux id dans form_comment
    {
        $form = new TagsForm();
        $request = $this->getRequest();
        if ($request->isPost()) {           
            $tags = new TagsModel();
            $form->setInputFilter($tags->getInputFilter());    
            $form->setData($request->getPost());
             if ($form->isValid()) {             
                $data = $form->getData();
                $tags->exchangeArray($data);                
                $tag_id = $this->getTagsTable()->saveTag($tags); 

                if ($dataId['form_id']) {
                    $formTag = new FormTagModel();

                    $dataId['form_id'] = $data['form_id'];
                    $dataId['tag_id'] = $tag_id;

                    $formTag->exchangeArray($dataId);   
                    $this->getFormTagTable()->saveFormTag($formTag);
                    
                    return $this->redirect()->toRoute('forms/default', array('controller'=>'Index', 'action'=>'admin-detail-form'));                 
                }
               
                return $this->redirect()->toRoute('forms/default', array('controller'=>'Index', 'action'=>'list-tag'));                 
                
                // \Zend\Debug\Debug::dump("fin"); die;             
            }            
        }
        return new ViewModel(array('form' => $form));   
    }     
   

    public function getTagsTable()
    {
        if (!$this->tagsTable) {
            $sm = $this->getServiceLocator();
            $this->tagsTable = $sm->get('Forms\Model\TagsTable');
        }
        return $this->tagsTable;
    }

    public function getFormTagTable()
    {
        if (!$this->formTagTable) {
            $sm = $this->getServiceLocator();
            $this->formTagTable = $sm->get('Forms\Model\FormTagTable');
        }
        return $this->formTagTable;
    } 

    public function getSelectTagsTable() // pr afficher les donnees depuis la bdd 
    {
        if (!$this->tagsTable) {
            $this->tagsTable = new TableGateway(
                'tags', 
                $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter')

            );
        }
        return $this->tagsTable;    
    } 

    public function deleteTagAction() //delete tag valide 
    {
        $id = $this->params()->fromRoute('id');
        if ($id) {
            $this->getTagsTable()->delete(array('id' => $id));
        }
        
        return $this->redirect()->toRoute('forms/default', array('controller' => 'index', 'action' => 'listTag'));                                         
    }

    public function listTagAction()
    {
        // ajouter une nouvelle categorie        
        $form = new TagsForm();
        $request = $this->getRequest();

        $list = $this->getSelectTagsTable()->select();
        return new ViewModel(array('form' =>$form ,'rowset' => $list)); 
    }


////////////////////////////////////////  Category  //////////////////////////////////////////////////////////
    // manque la recherche des formulaires suivant la categorie 

    public function addCategoryAction()
    {   
/* la requete sql permet de recuperer les id des formulaire de la category specifié  mais je n arrive pas a les afficher 
       
        $categoryId = 1;

        $select = new Select();
        $select->from('forms')->columns(array('id', 'form_name'))
            ->join('category', 'forms.category_id = category.id', array(), Select::JOIN_LEFT)            
            ->where('category.id ='.$categoryId);
        \Zend\Debug\Debug::dump($select->getSqlString()); die;
        $select->getSqlString();
        return $this->tableGateway->selectWith($select);
*/
        $form = new CategoryForm();
        $request = $this->getRequest();
        if ($request->isPost()) {           
            $category = new CategoryModel();
            $form->setInputFilter($category->getInputFilter());    
            $form->setData($request->getPost());
             if ($form->isValid()) {             
                $data = $form->getData();
                $category->exchangeArray($data);                
                 $this->getCategoryTable()->saveCategory($category);
                 echo 'success'; 
                // \Zend\Debug\Debug::dump($this->getCategoryTable()->saveCategory($category)); die;               
                    
                return $this->redirect()->toRoute('forms/default', array('controller'=>'Index', 'action'=>'listCategory'));                 
            }            
        }
        return new ViewModel(array('form' => $form));   
    } 

    public function listCategoryAction()
    {
        // ajouter une nouvelle categorie        
        $form = new CategoryForm();
        $request = $this->getRequest();

        $list = $this->getSelectCategoryTable()->select();
        return new ViewModel(array('form' =>$form ,'rowset' => $list)); 
    }

    public function updateCategoryAction() // modifier les donnes d'un formulaire avec ajout de categorie  
    {        
        $id = $this->params()->fromRoute('id');
        if (!$id) return $this->redirect()->toRoute('forms/default', array('controller' => 'Index', 'action' => 'listCategory'));
        
        $form = new CategoryForm();
        $request = $this->getRequest();
           
        if ($request->isPost()) {
            $form->setInputFilter(new CategoryFilter());
            $form->setData($request->getPost());        
            if ($form->isValid()) {
                $data = $form->getData();                                      
                unset($data['submit']);          
                $this->getSelectCategoryTable()->update($data, array('id' => $id));                         
                return $this->redirect()->toRoute('forms/default', array('controller' => 'Index', 'action' => 'updateCategory'));                                                 
            }            
        } else {
            
            $form->setData($this->getSelectCategoryTable()->select(array('id' => $id))->current());          
        }

        return new ViewModel(array('form'=> $form, 'id' => $id));
    }
   
    public function deleteCategoryAction() //delete category valide 
    {
        $id = $this->params()->fromRoute('id');
        if ($id) {
            $this->getCategoryTable()->delete(array('id' => $id));
        }
        
        return $this->redirect()->toRoute('forms/default', array('controller' => 'index', 'action' => 'listCategory'));                                         
    }


    public function getSelectCategoryTable()// pr l affichages des donnes 
    {        
        if (!$this->categoryTable) {
            $this->categoryTable = new TableGateway(
                'category', 
                $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter')

            );
        }
        return $this->categoryTable;    
    }  

    public function getCategoryTable()
    {
        if (!$this->CategoryTable) {
            $sm = $this->getServiceLocator();
            $this->CategoryTable = $sm->get('Forms\Model\CategoryTable');
        }
        return $this->CategoryTable;
    }    
}