<?php

namespace SimpleSAML\Module\drupalauth;

use SimpleSAML\Configuration;
use SimpleSAML\Utils\Config;

/**
 * Drupal authentication source configuration parser.
 */
class ConfigHelper
{


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
   * The name of the cookie
   */
    private $cookie_name;


    /**
     * Cookie path.
     */
    private $cookie_path;


    /**
     * Cookie salt.
     */
    private $cookie_salt;


    /**
   * The Drupal logout URL
   */
    private $drupal_logout_url;


  /**
   * The Drupal login URL
   */
    private $drupal_login_url;


    /**
     * Constructor for this configuration parser.
     *
     * @param array $config  Configuration.
     * @param string $location  The location of this configuration. Used for error reporting.
     */
    public function __construct($config, $location)
    {
        assert(is_array($config));
        assert(is_string($location));

        $this->location = $location;

        /* Get authsource configuration. */
        $config = Configuration::loadFromArray($config, $location);

        $this->drupalroot = $config->getString('drupalroot');
        $this->debug = $config->getBoolean('debug', false);
        $this->attributes = $config->getArray('attributes', []);
        $this->cookie_name = $config->getString('cookie_name', 'drupalauth4ssp');
        $this->drupal_logout_url = $config->getString('drupal_logout_url', null);
        $this->drupal_login_url = $config->getString('drupal_login_url', null);

        $this->cookie_path = Configuration::getInstance()->getBasePath();
        $this->cookie_salt = Config::getSecretSalt();
    }


    /**
     * Returns debug mode.
     *
     * @return boolean
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * Returns Drupal root directory.
     *
     * @return string
     */
    public function getDrupalroot()
    {
        return $this->drupalroot;
    }

  /**
   * Return the attributes
   *
   * @return array
   */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Returns cookie name.
     *
     * @return string
     */
    public function getCookieName()
    {
        return $this->cookie_name;
    }

    /**
     * Returns cookie path.
     *
     * @return string
     */
    public function getCookiePath()
    {
        return $this->cookie_path;
    }

    /**
     * Returns cookie salt.
   *
     * @return string
     */
    public function getCookieSalt()
    {
        return $this->cookie_salt;
    }

    /**
     * Returns Drupal logout URL.
     *
     * @return string
   */
    public function getDrupalLogoutURL()
    {
        return $this->drupal_logout_url;
    }

  /**
   * Returns Drupal login URL.
   *
   * @return string
   */
    public function getDrupalLoginURL()
    {
        return $this->drupal_login_url;
    }
}
