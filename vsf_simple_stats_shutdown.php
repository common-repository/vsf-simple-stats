<?php
include_once('vsf_simple_stats_setup.php');
include_once('vsf_simple_stats_query_extractor.php');

class VSFSimpleStatsShutdown
{
	private $tablePrefix = "";
	
	public function shutdownHook()
	{
		global $wpdb;
		$this->tablePrefix = $wpdb->prefix;
		
		$ipaddress = $_SERVER['REMOTE_ADDR'];
		// http://dev.mysql.com/doc/refman/5.0/en/miscellaneous-functions.html#function_inet-aton
		// See if the ip is equal to ip1 or between ip1 and ip2
		$ipAddressFilteredResult = mysql_fetch_row(mysql_query("SELECT count(*) FROM " . $this->tablePrefix . VSFSimpleStatsSetup::$TABLE_FILTER . " WHERE (INET_ATON('$ipaddress') = " . VSFSimpleStatsSetup::$TABLE_FILTER_IP1 . ") OR (INET_ATON('$ipaddress') BETWEEN " . VSFSimpleStatsSetup::$TABLE_FILTER_IP1 . " AND " . VSFSimpleStatsSetup::$TABLE_FILTER_IP2 . ")"));
		
		// only go on if the result is zero
		if( $ipAddressFilteredResult[0] == 0 )
		{
			$urlOwnDomain = get_option("siteurl") . "/";
			$maxhits = get_option("vsf_stats_max_hits");
			
			if ( !ctype_digit((string)$maxhits) || (strlen($maxhits) == 0) ) 
			{
				$maxhits = 500;
				update_option("vsf_stats_max_hits", $maxhits);
			}

			$url = $_SERVER['REQUEST_URI'];
			$is_administrative = strpos($url,'wp-admin/');
			// If the page is not administrative
			if ( !$is_administrative )
			{
				$host    	= gethostbyaddr($_SERVER['REMOTE_ADDR']);
				$browser	= $_SERVER['HTTP_USER_AGENT'];
				$referrer	= $_SERVER['HTTP_REFERER'];
				$domain   	= parse_url($_SERVER['HTTP_REFERER']);
				$owndomain	= parse_url($urlOwnDomain);
				$searchTerms = VSFSimpleStatsQueryUtil::searchTerms($referrer);
				$searchTerms = isset($searchTerms) ? "'$searchTerms'" : "null";
				
				$query = "INSERT INTO " . $this->tablePrefix . VSFSimpleStatsSetup::$TABLE_STATS . " (ip, url, host, date, browser, referrer, domain, searchTerms) VALUES (inet_aton('$ipaddress'), '$url', '$host', UNIX_TIMESTAMP(), '$browser', '$referrer', '$domain[host]', " . $searchTerms . ")";
				$result = mysql_query($query);
				
				$res = mysql_fetch_row(mysql_query("SELECT max(id) FROM " . $this->tablePrefix . VSFSimpleStatsSetup::$TABLE_STATS));
				if ( $res[0] > $maxhits )
				{
					mysql_query("DELETE FROM " . $this->tablePrefix . VSFSimpleStatsSetup::$TABLE_STATS . " WHERE id <= " . ($res[0] - $maxhits));
				}
			}
		}
	}
}

?>