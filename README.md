## Introduction

[![Build Status](https://travis-ci.com/drupalauth/simplesamlphp-module-drupalauth.svg?branch=master)](https://travis-ci.com/drupalauth/simplesamlphp-module-drupalauth)

Drupal + SimpleSAMLphp + drupalauth = Complete SAML Identity Provider (IdP)

Users interact with Drupal to create accounts, manage accounts, and authenticate. SAML SPs interact with [SimpleSAMLphp](https://simplesamlphp.org/). Drupalauth ties Drupal to SimpleSAMLphp.

The drupalauth module for simpleSAMLphp makes it easy to create a SAML or Shibboleth identity provider (IdP) by enabling authentication of users against a Drupal site on the same server. This allows the administrator to leverage the user management and integration capabilities of [Drupal](http://drupal.org) for managing the identity life cycle.

NOTE: This is software establishes a SAML identity provider (IdP) using Drupal as the user database instead of LDAP. If you want to establish your Drupal site as a SAML service provider (SP) connected to a SAML or Shibboleth IdP, see the [simplesamlphp_auth](https://www.drupal.org/project/simplesamlphp_auth) module for Drupal.

### simpleSAMLphp module

This module for SimpleSAMLphp provides an Authentication Source for authenticating users against a local Drupal site. This allows the administrator to leverage the user management and integration capabilities of Drupal for managing the identity life cycle and the power of SimpleSAMLphp for identity integration. This is a simpleSAMLphp module, NOT a Drupal module.
Download this module only if you want to use Drupal as Identity Provider.


### Drupal modules

If you want to use Drupal as Identity Provide you should also install [drupalauth4ssp](https://www.drupal.org/project/drupalauth4ssp) that is available on Drupal.org. Please note that all issues related to Drupal functionality should be reported there.

If you want to connect your Drupal site as Service Provider to a SAML or Shibboleth IdP, use the [simplesamlphp_auth](http://drupal.org/project/simplesamlphp_auth) module for Drupal.

## Branch and version naming

Following [Semantic Versioning](https://semver.org/) is hard when you have multiple upstream dependencies.

So in a X.Y.Z version:

- X - major SimpleSAMLphp version
- Y - major Drupal version
- Z - inthis module incremental version
    
Example: for SimpleSAMLphp version 1.15.4 with Drupal version 8.5.6 and this module version 1 we will have tag 1.8.1. Same thing for Drupal 7 will be 1.7.1.

`master` at the moment corresponds to 1.8.*. Branch `1.7` is respectfully for Drupal 7 (not composer integration yet).

## Installation

### Requirements

1. Install Drupal 8.x
2. Install simpleSAMLphp 
3. Install drupalauth
4. Configure SimpleSAMLphp to use something other than `phpsession` for session storage, e.g., SQL or memcache (See: `store.type` in `simplesamlphp/config/config.php`).
5. Configure the authentication source in `simplesamlphp/config/authsources.php` as described below.

#### Authenticate against Drupal but use the SimpleSAMLphp login page

The advantage of this approach is that there is no obvious connection between SimpleSAMLphp IdP and the Drupal site.

**Details**

Configure the authentication source by putting following code into `simplesamlphp/config/authsources.php`

```php
'drupal-userpass' => array(
    'drupalauth:UserPass',

    // The filesystem path of the Drupal directory.            
    'drupalroot' => '/var/www/drupal-8.0',

    // Whether to turn on debug
    'debug' => true,

    // Which attributes should be retrieved from the Drupal site.
   'attributes' => array(
       array('field_name' => 'uid', 'attribute_name' => 'uid'),
       array('field_name' => 'roles', 'attribute_name' => 'roles'), 
       array('field_name' => 'name', 'attribute_name' => 'cn'),
       array('field_name' => 'mail', 'attribute_name' => 'mail'),
       array('field_name' => 'field_first_name', 'attribute_name' => 'givenName'),
       array('field_name' => 'field_last_name', 'attribute_name' => 'sn'),
       array('field_name' => 'field_organization', 'attribute_name' => 'ou', 'field_property' => 'target_id'),
   ),
),
```

Leave 'attributes' empty or unset to get all available field values. Attribute names in this case would be "$field_name:$property_name".

#### Authenticate against Drupal but use the Drupal login page

The advantage of this approach is that the SimpleSAMLphp IdP session is tied to a Drupal session. This allows the user who is already logged into the Drupal site to then navigate to a SAML SP that uses the IdP without the need to authenticate again.

**Details**

Configure the authentication source by putting following code into `simplesamlphp/config/authsources.php`

```php
'drupal-userpass' => array('drupalauth:External',

 // The filesystem path of the Drupal directory.
 'drupalroot' => '/var/www/drupal',

 // Whether to turn on debug
 'debug' => true,

 // the URL of the Drupal logout page
 'drupal_logout_url' => 'https://www.example.com/drupal/user/logout',

 // the URL of the Drupal login page
 'drupal_login_url' => 'https://www.example.com/drupal/user/login',

 // Which attributes should be retrieved from the Drupal site.
    'attributes' => array(
        array('field_name' => 'uid', 'attribute_name' => 'uid'),
        array('field_name' => 'roles', 'attribute_name' => 'roles'), 
        array('field_name' => 'name', 'attribute_name' => 'cn'),
        array('field_name' => 'mail', 'attribute_name' => 'mail'),
        array('field_name' => 'field_first_name', 'attribute_name' => 'givenName'),
        array('field_name' => 'field_last_name', 'attribute_name' => 'sn'),
        array('field_name' => 'field_organization', 'attribute_name' => 'ou', 'field_property' => 'target_id'),
    ),
),
```
