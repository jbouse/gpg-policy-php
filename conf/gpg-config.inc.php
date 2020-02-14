<?php

/*
 * Set base directory that includes are read from. 
 * Safe to leave as is if files are all in the same directory.
 */
define('GPG_Policy_DIR', dirname(__FILE__));

/*
 * Set to path off of base policy directory for policy data files
 */
define('GPG_Data_DIR', 'data');

/*
 * Set to prefix on policy and signature files
 */
define('GPG_Policy_Prefix', 'jbouse');

/*
 * Set to suffix on signature files
 */
define('GPG_Sig_Suffix', 'sig');

/*
 * Set to your name for display in title
 *
 * define('GPG_Owner_Name', '');
 */
define('GPG_Owner_Name', 'Jeremy T. Bouse');

/*
 * Set to you Google Analytics Account ID to have the GA code
 * added for the page and links
 *
 * define('Google_Analytics_ID', 'UA-XXXXXXX-X'); // Enabled
 * define('Google_Analytics_ID', ''); // Disabled
 */
define('Google_Analytics_ID', 'UA-2819624-1');
?>
