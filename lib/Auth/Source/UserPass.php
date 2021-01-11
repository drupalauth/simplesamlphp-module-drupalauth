<?php

namespace SimpleSAML\Module\drupalauth\Auth\Source;

use Drupal\user\Entity\User;
use SimpleSAML\Error\Error;
use SimpleSAML\Module\core\Auth\UserPassBase;
use SimpleSAML\Module\drupalauth\ConfigHelper;
use SimpleSAML\Module\drupalauth\DrupalHelper;

/**
 * Drupal authentication source for SimpleSAMLphp.
 *
 * This class is a Drupal authentication source which authenticates users
 * against a Drupal site located on the same server.
 *
 * !!! NOTE WELL !!!
 *
 * You must configure store.type in config/config.php to be something
 * other than phpsession, or this module will not work. SQL and memcache
 * work just fine. The tell tail sign of the problem is infinite browser
 * redirection when the SimpleSAMLphp login page should be presented.
 *
 * -------------------------------------------------------------------
 *
 * To use this put something like this into config/authsources.php:
 *
 * 'drupal-userpass' => array(
 *     'drupalauth:UserPass',
 *
 *     // The filesystem path of the Drupal directory.
 *     'drupalroot' => '/var/www/drupal-8.0',
 *
 *     // Whether to turn on debug
 *     'debug' => true,
 *
 *     // Which attributes should be retrieved from the Drupal site.
 *    'attributes' => array(
 *        array('field_name' => 'uid', 'attribute_name' => 'uid'),
 *        array('field_name' => 'roles', 'attribute_name' => 'roles', 'field_property' => 'target_id'),
 *        array('field_name' => 'name', 'attribute_name' => 'cn'),
 *        array('field_name' => 'mail', 'attribute_name' => 'mail'),
 *        array('field_name' => 'field_first_name', 'attribute_name' => 'givenName'),
 *        array('field_name' => 'field_last_name', 'attribute_name' => 'sn'),
 *        array('field_name' => 'field_organization', 'attribute_name' => 'ou', 'field_property' => 'target_id'),
 *    ),
 * ),
 *
 * Format of the 'attributes' array explained:
 * - field_name - name of the Drupal field.
 * - field_property - name of the field property. "value" by default.
 * - attribute_name - name of the attribute to place field component value in.
 *
 * Leave 'attributes' empty or unset to get all available field values.
 * Attribute names in this case would be "$field_name:$property_name".
 */
class UserPass extends UserPassBase
{

    /**
     * Configuration object.
     *
     * @var \SimpleSAML\Module\drupalauth\ConfigHelper
     */
    private $config;

    /**
     * Constructor for this authentication source.
     *
     * @param array $info Information about this authentication source.
     * @param array $config Configuration.
     */
    public function __construct($info, $config)
    {
        assert(is_array($info));
        assert(is_array($config));

        /* Call the parent constructor first, as required by the interface. */
        parent::__construct($info, $config);

        /* Get the configuration for this module */
        $drupalAuthConfig = new ConfigHelper(
            $config,
            'Authentication source ' . var_export($this->getAuthId(), true)
        );

        $this->config = $drupalAuthConfig;
    }


    /**
     * Attempt to log in using the given username and password.
     *
     * On a successful login, this function should return the users attributes. On failure,
     * it should throw an exception. If the error was caused by the user entering the wrong
     * username or password, a SimpleSAML_Error_Error('WRONGUSERPASS') should be thrown.
     *
     * Note that both the username and the password are UTF-8 encoded.
     *
     * @param string $username The username the user wrote.
     * @param string $password The password the user wrote.
     * @return array  Associative array with the users attributes.
     */
    protected function login($username, $password)
    {
        assert(is_string($username));
        assert(is_string($password));

        $drupalHelper = new DrupalHelper();
        $drupalHelper->bootDrupal($this->config->getDrupalroot());

        /* @value \Drupal\user\UserAuth $userAuth */
        $userAuth = \Drupal::service('user.auth');

        // Authenticate the user.
        $uid = $userAuth->authenticate($username, $password);
        if ($uid === false) {
            throw new Error('WRONGUSERPASS');
        }

        // Load the user object from Drupal.
        $drupaluser = User::load($uid);
        if ($drupaluser->isBlocked()) {
            throw new Error('NOACCESS');
        }

        $requested_attributes = $this->config->getAttributes();

        return $drupalHelper->getAttributes($drupaluser, $requested_attributes);
    }
}
