<?php
namespace Admin\Controller;
use Application\Lib\AppController;
use Application\Model\DepartmentTable;
use Application\Model\UserTable;
use Application\Lib\User;
use Zend\View\Model\ViewModel;
use Application\Model\CommissionTable;
use \Application\Lib\Utils;
use \Zend\View\Model\JsonModel;

class UserController extends AppController {
	/**
	* @var \Zend\Form\Form
	*/
	var $form;

	/**
	 * 
	 * @var \Application\Lib\User
	 */
	var $userTable;


	public function ready() {
		parent::ready();

		$this->userTable = new User();
	}

	/**
	 * return form
	 * 
	 * @return \Zend\Form\Form
	 */
	public function getForm() {
		if(is_null($this->form)) {
			$this->form = new \Admin\Form\UserEditForm();
		}
		return $this->form;
	}
	
	public function indexAction() {
		$this->layout()->bodyClass = 'user';

		$result = array(
			'canAdd' => $this->user->isAllowed('Admin\Controller\User', 'add'),
			'canDelete' => $this->user->isAllowed('Admin\Controller\User', 'save'),
		);
		$this->renderHtmlIntoLayout('submenu', 'admin/user/submenu.phtml', $result);
		return $result;
	}

	public function editAction() {
		$this->layout('layout/iframe');
		$id = $this->params('id',0);
		
		if(!$id)
			return $this->redirect()->toRoute('admin', array('controller' => 'user', 'action' => 'add'));
			
    $form = $this->getForm();
    $canEdit = $this->user->isAllowed('Admin\Controller\User', 'save');
    try {
			$data = $this->userTable->setId($id);
			$form->setData($data);
		}
		catch(\Exception $e) {
			$this->error = _('User not found');
		}
   
    if ($this->request->isPost()) {
			if ($canEdit){
				$data = $this->request->getPost()->toArray();
				$form->setData($data);
				if(isset($data['submit'])) {
					$form->setInputFilter($form->getFormInputFilter('edit'));
					if ($form->isValid()) {
						$data = $form->getData();
						$this->userTable->set($data);
					}
				}
			}
			else {
				$this->error = _('You do not have enough permissions to make changes');
			}
		}

		$canClosePage = !count($form->getMessages());
		
		$view = new ViewModel(array(
			'form' => $form,
			'canClosePage' => $canClosePage,
			'error' => $this->error,
			'canEdit' => $canEdit,
			'title' => _('Edit profile'),
		));
		return $view;
	}
	
	public function addAction(){
		$this->layout('layout/iframe');
		$form = $this->getForm();
		
		if ($this->request->isPost()) {
			$data = $this->request->getPost()->toArray();
			$form->setData($data);
			if(isset($data['submit'])) {
				$form->setInputFilter($form->getFormInputFilter('add'));
				if ($form->isValid()) {
					$data = $form->getData();
					$id = $this->userTable->insert($data);
					$form->setAttribute('action', URL.'admin/user/edit/'.$id);
					$result =  new ViewModel(array(
						'form' => $form,
						'canClosePage' => true,
						'title' => _('Edit profile'),
						'wasAdded' => true,
						'addAction' => true,
					));
					$result->setTemplate('admin/user/edit');
					return $result;
				}
			}
		}
		
		$result = new ViewModel(array(
			'form' => $form,
			'title' => _('Create new account'),
			'canClosePage' => false,
			'addAction' => true,
		));
		
		$result->setTemplate('admin/user/edit');
		return $result;
	}

	
	public function deleteAction() {
		$id = (int)$this->request->getPost('id', 0);
		$this->userTable->deleteById($id);
		return $this->getResponse()->setContent('OK');
	}


	public function listAction() {
		
		header("Content-Type: text/xml");
		$count=50;
		if (isset($_GET['count']))
			$count=(int)$_GET['count'];
		$pos = 0;
		if (isset($_GET['posStart']))
			$pos=(int)$_GET['posStart'];
		$orderby='id';

		$params = array(
		);

		//apply filters
		
		if(isset($_GET['flPid'])&&!empty($_GET['flPid'])) {
			$params []= array('id','like', "{$_GET['flPid']}%");
		}
		//strict rule for Id to update only one item
		if(isset($_GET['flId'])&&!empty($_GET['flId'])) {
			$params []= array('id', '=', $_GET['flId']);
		}
		if(isset($_GET['flName'])&&!empty($_GET['flName'])) {
			$params []= array('name', 'like', "%{$_GET['flName']}%");
		}
		$selectedRole = '';
		if (isset($_GET['flRole'])&&!empty($_GET['flRole'])){
			$params []= array('level', '=', $_GET['flRole']);
		}

		if(isset($_GET['flEmail']) && trim($_GET['flEmail'])!==''){
			$params []= array('email', 'like ', "%{$_GET['flEmail']}%");
		}
		
		if (isset($_GET['flStatus']) && (int)$_GET['flStatus']>=0) {
			$params []= array('active', '=', (int)$_GET['flStatus']);
		}
		
		if(isset($_GET['orderby'])) {
			switch($_GET['order']) {
				case 'asc': $orderdir='asc'; break;
				default: $orderdir='desc';
			}
			switch($_GET['orderby']) {
				case 1: $orderby="id"; break;
				case 2: $orderby="name"; break;
				case 3: $orderby="email"; break;
				default: $orderby="id"; break;
			}
			$orderby.=' '.$orderdir;
		}

		$total = 0;
		$list = $this->userTable->find($params, $count, $pos, $orderby, $total);
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
		<rows pos='$pos' total_count='$total'>\n";
		$i=$pos;
		$levelText = array(
			"admin" => _('Admin'),
			"user" => _('User'),
		);
		
		foreach($list as $item) {
			$i++;
	    $userActions='';
			$userActions .= "<a class='edit' onclick='initFancybox(\"".$this->basePath("admin/user/edit/{$item->id}")."\"); (arguments[0]||window.event).cancelBubble=true; return false;' href='".$this->basePath('admin/user/edit')."?id={$item->id}'>"._("Edit Profile")."</a> ";
			$deleteActions = '';
			if ($this->user->isAllowed('Admin\Controller\User', 'delete'))
      	$deleteActions = "<a class='del' href='#' onclick='common.confdel(\"".URL."admin/user/delete?\",{$item->id}, common.removeItem); (arguments[0]||window.event).cancelBubble=true; return false;'>"._('Delete Profile')."</a>";

			echo "<row id='{$item->id}' >\n
				<cell>{$i}</cell>
				<cell>{$item->id}</cell>
				<cell>".htmlspecialchars($item->name)."</cell>
				<cell>".htmlspecialchars($item->email)."</cell>
				<cell>".htmlspecialchars($levelText[$item->level])."</cell>
				<cell>".(($item->active)? 'Active' : 'Inactive')."</cell>
				<cell>".htmlspecialchars($userActions)."</cell>".
				(($deleteActions)? "<cell>".htmlspecialchars($deleteActions)."</cell>" : "").
			"</row>\n";
		}
		echo "</rows>\n";
		exit();
	}

}
