<?php

/**
 * Drupal authentication source configuration parser.
 *
 * Copyright SIL International, Steve Moitozo, <steve_moitozo@sil.org>, http://www.sil.org 
 *
 * This class is a Drupal authentication source which authenticates users
 * against a Drupal site located on the same server.
 *
 *
 * The homepage of this project: http://code.google.com/p/drupalauth/
 *
 * See the drupalauth-entry in config-templates/authsources.php for information about
 * configuration of these options.
 *
 * @author Steve Moitozo <steve_moitozo@sil.org>, SIL International
 * @package drupalauth
 * @version $Id$
 */
class sspmod_drupalauth_ConfigHelper {


	/**
	 * String with the location of this configuration.
	 * Used for error reporting.
	 */
	private $location;


	/**
	 * The filesystem path to the Drupal directory
	 */
	private $drupalroot;


	/**
	 * Whether debug output is enabled.
	 *
	 * @var bool
	 */
	private $debug;


	/**
	 * The attributes we should fetch. Can be NULL in which case we will fetch all attributes.
	 */
	private $attributes;


	/**
	 * Constructor for this configuration parser.
	 *
	 * @param array $config  Configuration.
	 * @param string $location  The location of this configuration. Used for error reporting.
	 */
	public function __construct($config, $location) {
		assert('is_array($config)');
		assert('is_string($location)');

		$this->location = $location;

		/* Parse configuration. */
		$config = SimpleSAML_Configuration::loadFromArray($config, $location);

		$this->drupalroot = $config->getString('drupalroot');
		$this->debug = $config->getBoolean('debug', FALSE);
		$this->attributes = $config->getArray('attributes', NULL);

	}
	

	/**
	 * Return the debug
	 *
	 * @param boolean $debug whether or not debugging should be turned on
	 */
	public function getDebug() {
	   return $this->debug; 
	}

	/**
	 * Return the drupaldir
	 *
	 * @param string $drupalroot the directory of the Drupal site
	 */
	public function getDrupalroot() {
	   return $this->drupalroot; 
	}
		

	/**
	 * Return the attributes
	 *
	 * @param array $attributes the array of Drupal attributes to use, NULL means use all available attributes
	 */
	public function getAttributes() {
	   return $this->attributes; 
	}

}
