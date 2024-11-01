<?php

/**
	Interface for the VSF Simple Stats Import / Export classes.
	Contains just constants.
*/
interface IVSFSimpleStatsPort
{
	const DEBUG = true;
	
	const XML_HEADING = '<?xml version="1.0" encoding="ISO-8859-1" ?>';
	
	const ROOT_ELEMENT = 'vsfSimpleStats';
	const ELEMENT_QUANTITY = 'quantity';
	const ELEMENT_FILTERS = 'filters';
	const ELEMENT_FILTER = 'filter';
	const ELEMENT_IP_TO = 'ipTo';
	const ELEMENT_IP_FROM = 'ipFrom';
	const ELEMENT_DESCRIPTION = 'description';
	
	const START_ELEMENT = '#@SO#@';
	const END_ELEMENT = '#@EO#@';
}

?>