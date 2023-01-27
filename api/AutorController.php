<?php
include 'mainCall.php';
/**
 * Autor Controller
 */
class AutorController
{
	public $quellen;
	function __construct($baseApiURL)
	{
		$get_data = callAPI('GET', $baseApiURL.'quelle/all?is_paginate=0', false);
		$response = json_decode($get_data, true);
		$status = $response['status'];
		$quellen = $response['content']['data'];
		$this->quellen = $quellen;
	}
}