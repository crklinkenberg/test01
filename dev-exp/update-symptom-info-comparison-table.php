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
	$confirmation = 0;
	try {
		if(isset($_POST['update_filed']) AND $_POST['update_filed'] == "Kommentar"){
			$symptomId = $_POST['symptom_id'];
			$Kommentar = (isset($_POST['Kommentar']) AND $_POST['Kommentar'] != "") ? mysqli_real_escape_string($db, trim($_POST['Kommentar'])) : null;

			//new added 14th December
			$comparisonTableName = (isset($_POST['comparison_table_name']) AND $_POST['comparison_table_name'] != "") ? trim($_POST['comparison_table_name']) : "";
			if($symptomId != ""){
				$symptomUpdateQuery="UPDATE quelle_import_test SET Kommentar = NULLIF('".$Kommentar."', '') WHERE id = '".$symptomId."'";
				$db->query($symptomUpdateQuery);

				$symptomUpdateQuery2="UPDATE $comparisonTableName SET Kommentar = NULLIF('".$Kommentar."', '') WHERE symptom_id = '".$symptomId."'";
				$db->query($symptomUpdateQuery2);

				// $update_backup_sets_swapped_symptoms_info ="UPDATE backup_sets_swapped_symptoms SET Kommentar = NULLIF('".$Kommentar."', '') WHERE original_symptom_id = '".$symptomId."'";
				// $db->query($update_backup_sets_swapped_symptoms_info );
				
				$status = "success";
				$message = "success";
			}	
		}

		if(isset($_POST['update_filed']) AND $_POST['update_filed'] == "Nsc_note"){
			$symptomId = $_POST['symptom_id'];
			$initialId = $_POST['initial_id'];
			$ns_value = $_POST['ns_value'];
			$ns_confirm = $_POST['ns_confirm'];
			$ns_new = $_POST['ns_new'];
			$Nsc_note = (isset($_POST['Nsc_note']) AND $_POST['Nsc_note'] != "") ? mysqli_real_escape_string($db, trim($_POST['Nsc_note'])) : null;
			$ns_connect = '1';

			$comparisonTableName = (isset($_POST['comparison_table_name']) AND $_POST['comparison_table_name'] != "") ? trim($_POST['comparison_table_name']) : "";

			$connectionTableName = $comparisonTableName."_connections";
			if($symptomId != ""){
				//confirmation radio
				if($ns_confirm == 1){
		            $Nsc_note = "";
		            $ns_connect = '0';
		            $symptomUpdateQuery="UPDATE $connectionTableName SET ns_connect_comment = NULLIF('".$Nsc_note."', ''), ns_connect = NULLIF('".$ns_connect."', '') WHERE comparing_symptom_id = '".$symptomId."' AND initial_symptom_id = '".$initialId."'";
					$db->query($symptomUpdateQuery);
		            
		            $checkInitialIfExist = checkInitialInConnectionForConnectConfirm($db, $comparisonTableName, $initialId);
		            if($checkInitialIfExist == 0){
		            	$ns_value="0";
		            	$symptomUpdateQueryInitial="UPDATE $comparisonTableName SET non_secure_connect = NULLIF('".$ns_value."', '') WHERE symptom_id = '".$initialId."' AND `is_initial_symptom`= '1'";
						$db->query($symptomUpdateQueryInitial);
		            }
					$confirmation = 1;
				}

				//normal operation
				if($ns_new == 0 && $ns_confirm== 0){
					$symptomUpdateQuery="UPDATE $connectionTableName SET ns_connect_comment = NULLIF('".$Nsc_note."', ''), ns_connect = NULLIF('".$ns_connect."', '') WHERE comparing_symptom_id = '".$symptomId."' AND initial_symptom_id = '".$initialId."'";
					$db->query($symptomUpdateQuery);

					//$checkInitialIfExist = checkInitialInConnectionForConnect($db, $comparisonTableName, $initialId);
		            //if($checkInitialIfExist == 0){
		            	$symptomUpdateQueryInitial="UPDATE $comparisonTableName SET non_secure_connect = NULLIF('".$ns_value."', '') WHERE symptom_id = '".$initialId."' AND `is_initial_symptom`= '1'";	
						$db->query($symptomUpdateQueryInitial);
		            //}
				}
				
				$status = "success";
				$message = "success";
			}	
		}

		if(isset($_POST['update_filed']) AND $_POST['update_filed'] == "Nsc_note_paste"){
			$symptomId = $_POST['symptom_id'];
			$initialId = $_POST['initial_id'];
			$ns_value = $_POST['ns_value'];
			$ns_confirm = $_POST['ns_confirm'];
			$ns_new = $_POST['ns_new'];
			$Nsc_note_paste = (isset($_POST['Nsc_note_paste']) AND $_POST['Nsc_note_paste'] != "") ? mysqli_real_escape_string($db, trim($_POST['Nsc_note_paste'])) : null;
			$ns_paste = '1';
			$comparisonTableName = (isset($_POST['comparison_table_name']) AND $_POST['comparison_table_name'] != "") ? trim($_POST['comparison_table_name']) : "";

			$connectionTableName = $comparisonTableName."_connections";
			if($symptomId != ""){
				//confirmation radio
				if($ns_confirm == 1){
		            $Nsc_note_paste = "";
		            $ns_paste = '0';
		            $symptomUpdateQuery="UPDATE $connectionTableName SET ns_paste_comment = NULLIF('".$Nsc_note_paste."', ''), ns_paste = NULLIF('".$ns_paste."', '') WHERE comparing_symptom_id = '".$symptomId."' AND initial_symptom_id = '".$initialId."'";
					$db->query($symptomUpdateQuery);

	             	$checkInitialIfExist = checkInitialInConnectionForPasteConfirm($db, $comparisonTableName, $initialId);
		            if($checkInitialIfExist == 0){
		            	$ns_value="0";
		            	$symptomUpdateQueryInitial="UPDATE $comparisonTableName SET non_secure_paste = NULLIF('".$ns_value."', '') WHERE symptom_id = '".$initialId."' AND `is_initial_symptom`= '1'";
						$db->query($symptomUpdateQueryInitial);
		            }
					$confirmation = 1;
				}

				//normal operation
				if($ns_new == 0 && $ns_confirm== 0){
					$symptomUpdateQuery="UPDATE $connectionTableName SET ns_paste_comment = NULLIF('".$Nsc_note_paste."', ''), ns_paste = NULLIF('".$ns_paste."', '') WHERE comparing_symptom_id = '".$symptomId."' AND initial_symptom_id = '".$initialId."'";
					$db->query($symptomUpdateQuery);

					$symptomUpdateQueryInitial="UPDATE $comparisonTableName SET non_secure_paste = NULLIF('".$ns_value."', '') WHERE symptom_id = '".$initialId."' AND `is_initial_symptom`= '1'";	
					$db->query($symptomUpdateQueryInitial);
				}

				$status = "success";
				$message = "success";
			}	
		}

		if(isset($_POST['update_filed']) AND $_POST['update_filed'] == "Gen_nsc_note"){
			$initialId = $_POST['initial_id'];
			$gen_ns = $_POST['gen_ns_value'];
			$ns_confirm = $_POST['ns_confirm'];
			$ns_new = $_POST['ns_new'];
			$gen_ns_mark = '0';
			$Nsc_note = (isset($_POST['Nsc_note']) AND $_POST['Nsc_note'] != "") ? mysqli_real_escape_string($db, trim($_POST['Nsc_note'])) : null;

			$comparisonTableName = (isset($_POST['comparison_table_name']) AND $_POST['comparison_table_name'] != "") ? trim($_POST['comparison_table_name']) : "";
			if($gen_ns == '1'){
				$gen_ns_mark = '1';
			}
			if($ns_confirm == 1){
				$gen_ns = '0';
				$gen_ns_mark = '1';
			}
			if($gen_ns == '0'){
				$Nsc_note = "";
			}
			if($initialId != ""){
				$symptomUpdateQuery="UPDATE $comparisonTableName SET gen_ns_comment = NULLIF('".$Nsc_note."', ''), gen_ns = NULLIF('".$gen_ns."', ''), marked = NULLIF('".$gen_ns_mark."', '') WHERE symptom_id = '".$initialId."' AND `is_initial_symptom`= '1'";
				$db->query($symptomUpdateQuery);
				
				$status = "success";
				$message = "success";
			}	
		}

		if(isset($_POST['update_filed']) AND $_POST['update_filed'] == "Fussnote"){
			$symptomId = trim($_POST['symptom_id']);
			$Fussnote = (isset($_POST['Fussnote']) AND $_POST['Fussnote'] != "") ? mysqli_real_escape_string($db, trim($_POST['Fussnote'])) : null;
			$comparisonTableName = (isset($_POST['comparison_table_name']) AND $_POST['comparison_table_name'] != "") ? trim($_POST['comparison_table_name']) : "";

			if($symptomId != ""){
				$symptomUpdateQuery="UPDATE quelle_import_test SET Fussnote = NULLIF('".$Fussnote."', '') WHERE id = '".$symptomId."'";
				$db->query($symptomUpdateQuery);

				$symptomUpdateQuery2="UPDATE $comparisonTableName SET Fussnote = NULLIF('".$Fussnote."', '') WHERE symptom_id = '".$symptomId."'";
				$db->query($symptomUpdateQuery2);

				// $update_backup_sets_swapped_symptoms_info="UPDATE backup_sets_swapped_symptoms SET Fussnote = NULLIF('".$Fussnote."', '') WHERE original_symptom_id = '".$symptomId."'";
				// $db->query($update_backup_sets_swapped_symptoms_info);
				
				$status = "success";
				$message = "success";
			}	
		}

		if(isset($_POST['update_filed']) AND $_POST['update_filed'] == "Disconnect_earlier_connection"){
			//$isInitialSymptom = 0;
			$symptomId = trim($_POST['symptom_id']);
			$initialId = trim($_POST['initial_id']);
			$comparisonTableName = (isset($_POST['comparison_table_name']) AND $_POST['comparison_table_name'] != "") ? trim($_POST['comparison_table_name']) : "";
			$connectionTypeForDisconnection = (isset($_POST['connectionTypeForDisconnection']) AND $_POST['connectionTypeForDisconnection'] != "") ? trim($_POST['connectionTypeForDisconnection']) : "";
			$connectionTableName = $comparisonTableName."_connections";
			// $checkingIfSwapedQuery = "SELECT id FROM $comparison_table_name WHERE symptom_id = '".$initialId."' AND is_initial_symptom='1'";
			// $checkingIfSwaped = mysqli_query($db,$checkingIfSwapedQuery);
			// if(mysqli_num_rows($checkingIfSwaped) > 0){
			// 	$isInitialSymptom = 1;
			// }

			if($connectionTypeForDisconnection == 'CE'){
				$symptomUpdateQueryInitial="UPDATE $comparisonTableName SET final_version_de = NULL, final_version_en = NULL, is_final_version_available = 0, swap_ce = 0, swap_value_ce_de=NULL, swap_value_ce_en=NULL,swap=0, swap_value_de = NULL, swap_value_en = NULL WHERE symptom_id = $initialId";
				$db->query($symptomUpdateQueryInitial);
			}
			$connectionFound = 0;
			$connectionFound = checkComparativeConnection($db, $comparisonTableName, $symptomId);
			if($connectionFound == 1){
				$symptomUpdateQuery="UPDATE $comparisonTableName SET connection = '0' WHERE symptom_id = $symptomId";
				$db->query($symptomUpdateQuery);
			}
				
			$status = "success";
			$message = "success";
			
		}
	} catch (Exception $e) {
	    $status = 'error';
	    $message = 'Exception error';
	}

	echo json_encode( array( 'status' => $status, 'result_data' => $resultData, 'confirmation' => $confirmation) ); 
	exit;
?>
<?php
	include 'includes/php-foot-includes.php';
?>