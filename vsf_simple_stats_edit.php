<?php

include_once('vsf_simple_stats_setup.php');
include_once 'vsf_simple_stats_utilities.php';
include_once 'vsf_simple_stats_options.php';

class VSFSimpleStatsDisplay
{
	private $tablePrefix = "";
	
	private $numhits = 0;
	
	public function displayStats() 
	{
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');
		
		global $wpdb;
		$this->tablePrefix = $wpdb->prefix;
		
		// User has chosen to clear the stats.
		if ($_POST['clearStatsHidden'] > 0) 
		{
			$result = mysql_query("TRUNCATE TABLE " . $this->tablePrefix . VSFSimpleStatsSetup::$TABLE_STATS);
			if( $result > 0 ) VSFStatsUtilities::buildMessageDiv(false, 'Stats Cleared');
			else VSFStatsUtilities::buildMessageDiv(true, 'An error occured when trying to clear the stats table. ' . mysql_error());
		}
		
		if (isset($_POST['updateDisplayedPanels']))
		{
			update_option(VSFStatsOptions::$VSF_STATS_DISPLAY_GENERAL, ($_POST['displayGeneralStatistics'] == 'on' ? 1 : null));
			update_option(VSFStatsOptions::$VSF_STATS_DISPLAY_CONTENT_SUMMARY, ($_POST['displayContentSummary'] == 'on' ? 1 : null));
			update_option(VSFStatsOptions::$VSF_STATS_DISPLAY_REFERER, ($_POST['displayReferrerSummary'] == 'on' ? 1 : null));
			update_option(VSFStatsOptions::$VSF_STATS_DISPLAY_REFERER_KEYWORDS, ($_POST['displayReferrerKeywords'] == 'on' ? 1 : null));
			update_option(VSFStatsOptions::$VSF_STATS_DISPLAY_BROWSER_SUMMARY, ($_POST['displayBrowserSummary'] == 'on' ? 1 : null));
			update_option(VSFStatsOptions::$VSF_STATS_DISPLAY_VISITOR_SUMMARY, ($_POST['displayVisitorsSummary'] == 'on' ? 1 : null));
			
			update_option(VSFStatsOptions::$VSF_STATS_DISPLAY_TABLE_COLOUR, ($_POST['vsfStatsDisplayColour']));
		}
		
		$numhitsResult = mysql_fetch_row(mysql_query("SELECT COUNT(*) FROM " . $this->tablePrefix . VSFSimpleStatsSetup::$TABLE_STATS));
		$this->numhits = $numhitsResult[0];
		
	?>
		<style>
			table.vsfStatsTable { font-size: 8pt; background-color: #<?php echo get_option(VSFStatsOptions::$VSF_STATS_DISPLAY_TABLE_COLOUR); ?>; width: 100%; word-wrap: break-word;  }
			table.vsfStatsTable tr { vertical-align:text-top; }
			table.vsfStatsTable td { padding: 1px; font-size: 8pt; background-color: #FFFFFF; }
			table.vsfStatsTable th { text-align:left; }
			.vsfStatsWhite { background-color: #FFFFFF; }
			.vsfStatsWidth50 { width: 50px; }
			.vsfStatsWidth110 { width: 110px; }
			.vsfStatsWidth200 { width: 200px; }
		</style>
		<script type="text/javascript">
			function clearStats(formObject)
			{
				var confirmBox = window.confirm("<?php _e("Are you sure you want to clear the stats?", 'vsf-simple-stats'); ?>")
				if (confirmBox)
				{
					formObject.clearStatsHidden.value = 1;
					formObject.submit();
				}
			}
			
			function colourChanged(formObject)
			{
				var colourBoxValue = formObject.vsfStatsDisplayColour.value;
				//alert('#' + formObject.vsfStatsDisplayColour.value);
				if( colourBoxValue.length > 6 )
				{
					colourBoxValue = colourBoxValue.substr(0, 6);
				}
				
				var newColourValue = '';
				// check the characters are between 0 and f
				var regExp = /[^\da-f]/gi;
				var colourBoxValue = colourBoxValue.replace(regExp, "");
				formObject.vsfStatsDisplayColour.value = colourBoxValue;
				
				formObject.dynamicColourIndicator.style.backgroundColor = '#' + colourBoxValue;
			}
		</script>
		<div class="wrap">
			<h2><?php _e('Statistics', 'vsf-simple-stats'); ?></h2>
			<form method="post" enctype="multipart/form-data">
				<input type="hidden" name="clearStatsHidden">
					
				<div id="poststuff" class="metabox-holder">
					<div id="post-body">
						<div id="post-body-content">
							<?php $this->buildMainContent(); ?>
						</div>
					</div>
				</div>
				
			</form>
		</div>
		<script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready
		( 
			function() 
			{
				jQuery('.postbox h3').click
				( 
					function()
					{
						jQuery(jQuery(this).parent().get(0)).toggleClass('closed');
					}
				)
			}
		);
		//]]>
	</script>
	<?php
	}

	private function buildMainContent()
	{
		$this->buildResetStatsPanel();
		
		if( get_option(VSFStatsOptions::$VSF_STATS_DISPLAY_GENERAL) == '1')
		{
			$this->buildGeneralStatsPanel();
		}
		
		if( get_option(VSFStatsOptions::$VSF_STATS_DISPLAY_CONTENT_SUMMARY) == '1')
		{
			$this->buildContentSummaryPanel();
		}
		
		if( get_option(VSFStatsOptions::$VSF_STATS_DISPLAY_REFERER) == '1')
		{
			$this->buildReferrerSummaryPanel();
		}
		
		if( get_option(VSFStatsOptions::$VSF_STATS_DISPLAY_REFERER_KEYWORDS) == '1')
		{
			$this->buildReferrerKeywordsPanel();
		}
		
		if( get_option(VSFStatsOptions::$VSF_STATS_DISPLAY_BROWSER_SUMMARY) == '1')
		{
			$this->buildBrowserSummaryPanel();
		}
		
		if( get_option(VSFStatsOptions::$VSF_STATS_DISPLAY_VISITOR_SUMMARY) == '1')
		{
			$this->buildVisitorsPanel();
		}
	}
	
	private function buildResetStatsPanel()
	{
		?>
		<div id="vsfStatsLogging" class="postbox">
			<div title="Click to toggle" class="handlediv"><br></div>
			<h3 class="hndle"><span><?php _e('Settings', 'vsf-simple-stats'); ?></span></h3>
			<div class="inside">
				<p><?php _e('Display the following panels', 'vsf-simple-stats'); ?></p>
				<?php VSFStatsUtilities::createCheckBox('displayGeneralStatistics', __('General Statistics', 'vsf-simple-stats'), 'vsfStatsDisplayGeneralStatistics'); ?>
				<?php VSFStatsUtilities::createCheckBox('displayContentSummary', __('Content Summary', 'vsf-simple-stats'), 'vsfStatsDisplayContentSummary'); ?>
				<?php VSFStatsUtilities::createCheckBox('displayReferrerSummary', __('Referrer Summary', 'vsf-simple-stats'), 'vsfStatsDisplayReferrerSummary'); ?>
				<?php VSFStatsUtilities::createCheckBox('displayReferrerKeywords', __('Referrer Search Terms', 'vsf-simple-stats'), 'vsfStatsDisplayReferrerKeywords'); ?>
				<?php VSFStatsUtilities::createCheckBox('displayBrowserSummary', __('Browser Summary', 'vsf-simple-stats'), 'vsfStatsDisplayBrowserSummary'); ?>
				<?php VSFStatsUtilities::createCheckBox('displayVisitorsSummary', __('Visitors', 'vsf-simple-stats'), 'vsfStatsDisplayVisitorsSummary'); ?>
				<p>&#160;</p>
				<p><?php _e('Table colour - accepted characters are, 0-9, a-f, A-F - E.g. F5689e', 'vsf-simple-stats'); ?></p>
				<input id="vsfStatsDisplayColour" type="text" name="vsfStatsDisplayColour" value="<?php form_option('vsfStatsDisplayTableColor'); ?>" onkeyup="colourChanged(this.form)"> <input type="text" id="dynamicColourIndicator" disabled="true" value="<?php _e('Preview', 'vsf-simple-stats'); ?>" style="background-color: #<?php echo get_option('vsfStatsDisplayTableColor'); ?>">
				<p>&#160;</p>
				<input type="submit" value="<?php _e('Update Settings', 'vsf-simple-stats'); ?>" name="updateDisplayedPanels">
				<p>&#160;</p>
				<input type="button" value="<?php _e('Reset Stats', 'vsf-simple-stats'); ?>" onClick="clearStats(this.form)">
			</div>
		</div>				
		<?php
	}

	function buildGeneralStatsPanel() 
	{
	?>
		<div id="vsfStatsGeneralStats" class="postbox">
			<div title="Click to toggle" class="handlediv"><br></div>
			<h3 class="hndle"><?php _e('General Statistics', 'vsf-simple-stats'); ?></h3>
			<div class="inside">
				<table class="vsfStatsTable">
					<?php
						$selectQuery = "SELECT unix_timestamp(cdate), COUNT(ip), SUM(cnt) FROM ("
			."SELECT DATE(FROM_UNIXTIME(`date`)) as cdate, ip, COUNT( id ) AS cnt FROM " . $this->tablePrefix . VSFSimpleStatsSetup::$TABLE_STATS . " GROUP BY cdate, ip) AS ss "
			."GROUP BY cdate ORDER BY cdate ASC";
						
						$columns = array(
							array('', __('Date', 'vsf-simple-stats')), 
							array('', __('Unique', 'vsf-simple-stats')), 
							array('', __('Total', 'vsf-simple-stats'))
						);
		
						VSFStatsUtilities::buildTableHeadAndFooter($columns);
					?>
					
					<tbody>
						<?php
						
						$x = 0;
						$numuniq = 0;
						$dateColumnFormat = __('d-m-Y', 'vsf-simple-stats');
						
						//echo $selectQuery;
						$blockQueryResult = mysql_query($selectQuery);
						while ( $c = mysql_fetch_row($blockQueryResult) )
						{
							$numuniq += $c[1];
						?>
							<tr class="<?php if( ($x % 2) == 0 ) {echo "alternate";} else {echo "";} ?>" style="padding: 1px;">
								<td class=""><?php echo date($dateColumnFormat, $c[0]); ?></td>
								<td class=""><?php echo $c[1]; ?></td>
								<td class=""><?php echo $c[2]; ?></td>
							</tr>
						<?php
							
							$x++;
						}
						
						?><tr class="<?php if( ($x % 2) == 0 ) {echo "alternate";} else {echo "";} ?>"><td><b><?php _e('Total', 'vsf-simple-stats'); ?></b></td><td><b><?php echo $numuniq; ?></b></td><td><b><?php echo $this->numhits; ?></b></td></tr><?php
						
						?>
					</tbody>
				</table>
				
			</div>
		</div>
	<?php
	}
	
	function buildContentSummaryPanel()
	{
		$divTitle = __('Content Summary', 'vsf-simple-stats');
		
		$fields = array("url", "COUNT(id) as countId");
		$table = $this->tablePrefix . VSFSimpleStatsSetup::$TABLE_STATS . " GROUP BY url";
		$where = null;
		$order = "countId desc";
		
		$selectQuery = VSFStatsUtilities::buildSelectQuery(false, $fields, $table, $where, $order);
						
		$columns = array(
			array('', __('Path', 'vsf-simple-stats')), 
			array('vsfStatsWidth50', __('# Hits', 'vsf-simple-stats')), 
			array('vsfStatsWidth50', __('% Hits', 'vsf-simple-stats'))
		);
		
		$this->buildBasicStatsPanel($divTitle, $selectQuery, $columns);
	}
	
	function buildReferrerSummaryPanel()
	{
		$divTitle = __('Referrer Summary', 'vsf-simple-stats');
		
		$fields = array("referrer", "COUNT(id) as countId");
		$table = $this->tablePrefix . VSFSimpleStatsSetup::$TABLE_STATS . " GROUP BY referrer";
		$where = null;
		$order = "countId desc";
		
		$selectQuery = VSFStatsUtilities::buildSelectQuery(false, $fields, $table, $where, $order);
		
		$columns = array(
			array('', __('Referrer', 'vsf-simple-stats')), 
			array('vsfStatsWidth50', __('# Hits', 'vsf-simple-stats')), 
			array('vsfStatsWidth50', __('% Hits', 'vsf-simple-stats'))
		);
		
		$this->buildBasicStatsPanel($divTitle, $selectQuery, $columns);
	}
	
	function buildReferrerKeywordsPanel()
	{
		$fields = array(VSFSimpleStatsSetup::$TABLE_STATS_DOMAIN, VSFSimpleStatsSetup::$TABLE_STATS_SEARCH_TERMS);
		$table = $this->tablePrefix . VSFSimpleStatsSetup::$TABLE_STATS;
		$where = VSFSimpleStatsSetup::$TABLE_STATS_SEARCH_TERMS . " IS NOT NULL";
		$order = VSFSimpleStatsSetup::$TABLE_STATS_DOMAIN . " asc, " . VSFSimpleStatsSetup::$TABLE_STATS_ID;
		
		$selectQuery = VSFStatsUtilities::buildSelectQuery(false, $fields, $table, $where, $order);
		
		$columns = array(
			array('vsfStatsWidth200', __('Referrer', 'vsf-simple-stats')), 
			array('', __('Search Terms', 'vsf-simple-stats'))
		);
		
		?><div id="vsfStatsGeneralStats" class="postbox">
			<div title="Click to toggle" class="handlediv"><br></div>
			<h3 class="hndle"><?php _e('Referrer Search Terms', 'vsf-simple-stats'); ?></h3>
			<div class="inside">
				<table class="vsfStatsTable">
					<?php VSFStatsUtilities::buildTableHeadAndFooter($columns);	?>
					
					<tbody><?php
						$x = 0;
						
						//echo $selectQuery;
						$blockQueryResult = mysql_query($selectQuery);
						while ( $c = mysql_fetch_row($blockQueryResult) ) { ?>
							<tr class="<?php if( ($x % 2) == 0 ) {echo "alternate";} else {echo "";} ?>">
								<td><?php echo $c[0]; ?></td>
								<td><?php echo $c[1]; ?></td>
							</tr>
						<?php $x++;
						}
						
					?></tbody>
				</table>
				
			</div>
		</div><?php
	}
	
	function buildBrowserSummaryPanel()
	{
		$divTitle = __('Browser Summary', 'vsf-simple-stats');
		
		$fields = array("browser", "COUNT(id) as countId");
		$table = $this->tablePrefix . VSFSimpleStatsSetup::$TABLE_STATS . " GROUP BY browser";
		$where = null;
		$order = "countId desc";
		
		$selectQuery = VSFStatsUtilities::buildSelectQuery(false, $fields, $table, $where, $order);
		
		$columns = array(
			array('', __('Browser', 'vsf-simple-stats')), 
			array('vsfStatsWidth50', __('# Hits', 'vsf-simple-stats')), 
			array('vsfStatsWidth50', __('% Hits', 'vsf-simple-stats'))
		);
		
		$this->buildBasicStatsPanel($divTitle, $selectQuery, $columns);
	}
	
	function buildVisitorsPanel()
	{
	?>
		<div id="vsfStatsGeneralStats" class="postbox">
			<div title="Click to toggle" class="handlediv"><br></div>
			<h3 class="hndle"><?php _e('Visitors', 'vsf-simple-stats'); ?></h3>
			<div class="inside">
				<table class="vsfStatsTable">
					<?php
						$fields = array(VSFSimpleStatsSetup::$TABLE_STATS_DATE, VSFSimpleStatsSetup::$TABLE_STATS_HOST, "inet_ntoa(" . VSFSimpleStatsSetup::$TABLE_STATS_IP . ")", VSFSimpleStatsSetup::$TABLE_STATS_BROWSER, VSFSimpleStatsSetup::$TABLE_STATS_URL);
						$table = $this->tablePrefix . VSFSimpleStatsSetup::$TABLE_STATS;
						$where = null;
						$order = "id desc";
						
						$selectQuery = VSFStatsUtilities::buildSelectQuery(false, $fields, $table, $where, $order);
						
						$columns = array(
							array('vsfStatsWidth110', __('Date', 'vsf-simple-stats')), 
							array('', __('Host', 'vsf-simple-stats')), 
							array('vsfStatsWidth110', __('IP', 'vsf-simple-stats')), 
							array('', __('Browser Summary', 'vsf-simple-stats')), 
							array('', __('URL', 'vsf-simple-stats'))
						);
						VSFStatsUtilities::buildTableHeadAndFooter($columns);
					?>
					
					<tbody>
						<?php
						
						$x = 0;
						$dateFormatForVisit = __('H:i d-m-Y', 'vsf-simple-stats');
						
						//echo $selectQuery;
						$selectQueryResult = mysql_query($selectQuery);
						while ( $c = mysql_fetch_row($selectQueryResult) )
						{
						?>
							<tr class="<?php if( ($x % 2) == 0 ) {echo "alternate";} else {echo "";} ?>">
								<td class="vsfStatsWidth110"><?php echo date($dateFormatForVisit, $c[0]); ?></td>
								<td><?php echo $c[1]; ?></td>
								<td class="vsfStatsWidth110"><?php echo $c[2]; ?></td>
								<td><?php echo $c[3]; ?></td>
								<td><?php echo $c[4]; ?></td>
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
	
	private function buildBasicStatsPanel($divTitle, $selectQuery, $columns)
	{
	?>
		<div id="vsfStatsGeneralStats" class="postbox">
			<div title="Click to toggle" class="handlediv"><br></div>
			<h3 class="hndle"><?php echo $divTitle; ?></h3>
			<div class="inside">
				<table class="vsfStatsTable">
					<?php VSFStatsUtilities::buildTableHeadAndFooter($columns);	?>
					
					<tbody>
						<?php
						
						$x = 0;
						
						//echo $selectQuery;
						$blockQueryResult = mysql_query($selectQuery);
						while ( $c = mysql_fetch_row($blockQueryResult) )
						{
						?>
							<tr class="<?php if( ($x % 2) == 0 ) {echo "alternate";} else {echo "";} ?>">
								<td><?php echo $c[0]; ?></td>
								<td class="vsfStatsWidth50" align="right"><?php echo $c[1]; ?></td>
								<td class="vsfStatsWidth50" align="right"><?php echo number_format(100 * ($c[1] / $this->numhits), 2); ?></td>
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