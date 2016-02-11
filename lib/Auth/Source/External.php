<?php

/**
 * Drupalath authentication source for using Drupal's login page.
 *
 * Copyright SIL International, Steve Moitozo, <steve_moitozo@sil.org>, http://www.sil.org
 *
 * This class is an authentication source which is designed to
 * more closely integrate with a Drupal site. It causes the user to be
 * delivered to Drupal's login page, if they are not already authenticated.
 *
 *
 * The homepage of this project: http://code.google.com/p/drupalauth/
 *
 * !!! NOTE WELLL !!!
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
 *  'drupal-userpass' => array(
 *    'drupalauth:External',
 *
 *    // The filesystem path of the Drupal directory.
 *    'drupalroot' => '/var/www/drupal-7.0',
 *
 *    // Whether to turn on debug
 *    'debug' => true,
 *
 *    // the URL of the Drupal logout page
 *    'drupal_logout_url' => 'https://www.example.com/drupal7/user/logout',
 *
 *    // the URL of the Drupal login page
 *    'drupal_login_url' => 'https://www.example.com/drupal7/user',
 *
 *    // Which attributes should be retrieved from the Drupal site.
 *
 *              'attributes' => array(
 *                                    array('drupaluservar'   => 'uid',  'callit' => 'uid'),
 *                                     array('drupaluservar' => 'name', 'callit' => 'cn'),
 *                                     array('drupaluservar' => 'mail', 'callit' => 'mail'),
 *                                     array('drupaluservar' => 'field_first_name',  'callit' => 'givenName'),
 *                                     array('drupaluservar' => 'field_last_name',   'callit' => 'sn'),
 *                                     array('drupaluservar' => 'field_organization','callit' => 'ou'),
 *                                     array('drupaluservar' => 'field_country:iso2','callit' => 'country'),
 *                                     array('drupaluservar' => 'roles','callit' => 'roles'),
 *                                   ),
 *  ),
 *
 * Format of the 'attributes' array explained:
 *
 * 'attributes' can be an associate array of attribute names, or NULL, in which case
 * all attributes are fetched.
 *
 * If you want everything (except) the password hash do this:
 *    'attributes' => NULL,
 *
 * If you want to pick and choose do it like this:
 * 'attributes' => array(
 *          array('drupaluservar' => 'uid',  'callit' => 'uid),
 *                     array('drupaluservar' => 'name', 'callit' => 'cn'),
 *                     array('drupaluservar' => 'mail', 'callit' => 'mail'),
 *                     array('drupaluservar' => 'roles','callit' => 'roles'),
 *                      ),
 *
 *  If you want to take another field column beside value you can declare it
 *  like this:
 * 'attributes' => array(
 *                       array('drupaluservar' => field_country:iso2','callit' => 'country'),
 *                      ),
 *
 *  The value for 'drupaluservar' is the variable name for the attribute in the
 *  Drupal user object.
 *
 *  The value for 'callit' is the name you want the attribute to have when it's
 *  returned after authentication. You can use the same value in both or you can
 *  customize by putting something different in for 'callit'. For an example,
 *  look at the entry for name above.
 *
 *
 * @author Steve Moitozo <steve_moitozo@sil.org>, SIL International
 * @package drupalauth
 * @version $Id$
 */
class sspmod_drupalauth_Auth_Source_External extends SimpleSAML_Auth_Source {

  /**
   * Whether to turn on debugging
   */
  private $debug;

  /**
   * The Drupal installation directory
   */
  private $drupalroot;

  /**
   * The Drupal user attributes to use, NULL means use all available
   */
  private $attributes;

  /**
   * The name of the cookie
   */
  private $cookie_name;

  /**
   * The cookie path
   */
  private $cookie_path;

  /**
   * The cookie salt
   */
  private $cookie_salt;

  /**
   * The logout URL of the Drupal site
   */
  private $drupal_logout_url;

  /**
   * The login URL of the Drupal site
   */
  private $drupal_login_url;

