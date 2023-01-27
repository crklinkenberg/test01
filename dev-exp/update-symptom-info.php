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
	try {
		if(isset($_POST['update_filed']) AND $_POST['update_filed'] == "Kommentar"){
			$symptomId = trim($_POST['symptom_id']);
			$Kommentar = (isset($_POST['Kommentar']) AND $_POST['Kommentar'] != "") ? mysqli_real_escape_string($db, trim($_POST['Kommentar'])) : null;

			if($symptomId != ""){
				$symptomUpdateQuery="UPDATE quelle_import_test SET Kommentar = NULLIF('".$Kommentar."', '') WHERE id = '".$symptomId."'";
				$db->query($symptomUpdateQuery);

				$update_backup_sets_swapped_symptoms_info ="UPDATE backup_sets_swapped_symptoms SET Kommentar = NULLIF('".$Kommentar."', '') WHERE original_symptom_id = '".$symptomId."'";
				$db->query($update_backup_sets_swapped_symptoms_info );
				
				$status = "success";
				$message = "success";
			}	
		}

		if(isset($_POST['update_filed']) AND $_POST['update_filed'] == "Fussnote"){
			$symptomId = trim($_POST['symptom_id']);
			$Fussnote = (isset($_POST['Fussnote']) AND $_POST['Fussnote'] != "") ? mysqli_real_escape_string($db, trim($_POST['Fussnote'])) : null;

			if($symptomId != ""){
				$symptomUpdateQuery="UPDATE quelle_import_test SET Fussnote = NULLIF('".$Fussnote."', '') WHERE id = '".$symptomId."'";
				$db->query($symptomUpdateQuery);

				$update_backup_sets_swapped_symptoms_info="UPDATE backup_sets_swapped_symptoms SET Fussnote = NULLIF('".$Fussnote."', '') WHERE original_symptom_id = '".$symptomId."'";
				$db->query($update_backup_sets_swapped_symptoms_info);
				
				$status = "success";
				$message = "success";
			}	
		}
	} catch (Exception $e) {
	    $status = 'error';
	    $message = 'Exception error';
	}

	echo json_encode( array( 'status' => $status, 'result_data' => $resultData, 'message' => $message) ); 
	exit;
?>
<?php
	include 'includes/php-foot-includes.php';
?>