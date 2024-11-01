<?php
	/*
		Plugin Name: VSF Simple Stats
		Plugin URI: http://blog.v-s-f.co.uk/simple-stats/
		Description: Records all hits to your website, but excludes hits from IPs that have been added to the filter list.  IPs can be filtered individually or as a range from the admin page.
		Version: 0.8
		Author: Victoria Scales
		Author URI: http://www.v-s-f.co.uk
		Donation URI: http://www.amazon.co.uk/wishlist/2FRM957UJWLZ2
		Text Domain: vsf-simple-stats
	*/
	
	/*
		Copyright 2011 Victoria Scales

		This program is free software; you can redistribute it and/or modify
		it under the terms of the GNU General Public License as published by
		the Free Software Foundation; either version 2 of the License, or
		(at your option) any later version.

		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.

		You should have received a copy of the GNU General Public License
		along with this program; if not, see <http://www.gnu.org/licenses/>.
	*/
	

	define('VSF_STATS_DEBUG', false);
    
	include_once('vsf_simple_stats_setup.php');
	include_once('vsf_simple_stats_edit.php');
	include_once('vsf_simple_stats_admin.php');
	include_once('vsf_simple_stats_shutdown.php');
	
	// Export the xml file of the database filter table
	if ( isset($_POST['Export']) )
	{
		include('vsf_simple_stats_export.php');
		$xmlGenerator = new VSFSimpleStatsExport();
		$xmlGenerator->generateExport();
	}
	
	if( !class_exists("VSFSimpleStats") )
	{
		class VSFSimpleStats
		{
			public function vsfSimpleStatsCreateAdminMenu()
			{
				if ( function_exists('add_options_page') ) 
				{
					$adminPage = new VSFSimpleStatsAdmin();
					add_options_page(__('VSF Simple Stats Options', 'vsf-simple-stats'), __('VSF Simple Stats', 'vsf-simple-stats'), 1, basename(__FILE__), array($adminPage, 'createAdminMenu'));
					
					$displayStatsPage = new VSFSimpleStatsDisplay();
					add_submenu_page('edit.php', __('VSF Simple Stats', 'vsf-simple-stats'), __('Show Stats', 'vsf-simple-stats'), 1, basename(__FILE__), array($displayStatsPage, 'displayStats'));
				}
			}
		}
	}
	
	$activationObj = new VSFSimpleStatsSetup();
	register_activation_hook(__FILE__, array($activationObj, 'activation'));
	
	$simpleStatsObj = new VSFSimpleStats();
	add_action('admin_menu', array($simpleStatsObj, 'vsfSimpleStatsCreateAdminMenu'));
	
	/** Add a hook to the shutdown to record the new hit **/
	$shutdownObj = new VSFSimpleStatsShutdown();
	add_action('shutdown', array($shutdownObj, 'shutdownHook'));

?>