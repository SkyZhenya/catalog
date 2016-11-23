<?php
namespace Admin\Controller;

use CodeIT\Controller\AbstractController;
use Application\Lib\AppController;
use Application\Lib\User as UserLib;
use Zend\View\Model\ViewModel;

class User extends AbstractController {
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

		$this->userTable = new UserLib();
	}

	/**
	 * return form
	 * 
	 * @return \Zend\Form\Form
	 */
	public function getForm($action = 'edit', $id = null) {
		if(is_null($this->form)) {
			$this->form = new \Admin\Form\UserEditForm($action, $id);
		}
		return $this->form;
	}

	
	public function indexAction() {
		$this->layout()->bodyClass = 'user';

		$total = 0;
		$list = $this->userTable->find([], 1, 0, null, $total);

		$result = array(
			'canAdd' => $this->user->isAllowed('Admin\Controller\User', 'add'),
			'canDelete' => $this->user->isAllowed('Admin\Controller\User', 'save'),
			'total' => $total,
		);
		$this->renderHtmlIntoLayout('submenu', 'admin/user/submenu.phtml', $result);
		return $result;
	}

	public function editAction() {
		$id = $this->params('id',0);

		if(!$id)
		return $this->redirect()->toRoute('admin', array('controller' => 'user', 'action' => 'add'));

		$this->setBreadcrumbs(['user' => 'Users',], true);
		$form = $this->getForm('edit', $id);
		$canEdit = $this->user->isAllowed('Admin\Controller\User', 'save');
		try {
			$user = $this->userTable->setId($id);
			$form->setUpdated($user->updated);
			$form->setData($user);
		}
		catch(\Exception $e) {
			return $this->notFoundAction();
		}

		if ($this->request->isPost()) {
			if ($canEdit){
				$data = array_merge_recursive($this->request->getPost()->toArray(), $this->request->getFiles()->toArray());
				$form->setData($data);
				if ($form->isValid()) {
					$data = $form->getData();
					$data['updated'] = TIME;
					if (!empty($data['pass'])) {
						$data['password'] = $this->user->passwordHash($data['pass']);
					}
					//manage avatar
					$data['removeAvatar'] = (int)$data['removeAvatar'];
					if (!empty($data['removeAvatar'])) {
						$this->userTable->removeImages($id);
					}
					$this->userTable->set($data);
					if (!empty($data['avatar']['tmp_name'])) {
						$this->userTable->setAvatar($data['avatar']['tmp_name'], $id);
					}

					$this->redirect()->toUrl(URL.'admin/user/');
				}
			}
			else {
				$this->error = _('You do not have enough permissions to make changes');
			}
		}
		$form->get('avatar')->setValue(null);

		return  new ViewModel(array(
			'form' => $form,
			'error' => $this->error,
			'title' => _('Edit User'),
			'item' => $user,
		));
	}

	public function addAction(){
		$this->setBreadcrumbs(['user' => 'Users',], true);
		$form = $this->getForm('add');

		if ($this->request->isPost()) {
			$data = array_merge_recursive($this->request->getPost()->toArray(), $this->request->getFiles()->toArray());
			$form->setData($data);
			if ($form->isValid()) {
				$data = $form->getData();
				$data['updated'] = TIME;
				if (!empty($data['pass'])) {
					$data['password'] = $this->user->passwordHash($data['pass']);
				}
				//manage avatar
				$id = $this->userTable->insert($data);
				if (!empty($data['avatar']['tmp_name'])) {
					$this->userTable->setAvatar($data['avatar']['tmp_name'], $id);
				}

				$this->redirect()->toUrl(URL.'admin/user/');
			}
		}
		$form->get('avatar')->setValue(null);

		$result =  new ViewModel(array(
			'form' => $form,
			'error' => $this->error,
			'title' => _('Add New User'),
		));
		$result->setTemplate('admin/user/edit.phtml');
		return $result;
	}


	public function deleteAction() {
		$id = (int)$this->request->getPost('id', 0);
		$this->userTable->delete($id);
		return $this->getResponse()->setContent('OK');
	}


	public function listAction() {
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
		if(trim($flPid) !== '') {
			$params []= array('id', 'LIKE', "{$flPid}%");
		}
		$flId = $this->params()->fromQuery('flId');
		if(!empty($flId)) {
			$params []= array('id', '=', "{$flId}");
		}

		$flName = $this->params()->fromQuery('flName');
		if(trim($flName) !== '') {
			$params []= array('name', 'LIKE', "{$flName}%");
		}

		$flEmail = $this->params()->fromQuery('flEmail');
		if(trim($flEmail) !== '') {
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

		$flCreated = $this->params()->fromQuery('flCreated');
		if(!empty($flCreated)) {
			$params []= array('created', '>=', strtotime($flCreated.' 00:00:00'));
			$params []= array('created', '<=', strtotime($flCreated.' 23:59:59'));
		}

		$flCreatedFrom = $this->params()->fromQuery('flCreatedFrom');
		if(!empty($flCreatedFrom)) {
			$params []= array('created', '>=', strtotime($flCreatedFrom.' 00:00:00'));
		}

		$flCreatedTo = $this->params()->fromQuery('flCreatedTo');
		if(!empty($flCreatedTo)) {
			$params []= array('created', '<=', strtotime($flCreatedTo.' 23:59:59'));
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
