<?php

namespace Application\Lib;

use Zend\Mail\Transport\SmtpOptions;
use Zend\View\Model\ViewModel;

class Email {
	
	/**
	 * @var User
	 */
	var $recipient = null;

	
	/**
	 * send specified email to user
	 * 
	 * @param string $templateName
	 * @param User $recipient 
	 * @param array $content - variables for template
	 * @param mixed $sender (int - ID or array - from user table or string - email or User object)
	 * @param string $layout
	 */
	public function sendTemplate($templateName, User $recipient, $data = [], $sender = false, $checkSettings = false) {
		if($checkSettings) {
			$allowedList = $recipient->getEmailNotifications();
			if(!in_array($checkSettings, $allowedList)) return false;
		}
		$file = null;
		if (isset($data['file'])) {
			$file = $data['file'];
			unset($data['file']);
		}
		
		$templateTable = new \Application\Lib\Template();
		try {
			$template = $templateTable->prepareMessage($templateName, $data);
		}
		catch (\Exception $e) {
			//do not send anything
			return false;
		}
		$this->recipient = $recipient;
		$this->processSenderData($sender);
			
		//render layout with content
		$content = $this->renderTemplate($template['text']);

		$this->sendMail($template['subject'], $content, $this->recipient->email, $this->recipient->name, $sender->email, $sender->name, $file); 
		
		return true;
	}

	
	/**
	 * returns $sender as arrayObject with mandatory fields - email, name
	 * 
	 * @param mixed $sender (int or array or string or User object)
	 */
	private function processSenderData(&$sender) {
		$userTable = new \Application\Model\UserTable();
		
		if(is_numeric($sender)) {
			try {
				$sender = $userTable->get($sender);
			}
			catch(\Exception $e) {
				$sender = false;
			}
		}
		
		if(is_string($sender)) {
			$sender = [
				'name' => SITE_NAME,
				'email' => $sender
			];
		}
		
		if(!$sender) {
			$sender = [
				'name' => SITE_NAME,
				'email' => SUPPORT_EMAIL
			];
		}
		
		if(is_array($sender)) {
			$sender = new \ArrayObject($sender, 2);
		}

	}

	/**
	 * send email with HTML content
	 * 
	 * @param string $subject
	 * @param string $content
	 * @param string $toEmail
	 * @param string $toName
	 * @param string $fromEmail
	 * @param string $fromName
	 */
	public function sendMail($subject, $content, $toEmail, $toName = null, $fromEmail = false, $fromName = false, $file = array()) {
		if(!isset($toEmail) || empty($toEmail)) {
			throw new \Exception(_('E-Mail is not set or is empty'));
		}

		if(!$fromEmail) $fromEmail = SUPPORT_EMAIL;
		if(!$fromName) $fromName = SITE_NAME;

		$toEmail = explode(',', $toEmail);
		$fromEmail = current(explode(',', $fromEmail));
		
		$m = new \Zend\Mail\Message();
		$m->addFrom($fromEmail, $fromName);
		foreach($toEmail as $e) {
			$m->addTo($e, $toName);
		}
		$m->setSubject($subject);

		$bodyPart = new \Zend\Mime\Message();

		$bodyMessage = new \Zend\Mime\Part($content);
		$bodyMessage->type = 'text/html; charset=utf-8';

		$bodyPart->setParts(array($bodyMessage));
		
		if (!empty($file)) {
			$bodyFile = new \Zend\Mime\Part($file['content']);
			$bodyFile->type = (isset($file['type']) ? $file['type'] : 'application/octet-stream');
			$bodyFile->encoding = \Zend\Mime\Mime::ENCODING_BASE64;
			$bodyFile->disposition = (isset($file['disposition']) ? $file['disposition'] : 'attachment');
			$bodyFile->filename = $file['name'];
			if (isset($file['id'])) {
				$bodyFile->id = $file['id'];
			}

			$bodyPart->addPart($bodyFile);
		}

		$m->setBody($bodyPart);
		$m->setEncoding('UTF-8');

		$transport = $this->getTransport();
		$transport->send($m);
	}
  
	/**
	 * Returns SMTP transport depending on email confirmation status
	 * 	
	 * @return \Zend\Mail\Transport\Smtp
	 */
	public function getTransport() {
		$options = NULL;
		return new \Zend\Mail\Transport\Smtp($options);
	}
	
	/**
	 * render email template with layout
	 * 
	 * @param string $content
	 * @param array $layoutData
	 * @param string $layoutName
	 */
	public function renderTemplate($content, $layoutData = array(), $layoutName = 'email/layout.phtml') {
		$view = new \Zend\View\Renderer\PhpRenderer();
		$resolver = new \Zend\View\Resolver\TemplateMapResolver();
		$resolver->setMap([
			'mailLayout' => BASEDIR . '/module/Application/view/'.$layoutName,
		]);
		$view->setResolver($resolver);
		
		
		$htmlViewPart = new ViewModel();
		$layoutData['content'] = $content;
		$htmlViewPart->setTerminal(true)
		             ->setTemplate('mailLayout')
		             ->setVariables($layoutData);

		return $view->render($htmlViewPart);
	}

}