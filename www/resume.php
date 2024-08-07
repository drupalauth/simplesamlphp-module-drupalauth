<?php

/**
 * This page serves as the point where the user's authentication
 * process is resumed after the login page.
 *
 * It simply passes control back to the class.
 */

use SimpleSAML\Module\drupalauth\Auth\Source\External;

External::resume($_REQUEST['State']);
