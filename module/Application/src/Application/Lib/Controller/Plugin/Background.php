<?php

namespace Application\Lib\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Model\ViewModel;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Background extends AbstractPlugin implements ServiceLocatorAwareInterface {

	protected $serviceLocator;

	protected function before() {
		session_write_close();
		ignore_user_abort(true);
		set_time_limit(0);
	}

	/**
	 * @param callable $callback
	 */
	protected function after(callable $callback) {
		flush();
		if (function_exists('fastcgi_finish_request')) {
			fastcgi_finish_request();
		}

		call_user_func($callback);
		exit;
	}

	/**
	 * @param string $redirectUrl
	 * @param callable $callback
	 */
	public function sendDisconnectRedirect($redirectUrl, callable $callback) {
		$this->before();

		header("Location: $redirectUrl", true);
		header('Connection: close', true);
		header('Content-Encoding: none\r\n');
		header('Content-Length: 0', true);
		ob_flush();

		$this->after($callback);
	}

	/**
	 * @param ViewModel $view
	 * @param callable $callback
	 */
	public function sendDisconnect(ViewModel $view, callable $callback) {
		$this->before();

		ob_end_clean();
		ob_start();
		$renderer = $this->getController()->getServiceLocator()->get('Zend\View\Renderer\RendererInterface');
		$this->getController()->layout()->setVariable('content', $renderer->render($view));
		echo $renderer->render($this->getController()->layout());
		$size = ob_get_length();
		header("Content-Length: $size");
		ob_end_flush();

		$this->after($callback);
	}

	/**
	 * Returns standard json response (use it for all ajax actions) and try to disconnect client, then continue running
	 *
	 * @param mixed $data - any data for js processor
	 * @param ViewModel|false $view - ViewModel object or string to be placed on frontend
	 * @param string $action - (values: none, redirect, alert, content, error)
	 * @param string $status - (values: succes, error)
	 * @param callable $callback
	 */
	public function sendJSONResponseDisconnect($data = [], $view = false, $action = 'content', $status = 'success', callable $callback) {
		$this->before();

		ob_end_clean();
		ob_start();
		echo $this->getController()
			->getServiceLocator()
			->get('ViewRenderer')
			->render($this->getController()->sendJSONResponse($data, $view, $action, $status));
		$size = ob_get_length();
		header("Content-Length: $size");
		ob_end_flush();

		$this->after($callback);
	}

	/**
	 * Set service locator
	 *
	 * @param ServiceLocatorInterface $serviceLocator
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
		$this->serviceLocator = $serviceLocator;
	}

	/**
	 * Get service locator
	 *
	 * @return ServiceLocatorInterface
	 */
	public function getServiceLocator() {
		return $this->serviceLocator;
	}
}
