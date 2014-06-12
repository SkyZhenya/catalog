<?php
namespace Application\Lib\Validator;

use Zend\Validator\EmailAddress;

class CustomEmailValidator extends EmailAddress
{
    /**
     * Returns array of validation failure messages
     *
     * @return array
     */
    public function getMessages()
    {
      $result = parent::getMessages();
      if (!empty($result)) {
				$result = array(
					_("Email address doesn't appear to be valid."),
				);
      }
       return $result;
    }
}