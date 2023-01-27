<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Updating the comment or Foot note of a particular symptom 
	*/
?>
<?php  
	$resultData = array();
	$unmarkedSymptomsArray = array();
	$unmarkedSymptomsArrayFinal = array();
	$status = '';
	$message = '';
	$unmarkedInitialsCheck = 0;
	$nonSecureCheck = 0;
	$comparisonTableName = (isset($_POST['comparisonTableName']) AND $_POST['comparisonTableName'] != "") ? $_POST['comparisonTableName'] : null;
	$similarityRate= (isset($_POST['similarityRate']) AND $_POST['similarityRate'] != "") ? $_POST['similarityRate'] : "";
	$role= (isset($_POST['role']) AND $_POST['role'] != "") ? $_POST['role'] : "";

	$query="";
	$returnValue = 0;
	$returnType = 'default';
	if($role == 1){
		$returnType = 'unmarked';
	}
	try {
		if($comparisonTableName != ""){
			$totalUnmarkedNoComparatives = unmarkedSymptoms($db, $comparisonTableName, $similarityRate,0);
			$unmarkedInitialsCheckResult = mysqli_query($db, "SELECT id FROM $comparisonTableName  WHERE `is_initial_symptom`='1' AND `marked`='0' AND connection = '0'");
			if(mysqli_num_rows($unmarkedInitialsCheckResult) > 0){
				$unmarkedInitialsCheck = 1;
			}

			//$returnedRows = 10;
			$rowsInitial = mysqli_num_rows($unmarkedInitialsCheckResult);
			$returnedRows = mysqli_num_rows($unmarkedInitialsCheckResult) - $totalUnmarkedNoComparatives;
			if($returnedRows > 0){
				$returnValue = $returnedRows;
				$returnType = 'unmarked';
			}else{
				if($role == 2){
					$connectionsTable = $comparisonTableName."_connections";
					//non secure connect check 
					$nonSecureConnectCheckResult = mysqli_query($db, "SELECT id FROM $connectionsTable  WHERE `ns_connect`='1'");
					//non secure paste check
					$nonSecurePasteCheckResult = mysqli_query($db, "SELECT id FROM $connectionsTable  WHERE `ns_paste`='1'");
					//general non secure check 
					$genNonSecureCheckResult = mysqli_query($db, "SELECT id FROM $comparisonTableName  WHERE `gen_ns`='1'");
					if(mysqli_num_rows($nonSecureConnectCheckResult) > 0){
						$nonSecureCheck = 1;
						$returnValue = mysqli_num_rows($nonSecureConnectCheckResult);
						$returnType = 'ns_connect';
					}else if(mysqli_num_rows($nonSecurePasteCheckResult) > 0){
						$nonSecureCheck = 2;
						$returnValue = mysqli_num_rows($nonSecurePasteCheckResult);
						$returnType = 'ns_paste';
					}else if(mysqli_num_rows($genNonSecureCheckResult) > 0){
						$nonSecureCheck = 3;
						$returnValue = mysqli_num_rows($genNonSecureCheckResult);
						$returnType = 'ns_general';
					}else{
						$returnValue = 0;
						$returnType = 'default';
					}
				}
			}
			$status = "success";
			$message = "success";
		}
	} catch (Exception $e) {
	    $status = 'error';
	    $message = 'Exception error';
	}

	echo json_encode( array( 'status' => $status, 'result_data' => $returnValue, 'message' => $message, 'returnType' => $returnType, 'totalUnmarkedNoComparatives' => $totalUnmarkedNoComparatives, 'rowsInitial' => $rowsInitial) ); 
	exit;
?>
<?php
	include 'includes/php-foot-includes.php';
?>