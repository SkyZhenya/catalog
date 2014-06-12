<?php
namespace Application\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;
use Zend\Form\ElementInterface;

class FormStylizedFile extends AbstractHelper {
    protected $validTagAttributes = array(
        'class'   => true,
        'style'        => true,
    );

    /**
     * Render a form <div> element from the provided $element
     *
     * @param  ElementInterface $element
     * @return string
     */
    public function render(ElementInterface $element)
    {

        $attributes         = $element->getAttributes();
        $button_label = _('Browse');
        $custom_label = $element->getOption('button_label');
        $input_label = $element->getOption('input_label');
        if (!empty($custom_label)){
					$button_label = $custom_label;
        }
    		$helper = new \Zend\Form\View\Helper\FormFile();
    		$content = $helper($element);
    		
        return sprintf(
            '<div class="fileload">
							<div class="file-load-block">
								%s
								<div class="fileLoad">
									<input type="text" value="'.$input_label.'" />
									<button>'.$button_label.'</button>
								</div>
							</div>
						</div>',
            $content
        );
    }

    /**
     * Invoke helper as functor
     *
     * Proxies to {@link render()}.
     *
     * @param  ElementInterface|null $element
     * @return string|FormTextarea
     */
    public function __invoke(ElementInterface $element = null)
    {
        if (!$element) {
            return $this;
        }

        return $this->render($element);
    }
} 