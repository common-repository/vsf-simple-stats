<?php

include_once('vsf_simple_stats_setup.php');
include_once('pagination.php');
include_once 'vsf_simple_stats_utilities.php';

class VSFSimpleStatsAdmin
{
	private static $ipRegExp = "/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/";
    
	private static $VIEW_FILTERS = "filters";
	private static $VIEW_SEARCH_PARAMS = "searchParams";
    private static $VIEW_PARAMETER = "vsfStatsSelectedView";
        
	private $currentPage;
    private $searchQueryParamCurrentPage;
	private $tablePrefix = "";
    private $selectedView;
	
	public function createAdminMenu()
	{
		global $wpdb;
		$this->tablePrefix = $wpdb->prefix;
		
		$this->currentPage = $_GET[filteredIp];
        $this->searchQueryParamCurrentPage = $_GET['searchQueryParamPage'];
			
        if ( !VSFStatsUtilities::isEmpty($_POST[self::$VIEW_PARAMETER]) )
        {
            $resetAllPageNumbers = true;

            $this->selectedView = $_POST[self::$VIEW_PARAMETER];
        }

        if ( VSFStatsUtilities::isEmpty($this->selectedView) )
        {
            $this->selectedView = self::$VIEW_FILTERS;
        }
		
		if (isset($_POST['add_ip']))
		{
			$this->currentPage = null;
			
			if (!preg_match(self::$ipRegExp, $_POST['newip1'])) 
			{
				VSFStatsUtilities::buildErrorDiv(__('Invalid IP!', 'vsf-simple-stats'));
			}
			else if( (($_POST['newip2'] != null) && ($_POST['newip2'] != "") && !preg_match(self::$ipRegExp, $_POST['newip2'])) )
			{
				VSFStatsUtilities::buildErrorDiv(__('Invalid IP!', 'vsf-simple-stats'));
			}
			else
			{
				$ip1 = $_POST['newip1']; 
				$ip2FromField = $_POST['newip2'];
				$ip2 = ($ip2FromField != null && $ip2FromField != "") ? $ip2FromField : 0;
				$description = $_POST['newipdescription'];
				
				// clean up the description
				if( $description != null && $description != "" )
				{
                    $description = VSFStatsUtilities::cleanUpString($description, 250);
				}
				
				//echo $ip1 . "  " . $ip2 . "  " . $description;
				
				$query = "INSERT INTO " . $this->tablePrefix . VSFSimpleStatsSetup::$TABLE_FILTER . " (" . VSFSimpleStatsSetup::$TABLE_FILTER_IP1 . ", " . VSFSimpleStatsSetup::$TABLE_FILTER_IP2 . ", " . VSFSimpleStatsSetup::$TABLE_FILTER_DESCRIPTION . ") VALUES (INET_ATON('$ip1'), INET_ATON('$ip2'), '$description')";
				$result = mysql_query($query);
				//echo $result;
				if( $result == 1 ) VSFStatsUtilities::buildUpdateDiv(__('Added Filtered IP', 'vsf-simple-stats'));
				else VSFStatsUtilities::buildErrorDiv(__('An error occured when inserting a filter IP record', 'vsf-simple-stats'));
			}
		}
		
		if ($_POST['del'] > 0) 
		{
			$this->currentPage = null;
			
			$ipIdToDelete = $_POST['del'];
			
			$result = mysql_query("DELETE FROM " . $this->tablePrefix . VSFSimpleStatsSetup::$TABLE_FILTER . " WHERE " . VSFSimpleStatsSetup::$TABLE_FILTER_ID . " = $ipIdToDelete");
			if( $result == 1 ) VSFStatsUtilities::buildUpdateDiv(__('Deleted Filtered IP', 'vsf-simple-stats'));
			else VSFStatsUtilities::buildErrorDiv(__('An error occured when deleting a filter IP record', 'vsf-simple-stats'));
		}
        
        if( isset($_POST['add_searchQueryParameter']) )
        {
            $this->currentPage = null;
            $searchQueryParameterToAdd = $_POST['searchQueryParameter'];
            $searchQueryParameterToAdd = VSFStatsUtilities::cleanUpString($searchQueryParameterToAdd, VSFSimpleStatsSetup::$TABLE_URL_PARAMS_VALUE_MAX_LENGTH);
            
            $query = "INSERT INTO " . $this->tablePrefix . VSFSimpleStatsSetup::$TABLE_URL_PARAMS . " (" . VSFSimpleStatsSetup::$TABLE_URL_PARAMS_VALUE . ") VALUES ('$searchQueryParameterToAdd')";
            $result = mysql_query($query);
            //echo $result;
            if( $result == 1 ) VSFStatsUtilities::buildUpdateDiv(__('Added Search Query Parameter', 'vsf-simple-stats'));
            else VSFStatsUtilities::buildErrorDiv(__('An error occured when inserting a search query p arameter', 'vsf-simple-stats'));
        }
		
		if ($_POST['delSearchQueryParameter'] > 0) 
		{
			$this->currentPage = null;
			
			$ipIdToDelete = $_POST['delSearchQueryParameter'];
			
			$result = mysql_query("DELETE FROM " . $this->tablePrefix . VSFSimpleStatsSetup::$TABLE_URL_PARAMS . " WHERE " . VSFSimpleStatsSetup::$TABLE_URL_PARAMS_ID . " = $ipIdToDelete");
			if( $result == 1 ) VSFStatsUtilities::buildUpdateDiv(__('Deleted Search Query Parameter', 'vsf-simple-stats'));
			else VSFStatsUtilities::buildErrorDiv(__('An error occured when deleting a search query parameter', 'vsf-simple-stats'));
		}
		
		// User has chosen to clear the stats.
		if ($_POST['clearStatsHidden'] > 0) 
		{
			$this->currentPage = null;
			
			$result = mysql_query("TRUNCATE TABLE " . $this->tablePrefix . VSFSimpleStatsSetup::$TABLE_STATS);
			if( $result == 1 ) VSFStatsUtilities::buildUpdateDiv(__('Stats Cleared', 'vsf-simple-stats'));
			else VSFStatsUtilities::buildErrorDiv(__('An error occured when clearing the stats', 'vsf-simple-stats'));
		}
		
		if (isset($_POST['Submit'])) 
		{
			$this->currentPage = null;
			
			update_option("vsf_stats_max_hits",$_POST['max']);
			VSFStatsUtilities::buildUpdateDiv(__('Options Saved', 'vsf-simple-stats'));
		}
		
		if( isset($_POST['Import']) )
		{
			$this->currentPage = null;
			
			if ($_FILES["import-filtered-ip-file"]["error"] > 0)
			{
				VSFStatsUtilities::buildErrorDiv(__('Error: There was a problem uploading the filter ip file.  Please try again.', 'vsf-simple-stats'));
			}
			else
			{
				if( $_FILES["import-filtered-ip-file"]["type"] != 'text/xml' )
				{
					VSFStatsUtilities::buildErrorDiv(__('File type is not correct!', 'vsf-simple-stats'));
				}
				else if ( ($_FILES["import-filtered-ip-file"]["size"] / 1024) > 100 )
				{
					VSFStatsUtilities::buildErrorDiv(__('File size is too large.  File needs to be less than 100kb!', 'vsf-simple-stats'));
				}
				else
				{
					$filterIpfileContent = file_get_contents($_FILES["import-filtered-ip-file"]["tmp_name"]);
					
					include('vsf_simple_stats_import.php');
					
					$xmlReaderForSimpleStats = new VSFSimpleStatsImport();
					$xmlReaderForSimpleStats->importFileValues($filterIpfileContent);
				}
			}
		}
		
		$maxhits = get_option("vsf_stats_max_hits");
		
		if ( !ctype_digit((string)$maxhits) || (strlen($maxhits) == 0) )
		{
			$maxhits = 500;
			update_option("vsf_stats_max_hits",$maxhits);
		}
		?>
		
		<style>
			table.vsf_stats_table { font-size: 8pt; background-color: #6da6d1; width: 100%; }
			.vsf_stats_table td { font-size: 8pt; background-color: #FFFFFF; }
			.vsf_stats_table th { text-align:left; }
		</style>	
		<script>
			function delfip(formObject, ipToDelete)
			{
				formObject.del.value = ipToDelete;
				formObject.submit();
			}
            
            function delsqp(formObject, searchQueryToDelete)
            {
                formObject.delSearchQueryParameter.value = searchQueryToDelete;
				formObject.submit();
            }
			
			function clearStats(formObject)
			{
				var confirmBox = window.confirm("<?php _e("Are you sure you want to clear the stats?", 'vsf-simple-stats'); ?>")
				if (confirmBox)
				{
					formObject.clearStatsHidden.value = 1;
					formObject.submit();
				}
			}
				
            function vsfStatsChangeSelectedView(view)
            {
                document.getElementById('<?php echo self::$VIEW_PARAMETER; ?>').value = view;
                document.getElementById('vsfStatsForm').submit();
            }
			
		</script>
		<div class="wrap">
			<h2><?php _e('Options', 'vsf-simple-stats'); ?></h2>
			<form method="post" enctype="multipart/form-data" id="vsfStatsForm">
                <input type="hidden" name="del"><input type="hidden" name="clearStatsHidden"><input type="hidden" name="delSearchQueryParameter">
                <input id="vsfStatsSelectedView" type="hidden" name="vsfStatsSelectedView" value="<?php echo $this->selectedView; ?>">
					
				<div id="poststuff" class="metabox-holder has-right-sidebar">
					<div id="side-info-column" class="inner-sidebar">
						<?php $this->buildSideBarContent(); ?>
					</div>
					
					<div id="post-body" class="has-sidebar">
						<div class="has-sidebar-content" id="post-body-content">
							<?php $this->buildMainContent(); ?>
						</div>
					</div>
				</div>
				
			</form>
		</div>
	<?php
	}
	
	private function buildSideBarContent()
	{
		$this->buildAboutPanel();
		$this->buildResetStatsPanel();
		$this->buildLoggingPanel();
		$this->buildImportExportPanel();
	}
	
	private function buildAboutPanel()
	{
		?>
		<div id="vsfBlockAbout" class="postbox">
			<h3 class="hndle"><?php _e('About VSF Simple Stats', 'vsf-simple-stats'); ?></h3>
			<div class="inside">
				<ul>
					<li><a href="http://blog.v-s-f.co.uk/simple-stats/?simple-stats-admin" target="_blank">VSF Simple Stats - Home Page</a></li>
					<li><a href="http://wordpress.org/extend/plugins/vsf-simple-stats/" target="_blank">VSF Simple Stats - Wordpress Page</a></li>
					<li><?php _e('Please rate whether this plug-in works on the WordPress website to help others make an informed decision', 'vsf-simple-stats'); ?></li>
					<li><a href="http://www.amazon.co.uk/wishlist/2FRM957UJWLZ2" target="_blank">VSF Simple Stats - Donate</a></li>
					<li><?php _e('Other Plugins', 'vsf-simple-stats'); ?></li>
					<li><a href="http://blog.v-s-f.co.uk/simple-block/?simple-stats-admin" target="_blank">VSF Simple Block - Home Page</a></li>
				</ul>
			</div>
		</div>
		<?php
	}
	
	private function buildResetStatsPanel()
	{
		?>
		<div id="vsfStatsLogging" class="postbox">
			<h3 class="hndle"><?php _e('Reset Stats', 'vsf-simple-stats'); ?></h3>
			<div class="inside">
				<input type="button" value="<?php _e('Reset stats', 'vsf-simple-stats'); ?>" onClick="clearStats(this.form)">
			</div>
		</div>				
		<?php
	}
		
	private function buildLoggingPanel()
	{
		?>
		<div id="vsfStatsLogging" class="postbox">
			<h3 class="hndle"><?php _e('Logging', 'vsf-simple-stats'); ?></h3>
			<div class="inside">
				<?php _e('Store maximum', 'vsf-simple-stats'); ?> <input type="text" name="max" value="<?php form_option('vsf_stats_max_hits'); ?>" size="3"> <?php _e('hits', 'vsf-simple-stats'); ?><br />
				<br />
				<input type="submit" name="Submit" value="<?php _e('Update Options', 'vsf-simple-stats'); ?>" /><br />
			</div>
		</div>				
		<?php
	}
	
	private function buildImportExportPanel()
	{
		?>
		<div id="vsfStatsImportExport" class="postbox">
			<h3 class="hndle"><?php _e('Import / Export', 'vsf-simple-stats'); ?></h3>
			<div class="inside">
				<input type="submit" name="Export" value="<?php _e('Export Filtered IPs', 'vsf-simple-stats'); ?>" /><br />
				<br />
				<input type="file" name="import-filtered-ip-file" id="file" /><input type="submit" name="Import" value="<?php _e('Import Filtered IPs', 'vsf-simple-stats'); ?> &raquo;" />
			</div>
		</div>				
		<?php
	}

	private function buildMainContent()
	{
        ?><div id="vsfBlockView" class="postbox">
            <h3 class="hndle"><?php _e('View', 'vsf-simple-stats'); ?></h3>
            <div class="inside">
                <ul class="subsubsub">
                    <li><a <?php if( $this->selectedView == self::$VIEW_FILTERS ) echo 'class="current" '; ?>onClick="vsfStatsChangeSelectedView('<?php echo self::$VIEW_FILTERS ?>')"><?php _e('Filters', 'vsf-simple-stats'); ?></a> |</li>
                    <li><a <?php if( $this->selectedView == self::$VIEW_SEARCH_PARAMS ) echo 'class="current" '; ?>onClick="vsfStatsChangeSelectedView('<?php echo self::$VIEW_SEARCH_PARAMS ?>')"><?php _e('Search Query Parameters', 'vsf-simple-stats'); ?></a></li>
                </ul>
                <br />
                <br />
            </div>
            <p>&#160;</p><p>&#160;</p>
        </div><?php

        if ( $this->selectedView == self::$VIEW_FILTERS ) $this->buildFilteredIPsPanel();
        else if ( $this->selectedView == self::$VIEW_SEARCH_PARAMS ) $this->buildSearchQueryParametersPanel();
	}

	function buildFilteredIPsPanel() 
	{
	?>
		<div id="vsfStatsFilteredIPs" class="postbox">
			<h3 class="hndle"><?php _e('Filtered IPs', 'vsf-simple-stats'); ?></h3>
			<div class="inside">
				<p><?php _e('For each hit on the website, a record is added to the stats table.  There may however be situations where you are not interested in hits from a particular IP address.  For example, if you have a static IP address, you might not be interested in your own activity on the site.  The following list is a list of IP addresses and ranges that will be ignored when a hit to the website is made.', 'vsf-simple-stats'); ?></p>
				<p><?php _e('To add a new hit filter, add in an "IP (from)" address and if you want to exclude hits from a range of IP\'s, fill in the "IP (to)" box as well.  Fill in a description for your filter if required.  Then click "Add New Filter."', 'vsf-simple-stats'); ?></p>
				<table width="100%">
					<tr valign="top">
						<td width="80"><?php _e('IP (from)', 'vsf-simple-stats'); ?></td>
						<td width="80"><?php _e('IP (to)', 'vsf-simple-stats'); ?></td>
						<td width="80"><?php _e('Description', 'vsf-simple-stats'); ?></td>
						<td width="50"></td>
					</tr>
					<tr valign="top">
						<td><input type="text" name="newip1" /></td>
						<td><input type="text" name="newip2" /></td>
						<td><input type="text" name="newipdescription" /></td>
						<td><input type="submit" name="add_ip" value="<?php _e('Add new filter', 'vsf-simple-stats'); ?>" /></td>
					</tr>
				</table>
				<div class="tablenav">
					<div class='tablenav-pages'>
						<?php
							$fields = array(VSFSimpleStatsSetup::$TABLE_FILTER_ID, "INET_NTOA(" . VSFSimpleStatsSetup::$TABLE_FILTER_IP1 . ")", "INET_NTOA(" . VSFSimpleStatsSetup::$TABLE_FILTER_IP2 . ")", VSFSimpleStatsSetup::$TABLE_FILTER_DESCRIPTION);
							$table = $this->tablePrefix . VSFSimpleStatsSetup::$TABLE_FILTER;
							$order = VSFSimpleStatsSetup::$TABLE_FILTER_ID . " desc"; // most recent first
							
							$selectQuery = VSFStatsUtilities::buildSelectQuery(false, $fields, $table, null, $order);
							
							$countQuery = VSFStatsUtilities::buildSelectQuery(true, null, $table, $where, null);
							$items = mysql_fetch_row(mysql_query($countQuery)); // number of total rows in the database
							//echo "number of items found " . $items[0] . "" . ($items[0] > 0);
							
							if( $items[0] > 0 )
							{
								$pagingForBlocks = new pagination;
								$pagingForBlocks->items($items[0]);
								$pagingForBlocks->limit(30); // Limit entries per page
								$pagingForBlocks->target("?page=vsf_simple_stats.php");
								$pagingForBlocks->parameterName("filteredIp");
								$pagingForBlocks->currentPage(($this->currentPage > 0 ? $this->currentPage : 1)); // Gets and validates the current page

								//Query for limit paging
								$lowerLimit = (($pagingForBlocks->page - 1) * $pagingForBlocks->limit);
								$selectQuery .= " LIMIT " . ($lowerLimit >= 0 ? $lowerLimit : 0) . ", " . $pagingForBlocks->limit;
								//echo "before show pagination<br />";
								
								// Doesn't show anything if there is only 1 page... took me some time to work this out...
								echo $pagingForBlocks->show();  // Echo out the list of paging.
								//echo "<br />after show pagination";
							}
						?>
					</div>
				</div>
			
				<table cellspacing="0" class="widefat tag fixed">
					<?php
						$columns = array(
                                array('', __('Remove', 'vsf-simple-stats')), 
                                array('', __('IP (from)', 'vsf-simple-stats')), 
                                array('', __('IP (to)', 'vsf-simple-stats')), 
                                array('', __('Description', 'vsf-simple-stats'))
                        );
						VSFStatsUtilities::buildTableHeadAndFooter($columns);
					?>
					
					<tbody class="list:tag" id="the-list">
						<?php
						
						$x = 0;
						//echo $selectQuery;
						$blockQueryResult = mysql_query($selectQuery);
						while ( $c = mysql_fetch_row($blockQueryResult) )
						{
							?>
								<tr class="<?php if( ($x % 2) == 0 ) {echo "alternate";} else {echo "";} ?>">
									<td><input type="button" value="<?php _e('Remove', 'vsf-simple-stats'); ?>" onClick="delfip(this.form, '<?php echo $c[0]; ?>');"></td>
									<td class="name column-name"><?php echo $c[1]; ?></td>
									<td class="name column-name"><?php echo (($c[2] > 0) ? $c[2] : ''); ?></td>
									<td class="name column-name"><?php echo $c[3]; ?></td>
								</tr>
							<?php
							
							$x++;
						}
						
						?>
					</tbody>
				</table>
				
			</div>
		</div>
	<?php
	}
    
    function buildSearchQueryParametersPanel() 
	{
	?>
		<div id="vsfStatsSearchQueryParameters" class="postbox">
			<h3 class="hndle"><?php _e('Search Query Parameters', 'vsf-simple-stats'); ?></h3>
			<div class="inside">
				<p><?php _e('A search query parameter is the unique part of the referers url which identifies the search terms used to visit your site.  An example is if I went to google and searched for "hello world".  Say one of those results was one of your pages and a user clicked on that link, the referer url when they arrive on your site might be: "http://www.google.co.uk/search?q=hello+world&ie=utf-8&oe=utf-8&aq=t."  The search query parameter in this case is "q."  Add as many as you like and any refering urls which contain your requested search parameters will be added to the database and will appear in the "Referrer Search Terms" box on the "Show stats" page.', 'vsf-simple-stats'); ?></p>
				<p><?php _e('To add a new search term, add in a "Search Query Parameter" below.  Then click "Add new search query parameter."', 'vsf-simple-stats'); ?></p>
				<table width="100%">
					<tr valign="top">
						<td width="80"><?php _e('Search Query Parameter', 'vsf-simple-stats'); ?></td>
						<td width="50"></td>
					</tr>
					<tr valign="top">
						<td><input type="text" name="searchQueryParameter" /></td>
						<td><input type="submit" name="add_searchQueryParameter" value="<?php _e('Add new search query parameter', 'vsf-simple-stats'); ?>" /></td>
					</tr>
				</table>
				<div class="tablenav">
					<div class='tablenav-pages'>
						<?php
							$fields = array(VSFSimpleStatsSetup::$TABLE_URL_PARAMS_ID, VSFSimpleStatsSetup::$TABLE_URL_PARAMS_VALUE);
							$table = $this->tablePrefix . VSFSimpleStatsSetup::$TABLE_URL_PARAMS;
							$order = VSFSimpleStatsSetup::$TABLE_URL_PARAMS_VALUE . " asc"; // most recent first
							
							$selectQuery = VSFStatsUtilities::buildSelectQuery(false, $fields, $table, null, $order);
							
							$countQuery = VSFStatsUtilities::buildSelectQuery(true, null, $table, $where, null);
							$items = mysql_fetch_row(mysql_query($countQuery)); // number of total rows in the database
							//echo "number of items found " . $items[0] . "" . ($items[0] > 0);
							
							if( $items[0] > 0 )
							{
								$pagingForBlocks = new pagination;
								$pagingForBlocks->items($items[0]);
								$pagingForBlocks->limit(30); // Limit entries per page
								$pagingForBlocks->target("?page=vsf_simple_stats.php");
								$pagingForBlocks->parameterName("searchQueryParamPage");
								$pagingForBlocks->currentPage(($this->searchQueryParamCurrentPage > 0 ? $this->searchQueryParamCurrentPage : 1)); // Gets and validates the current page

								//Query for limit paging
								$lowerLimit = (($pagingForBlocks->page - 1) * $pagingForBlocks->limit);
								$selectQuery .= " LIMIT " . ($lowerLimit >= 0 ? $lowerLimit : 0) . ", " . $pagingForBlocks->limit;
								//echo "before show pagination<br />";
								
								// Doesn't show anything if there is only 1 page... took me some time to work this out...
								echo $pagingForBlocks->show();  // Echo out the list of paging.
								//echo "<br />after show pagination";
							}
						?>
					</div>
				</div>
			
				<table cellspacing="0" class="widefat tag">
					<?php
						$columns = array(
                                array('', __('Remove', 'vsf-simple-stats')),
                                array('', __('Search Query Parameter', 'vsf-simple-stats'))
                        );
						VSFStatsUtilities::buildTableHeadAndFooter($columns);
					?>
					
					<tbody class="list:tag" id="the-list">
						<?php
						
						$x = 0;
						//echo $selectQuery;
						$blockQueryResult = mysql_query($selectQuery);
						while ( $c = mysql_fetch_row($blockQueryResult) )
						{
							?>
								<tr class="<?php if( ($x % 2) == 0 ) {echo "alternate";} else {echo "";} ?>">
									<td><input type="button" value="<?php _e('Remove', 'vsf-simple-stats'); ?>" onClick="delsqp(this.form, '<?php echo $c[0]; ?>');"></td>
									<td class="name column-name"><?php echo $c[1]; ?></td>
								</tr>
							<?php
							
							$x++;
						}
						
						?>
					</tbody>
				</table>
				
			</div>
		</div>
	<?php
	}
}

?>