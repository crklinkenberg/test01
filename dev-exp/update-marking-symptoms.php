<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Updating the comment or Foot note of a particular symptom 
	*/
?>
<?php  
	$resultData = array();
	$status = '';
	$message = '';
	$initialSymptom = (isset($_POST['initialSymptom']) AND $_POST['initialSymptom'] != "") ? $_POST['initialSymptom'] : null;
	$markedValue = (isset($_POST['markedValue']) AND $_POST['markedValue'] != "") ? $_POST['markedValue'] : null;
	$comparisonTableName = (isset($_POST['comparisonTableName']) AND $_POST['comparisonTableName'] != "") ? $_POST['comparisonTableName'] : null;

	try {
		if($comparisonTableName != "" AND $initialSymptom != ""){
			$symptomUpdateQuery="UPDATE $comparisonTableName SET marked = NULLIF('".$markedValue."', '') WHERE symptom_id = '".$initialSymptom."' AND is_initial_symptom='1'";
			$db->query($symptomUpdateQuery);
			$status = "update success";
			$message = "success";
		}
	} catch (Exception $e) {
	    $status = 'error';
	    $message = 'Exception error';
	}

	echo json_encode( array( 'status' => $status, 'result_data' => $resultData, 'message' => $symptomUpdateQuery) ); 
	exit;
?>
<?php
	include 'includes/php-foot-includes.php';
?>