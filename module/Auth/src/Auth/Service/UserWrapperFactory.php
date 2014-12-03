<?php

namespace Auth\Service;

/**
 * This class works as factory to get an Object implementing the UserInterface
 */
class UserWrapperFactory
{
	/**
	 * Create the user-Proxy according to the given User-Object
	 *
	 * @return UserInterface
	 * @throws \UnexpectedValueException
	 */
	public function factory($userObject)
	{
		if ($userObject instanceof \Hybridauth\Entity\Profile) {
			$userProxy = new \Auth\HybridAuthUserWrapper();
			$userProxy->setUser($userObject);
			return $userProxy;
		}

		throw new \UnexpectedValueException(sprintf(
			'The given Object could not be found. Found "%s" instead',
			get_Class($userObject)
		));
	}
}