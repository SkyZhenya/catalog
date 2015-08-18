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
	public function getForm($action = 'edit') {
		if(is_null($this->form)) {
			$this->form = new \Admin\Form\UserEditForm($action);
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
		$form->setUserId($id);
		$canEdit = $this->user->isAllowed('Admin\Controller\User', 'save');
		try {
			$user = $this->userTable->setId($id);
			$form->setData($user);
		}
		catch(\Exception $e) {
			$this->error = _('User not found');
		}

		if ($this->request->isPost()) {
			if ($canEdit){
				$data = array_merge_recursive($this->request->getPost()->toArray(), $this->request->getFiles()->toArray());
				$form->setData($data);
				if(isset($data['submit'])) {
					if ($form->isValid()) {
						$data = $form->getData();
						if (!empty($data['pass'])) {
							$data['password'] = $this->user->passwordHash($data['pass']);
						}
						$this->userTable->set($data);
						//manage avatar
						$data['removeAvatar'] = (int)$data['removeAvatar'];
						if (!empty($data['removeAvatar'])) {
							$this->userTable->removeImages($id);
						}
						$this->userTable->set($data);
						if (!empty($data['avatar']['tmp_name'])) {
							$this->userTable->setAvatar($data['avatar']['tmp_name'], $id);
						}
						$user = $this->userTable->get($id);
						$form->get('avatar')->setValue(null);
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
			'item' => $user,
		));
		return $view;
	}
	
	public function addAction(){
		$this->layout('layout/iframe');
		$form = $this->getForm('add');
		$user = null;
		
		if ($this->request->isPost()) {
			$data = array_merge_recursive($this->request->getPost()->toArray(), $this->request->getFiles()->toArray());
			$form->setData($data);
			if(isset($data['submit'])) {
				if ($form->isValid()) {
					$data = $form->getData();
					if (!empty($data['pass'])) {
						$data['password'] = $this->user->passwordHash($data['pass']);
					}
					$id = $this->userTable->insert($data);
					if (!empty($data['avatar']['tmp_name'])) {
						$userData = $this->userTable->setAvatar($data['avatar']['tmp_name'], $id);
					}
					$user = $this->userTable->get($id);
					$form->get('avatar')->setValue(null);
					$form->setAttribute('action', URL.'admin/user/edit/'.$id);
					$result =  new ViewModel(array(
						'form' => $form,
						'canClosePage' => true,
						'title' => _('Edit profile'),
						'wasAdded' => true,
						'item' => $user,
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
		$count = (int)$this->params()->fromQuery('count', 50);
		$pos = (int)$this->params()->fromQuery('posStart', 0);
		$params = $this->resolveParams();
		$orderby = $this->resolveOrderby();

		$total = 0;
		$list = $this->userTable->find($params, $count, $pos, $orderby, $total);
		$xmlResult = new ViewModel(array(
			'pos' => $pos,
			'total' => $total,
			'list' => $list,
			'isAllowedDelete' => $this->user->isAllowed('Admin\Controller\User', 'delete'),
		));
		$xmlResult->setTerminal(true);
		return $xmlResult;
	}
	
	/**
	 * apply filters
	 * 
	 * @return array
	 */
	protected function resolveParams() {
		$params = array();

		$flPid = $this->params()->fromQuery('flPid');
		if(!empty($flPid)) {
			$params []= array('id', 'LIKE', "{$flPid}%");
		}
		$flId = $this->params()->fromQuery('flId');
		if(!empty($flId)) {
			$params []= array('id', '=', "{$flId}");
		}
		
		$flName = $this->params()->fromQuery('flName');
		if(!empty($flName)) {
			$params []= array('name', 'LIKE', "{$flName}%");
		}
		
		$flEmail = $this->params()->fromQuery('flEmail');
		if(!empty($flEmail)) {
			$params []= array('email', 'LIKE', "{$flEmail}%");
		}
		
		$flRole = $this->params()->fromQuery('flRole');
		if(!empty($flRole)) {
			$params []= array('level', '=', $flRole);
		}
		
		$flStatus = $this->params()->fromQuery('flStatus', -1);
		if($flStatus >= 0) {
			$params []= array('active', '=', $flStatus);
		}
			
		return $params;
	}
	
/**
	 * return orderby rule for list ordering
	 * 
	 * @return string
	 */
	protected function resolveOrderby() {
		$orderby='id';
			
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
		
		return $orderby;
	}

}
