## Introduction

This module for SimpleSAMLphp provides an Authentication Source for authenticating users against a local Drupal site. This allows the administrator to leverage the user management and integration capabilities of Drupal for managing the identity life cycle and the power of SimpleSAMLphp for identity integration.

**NOTE:** This is a simpleSAMLphp module, NOT a Drupal module. If you want to connect your Drupal site to a SAML or Shibboleth IdP, see the simplesamlphp_auth module for Drupal at http://drupal.org/project/simplesamlphp_auth. 

## Installation

#### Reqirements
1. Install Drupal 7.x
2. Install simpleSAMLphp 
3. Configure SimpleSAMLphp to use something other than "phpsession" for session storage, e.g., SQL or memcache (See: store.type in simplesamlphp/config/config.php).
4. Download drupalauth
5. Unpack drupalauth
6. Move the drupalauth module directory into simplesamlphp/modules directory
7. Configure the authentication source in simplesamlphp/config/authsources.php as described below

#### Authenticate against Drupal but use the Drupal login page

The advantage of this approach is that the SimpleSAMLphp IdP session is tied to a Drupal session. This allows the user who is already logged into the Drupal site to then navigate to a SAML SP that uses the IdP without the need to authenticate again.

**Details**

Configure the authentication source by putting following code into `simplesamlphp/config/authsources.php`

```php
'drupal-userpass' => array( 'drupalauth:External',

 // The filesystem path of the Drupal directory.
 'drupalroot' => '/var/www/drupal',

 // Whether to turn on debug
 'debug' => true,

 // the URL of the Drupal logout page
 'drupal_logout_url' => 'https://www.example.com/drupal/user/logout',

 // the URL of the Drupal login page
 'drupal_login_url' => 'https://www.example.com/drupal/user',

 // Which attributes should be retrieved from the Drupal site.
 'attributes' => array(
   array('drupaluservar'   => 'uid',  'callit' => 'uid'),
   array('drupaluservar' => 'name', 'callit' => 'cn'),
   array('drupaluservar' => 'mail', 'callit' => 'mail'),
   array('drupaluservar' => 'field_first_name',  'callit' => 'givenName'),
   array('drupaluservar' => 'field_last_name',   'callit' => 'sn'),
   array('drupaluservar' => 'field_organization','callit' => 'ou'),
   array('drupaluservar' => 'roles','callit' => 'roles'),
  ),
),
```

#### Authenticate against Drupal but use the SimpleSAMLphp login page

The advantage of this approach is that their is no obvious connection between SimpleSAMLphp IdP and the Drupal site.

**Details**

Configure the authentication source by putting following code into `simplesamlphp/config/authsources.php`

```php
'drupal-userpass' => array( 'drupalauth:UserPass',

    // The filesystem path of the Drupal directory.
    'drupalroot' => '/home/drupal',            

    // Whether to turn on debug
    'debug' => true,

    // Which attributes should be retrieved from the Drupal site.
    // This can be an associate array of attribute names, or NULL, in which case
    // all attributes are fetched.
    //
    // If you want everything (except) the password hash do this:
    //      'attributes' => NULL,
    //
    // If you want to pick and choose do it like this:
    //'attributes' => array(
    //                    array('drupaluservar'   => 'uid',  'callit' => 'uid'),
    //                      array('drupaluservar' => 'name', 'callit' => 'cn'),
    //                      array('drupaluservar' => 'mail', 'callit' => 'mail'),
    //                      array('drupaluservar' => 'field_first_name',  'callit' => 'givenName'),
    //                      array('drupaluservar' => 'field_last_name',   'callit' => 'sn'),
    //                      array('drupaluservar' => 'field_organization','callit' => 'ou'),
    //                      array('drupaluservar' => 'roles','callit' => 'roles'),
    //                     ),
    //
    // The value for 'drupaluservar' is the variable name for the attribute in the
    // Drupal user object.
    //
    // The value for 'callit' is the name you want the attribute to have when it's
    // returned after authentication. You can use the same value in both or you can
    // customize by putting something different in for 'callit'. For an example,
    // look at uid and name above.
    'attributes' => array(
      array('drupaluservar'   => 'uid',  'callit' => 'uid'),
      array('drupaluservar' => 'name', 'callit' => 'cn'),
      array('drupaluservar' => 'mail', 'callit' => 'mail'),
      array('drupaluservar' => 'field_first_name',  'callit' => 'givenName'),
      array('drupaluservar' => 'field_last_name',   'callit' => 'sn'),
      array('drupaluservar' => 'field_organization','callit' => 'ou'),
      array('drupaluservar' => 'roles','callit' => 'roles'),
  ),
),
```
