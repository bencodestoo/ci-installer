<?php
/**
 * Configuration file for the CodeIgniter 3+ installer
 * 
 */
$site_title = 'CodeIgniter'; // Website title
$config_path = '../application/config'; //Path to your application config folder, relative to this (config.php) file
$sql_file = 'sql/install.sql'; //SQL Data Dump file. Leave empty to start with an empty database

define('SITE_TITLE', $site_title);
define('CONFIG_PATH', $config_path);
?>
