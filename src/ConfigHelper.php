<?php

namespace SimpleSAML\Module\drupalauth;

use SimpleSAML\Configuration;

/**
 * Drupal authentication source configuration parser.
 */
class ConfigHelper
{
    /**
     * @var \SimpleSAML\Configuration
     */
    private Configuration $config;

    /**
     * Constructor for this configuration parser.
     *
     * @param array $config  Configuration.
     * @param string $location  The location of this configuration. Used for error reporting.
     */
    public function __construct(array $config, string $location)
    {
        $this->config = Configuration::loadFromArray($config, $location);
    }

    /**
     * Returns debug mode.
     *
     * @return bool
     */
    public function getDebug(): bool
    {
        return $this->config->getOptionalBoolean('debug', false);
    }

    /**
     * Returns Drupal root directory.
     *
     * @return string
     */
    public function getDrupalRoot(): string
    {
        return $this->config->getString('drupalroot');
    }

  /**
   * Return the attributes
   *
   * @return array
   */
    public function getAttributes(): ?array
    {
        return $this->config->getOptionalArray('attributes', null);
    }


    /**
     * Returns Drupal logout URL.
     *
     * @return string
   */
    public function getDrupalLogoutUrl(): string
    {
        return $this->config->getString('drupal_logout_url');
    }

  /**
   * Returns Drupal login URL.
   *
   * @return string
   */
    public function getDrupalLoginUrl(): string
    {
        return $this->config->getString('drupal_login_url');
    }
}
