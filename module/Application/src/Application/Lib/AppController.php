<?php
namespace Application\Lib;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\EventManager\EventManagerInterface;
use Zend\View\Model\ViewModel;

/**
 * Class AppController
 * @package Application\Lib
 *
 * @method \Application\Lib\Controller\Plugin\Background background()
 */
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
	 * Json Format
	 * 
	 * @var string
	 */
	public $jsonFormat = null;

	/**
	 * Construct default controller, create lang table
	 * 
	 * @param mixed $forceAuth
	 * @return AppController
	 */
	public function __construct($forceAuth = false) {
		$this->forceAuth = $forceAuth;
		$this->jsonFormat = \JSON_UNESCAPED_UNICODE;
		if(DEBUG) $this->jsonFormat |= \JSON_PRETTY_PRINT;
	}

	public function ready() {	
		try {
			$user = \Utils\Registry::get('User');
		}
		catch(\Exception $e) {
			$user = new User();
			$user->auth($this->forceAuth);
			\Utils\Registry::set('User', $user);
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
					\Utils\Registry::set('lang', $controller->lang->id);
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
	protected function renderViewByTemplate($template, $variables = array()) {
		$htmlViewPart = new ViewModel();
		$htmlViewPart->setTerminal(true)
		->setTemplate($template)
		->setVariables($variables);

		$viewRender = $this->getServiceLocator()->get('ViewRenderer');
		return $viewRender->render($htmlViewPart);
	}

	/**
	 * function return text from view
	 * 
	 * @param \Zend\View\Model\ViewModel $view
	 * @return string
	 */
	protected function renderView($view) {
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

	/**
	 * return unified json responce (use it for all ajax actions)
	 * 
	 * @param mixed $data - any data for js processor
	 * @param string $view - ViewModel object or string to be placed on frontend
	 * @param string $action - (values: none, redirect, alert, content, error)
	 * @param string $status - (values: succes, error)
	 * @param boolean $exit - echo data and die
	 * @return ViewModel 
	 * 
	 */
	public function sendJSONResponse($data = [], $view = false, $action = 'content', $status = 'success', $exit = false) {

		$statusList = [
			'success',
			'error'
		];

		$actionList = [
			'none',
			'redirect',
			'login', // display sign in/sign up form
			'alert',
			'content',
			'replaceContent',
		];

		if (!in_array($status, $statusList))
			throw new \Exception('prepareJSONResponse: Wrong response status');
		if (!in_array($action, $actionList))
			throw new \Exception('prepareJSONResponse: Wrong action');

		if ($view instanceof ViewModel) {
			$view->setTerminal(true);
			$content = $this->renderView($view);
		} else {
			$content = $view;
		}

		$result = [
			'status' => $status,
			'action' => $action,
			'content' => $content,
			'data' => $data
		];

		$jsonView = new ViewModel([
			'json' => $result,
			'jsonFormat' => $this->jsonFormat,
		]);
		$jsonView->setTerminal(true);
		$jsonView->setTemplate('service/json');

		if ($exit) {
			echo $this->renderView($jsonView);
			exit;
		} else
			return $jsonView;
	}

	/**
	 * send json error to client
	 * 
	 * @param string $text
	 * @param int $code
	 * @return \Zend\View\Model\ViewModel
	 */
	public function sendJSONError($text = '', $code = false, $title = '') {
		if (empty($title)) 
			$title = _('Error');
		return $this->sendJSONResponse(['code' => $code, 'message' => $text, 'title' => $title], false, 'none', 'error', true);
	}

	public function sendJSONAlert($text, $title = '') {
		if (empty($title)) 
			$title = _('Warning');
		return $this->sendJSONResponse(['content' => $text, 'title' => $title], false, 'alert', 'success');
	}

	/**
	 * retunrns json responce with error status
	 * 
	 * @param string $url
	 */
	public function sendJSONRedirect($url) {
		return $this->sendJSONResponse([$url], false, 'redirect', 'success');
	}

}
