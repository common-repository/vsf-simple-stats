<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of vsf_simple_stats_options
 *
 * @author Victoria
 */
if( !class_exists("VSFStatsOptions") )
{
    class VSFStatsOptions {
        public static $VSF_STATS_VERSION = "vsf_stats_version";
        public static $VSF_STATS_MAX_RECORDED_HITS = "vsf_stats_max_hits";
        public static $VSF_STATS_DISPLAY_GENERAL = "vsfStatsDisplayGeneralStatistics";
        public static $VSF_STATS_DISPLAY_CONTENT_SUMMARY = "vsfStatsDisplayContentSummary";
        public static $VSF_STATS_DISPLAY_REFERER = "vsfStatsDisplayReferrerSummary";
        public static $VSF_STATS_DISPLAY_REFERER_KEYWORDS = "vsfStatsDisplayReferrerKeywords";
        public static $VSF_STATS_DISPLAY_BROWSER_SUMMARY = "vsfStatsDisplayBrowserSummary";
        public static $VSF_STATS_DISPLAY_VISITOR_SUMMARY = "vsfStatsDisplayVisitorsSummary";
        public static $VSF_STATS_DISPLAY_TABLE_COLOUR = "vsfStatsDisplayTableColor";
        
        public static function removeOptions()
        {
            delete_option(self::$VSF_STATS_VERSION);
            delete_option(self::$VSF_STATS_MAX_RECORDED_HITS);
            delete_option(self::$VSF_STATS_DISPLAY_GENERAL);
            delete_option(self::$VSF_STATS_DISPLAY_CONTENT_SUMMARY);
            delete_option(self::$VSF_STATS_DISPLAY_REFERER);
            delete_option(self::$VSF_STATS_DISPLAY_REFERER_KEYWORDS);
            delete_option(self::$VSF_STATS_DISPLAY_BROWSER_SUMMARY);
            delete_option(self::$VSF_STATS_DISPLAY_VISITOR_SUMMARY);
            delete_option(self::$VSF_STATS_DISPLAY_TABLE_COLOUR);
        }
    }
}

?>
