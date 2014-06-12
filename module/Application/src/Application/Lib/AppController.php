<?php
namespace Application\Lib;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\EventManager\EventManagerInterface;
use Zend\View\Model\ViewModel;

abstract class AppController extends AbstractActionController {
	protected $userId;
	public $lang;
	/**
	 * breadcrunbs array; now is used only for admin panel
	 * 
	 * @var array
	 */
	public $breadcrumbs;
	public $error = '';
	
	protected $forceAuth;
	
	/**
	* User class
	*
	* @var User
	*/
	protected $user;
	
	/**
	* Construct default controller, create lang table
	* 
	* @param mixed $forceAuth
	* @return AppController
	*/
	public function __construct($forceAuth = false) {
		$this->forceAuth = $forceAuth;
	}

	public function ready() {	
		try {
			$user = \Zend\Registry::get('User');
		}
		catch(\Exception $e) {
			$user = new User();
			$user->auth($this->forceAuth);
			\Zend\Registry::set('User', $user);
		}
		
		$this->user = $user;
		$this->userId = $user->getId();
	}
	
  /**
  * Inject an EventManager instance
  *
  * @param  EventManagerInterface $eventManager
  * @return void
  */
	public function setEventManager(EventManagerInterface $eventManager) {
		$controller = $this;
		$eventManager->attach('dispatch', function ($e) use ($controller) {
			$matches = $e->getRouteMatch();
			$params = $matches->getParams();
			//check language and set locale for translations;
			$lang = LOCALE;
			if (isset($params['lang']))
				$lang = $params['lang'];

			if($lang != '') {
				try {
					//get language data from DB to set locale
					//use it if there is possibility for site visitirs to change language 
					/*$langTable = new \Application\Model\LangTable();
					$controller->lang = $langTable->getByCode($lang);
					\Zend\Registry::set('lang', $controller->lang->id);
					*/
					
					setlocale(LC_ALL, $lang.'.UTF-8');
					bind_textdomain_codeset('messages', 'UTF8');
					bindtextdomain('messages', BASEDIR.'/module/Application/language');
					textdomain('messages');
				}
				catch(\Exception $e) {}
			}
			//end localization 
			$controller->ready();
			if (isset($params['__NAMESPACE__']) && $params['__NAMESPACE__']==='Admin\Controller') {
				$controller->setBreadcrumbs(array(), true);
			}
			$auth = new \Application\Lib\Authentication();
			$auth->preDispatch($params, $this);
		});
		parent::setEventManager($eventManager);
	}

	 public function basePath($path='') {
		 return URL.$path;
	 }

	 public function getParam($param){
	 	return $this->getEvent()->getRouteMatch()->getParam($param);
	 }

  /**
  * returnes error view template with current message
  *
  * @param str $mess
  * @return \Zend\View\Model\ViewModel
  */
  public function returnError($mess){
		$view = new \Zend\View\Model\ViewModel(array('err' => $mess));
		$view->setTemplate('service/error');
		return $view;
  }
  
	/**
	 * method sets breadcrumbs;
	 * add link to site home page by default
	 * 
	 * @param array $data
	 * @param bool $isAdmin;
	 */
	 protected function setBreadcrumbs($data = array(), $isAdmin = false){	 
		 $this->breadcrumbs = array(URL => SITE_NAME);
		 $baseUrl = URL.($isAdmin ? 'admin/' : '');
		 
		 if($isAdmin)
	 		 $this->breadcrumbs[$baseUrl] = _('Administration Panel');
		 
		 foreach($data as $url => $name)
	 		 $this->breadcrumbs[$baseUrl.$url] = $name;
		 
		 $this->layout()->breadcrumbs = $this->breadcrumbs; 
	 }
	 
	  /**
	   * Action called if matched action is forbidden
	   *
	   * @return array
	   */
	  public function forbiddenAction() {
	    $response   = $this->getResponse();
	    
	    $event      = $this->getEvent();
	    $routeMatch = $event->getRouteMatch();
	    $routeMatch->setParam('action', 'forbidden');

		  $response->setStatusCode(403);
		  $view = new ViewModel(array(
		    'error' => _('This action is forbidden for your role'),
		  ));
		  $view->setTemplate('service/forbidden.phtml');
		  return $view;
	  }

		/**
	   * Action called if matched action is 
	   *
	   * @return array
	   */
	  public function siteClosedAction() {
	    $response   = $this->getResponse();
	    
	    $event      = $this->getEvent();
	    $routeMatch = $event->getRouteMatch();
	    $routeMatch->setParam('action', 'siteClosed');

		  $response->setStatusCode(403);
		  $view = new ViewModel();
		  $view->setTemplate('service/siteClosed.phtml');
		  return $view;
	  } 
	  
		/**
		 * function return text from view template $template, replacing php-variables with data in $variables
		 * 
		 * @param string $template
		 * @param array $variables
		 * @return string
		 */
		protected function renderView($template, $variables = array()) {
			$htmlViewPart = new ViewModel();
		  $htmlViewPart->setTerminal(true)
		               ->setTemplate($template)
		               ->setVariables($variables);

		  $viewRender = $this->getServiceLocator()->get('ViewRenderer');
			return $viewRender->render($view);
		}	
		
		/**
		 * render html template into layout with $layoutVariable name
		 * 
		 * @param string $layoutVariable
		 * @param string $viewTemplate
		 * @param array $viewData
		 */
		public function renderHtmlIntoLayout($layoutVariable, $viewTemplate, $viewData = array()) {
			$controls = new ViewModel($viewData);
			$controls->setTemplate($viewTemplate);
			$this->layout()->addChild($controls, $layoutVariable);
		}
}
