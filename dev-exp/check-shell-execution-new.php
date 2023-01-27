<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	$resultData = array();
	$status = '';
	$message = '';

	try {
		$dynamicTableName = (isset($_POST['dynamic_table_name']) AND $_POST['dynamic_table_name'] != "") ? $_POST['dynamic_table_name'] : "";
		$resultData['script_status'] = "Complete";
		if($dynamicTableName != ""){
			$checkTableStatus = mysqli_query($db,"SELECT id, table_name, status FROM pre_comparison_master_data  WHERE table_name = '".$dynamicTableName."'");
			if(mysqli_num_rows($checkTableStatus) > 0){
				$comparisonStatusRow = mysqli_fetch_assoc($checkTableStatus);
				if($comparisonStatusRow['status'] != "done")
					$resultData['script_status'] = "Running";
			}
		}
		$status = 'success';
		$message = 'Success';
		// $output = shell_exec('ps -C php -f');
		// if (strpos($output, "/usr/bin/php create-dynamic-comparison-table-new.php")===false)
		// 	$resultData['script_status'] = "Complete";
		// else
		// 	$resultData['script_status'] = "Running";
		// $status = 'success';
	} catch (Exception $e) {
	    $status = 'error';
	    $message = 'Exception error';
	}


	echo json_encode( array( 'status' => $status, 'result_data' => $resultData, 'message' => $message) ); 
	exit;
?>