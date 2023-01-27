<?php
	include '../config/route.php';
	include 'sub-section-config.php';

	$resultData = array();
	$status = 'error';
	$message = 'Could not perform the action.';
	try {
		$type = (isset($_POST['type']) AND $_POST['type'] != "") ? $_POST['type'] : "";
		$symptom = (isset($_POST['symptom']) AND $_POST['symptom'] != "") ? mysqli_real_escape_string($db, $_POST['symptom']) : "";
		$operation = (isset($_POST['operation']) AND $_POST['operation'] != "") ? mysqli_real_escape_string($db, $_POST['operation']) : "";
		$initialSymptom = (isset($_POST['initialSymptom']) AND $_POST['initialSymptom'] != "") ? mysqli_real_escape_string($db, $_POST['initialSymptom']) : "";
		// Getting main comparison data array from session
		$comparisonTableDataArr = (isset($_SESSION['comparison_table_data']) AND !empty($_SESSION['comparison_table_data'])) ? $_SESSION['comparison_table_data'] : array(); 
		// Comparison table don't exist in DB then the session data and other required data empty. 
		$comparisonTable = (isset($comparisonTableDataArr['comparison_table']) AND $comparisonTableDataArr['comparison_table'] != "") ? $comparisonTableDataArr['comparison_table'] : ""; 
		$fetchedRow = 0;
		$checkInitialIfExist = 0;
		if($comparisonTable != "" AND $type != "" AND $initialSymptom != ""){
			$comparisonSavedDataTable = $comparisonTable."_connections";
			if($operation == "connect" || $operation == "CE"){
				//updatng value if non secure exist start
				$checkInitialIfExist = checkInitialInConnectionForConnect($db, $comparisonTable, $initialSymptom);
	            if($checkInitialIfExist == 0){
	            	$ns_value = '0';
					$symptomUpdateQueryInitial="UPDATE $comparisonTable SET non_secure_connect = NULLIF('".$ns_value."', '') WHERE symptom_id = '".$initialSymptom."' AND `is_initial_symptom`= '1'";
					$db->query($symptomUpdateQueryInitial);
	            }
			    //updatng value if non secure exist ends
			}

			if($operation == "PE" || $operation == "paste"){
				//updatng value if non secure exist start
	            $checkInitialIfExist = checkInitialInConnectionForPaste($db, $comparisonTable, $initialSymptom);
	            if($checkInitialIfExist == 0){
	            	$ns_value = '0';
					$symptomUpdateQueryInitial="UPDATE $comparisonTable SET non_secure_paste = NULLIF('".$ns_value."', '') WHERE symptom_id = '".$initialSymptom."' AND `is_initial_symptom`= '1'";
					$db->query($symptomUpdateQueryInitial);
	            }
			    //updatng value if non secure exist ends
			}
				
			//deleting from the connectiions table
			// if($operation == "connect" || $operation == "CE"){
			// 	$deleteExistingQuery="DELETE FROM $comparisonSavedDataTable WHERE `comparing_symptom_id` = $symptom";
			// }else{
				$deleteExistingQuery="DELETE FROM $comparisonSavedDataTable WHERE `comparing_symptom_id` = $symptom AND `initial_symptom_id` = $initialSymptom";
			// }
            $db->query($deleteExistingQuery);

            //updating marking in the comparison table
			markingUpdation($db,$comparisonTable,"0",$initialSymptom);
			genNsUpdation($db,$comparisonTable,"0",$initialSymptom);
            //selecting the data for revertig back
            $fetchIdResult = mysqli_query($db,"SELECT id,connection_type FROM $comparisonSavedDataTable WHERE comparing_symptom_id = '".$initialSymptom."'");
            if(mysqli_num_rows($fetchIdResult) > 0){
				$fetchedRow = 1;
			}
			if($fetchedRow != 1){
				if($operation == "PE" || $operation == "paste"){
					//Resetting the value of connection to 0 to eliminate any connect/ CE connections
					$updateConnection = "UPDATE $comparisonTable SET connection = '0' WHERE symptom_id = '".$initialSymptom."'";
					$updateResConnection = $db->query($updateConnection);

					$updateConnectionForPeSymptom = "UPDATE $comparisonTable SET final_version_de=NULL,final_version_en=NULL,is_final_version_available=0 WHERE symptom_id = '".$symptom."'";
					$updateResConnectionPe = $db->query($updateConnectionForPeSymptom);
				}
			}
			$status = 'success';
			$message = 'Success';
		}else{
			$status = 'error';
	    	$message = 'Required data not found';
		}
	} catch (Exception $e) {
	    $status = 'error';
	    $message = 'Something went wrong';
	}

	echo json_encode( array( 'status' => $status, 'result_data' => $resultData, 'checkInitialIfExist' => $checkInitialIfExist) ); 
	exit;
?>