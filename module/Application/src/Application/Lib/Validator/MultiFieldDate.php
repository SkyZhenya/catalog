<?php

namespace Application\Lib\Validator;

use Zend\Validator\AbstractValidator;

/**
 * Date validator for forms where date consists of three fields
 * @author Valeriy Tropin
 */
class MultiFieldDate extends AbstractValidator {

	const MISSING_YEAR = 'missingYear';
	const MISSING_MONTH = 'missingMonth';
	const MISSING_DAY = 'missingDay';
	const WRONG_DATE = 'invalidDate';

	protected $messageTemplates = array(
		self::MISSING_YEAR => 'These fields must be filled',
		self::MISSING_MONTH  => 'These fields must be filled',
		self::MISSING_DAY  => 'These fields must be filled',
		self::WRONG_DATE  => 'Selected date is wrong',
	);

	protected $yearFieldName = 'year';

	protected $monthFieldName = 'month';

	protected $dayFieldName = 'day';

	/**
	 * @param mixed $value
	 * @param array $context
	 * @return bool|void
	 */
	public function isValid($value,  array $context = null) {
		$this->setValue($value);

		if (!isset($context[$this->getYearFieldName()]) ||
			!isset($context[$this->getMonthFieldName()]) ||
			!isset($context[$this->getDayFieldName()])) {
			if (!isset($context[$this->getYearFieldName()]))
				$this->error(self::MISSING_YEAR);

			if (!isset($context[$this->getMonthFieldName()]))
				$this->error(self::MISSING_MONTH);

			if (!isset($context[$this->getDayFieldName()]))
				$this->error(self::MISSING_DAY);

			return false;
		}

		if (!checkdate($context[$this->getMonthFieldName()],
			$context[$this->getDayFieldName()],
			$context[$this->getYearFieldName()])) {
			$this->error(self::WRONG_DATE);
			return false;
		}

		return true;
	}

	/**
	 * @return string
	 */
	public function getDayFieldName() {
		return $this->dayFieldName;
	}

	/**
	 * @param string $dayFieldName
	 * @return $this
	 */
	public function setDayFieldName($dayFieldName) {
		$this->dayFieldName = $dayFieldName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMonthFieldName() {
		return $this->monthFieldName;
	}

	/**
	 * @param string $monthFieldName
	 * @return $this
	 */
	public function setMonthFieldName($monthFieldName) {
		$this->monthFieldName = $monthFieldName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getYearFieldName() {
		return $this->yearFieldName;
	}

	/**
	 * @param string $yearFieldName
	 * @return $this
	 */
	public function setYearFieldName($yearFieldName) {
		$this->yearFieldName = $yearFieldName;
		return $this;
	}
}
