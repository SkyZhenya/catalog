<?php
/**
 * @author Valeriy Tropin <valeriy.tropin@codeit.com.ua>
 */

namespace Application\Lib\Form\View\Helper\Captcha;

use Application\Lib\Captcha\Adapter\InOutputImage as CaptchaAdapter;
use Zend\Form\ElementInterface;
use Zend\Form\Exception;
use Zend\Form\View\Helper\Captcha\AbstractWord;

class InOutputImage extends AbstractWord {

	/**
	 * Render the captcha
	 *
	 * @param ElementInterface $element
	 * @throws Exception\DomainException
	 * @return string
	 */
	public function render(ElementInterface $element)
	{
		$captcha = $element->getCaptcha();

		if ($captcha === null || !$captcha instanceof CaptchaAdapter) {
			throw new Exception\DomainException(sprintf(
				'%s requires that the element has a "captcha" attribute of type Application\Lib\Captcha\Adapter\InOutputImage; none found',
				__METHOD__
			));
		}

		$imgAttributes = array(
			'width' => $captcha->getWidth(),
			'height' => $captcha->getHeight(),
			'alt' => $captcha->getImgAlt(),
			'src' => $captcha->getImgUrl(),
		);

		if ($element->hasAttribute('id')) {
			$imgAttributes['id'] = $element->getAttribute('id') . '-image';
		}

		$closingBracket = $this->getInlineClosingBracket();
		$img = sprintf(
			'<img %s%s',
			$this->createAttributesString($imgAttributes),
			$closingBracket
		);

		$position = $this->getCaptchaPosition();
		$separator = $this->getSeparator();
		$captchaInput = $this->renderCaptchaInputs($element);

		$pattern = '%s%s%s';
		if ($position == self::CAPTCHA_PREPEND) {
			return sprintf($pattern, $captchaInput, $separator, $img);
		}

		return sprintf($pattern, $img, $separator, $captchaInput);
	}
}