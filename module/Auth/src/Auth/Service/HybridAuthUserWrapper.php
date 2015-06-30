<?php

namespace Auth\Service;

use Auth\Service\UserInterface;

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
	public function setUser(\Hybrid_User_Profile $user)
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
		return $this->user->identifier;
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
		return $this->user->displayName;
	}

	/**
	 * Get the eMail-Address of the user
	 *
	 * @return string
	 */
	public function getMail()
	{
		return $this->user->email;
	}

	/**
	 * Get the language of the user
	 *
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->user->language;
	}

	/**
	 * Get the display-name of the user.
	 */
	public function getDisplayName()
	{
		return $this->user->displayName;
	}

	/**
	 * Get the firstname of the user.
	 */
	public function getFirstName()
	{
		return $this->user->firstName;
	}

	/**
	 * Get the lastname of the user.
	 */
	public function getLastName()
	{
		return $this->user->lastName;
	}

	/**
	 * get birth day of the user
	 * 
	 * @return int
	 */
	function getBirthDay()
	{
		return $this->user->birthDay;
	}

	/**
	 * get birth month of the user
	 * 
	 * @return int
	 */
	function getBirthMonth()
	{
		return $this->user->birthMonth;
	}

	
	/**
	 * get birth year of the user
	 * 
	 * @return int
	 */
	function getBirthYear()
	{
		return $this->user->birthYear;
	}

	/**
	 * get url to the biggest avater size
	 * 
	 * @return string
	 */
	function getPhotoURL()
	{
		return $this->user->photoURL;
	}
}
