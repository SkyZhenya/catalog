<?php

namespace Auth\Service;

interface UserInterface {
	/**
	 * Get the ID of the user
	 *
	 * @return string
	 */
	public function getUID();

	/**
	 * Get the name of the user
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Get the eMail-Address of the user
	 *
	 * @return string
	 */
	public function getMail();

	/**
	 * Get the language of the user
	 *
	 * @return string
	 */
	public function getLanguage();
}
