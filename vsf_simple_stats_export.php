<?php

include_once('vsf_simple_stats_setup.php');
include('vsf_simple_stats_port_i.php');

class VSFSimpleStatsExport implements IVSFSimpleStatsPort
{
	private $tablePrefix = "";
	
	function generateExport()
	{
		global $wpdb;
		$this->tablePrefix = $wpdb->prefix;
		
		$xmlstr = self::XML_HEADING . '<' . self::ROOT_ELEMENT . '>';
			
		$filtered_ip_query_result = mysql_query("SELECT " . VSFSimpleStatsSetup::$TABLE_FILTER_IP1 . ", " . VSFSimpleStatsSetup::$TABLE_FILTER_IP2 . ", " . VSFSimpleStatsSetup::$TABLE_FILTER_DESCRIPTION . " FROM " . $this->tablePrefix . VSFSimpleStatsSetup::$TABLE_FILTER . " ORDER BY " . VSFSimpleStatsSetup::$TABLE_FILTER_ID . " ASC");
		$xmlstr .= '<' . self::ELEMENT_QUANTITY . '>' . mysql_num_rows($filtered_ip_query_result) . '</' . self::ELEMENT_QUANTITY . '>';
		$xmlstr .= '<' . self::ELEMENT_FILTERS . '>';
		
		while ( $filteredIpInst = mysql_fetch_row($filtered_ip_query_result) )
		{
			$xmlstr .= '<' . self::ELEMENT_FILTER . '>';
			$xmlstr .= '<' . self::ELEMENT_IP_TO . '>' . $filteredIpInst[0] . '</' . self::ELEMENT_IP_TO . '>';
			$xmlstr .= '<' . self::ELEMENT_IP_FROM . '>' . $filteredIpInst[1] . '</' . self::ELEMENT_IP_FROM . '>';
			$xmlstr .= '<' . self::ELEMENT_DESCRIPTION . '>' . $filteredIpInst[2] . '</' . self::ELEMENT_DESCRIPTION . '>';
			$xmlstr .= '</' . self::ELEMENT_FILTER . '>';
		}
		
		$xmlstr .= '</' . self::ELEMENT_FILTERS . '>';
		$xmlstr .= '</' . self::ROOT_ELEMENT . '>';
		
		header ("Content-Type:text/xml");
			
		$now = gmdate('Y-m-d H:i');
		header('Content-Disposition: attachment; filename="VSF stats filter ' . $now . '.xml"');
		
		echo $xmlstr;
		
		exit;
	}
}

?>