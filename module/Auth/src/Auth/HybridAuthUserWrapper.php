<?php

namespace Auth;

use Hybridauth\Entity\Profile;
use Auth\UserInterface;

/**
 * This class works as proxy to the HybridAuth-User-Object
 */
class HybridAuthUserWrapper implements UserInterface
{
	/**
	 * The HybridAuth-User-object
	 *
	 * @var Hybridauth\Entity\Profile $userProfile
	 */
	protected $user = null;

	/**
	 * Set the user-object
	 *
	 * @param Hybridauth\Entity\Profile $userProfile The userprofile to use
	 *
	 * @return HybridAuthUserProxy
	 */
	public function setUser(Profile $user)
	{
		$this->user = $user;
		return $this;
	}

	/**
	 * Get the ID of the user
	 *
	 * @return string
	 */
	public function getUID()
	{
		return $this->user->getIdentifier();
	}

	/**
	 * get provider Id
	 * 
	 * @return string
	 */
	public function getProdiverId()
	{
		return $this->user->getAdapter();
	}

	/**
	 * Get the name of the user
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->user->getDisplayName();
	}

	/**
	 * Get the eMail-Address of the user
	 *
	 * @return string
	 */
	public function getMail()
	{
		return $this->user->getEmail();
	}

	/**
	 * Get the language of the user
	 *
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->user->getLanguage();
	}

	/**
	 * Get the display-name of the user.
	 */
	public function getDisplayName()
	{
		return $this->user->getDisplayName();
	}

	/**
	 * Get the firstname of the user.
	 */
	public function getFirstName()
	{
		return $this->user->getFirstName();
	}

	/**
	 * Get the lastname of the user.
	 */
	public function getLastName()
	{
		return $this->user->getLastName();
	}

	/**
	 * get birth day of the user
	 * 
	 * @return int
	 */
	function getBirthDay()
	{
		return $this->user->getBirthDay();
	}

	/**
	 * get birth month of the user
	 * 
	 * @return int
	 */
	function getBirthMonth()
	{
		return $this->user->getBirthMonth();
	}

	
	/**
	 * get birth year of the user
	 * 
	 * @return int
	 */
	function getBirthYear()
	{
		return $this->user->getBirthYear();
	}

	/**
	 * get url to the biggest avater size
	 * 
	 * @return string
	 */
	function getPhotoURL()
	{
		return $this->user->getPhotoURL(9999, 9999);
	}
}
