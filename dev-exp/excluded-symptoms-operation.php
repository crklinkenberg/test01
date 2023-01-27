<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Fetching basic informations of a particular symptom
	*/
?>
<?php
	$stopWords = array();
	$stopWords = getStopWords();
	
	$resultData = array();
	$status = '';
	$message = '';
	try {
		if(isset($_POST['form']) AND !empty($_POST['form'])) {	
			parse_str( $_POST['form'], $formData );
			
			$exluded_symptoms = (isset($formData['exluded_symptoms']) AND $formData['exluded_symptoms'] != "") ? $formData['exluded_symptoms'] : array();
			$db_table_name = (isset($formData['db_table_name']) AND $formData['db_table_name'] != "") ? $formData['db_table_name'] : "";
			
			if(!empty($exluded_symptoms) OR $db_table_name != "") {
				$data = array();
				$tableCheck = mysqli_query($db,"SHOW TABLES LIKE '".$db_table_name."'");
				if(mysqli_num_rows($tableCheck) > 0){
					foreach($exluded_symptoms as $exKey => $exVal){
						$symptomUpdateQuery="UPDATE ".$db_table_name." SET is_excluded_in_comparison = 0 WHERE id = ".$exVal;
						$db->query($symptomUpdateQuery);
					}
				}
				$resultData = $data;
				$status = "success";
				$message = "success";
			} else {
				$status = 'error';
	    		$message = 'Operation failed, required data not found.';
			}
		}
	} catch (Exception $e) {
	    $status = 'error';
	    $message = 'Exception error';
	}


	echo json_encode( array( 'status' => $status, 'result_data' => $data, 'message' => $message) ); 
	exit;
?>
<?php
	include 'includes/php-foot-includes.php';
?>