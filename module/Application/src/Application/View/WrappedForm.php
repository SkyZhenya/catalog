<?php

namespace Application\View;

use Zend\View\Helper\AbstractHelper;

class WrappedForm extends AbstractHelper {
	public function __invoke(\Zend\Form\Form $form) {
		$view   = $this->getView();

		$elements = $form->getElements();
		$form->prepare();

		$html = $view->form()->openTag($form);

		$html .= '<div class="zendForm">';
		foreach($elements as $element) {
			//\Zend\Debug\Debug::dump($element);
			$html .= $view->wrappedElement($element);
		}

		$html .= '</div>';
		$html .= $view->form()->closeTag($form);

		return $html;
	}
}