	/**
	 * Constructor for this authentication source.
	 *
	 * @param array $info  Information about this authentication source.
	 * @param array $config  Configuration.
	 */
	public function __construct($info, $config) {
    assert('is_array($info)');
    assert('is_array($config)');

    /* Call the parent constructor first, as required by the interface. */
    parent::__construct($info, $config);


    /* Get the configuration for this module */
    $drupalAuthConfig = new sspmod_drupalauth_ConfigHelper($config,
      'Authentication source ' . var_export($this->authId, TRUE));

    $this->debug       = $drupalAuthConfig->getDebug();
    $this->attributes  = $drupalAuthConfig->getAttributes();
    $this->cookie_name = $drupalAuthConfig->getCookieName();
    $this->drupal_logout_url = $drupalAuthConfig->getDrupalLogoutURL();
    $this->drupal_login_url = $drupalAuthConfig->getDrupalLoginURL();

    if (!defined('DRUPAL_ROOT')) {
      define('DRUPAL_ROOT', $drupalAuthConfig->getDrupalroot());
    }

    $ssp_config = SimpleSAML_Configuration::getInstance();
    $this->cookie_path = '/' . $ssp_config->getValue('baseurlpath');
    $this->cookie_salt = $ssp_config->getValue('secretsalt');

    $a = getcwd();
    chdir(DRUPAL_ROOT);

    /* Include the Drupal bootstrap */
    //require_once(DRUPAL_ROOT.'/includes/common.inc');
    require_once(DRUPAL_ROOT.'/includes/bootstrap.inc');
    require_once(DRUPAL_ROOT.'/includes/file.inc');

    /* Using DRUPAL_BOOTSTRAP_FULL means that SimpleSAMLphp must use an session storage
     * mechanism other than phpsession (see: store.type in config.php). However, this trade-off
     * prevents the need for hackery here and makes this module work better in different environments.
     */
    drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

    // we need to be able to call Drupal user function so we load some required modules
    drupal_load('module', 'system');
    drupal_load('module', 'user');
    drupal_load('module', 'field');

    chdir($a);
  }


	/**
	 * Retrieve attributes for the user.
	 *
	 * @return array|NULL  The user's attributes, or NULL if the user isn't authenticated.
	 */
	private function getUser() {

    $drupaluid          = NULL;

    // pull the Drupal uid out of the cookie
    if(isset($_COOKIE[$this->cookie_name]) && $_COOKIE[$this->cookie_name]) {
      $strCookie = $_COOKIE[$this->cookie_name];
      $arrCookie = explode(':',$strCookie);

      // make sure the hash matches
      // make sure the UID is passed
      if( (isset($arrCookie[0]) && $arrCookie[0]) && (isset($arrCookie[1]) && $arrCookie[1]) ) {

        // Make sure no one manipulated the hash or the uid in the cookie before we trust the uid
        if(sha1($this->cookie_salt . $arrCookie[1]) == $arrCookie[0]) {
            $drupaluid = $arrCookie[1];
        } else {
            throw new SimpleSAML_Error_Exception('Cookie hash invalid. This indicates either tampering or an out of date drupal4ssp module.');
        }
      }

    }


    // Delete the cookie, we don't need it anymore
    if(isset($_COOKIE[$this->cookie_name])) {
      setcookie($this->cookie_name, "", time() - 3600, $this->cookie_path);
    }

    if (!empty($drupaluid)) {

      $a = getcwd();
      chdir(DRUPAL_ROOT);

      // load the user object from Drupal
      $drupaluser = user_load($drupaluid);

      chdir($a);

      // get all the attributes out of the user object
      $userAttrs = get_object_vars($drupaluser);
      $wrapper = entity_metadata_wrapper('user', $drupaluser->uid);

      // define some variables to use as arrays
      $userAttrNames = null;
      $attributes = null;

      // figure out which attributes to include
      if(NULL == $this->attributes){
        $userKeys = array_keys($userAttrs);

        // populate the attribute naming array
        foreach($userKeys as $userKey){
            $userAttrNames[$userKey] = $userKey;
        }

      }else{
        // populate the array of attribute keys
        // populate the attribute naming array
        foreach($this->attributes as $confAttr){

            $userKeys[] = $confAttr['drupaluservar'];
            $userAttrNames[$confAttr['drupaluservar']] = $confAttr['callit'];

        }

      }

      // an array of the keys that should never be included
      // (e.g., pass)
      $skipKeys = array('pass');

      // Package up the user attributes.
      foreach($userKeys as $userKey){
        $value = '';
        $attributes[$userAttrNames[$userKey]] = array($value);

        // Skip any keys that should never be included.
        if (!in_array($userKey, $skipKeys)) {
          if (isset($userAttrs[$userKey])
            && (is_string($userAttrs[$userKey])
              || is_numeric($userAttrs[$userKey])
              || is_bool($userAttrs[$userKey]))
          ) {
            $attributes[$userAttrNames[$userKey]] = array($userAttrs[$userKey]);
          }
          // Get attributes from user fields.
          else {
            try {
              list($field_name, $col_name) = explode(':', "$userKey:");
              // Get value from a specific column from wrapper.
              if (!empty($col_name) && !in_array($col_name, array('value', 'safe_value'))) {
                if ($wrapper->{$field_name}->value()) {
                  $value = $wrapper->{$field_name}->{$col_name}->value();
                }
              }
              // Default get value from wrapper.
              elseif ($wrapper->{$field_name}->value()) {
                $value = $wrapper->{$field_name}->value();
              }
              $attributes[$userAttrNames[$userKey]] = is_array($value) ? $value : array($value);
            }
            catch (Exception $e) {
              watchdog_exception('simplesaml', $e);
            }
          }
        }
      }
      drupal_alter('drupalauth_attributes', $attributes, $drupaluser);

      return $attributes;
    }
	}


