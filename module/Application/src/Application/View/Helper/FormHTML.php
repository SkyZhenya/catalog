<?php
namespace Application\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;
use Zend\Form\ElementInterface;

class FormHTML extends AbstractHelper {
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
        $content            = (string) $element->getValue();

        return sprintf(
            '<div %s>%s</div>',
            $this->createAttributesString($attributes),
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