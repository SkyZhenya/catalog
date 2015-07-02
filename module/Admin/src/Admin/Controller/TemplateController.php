<?php
namespace Admin\Controller;
use Application\Lib\AppController;
use Application\Model\TemplateTable;
use Admin\Form\TemplateForm;
use Zend\View\Model\ViewModel;

/**
 * email templates management
 */
class TemplateController extends AppController {
	/**
	* @var \Zend\Form\Form
	*/
	var $form;
	
	/**
	 * @var Application\Model\TemplateTable
	 */
	var $templateTable;
	
	var $url = 'template';

	public function ready() {
		parent::ready();

		$this->templateTable = new TemplateTable();
	}
	
	/**
	 * return form
	 * 
	 * @return \Zend\Form\Form
	 */
	public function getForm() {
		if(is_null($this->form)) {
			$this->form = new TemplateForm();
		}
		return $this->form;
	}

	public function indexAction() {
		$this->layout()->bodyClass = 'templates';
		$this->renderHtmlIntoLayout('submenu', 'admin/template/submenu.phtml');
	}

	public function editAction() {	
		$form = $this->getForm();
		$id = $this->params('id',0);
		$this->setBreadcrumbs(['template' => 'Email Templates'], true);
		$this->layout()->bodyClass = 'editcontent';
		
		
		if ($id > 0) {
			try {
				$data = (array)$this->templateTable->getFullLocalData($id); 
				$form->setData($data);
			}
			catch(\Exception $e) {
                return $this->notFoundAction();
			}
		}
		
		$wasAdded = false;
		if ($this->request->isPost()) {
			$data = $this->request->getPost()->toArray();
			$form->setData($data);
			if(isset($data['submit'])) {

				if ($form->isValid()) {
					$data = $form->getData();
					if ($id > 0){
						$oldValue = $this->templateTable->setId($id);
						$this->templateTable->cacheDelete(base64_encode($oldValue->name));
						$this->templateTable->set($data);
					}
					else {
						$id = $this->templateTable->insert($data);
						$form->setAttribute('action', URL.'admin/'.$this->url.'/edit/'.$id);
						$form->get('id')->setValue($id);
						$wasAdded = true;
					}
					return $this->redirect()->toUrl(URL.'admin/template');
				}
			}
		}
		
		$view = new ViewModel(array(
			'form' => $form,
			'error' => $this->error,
			'title' => _('Email template'),
			'activeLang' =>\Zend\Registry::get('lang'),
		));
		return $view;
	}

	public function addAction(){
		$this->setBreadcrumbs(['template' => 'Email Templates'], true);
		$this->layout()->bodyClass = 'editcontent';
		
		$form = $this->getForm();
		$form->setAttribute('action', URL.'admin/'.$this->url.'/edit/');
		
		$langsTable = new \Application\Model\LangTable();
		$langs = $langsTable->getList();
		
		$result = new ViewModel(array(
			'form' => $form,
			'title' => _('New email template'),
			'langs' => $langs,
			'activeLang' =>\Zend\Registry::get('lang'),
		));
		$result->setTemplate('admin/'.$this->url.'/edit');
		return $result;
	}

	
	public function deleteAction() {
		$id = (int)$this->request->getPost('id');

		$this->templateTable->delete(array(
			'id' => $id,
		));
		return $this->getResponse()->setContent('OK');
	}
	
	/**
	 * apply filters
	 * 
	 * @return array
	 */
	protected function resolveParams() {
		return array();
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
				case 0: $orderby="id"; break;
				case 1: $orderby="name"; break;
				default: $orderby="id"; break;
			}
			$orderby.=' '.$orderdir;
		}
		
		return $orderby;
	}
	
	public function listAction() {
		header("Content-Type: application/json");
		$count = (int)$this->params()->fromQuery('count', 50);
		$pos = (int)$this->params()->fromQuery('posStart', 0);
		$params = $this->resolveParams();
		$orderby = $this->resolveOrderby();

		$total = 0;
		$list = $this->templateTable->find($params, $count, $pos, $orderby, $total);
		$xmlResult = new ViewModel(array(
			'pos' => $pos,
			'total' => $total,
			'list' => $list,
			'isAllowedDelete' => $this->user->isAllowed('Admin\Controller\Template', 'delete'),
		));
		$xmlResult->setTerminal(true);
		return $xmlResult;
	}

}
