<?php

namespace SimpleSAML\Module\drupalauth;

use SimpleSAML\Configuration;

/**
 * Drupal authentication source configuration parser.
 */
class ConfigHelper
{
    /**
     * String with the location of this configuration.
     * Used for error reporting.
     */
    private string $location;


    /**
     * The filesystem path to the Drupal directory
     */
    private string $drupalroot;


    /**
     * Whether debug output is enabled.
     *
     * @var bool
     */
    private bool $debug;


  /**
   * The attributes we should fetch. Can be NULL in which case we will fetch all attributes.
   */
    private ?array $attributes;


    /**
   * The Drupal logout URL
   */
    private string $drupal_logout_url;


  /**
   * The Drupal login URL
   */
    private string $drupal_login_url;


    /**
     * Constructor for this configuration parser.
     *
     * @param array $config  Configuration.
     * @param string $location  The location of this configuration. Used for error reporting.
     */
    public function __construct(array $config, string $location)
    {
        assert(is_array($config));
        assert(is_string($location));

        $this->location = $location;

      /* Get authsource configuration. */
        $config = Configuration::loadFromArray($config, $location);

        $this->drupalroot = $config->getString('drupalroot');
        $this->debug = $config->getOptionalBoolean('debug', false);
        $this->attributes = $config->getOptionalArray('attributes', null);
        $this->drupal_logout_url = $config->getString('drupal_logout_url');
        $this->drupal_login_url = $config->getString('drupal_login_url');
    }

    /**
     * Returns debug mode.
     *
     * @return bool
     */
    public function getDebug(): bool
    {
        return $this->debug;
    }

    /**
     * Returns Drupal root directory.
     *
     * @return string
     */
    public function getDrupalroot(): string
    {
        return $this->drupalroot;
    }

  /**
   * Return the attributes
   *
   * @return array
   */
    public function getAttributes(): ?array
    {
        return $this->attributes;
    }


    /**
     * Returns Drupal logout URL.
     *
     * @return string
   */
    public function getDrupalLogoutURL(): string
    {
        return $this->drupal_logout_url;
    }

  /**
   * Returns Drupal login URL.
   *
   * @return string
   */
    public function getDrupalLoginURL(): string
    {
        return $this->drupal_login_url;
    }
}
