<?php

namespace SimpleSAML\Module\drupalauth\Auth\Source;

use Drupal\Component\Utility\Crypt;
use Drupal\user\Entity\User;
use SimpleSAML\Auth\Source;
use SimpleSAML\Auth\State;
use SimpleSAML\Error\BadRequest;
use SimpleSAML\Error\Error;
use SimpleSAML\Error\Exception;
use SimpleSAML\Module;
use SimpleSAML\Module\drupalauth\ConfigHelper;
use SimpleSAML\Module\drupalauth\DrupalHelper;
use SimpleSAML\Utils\HTTP;

/**
 * Drupal authentication source for SimpleSAMLphp using Drupal's login page.
 *
 * This class is an authentication source which is designed to
 * more closely integrate with a Drupal site. It causes the user to be
 * delivered to Drupal's login page, if they are not already authenticated.
 *
 * !!! NOTE WELL !!!
 *
 * You must configure store.type in config/config.php to be something
 * other than phpsession, or this module will not work. SQL and memcache
 * work just fine. The tell tail sign of the problem is infinite browser
 * redirection when the SimpleSAMLphp login page should be presented.
 *
 *
 * You must install the drupalauth4ssp module into Drupal to complete the
 * login integration, since this class will send users to the Drupal login
 * page to authenticate instead of presenting a SimpleSAMLphp login page.
 *
 * -------------------------------------------------------------------
 *
 * To use this put something like this into config/authsources.php:
 *
 * 'drupal-userpass' => array(
 *     'drupalauth:External',
 *
 *     // The filesystem path of the Drupal directory.
 *     'drupalroot' => '/var/www/drupal-8.0',
 *
 *     // Whether to turn on debug
 *     'debug' => true,
 *
 *     // URL of the Drupal logout page.
 *     'drupal_logout_url' => 'https://www.example.com/drupal7/user/logout',
 *
 *     // URL of the Drupal login page.
 *     'drupal_login_url' => 'https://www.example.com/drupal7/user',
 *
 *     // Which attributes should be retrieved from the Drupal site.
 *     'attributes' => array(
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
class External extends Source
{

  /**
   * The string used to identify Drupal user ID.
   */
    const DRUPALAUTH_EXTERNAL_USER_ID = 'drupalauth:External:UserID';

  /**
   * The string used to identify authentication source.
   */
    const DRUPALAUTH_AUTH_ID = 'drupalauth:AuthID';

  /**
   * The string used to identify our states.
   */
    const DRUPALAUTH_EXTERNAL = 'drupalauth:External';

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
     * Retrieve attributes for the user.
     *
     * @return array|NULL  The user's attributes, or NULL if the user isn't authenticated.
     */
    private function getUser($drupaluid)
    {
        if (!empty($drupaluid)) {
            $drupalHelper = new DrupalHelper();
            $drupalHelper->bootDrupal($this->config->getDrupalroot());

          // Load the user object from Drupal.
            $drupaluser = User::load($drupaluid);
            if ($drupaluser->isBlocked()) {
                throw new Error('NOACCESS');
            }

            $requested_attributes = $this->config->getAttributes();

            return $drupalHelper->getAttributes($drupaluser, $requested_attributes);
        }
    }

    /**
     * Log in using an external authentication helper.
     *
     * @param array &$state  Information about the current authentication.
     */
    public function authenticate(&$state)
    {
        assert(is_array($state));

      /*
         * The user is already authenticated.
         *
         * Add the users attributes to the $state-array, and return control
         * to the authentication process.
         */
      if (!empty($state[self::DRUPALAUTH_EXTERNAL_USER_ID])) {
          $state['Attributes'] = $this->getUser($state[self::DRUPALAUTH_EXTERNAL_USER_ID]);
          return;
        }

        /*
         * The user isn't authenticated. We therefore need to
         * send the user to the login page.
         */

        /*
         * First we add the identifier of this authentication source
         * to the state array, so that we know where to resume.
         */
        $state[self::DRUPALAUTH_AUTH_ID] = $this->getAuthId();

        /*
         * We need to save the $state-array, so that we can resume the
         * login process after authentication.
         *
         * Note the second parameter to the saveState-function. This is a
         * unique identifier for where the state was saved, and must be used
         * again when we retrieve the state.
         *
         * The reason for it is to prevent
         * attacks where the user takes a $state-array saved in one location
         * and restores it in another location, and thus bypasses steps in
         * the authentication process.
         */
        $stateId = State::saveState($state, self::DRUPALAUTH_EXTERNAL);

        /*
         * Now we generate a URL the user should return to after authentication.
         * We assume that whatever authentication page we send the user to has an
         * option to return the user to a specific page afterwards.
         */
        $returnTo = Module::getModuleURL('drupalauth/resume.php', [
            'State' => $stateId,
        ]);

        /*
         * Get the URL of the authentication page.
         *
         * Here we use the getModuleURL function again, since the authentication page
         * is also part of this module, but in a real example, this would likely be
         * the absolute URL of the login page for the site.
         */
        $authPage = $this->config->getDrupalLoginURL();

        /*
         * The redirect to the authentication page.
         *
         * Note the 'ReturnTo' parameter. This must most likely be replaced with
         * the real name of the parameter for the login page.
         */
        HTTP::redirectTrustedURL($authPage, [
            'ReturnTo' => $returnTo,
        ]);

        /*
         * The redirect function never returns, so we never get this far.
         */
        assert(false);
    }

    /**
     * Resume authentication process.
     *
     * This function resumes the authentication process after the user has
     * entered his or her credentials.
     *
     * @param array &$state  The authentication state.
     */
    public static function resume($stateID)
    {
        /*
         * First we need to restore the $state-array. We should have the identifier for
         * it in the 'State' request parameter.
         */
        if (!isset($stateID)) {
            throw new BadRequest('Missing "State" parameter.');
        }

        /*
         * Once again, note the second parameter to the loadState function. This must
         * match the string we used in the saveState-call above.
         */
        $state = State::loadState($stateID, self::DRUPALAUTH_EXTERNAL);

        /*
         * Now we have the $state-array, and can use it to locate the authentication
         * source.
         */
        $source = Source::getById($state[self::DRUPALAUTH_AUTH_ID]);
        if ($source === null) {
            /*
             * The only way this should fail is if we remove or rename the authentication source
             * while the user is at the login page.
             */
            throw new Exception('Could not find authentication source with ID: ' . $state[self::DRUPALAUTH_AUTH_ID]);
        }

        /*
         * Make sure that we haven't switched the source type while the
         * user was at the authentication page. This can only happen if we
         * change config/authsources.php while an user is logging in.
         */
        if (!($source instanceof self)) {
            throw new Exception('Authentication source type changed.');
        }

        /*
         * First we check that the user is acutally logged in, and didn't simply skip the login page.
         */
        if (empty($state[self::DRUPALAUTH_EXTERNAL_USER_ID])) {
          throw new Exception('User ID is missing.');
        }

        /*
         * OK, now we know that our current state is sane. Time to actually log the user in.
         */
        $attributes = $source->getUser($state[self::DRUPALAUTH_EXTERNAL_USER_ID]);
        if ($attributes === null) {
            /*
             * The user isn't authenticated.
             *
             * Here we simply throw an exception, but we could also redirect the user back to the
             * login page.
             */
            throw new Exception('User not authenticated after login page.');
        }

        /*
         * So, we have a valid user. Time to resume the authentication process where we
         * paused it in the authenticate()-function above.
         */

        $state['Attributes'] = $attributes;
        Source::completeAuth($state);

        /*
         * The completeAuth-function never returns, so we never get this far.
         */
        assert(false);
    }

    /**
     * This function is called when the user start a logout operation, for example
     * by logging out of a SP that supports single logout.
     *
     * @param array &$state  The logout state array.
     */
    public function logout(&$state)
    {
        assert(is_array($state));

        if (!session_id()) {
            // session_start not called before. Do it here
            session_start();
        }

        $logout_url = $this->config->getDrupalLogoutURL();
        $parameters = [];
        if (!empty($state['ReturnTo'])) {
            $parameters['ReturnTo'] = $state['ReturnTo'];
        }

        HTTP::redirectTrustedURL($logout_url, $parameters);
    }
}