	/**
	 * Log in using an external authentication helper.
	 *
	 * @param array &$state  Information about the current authentication.
	 */
	public function authenticate(&$state) {
		assert('is_array($state)');

		$attributes = $this->getUser();
		if ($attributes !== NULL) {
			/*
			 * The user is already authenticated.
			 *
			 * Add the users attributes to the $state-array, and return control
			 * to the authentication process.
			 */
			$state['Attributes'] = $attributes;
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
		$state['drupalauth:AuthID'] = $this->authId;


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
		$stateId = SimpleSAML_Auth_State::saveState($state, 'drupalauth:External');

		/*
		 * Now we generate an URL the user should return to after authentication.
		 * We assume that whatever authentication page we send the user to has an
		 * option to return the user to a specific page afterwards.
		 */
		$returnTo = SimpleSAML_Module::getModuleURL('drupalauth/resume.php', array(
			'State' => $stateId,
		));

		/*
		 * Get the URL of the authentication page.
		 *
		 * Here we use the getModuleURL function again, since the authentication page
		 * is also part of this module, but in a real example, this would likely be
		 * the absolute URL of the login page for the site.
		 */
		$authPage = $this->drupal_login_url . '?ReturnTo=' . $returnTo;

		/*
		 * The redirect to the authentication page.
		 *
		 * Note the 'ReturnTo' parameter. This must most likely be replaced with
		 * the real name of the parameter for the login page.
		 */
		SimpleSAML_Utilities::redirect($authPage, array(
			'ReturnTo' => $returnTo,
		));

		/*
		 * The redirect function never returns, so we never get this far.
		 */
		assert('FALSE');
	}


	/**
	 * Resume authentication process.
	 *
	 * This function resumes the authentication process after the user has
	 * entered his or her credentials.
	 *
	 * @param array &$state  The authentication state.
	 */
	public static function resume() {

		/*
		 * First we need to restore the $state-array. We should have the identifier for
		 * it in the 'State' request parameter.
		 */
		if (!isset($_REQUEST['State'])) {
			throw new SimpleSAML_Error_BadRequest('Missing "State" parameter.');
		}
		$stateId = (string)$_REQUEST['State'];

		/*
		 * Once again, note the second parameter to the loadState function. This must
		 * match the string we used in the saveState-call above.
		 */
		$state = SimpleSAML_Auth_State::loadState($stateId, 'drupalauth:External');

		/*
		 * Now we have the $state-array, and can use it to locate the authentication
		 * source.
		 */
		$source = SimpleSAML_Auth_Source::getById($state['drupalauth:AuthID']);
		if ($source === NULL) {
			/*
			 * The only way this should fail is if we remove or rename the authentication source
			 * while the user is at the login page.
			 */
			throw new SimpleSAML_Error_Exception('Could not find authentication source with id ' . $state[self::AUTHID]);
		}

		/*
		 * Make sure that we haven't switched the source type while the
		 * user was at the authentication page. This can only happen if we
		 * change config/authsources.php while an user is logging in.
		 */
		if (! ($source instanceof self)) {
			throw new SimpleSAML_Error_Exception('Authentication source type changed.');
		}


		/*
		 * OK, now we know that our current state is sane. Time to actually log the user in.
		 *
		 * First we check that the user is acutally logged in, and didn't simply skip the login page.
		 */
		$attributes = $source->getUser();
		if ($attributes === NULL) {
			/*
			 * The user isn't authenticated.
			 *
			 * Here we simply throw an exception, but we could also redirect the user back to the
			 * login page.
			 */
			throw new SimpleSAML_Error_Exception('User not authenticated after login page.');
		}

		/*
		 * So, we have a valid user. Time to resume the authentication process where we
		 * paused it in the authenticate()-function above.
		 */

		$state['Attributes'] = $attributes;
		SimpleSAML_Auth_Source::completeAuth($state);

		/*
		 * The completeAuth-function never returns, so we never get this far.
		 */
		assert('FALSE');
	}


	/**
	 * This function is called when the user start a logout operation, for example
	 * by logging out of a SP that supports single logout.
	 *
	 * @param array &$state  The logout state array.
	 */
	public function logout(&$state) {
    assert('is_array($state)');

    if (!session_id()) {
      /* session_start not called before. Do it here. */
      session_start();
    }

    /*
     * In this example we simply remove the 'uid' from the session.
     */
    unset($_SESSION['uid']);

    // Added armor plating, just in case
    if (isset($_COOKIE[$this->cookie_name])) {
      setcookie($this->cookie_name, "", time() - 3600, $this->cookie_path);

    }

    $logout_url = $this->drupal_logout_url;
    if (!empty($state['ReturnTo'])) {
      $logout_url .= '?ReturnTo=' . $state['ReturnTo'];
    }

    /**
     * Redirect the user to the Drupal logout page
     */
    header('Location: ' . $logout_url);
    die;

  }

}
