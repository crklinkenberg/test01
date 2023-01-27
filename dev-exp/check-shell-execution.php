<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	$resultData = array();
	$status = '';
	$message = '';

	try {
		$output = shell_exec('ps -C php -f');
		if (strpos($output, "/usr/bin/php create-dynamic-comparison-table.php")===false)
			$resultData['script_status'] = "Complete";
		else
			$resultData['script_status'] = "Running";
		$status = 'success';
	} catch (Exception $e) {
	    $status = 'error';
	    $message = 'Exception error';
	}


	echo json_encode( array( 'status' => $status, 'result_data' => $resultData, 'message' => $message) ); 
	exit;
?>