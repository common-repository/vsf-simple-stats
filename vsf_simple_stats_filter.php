<?php

/**
 * Simple filter object
 */
class VSFSimpleStatsFilter
{
	protected $ipTo;
	protected $ipFrom;
	protected $description;
	
	/** Getter */
	public function getIPTo() { return $this->ipTo; }
	/** Getter */
	public function getIPFrom() { return $this->ipFrom; }
	/** Getter */
	public function getDescription() { return $this->description; }
	
	/** Setter */
	public function setIPTo($value)
	{
		if( $value != null && $value != "" )
		{
			$this->ipTo = $value;
		}
	}
	
	/** Setter */
	public function setIPFrom($value)
	{
		if( $value != null && $value != "" && $value != "0" && $value != 0 )
		{
			$this->ipFrom = $value;
		}
	}
	
	/** Setter */
	public function setDescription($value)
	{
		if( $value != null && $value != "" )
		{
			$this->description = $value;
		}
	}
	
	/** Default toString */
	public function toString() { echo 'Filter Object: ipTo: ' . $this->ipTo . ' ipFrom: ' . $this->ipFrom . ' description: ' . $this->description . '<br />'; }
}

?>