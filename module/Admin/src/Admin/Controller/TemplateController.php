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

		$total = 0;
		$list = $this->templateTable->find([], 1, 0, null, $total);
		$result = [
			'total' => $total,
		];

		$this->renderHtmlIntoLayout('submenu', 'admin/template/submenu.phtml', $result);

		return $result;
	}

	public function editAction() {
		$id = $this->params('id',0);

		$this->setBreadcrumbs(['template' => 'Email Templates',], true);
		$form = $this->getForm();

		try {
			$data = (array)$this->templateTable->getFullLocalData($id);
			$form->setUpdated($data['updated']);
			$form->setData($data);
		}
		catch(\Exception $e) {
			return $this->notFoundAction();
		}

		if ($this->request->isPost()) {
			$data = $this->request->getPost()->toArray();
			$form->setData($data);
			if ($form->isValid()) {
				$data = $form->getData();
				$data['updated'] = TIME;
				$oldValue = $this->templateTable->setId($id);
				$this->templateTable->cacheDelete(base64_encode($oldValue->name));

				$this->templateTable->set($data);

				return $this->redirect()->toUrl(URL.'admin/template');
			}
		}

		$langsTable = new \Application\Model\LangTable();
		$langs = $langsTable->getList();

		$view = new ViewModel(array(
			'form' => $form,
			'error' => $this->error,
			'langs' => $langs->toArray(),
			'activeLang' =>\Utils\Registry::get('lang'),
		));
		return $view;
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
			$params []= array('name', 'LIKE', "%{$flName}%");
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
				case 0: $orderby="id"; break;
				case 1: $orderby="name"; break;
				default: $orderby="id"; break;
			}
			$orderby.=' '.$orderdir;
		}

		return $orderby;
	}

	public function listAction() {
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
			'isAllowedDelete' => false,
		));
		$xmlResult->setTerminal(true);
		return $xmlResult;
	}

}
