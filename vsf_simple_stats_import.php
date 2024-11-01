<?php

include_once('vsf_simple_stats_setup.php');
include_once('vsf_simple_stats_filter.php');
include_once('vsf_simple_stats_port_i.php');

class VSFSimpleStatsImport implements IVSFSimpleStatsPort
{
	private $tablePrefix = "";
	
	public function importFileValues($filterIpsXMLFileContent)
	{
		global $wpdb;
		$this->tablePrefix = $wpdb->prefix;
		
		// remove the xml definition
		$filterIpsXMLFileContent = str_replace(self::XML_HEADING, '', $filterIpsXMLFileContent);
		// replace close xml element with eo tag
		$filterIpsXMLFileContent = str_replace('</', self::END_ELEMENT, $filterIpsXMLFileContent);
		// replace open xml element with so tag
		$filterIpsXMLFileContent = str_replace('<', self::START_ELEMENT, $filterIpsXMLFileContent);
		
		//if( self::DEBUG ) echo $filterIpsXMLFileContent . '\n';
		
		
		/// QUANTITY ///
		$quantity = $this->findValueFromTag($filterIpsXMLFileContent, self::ELEMENT_QUANTITY);
		
		if( $quantity == null || (strlen($quantity) == 0) )
		{
			?><div class="error"><p><strong><?php _e('Quantity in uploaded file is wrong!', 'vsf-simple-stats') ?></strong></p></div><?php
			return;
		}
		
		
		/// FILTERS ///
		
		$filtersArray[$quantity];
		
		for( $i = 0 ; $i < $quantity ; $i++ )
		{
			//if( self::DEBUG ) echo $filterIpsXMLFileContent . '<br /><br />';
			// Filter
			$filter = $this->findValueFromTag($filterIpsXMLFileContent, self::ELEMENT_FILTER);
			
			// remove that filter
			$removeFilter = self::START_ELEMENT . self::ELEMENT_FILTER . '>' . $filter . self::END_ELEMENT . self::ELEMENT_FILTER . '>';
			//if( self::DEBUG ) echo 'FILTER: ' . $removeFilter . '<br />';
			$filterIpsXMLFileContent = str_replace($removeFilter, '', $filterIpsXMLFileContent);
			
			if( $filter == null || (strlen($filter) == 0) )
			{
				?><div class="error"><p><strong><?php _e('Filter in uploaded file is wrong!', 'vsf-simple-stats'); echo ' - ' . $quantity; ?></strong></p></div><?php
				return;
			}
			
			
			// IP TO
			$ipTo = $this->findValueFromTag($filter, self::ELEMENT_IP_TO);
			
			if( $ipTo == null || (strlen($ipTo) == 0) )
			{
				?><div class="error"><p><strong><?php _e('Filter IP to in uploaded file is wrong!', 'vsf-simple-stats'); echo ' - ' . $quantity; ?></strong></p></div><?php
				return;
			}
			
			
			// IP FROM
			$ipFrom = $this->findValueFromTag($filter, self::ELEMENT_IP_FROM);
			
			
			// Description
			$description = $this->findValueFromTag($filter, self::ELEMENT_DESCRIPTION);
			
			//if( self::DEBUG ) echo 'ipto: ' . $ipTo . ' ipFrom: ' . $ipFrom . ' desc: ' . $description . '<br />';
			
			$filterObject = new VSFSimpleStatsFilter();
			$filterObject->setIPTo($ipTo);
			$filterObject->setIPFrom($ipFrom);
			$filterObject->setDescription($description);
			
			//if( self::DEBUG ) $filterObject->toString();
			//if( self::DEBUG ) echo '<br />';
			
			$filtersArray[$i] = $filterObject;
		}
		
		//if( self::DEBUG ) echo '<br /><br />';
		
		//if( self::DEBUG ) $this->printOutFilterArray($filtersArray);
		
		$this->processFilters($filtersArray);
	}
	
	private function findValueFromTag($stringToGetValueFrom, $tag)
	{
		$openTag = self::START_ELEMENT . $tag . '>';
		$closeTag = self::END_ELEMENT . $tag . '>';
		//if( self::DEBUG ) echo $openTag . '\n';
		//if( self::DEBUG ) echo $closeTag . '\n';
	
		$endOfOpenTag = strpos($stringToGetValueFrom, $openTag) + strlen($openTag);
		$startOfCloseTag = strpos($stringToGetValueFrom, $closeTag);
		//if( self::DEBUG ) echo 'start: ' . $endOfOpenTag . '   end: ' .  $startOfCloseTag;
		$tagValue = substr($stringToGetValueFrom, $endOfOpenTag, ($startOfCloseTag - $endOfOpenTag));
		//if( self::DEBUG ) echo 'result: ' . $tagValue;
		
		return $tagValue;
	}
	
