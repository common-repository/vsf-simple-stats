<?php

include_once 'vsf_simple_stats_options.php';

class VSFSimpleStatsSetup
{
	public static $TABLE_STATS = "vsf_stats";
	public static $TABLE_FILTER = "vsf_stats_filter";
	public static $TABLE_URL_PARAMS = "vsf_stats_url_params";
	
	public static $INDEX_PREFIX = "idxVsfStats";
	public static $INDEX_FILTER = "Filter";
	
	public static $TABLE_STATS_ID = "id";
	public static $TABLE_STATS_URL = "url";
	public static $TABLE_STATS_IP = "ip";
	public static $TABLE_STATS_HOST = "host";
	public static $TABLE_STATS_DATE = "date";
	public static $TABLE_STATS_BROWSER = "browser";
	public static $TABLE_STATS_REFERRER = "referrer";
	public static $TABLE_STATS_DOMAIN = "domain";
	public static $TABLE_STATS_SEARCH_TERMS = "searchTerms";
	public static $TABLE_STATS_SEARCH_TERMS_MAX_LENGTH = 1000;
	
	public static $TABLE_FILTER_ID = "id";
	public static $TABLE_FILTER_IP1 = "ip1";
	public static $TABLE_FILTER_IP2 = "ip2";
	public static $TABLE_FILTER_DESCRIPTION = "description";
	public static $TABLE_FILTER_DESCRIPTION_MAX_LENGTH = 250;
	
	public static $TABLE_URL_PARAMS_ID = "id";
	public static $TABLE_URL_PARAMS_VALUE = "queryparameter";
	public static $TABLE_URL_PARAMS_VALUE_MAX_LENGTH = 10;
	
	private static $IP_SIZE = 16;
	
	private $tablePrefix = "";
	
	private static $CURRENT_VERSION = '0.8';
	
