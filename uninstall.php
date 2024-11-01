<?php
include_once('vsf_simple_stats_setup.php');
include_once 'vsf_simple_stats_options.php';

if (!defined('ABSPATH') || !defined('WP_UNINSTALL_PLUGIN')) die();

	global $wpdb;
	$tablePrefix = $wpdb->prefix;
			
	// delete data from wp_options
	VSFStatsOptions::removeOptions();
	
	mysql_query("DROP INDEX " . VSFSimpleStatsSetup::$INDEX_PREFIX . VSFSimpleStatsSetup::$INDEX_FILTER);
	
	mysql_query("DROP TABLE " . $tablePrefix . VSFSimpleStatsSetup::$TABLE_STATS);
	mysql_query("DROP TABLE " . $tablePrefix . VSFSimpleStatsSetup::$TABLE_FILTER);
	mysql_query("DROP TABLE " . $tablePrefix . VSFSimpleStatsSetup::$TABLE_URL_PARAMS);
?>