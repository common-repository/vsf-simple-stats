<?php

include_once('vsf_simple_stats_setup.php');

/**
 * Reference: http://www.electrictoolbox.com/php-keywords-search-engine-referer-url/
 */
class VSFSimpleStatsQueryUtil {

    private static $QUERY = "query";
	private static $FRAGMENT = "fragment";
	
	public static function searchTerms($url) {
        
		if(!isset($url)) {
	        return null;
	    }
	
	    $partsOfURL = parse_url($url);
	    
	    $query = isset($partsOfURL[self::$QUERY]) ? $partsOfURL[self::$QUERY] : (isset($partsOfURL[self::$FRAGMENT]) ? $partsOfURL[self::$FRAGMENT] : null);
	    
	    if(!isset($query)) {
	        return null;
	    }
	    
	    parse_str($query, $partsOfURL);
        
        global $wpdb;
        
        $blockQueryResult = mysql_query("SELECT " . VSFSimpleStatsSetup::$TABLE_URL_PARAMS_VALUE . " FROM " . $wpdb->prefix . VSFSimpleStatsSetup::$TABLE_URL_PARAMS);
		while ( $c = mysql_fetch_row($blockQueryResult) )
        {
            if( array_key_exists($c[0], $partsOfURL) )
            {
                $searchTerms = $partsOfURL[$c[0]];

                if(isset($searchTerms)) {
                    if(strlen($searchTerms) > VSFSimpleStatsSetup::$TABLE_STATS_SEARCH_TERMS_MAX_LENGTH) {
                        $searchTerms = substr($searchTerms, 0, VSFSimpleStatsSetup::$TABLE_STATS_SEARCH_TERMS_MAX_LENGTH);
                    }
                }

                return $searchTerms;
            }
        }
	}
}
?>