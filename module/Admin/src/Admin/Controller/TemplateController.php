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
	
	var $inputFilter;
	
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
		
		if ($id > 0) {
			try {
				$data = (array)$this->templateTable->getFullLocalData($id); 
				$form->setData($data);
			}
			catch(\Exception $e) {
				$this->error = _('Template not found');
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
						$this->templateTable->setId($id);
						$this->templateTable->set($data);
					}
					else {
						$id = $this->templateTable->insert($data);
						$form->setAttribute('action', URL.'admin/'.$this->url.'/edit/'.$id);
						$form->get('id')->setValue($id);
						$wasAdded = true;
					}
				}
			}
		}

		$canClosePage = !count($form->getMessages());
		
		$langsTable = new \Application\Model\LangTable();
		$langs = $langsTable->getList();
		
		$view = new ViewModel(array(
			'form' => $form,
			'canClosePage' => $canClosePage,
			'error' => $this->error,
			'title' => _('Email template'),
			'langs' => $langs,
			'activeLang' =>\Zend\Registry::get('lang'),
			'wasAdded' => $wasAdded,
		));
		$this->layout('layout/iframe');
		return $view;
	}

	public function addAction(){
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
		$this->layout('layout/iframe');
		return $result;
	}

	
	public function deleteAction() {
		$id = (int)$this->request->getPost('id');

		$this->templateTable->delete(array(
			'id' => $id,
		));
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
		$orderby=$this->templateTable->table.".id";

		$params = array();


		if(isset($_GET['orderby'])) {
			switch($_GET['order']) {
				case 'asc': $orderdir='asc'; break;
				default: $orderdir='desc';
			}
			switch($_GET['orderby']) {
				case 1: $orderby=$this->templateTable->table.".id"; break;
				default: $orderby=$this->templateTable->table.".id"; break;
			}
			$orderby.=' '.$orderdir;
		}

		$total = 0;
		$list = $this->templateTable->find($params, $count, $pos, $orderby, $total);
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
		<rows pos='$pos' total_count='$total'>\n";
		$i=$pos;
		foreach($list as $item) {
			$i++;
	    $itemActions = "<a class='edit' onclick='initFancybox(\"".$this->basePath('admin/'.$this->url.'/edit/')."{$item->id}\"); (arguments[0]||window.event).cancelBubble=true; return false;' href='".$this->basePath('admin/'.$this->url.'/edit')."?id={$item->id}'>"._("Edit")."</a> ";
      $deleteActions = "<a class='del' href='#' onclick='common.confdel(\"".URL."admin/".$this->url."/delete?\",{$item->id}, common.removeItem); (arguments[0]||window.event).cancelBubble=true; return false;'>"._('Delete')."</a>";

			echo "<row id='{$item->id}'>\n
				<cell>{$i}</cell>
				<cell>{$item->id}</cell>
				<cell>".htmlspecialchars($item->name)."</cell>
				<cell>".htmlspecialchars($itemActions)."</cell>
				<cell>".htmlspecialchars($deleteActions)."</cell>
			</row>\n";
		}
		echo "</rows>\n";
		exit();
	}

}