	/**
	 * Processes the list of filteres, inserting them in to the database if they are not already present, otherwise ignoring them.
	 */
	private function processFilters($filters)
	{
		$alreadyInDB = 0;
		
		$errorsFromInsert = 0;
		$errorsThatOccured[$filters];
		
		foreach( $filters as $filterInstance )
		{
			$inDB = $this->checkForFilterInDB($filterInstance);
			//if( self::DEBUG ) echo $inDB . '<br />';
			if( $inDB )
			{
				$alreadyInDB++;
			}
			else
			{
				$error = $this->addFilterToDB($filterInstance);
				//if( self::DEBUG ) echo $error . '<br />';
				if( $error )
				{
					$errorsFromInsert++;
					$errorsThatOccured[count($errorsThatOccured)] = $filterInstance;
				}
			}
		}
		
		//if( self::DEBUG ) echo '<br />errorsFromInsert: ' . $errorsFromInsert . ' alreadyInDB: ' . $alreadyInDB . ' inserted: ' . (count($filters) - $alreadyInDB - $errorsFromInsert) . '<br />';
		
		if( $errorsFromInsert != 0 )
		{
			if( $errorsFromInsert > 1 )
			{
				?><div class="error"><p><strong><?php printf(__('There were %d errors during import.', 'vsf-simple-stats'), $errorsFromInsert); ?></strong></p><p><?php _e('Info:', 'vsf-simple-stats'); echo '<br />'; $this->printOutFilterArray($errorsThatOccured); ?></p></div><?php
			}
			else
			{
				?><div class="error"><p><strong><?php _e('There was 1 error during import.', 'vsf-simple-stats'); ?></strong></p><p><?php _e('Info:', 'vsf-simple-stats'); echo '<br />'; $this->printOutFilterArray($errorsThatOccured); ?></p></div><?php
			}
		}
		else
		{
			if( (count($filters) - $alreadyInDB - $errorsFromInsert) > 1 )
			{
				?><div class="updated"><p><strong><?php printf(__('Successfully imported %d filters', 'vsf-simple-stats'), (count($filters) - $alreadyInDB - $errorsFromInsert)); ?></strong></p></div><?php
			}
			else if ( (count($filters) - $alreadyInDB - $errorsFromInsert) == 0 )
			{
				?><div class="updated"><p><strong><?php _e('No filters to import.  Database already contains all filters in file.', 'vsf-simple-stats'); ?></strong></p></div><?php
			}
			else if ( (count($filters) - $alreadyInDB - $errorsFromInsert) == 1 )
			{
				?><div class="updated"><p><strong><?php _e('Successfully imported 1 filter', 'vsf-simple-stats'); ?></strong></p></div><?php
			}
		}
	}
	
	/** 
	 * Checks the current filter instance to see if it's already in the database. 
	 */
	private function checkForFilterInDB($filter)
	{
		$selectStatement = "SELECT count(*) FROM " . $this->tablePrefix . VSFSimpleStatsSetup::$TABLE_FILTER . " WHERE ('" . $filter->getIPTo() . "' = " . VSFSimpleStatsSetup::$TABLE_FILTER_IP1 . ") AND ('" . $filter->getIPFrom() . "' = " . VSFSimpleStatsSetup::$TABLE_FILTER_IP2 . ") AND ('" . $filter->getDescription() . "' = " . VSFSimpleStatsSetup::$TABLE_FILTER_DESCRIPTION . ")";
		//if( self::DEBUG ) echo $selectStatement . '<br />';
		$ipAddressFilteredResult = mysql_fetch_row(mysql_query($selectStatement));
		
		//if( self::DEBUG ) echo 'indb? : ' . $ipAddressFilteredResult[0] . '<br />';
		
		return ($ipAddressFilteredResult[0] > 0);
	}
	
	/** 
	 * Adds the current filter instance to the database. 
	 */
	private function addFilterToDB($filter)
	{
		$ip1 = $filter->getIPTo();
		//if( self::DEBUG ) echo $ip1 . ' is int? ' . ctype_digit($ip1) . '<br />';
		if( !ctype_digit($ip1) )
		{
			// will not accept values that are not integers!
			return true;
		}
		
		$ip2 = ($filter->getIPFrom() != null && $filter->getIPFrom() != "" && ctype_digit($filter->getIPFrom())) ? $filter->getIPFrom() : 0;
		$description = $filter->getDescription();
		
		// clean up the description
		if( $description != null && $description != "" )
		{
			$description = (strlen($description) <= VSFSimpleStatsSetup::$TABLE_FILTER_DESCRIPTION_MAX_LENGTH ? $description : substr($description, 0, VSFSimpleStatsSetup::$TABLE_FILTER_DESCRIPTION_MAX_LENGTH));
			$description = (phpversion() >= '4.3.0' ? mysql_real_escape_string($description) : mysql_escape_string($description));
			$description = str_replace(array('<', '>'), '', $description);
		}
		$query = "INSERT INTO " . $this->tablePrefix . VSFSimpleStatsSetup::$TABLE_FILTER . " (" . VSFSimpleStatsSetup::$TABLE_FILTER_IP1 . ", " . VSFSimpleStatsSetup::$TABLE_FILTER_IP2 . ", " . VSFSimpleStatsSetup::$TABLE_FILTER_DESCRIPTION . ") VALUES (" . $ip1 . ", " . $ip2 . ", '" . $description . "')";
		//if( self::DEBUG ) echo 'insert query: ' . $query . '<br />';
		
		$result = mysql_query($query);
		//if( self::DEBUG ) echo 'insert result: ' . $result . '<br />';
		
		// if the result was anything but 1, then there was an error.
		return $result != 1;
	}
	
	/**
	 * Prints out each filter object
	 */
	private function printOutFilterArray($filterArray)
	{
		foreach( $filterArray as $t )
		{
			$t->toString();
		}
	}
}

?>