	/** Creates the tables if necessary **/
	public function activation() 
	{
		if ( get_option(VSFStatsOptions::$VSF_STATS_VERSION) != self::$CURRENT_VERSION ) 
		{
			global $wpdb;
			$this->tablePrefix = $wpdb->prefix;
			
			if( get_option(VSFStatsOptions::$VSF_STATS_VERSION) == '0.1' )
			{
				// rename old tables
				mysql_query("RENAME TABLE wp_" . self::$TABLE_STATS . " TO oldVSFStats_" . self::$TABLE_STATS) or die ("Failed to rename tables for upgrade 1: " . mysql_error());
				mysql_query("RENAME TABLE wp_" . self::$TABLE_FILTER . " TO oldVSFStats_" . self::$TABLE_FILTER) or die ("Failed to rename tables for upgrade 2: " . mysql_error());
				
				$this->createTables();
				
				mysql_query("INSERT INTO " . $this->tablePrefix . self::$TABLE_FILTER . " SELECT * FROM oldVSFStats_" . self::$TABLE_FILTER);
				mysql_query("INSERT INTO " . $this->tablePrefix . self::$TABLE_STATS . " (url, ip, host, date, browser, referrer, domain) 
					SELECT url, inet_aton(ip), host, CAST(date AS DECIMAL(16,0)) as date, browser, referer, domain
					FROM oldVSFStats_" . self::$TABLE_STATS);
				
				mysql_query("DROP TABLE oldVSFStats_" . self::$TABLE_STATS);
				mysql_query("DROP TABLE oldVSFStats_" . self::$TABLE_FILTER);
				
				$this->upgrade06();
			}
			else if( get_option(VSFStatsOptions::$VSF_STATS_VERSION) == '0.6' )
			{
				$this->upgrade06();
			}
            else if( get_option(VSFStatsOptions::$VSF_STATS_VERSION) == '0.7' )
            {
                $this->upgrade07();
            }
			else
			{
				$this->createTables();
				
				// By default - set the max hits to 500
				update_option(VSFStatsOptions::$VSF_STATS_MAX_RECORDED_HITS, '500');
                $this->createAdditionalItems();
			}
			
			// Current version of this stats plugin database
			update_option(VSFStatsOptions::$VSF_STATS_VERSION, self::$CURRENT_VERSION);
			
			
		}
	}
    
    private function createAdditionalItems()
    {
        // Add in display settings
        update_option(VSFStatsOptions::$VSF_STATS_DISPLAY_GENERAL, '1');
        update_option(VSFStatsOptions::$VSF_STATS_DISPLAY_CONTENT_SUMMARY, '1');
        update_option(VSFStatsOptions::$VSF_STATS_DISPLAY_REFERER, '1');
        update_option(VSFStatsOptions::$VSF_STATS_DISPLAY_REFERER_KEYWORDS, '1');
        update_option(VSFStatsOptions::$VSF_STATS_DISPLAY_BROWSER_SUMMARY, '1');
        update_option(VSFStatsOptions::$VSF_STATS_DISPLAY_VISITOR_SUMMARY, '1');

        update_option(VSFStatsOptions::$VSF_STATS_DISPLAY_TABLE_COLOUR, '666666');
    }
	
	private function upgrade06()
	{
		mysql_query
		(
			"ALTER TABLE " . $this->tablePrefix . self::$TABLE_STATS . " "
			."ADD COLUMN " . self::$TABLE_STATS_SEARCH_TERMS . " varchar(" . self::$TABLE_STATS_SEARCH_TERMS_MAX_LENGTH . ")"
		) or die ("Cannot upgrade vsf stats table to version 0.7: " . mysql_error());
        
        $this->createAdditionalItems();
	}
    
    /**
     * Upgrades from version 0.7 to the latest version.
     */
    private function upgrade07()
    {
        $this->createURLParametersTable();
    }
	
	private function createTables()
	{
		// table to hold the recorded stats
		mysql_query
		(
			"CREATE TABLE `" . $this->tablePrefix . self::$TABLE_STATS . "` ("
			."`" . self::$TABLE_STATS_ID . "` int(100) NOT NULL auto_increment,"
			."`" . self::$TABLE_STATS_URL . "` varchar(150),"
			."`" . self::$TABLE_STATS_IP . "` int(" . self::$IP_SIZE . ") UNSIGNED,"
			."`" . self::$TABLE_STATS_HOST . "` varchar(250),"
			."`" . self::$TABLE_STATS_DATE . "` int(16) UNSIGNED,"
			."`" . self::$TABLE_STATS_BROWSER . "` varchar(200),"
			."`" . self::$TABLE_STATS_REFERRER . "` varchar(255),"
			."`" . self::$TABLE_STATS_DOMAIN . "` varchar(255),"
			."`" . self::$TABLE_STATS_SEARCH_TERMS . "` varchar(" . self::$TABLE_STATS_SEARCH_TERMS_MAX_LENGTH . "),"
			."PRIMARY KEY  (`" . self::$TABLE_STATS_ID . "`)"
			.")"
		) or die ("Cannot create vsf stats table: " . mysql_error());
		
		// table to hold the list of ips that should not be counted
		$queryResult = mysql_query
		(
			"CREATE TABLE `" . $this->tablePrefix . self::$TABLE_FILTER . "` ("
			."`" . self::$TABLE_FILTER_ID . "` int(100) NOT NULL auto_increment,"
			."`" . self::$TABLE_FILTER_IP1 . "` int(" . self::$IP_SIZE . ") UNSIGNED,"
			."`" . self::$TABLE_FILTER_IP2 . "` int(" . self::$IP_SIZE . ") UNSIGNED,"
			."`" . self::$TABLE_FILTER_DESCRIPTION . "` varchar(" . self::$TABLE_FILTER_DESCRIPTION_MAX_LENGTH . ") NOT NULL default '',"
			."PRIMARY KEY  (`" . self::$TABLE_FILTER_ID . "`)"
			.")"
		);
        
		if( !$queryResult )
		{
			die ("Cannot create vsf stats filter table: " . mysql_error());
		}
		
        $Index = "CREATE INDEX " . self::$INDEX_PREFIX . self::$INDEX_FILTER;
        $Index .= " on " . $this->tablePrefix . self::$TABLE_FILTER;
        $Index .= " (" . self::$TABLE_FILTER_IP1 . ", " . self::$TABLE_FILTER_IP2 . ")";

        $addIndexResult = mysql_query($Index);
        if ( !$addIndexResult ) die ("Cannot create vsf stats index on rules table.<br />" . mysql_error());

        $this->createURLParametersTable();
	}
    
    private function createURLParametersTable()
    {
        mysql_query
		(
			"CREATE TABLE " . $this->tablePrefix . self::$TABLE_URL_PARAMS . " ("
			. self::$TABLE_URL_PARAMS_ID . " int(100) NOT NULL auto_increment,"
			. self::$TABLE_URL_PARAMS_VALUE . " varchar(" . self::$TABLE_URL_PARAMS_VALUE_MAX_LENGTH . "),"
			."PRIMARY KEY  (" . self::$TABLE_URL_PARAMS_ID . ")"
			.")"
		) or die ("Cannot create vsf stats url parameters table: " . mysql_error());
        
        mysql_query("INSERT INTO " . $this->tablePrefix . self::$TABLE_URL_PARAMS . " (". self::$TABLE_URL_PARAMS_VALUE . ") VALUES ('q')");
        mysql_query("INSERT INTO " . $this->tablePrefix . self::$TABLE_URL_PARAMS . " (". self::$TABLE_URL_PARAMS_VALUE . ") VALUES ('p')");
    }
}

?>