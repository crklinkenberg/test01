<?php
	include '../config/route.php';
	include 'sub-section-config.php';

	$resultData = array();
	$test= array();
	$status = 'error';
	$message = 'Could not perform the action.';
	try {
		$checkInitialIfExistConnect = 0;
		$checkInitialIfExistPaste = 0;
		$type = (isset($_POST['connect_type']) AND $_POST['connect_type'] != "") ? $_POST['connect_type'] : "";
		$comparing_symptom_id = (isset($_POST['comparing_symptom_id']) AND $_POST['comparing_symptom_id'] != "") ? $_POST['comparing_symptom_id'] : "";
		$initial_symptom_id = (isset($_POST['initial_symptom_id']) AND $_POST['initial_symptom_id'] != "") ? $_POST['initial_symptom_id'] : "";
		$comparison_language = (isset($_POST['comparison_language']) AND $_POST['comparison_language'] != "") ? $_POST['comparison_language'] : "";
		$matched_percentage = (isset($_POST['matched_percentage']) AND $_POST['matched_percentage'] != "") ? $_POST['matched_percentage'] : "";
		$comparing_quelle_id = (isset($_POST['comparing_quelle_id']) AND $_POST['comparing_quelle_id'] != "") ? $_POST['comparing_quelle_id'] : "";
		$initial_quelle_id = (isset($_POST['initial_quelle_id']) AND $_POST['initial_quelle_id'] != "") ? $_POST['initial_quelle_id'] : "";
		$comparative_symptom_text = (isset($_POST['comparative_symptom_text']) AND $_POST['comparative_symptom_text'] != "") ? $_POST['comparative_symptom_text'] : "";
		$initial_symptom_text = (isset($_POST['initial_symptom_text']) AND $_POST['initial_symptom_text'] != "") ? $_POST['initial_symptom_text'] : "";
		$comparing_quelle_code = (isset($_POST['comparing_quelle_code']) AND $_POST['comparing_quelle_code'] != "") ? $_POST['comparing_quelle_code'] : "";
		$initial_quelle_code = (isset($_POST['initial_quelle_code']) AND $_POST['initial_quelle_code'] != "") ? $_POST['initial_quelle_code'] : "";
		$comparing_year = (isset($_POST['comparing_year']) AND $_POST['comparing_year'] != "") ? $_POST['comparing_year'] : "";
		$comparing_symptom_de = (isset($_POST['comparing_symptom_de']) AND $_POST['comparing_symptom_de'] != "") ? $_POST['comparing_symptom_de'] : "";
		$comparing_symptom_en = (isset($_POST['comparing_symptom_en']) AND $_POST['comparing_symptom_en'] != "") ? $_POST['comparing_symptom_en'] : "";
		$comparing_quelle_original_language = (isset($_POST['comparing_quelle_original_language']) AND $_POST['comparing_quelle_original_language'] != "") ? $_POST['comparing_quelle_original_language'] : "";
		$initial_quelle_original_language = (isset($_POST['initial_quelle_original_language']) AND $_POST['initial_quelle_original_language'] != "") ? $_POST['initial_quelle_original_language'] : "";
		$comparing_quelle_id = (isset($_POST['comparing_quelle_id']) AND $_POST['comparing_quelle_id'] != "") ? $_POST['comparing_quelle_id'] : "";
		$initial_year = (isset($_POST['initial_year']) AND $_POST['initial_year'] != "") ? $_POST['initial_year'] : "";
		$initial_symptom_de = (isset($_POST['initial_symptom_de']) AND $_POST['initial_symptom_de'] != "") ? $_POST['initial_symptom_de'] : "";
		$initial_symptom_en = (isset($_POST['initial_symptom_en']) AND $_POST['initial_symptom_en'] != "") ? $_POST['initial_symptom_en'] : "";
		$initial_quelle_id = (isset($_POST['initial_quelle_id']) AND $_POST['initial_quelle_id'] != "") ? $_POST['initial_quelle_id'] : "";

		$symptom = (isset($_POST['symptom']) AND $_POST['symptom'] != "") ? mysqli_real_escape_string($db, $_POST['symptom']) : "";
		$initialHtml = "";
		if(isset($_POST['initialHtml']) AND $_POST['initialHtml'] != ""){
			// Removing space between all html tags
			$initialHtml = preg_replace('/\>\s+\</m', '><', $_POST['initialHtml']);
			$initialHtml = mysqli_real_escape_string($db, $initialHtml);
		}

		$comparativeHtml = "";
		if(isset($_POST['comparativeHtml']) AND $_POST['comparativeHtml'] != ""){
			// Removing space between all html tags
			$comparativeHtml = preg_replace('/\>\s+\</m', '><', $_POST['comparativeHtml']);
			$comparativeHtml = mysqli_real_escape_string($db, $comparativeHtml);
		}
		
		$percentage = (isset($_POST['percentage']) AND $_POST['percentage'] != "") ? mysqli_real_escape_string($db, $_POST['percentage']) : "";
		$selectedPercentage = (isset($_POST['selectedPercentage']) AND $_POST['selectedPercentage'] != "") ? mysqli_real_escape_string($db, $_POST['selectedPercentage']) : "";
		
		// Getting main comparison data array from session
		$comparisonTableDataArr = (isset($_SESSION['comparison_table_data']) AND !empty($_SESSION['comparison_table_data'])) ? $_SESSION['comparison_table_data'] : array(); 
		// Comparison table don't exist in DB then the session data and other required data empty. 
		$comparisonTable = (isset($comparisonTableDataArr['comparison_table']) AND $comparisonTableDataArr['comparison_table'] != "") ? $comparisonTableDataArr['comparison_table'] : ""; 
		
		$comparisonSavedDataTable = $comparisonTable."_connections";
		//Symptom text according to language	
		if($comparison_language == "de"){
			$symptomTextDe = mysqli_real_escape_string($db, $comparative_symptom_text);
			$connectedSymptomTextDe = mysqli_real_escape_string($db, $initial_symptom_text);
			$symptomTextEn = "";
			$connectedSymptomTextEn = "";
		}
		else{
			$symptomTextDe = "";
			$connectedSymptomTextDe = "";
			$symptomTextEn = mysqli_real_escape_string($db, $comparative_symptom_text);
			$connectedSymptomTextEn = mysqli_real_escape_string($db, $initial_symptom_text);
		}

		//Decoding strings before inserting to the table
		$comparing_symptom_de = base64_decode($comparing_symptom_de);
		$comparing_symptom_en = base64_decode($comparing_symptom_en);
		$initial_symptom_de = base64_decode($initial_symptom_de);
		$initial_symptom_en = base64_decode($initial_symptom_en);

		$comparing_symptom_de = mysqli_real_escape_string($db, $comparing_symptom_de);
		$comparing_symptom_en = mysqli_real_escape_string($db, $comparing_symptom_en);
		$initial_symptom_de = mysqli_real_escape_string($db, $initial_symptom_de);
		$initial_symptom_en = mysqli_real_escape_string($db, $initial_symptom_en);

		$data = array(
				'comparing_symptom_id'=>$comparing_symptom_id,
				'initial_symptom_id'=> $initial_symptom_id,
				'comparison_language'=> $comparison_language,
				'matched_percentage' =>$matched_percentage,
				'comparing_quelle_id'=>$comparing_quelle_id,
				'initial_quelle_id'=>$initial_quelle_id,
				'comparative_symptom_text'=>$comparative_symptom_text,
				'initial_symptom_text'=>$initial_symptom_text,
				'comparing_quelle_code'=>$comparing_quelle_code,
				'initial_quelle_code'=>$initial_quelle_code,
				'comparing_year'=>$comparing_year,
				'comparing_symptom_de'=>$comparing_symptom_de,
				'comparing_symptom_en'=>$comparing_symptom_en,
				'comparing_quelle_original_language'=>$comparing_quelle_original_language,
				'initial_quelle_original_language'=>$initial_quelle_original_language,
				'initial_year'=>$initial_year,
				'initial_symptom_de'=>$initial_symptom_de,
				'initial_symptom_en'=>$initial_symptom_en,
				'initial_quelle_id'=>$initial_quelle_id,
				'symptomTextDe'=>$symptomTextDe,
				'symptomTextEn'=>$symptomTextEn,
				'connectedSymptomTextDe'=>$connectedSymptomTextDe,
				'connectedSymptomTextEn'=>$connectedSymptomTextEn,
				'saved_table_name'=>$comparisonSavedDataTable
			);
		switch($type){
		  	case "connect":
		  		{
		  			//non secure connect checking
		  			$checkInitialIfExistConnect = checkInitialInConnectionForConnect($db, $comparisonTable, $initial_symptom_id);
		            if($checkInitialIfExistConnect == 0){
		            	$ns_value = '0';
						$symptomUpdateQueryInitialConnect="UPDATE $comparisonTable SET non_secure_connect = NULLIF('".$ns_value."', '') WHERE symptom_id = '".$initial_symptom_id."' AND `is_initial_symptom`= '1'";
						$db->query($symptomUpdateQueryInitialConnect);
		            }
		            //non secure paste checking
			    	$checkInitialIfExistPaste = checkInitialInConnectionForPaste($db, $comparisonTable, $initial_symptom_id);
		            if($checkInitialIfExistPaste == 0){
		            	$ns_value = '0';
						$symptomUpdateQueryInitialPaste="UPDATE $comparisonTable SET non_secure_paste = NULLIF('".$ns_value."', '') WHERE symptom_id = '".$initial_symptom_id."' AND `is_initial_symptom`= '1'";
						$db->query($symptomUpdateQueryInitialPaste);
		            }
		  			$deleteExistingQuery="DELETE FROM $comparisonSavedDataTable WHERE comparing_symptom_id = '".$comparing_symptom_id."' AND initial_symptom_id = '".$initial_symptom_id."'";
		            $db->query($deleteExistingQuery);
		  			$insertData="INSERT INTO $comparisonSavedDataTable (comparing_symptom_id, initial_symptom_id, connection_type, matched_percentage, highlighted_comparing_symptom_en, highlighted_comparing_symptom_de, highlighted_initial_symptom_en, highlighted_initial_symptom_de, comparison_language, comparing_quelle_code, initial_quelle_code, comparing_year, comparing_symptom_de, comparing_symptom_en, comparing_quelle_original_language, initial_quelle_original_language, comparing_quelle_id, initial_year, initial_symptom_de, initial_symptom_en, initial_quelle_id) VALUES ($comparing_symptom_id, $initial_symptom_id, NULLIF('".$type."', ''), $matched_percentage, NULLIF('".$symptomTextEn."', ''), NULLIF('".$symptomTextDe."', ''), NULLIF('".$connectedSymptomTextEn."', ''), NULLIF('".$connectedSymptomTextDe."', ''), '".$comparison_language."', '".$comparing_quelle_code."', '".$initial_quelle_code."',NULLIF('".$comparing_year."', ''),NULLIF('".$comparing_symptom_de."', ''),NULLIF('".$comparing_symptom_en."', ''),NULLIF('".$comparing_quelle_original_language."', ''),NULLIF('".$initial_quelle_original_language."', ''),NULLIF('".$comparing_quelle_id."', ''),NULLIF('".$initial_year."', ''),NULLIF('".$initial_symptom_de."', ''),NULLIF('".$initial_symptom_en."', ''),NULLIF('".$initial_quelle_id."', ''))";
					$db->query($insertData);

					//updating marking in the comparison table
					markingUpdation($db,$comparisonTable,"1",$initial_symptom_id);
					$status = 'success';
					$message = 'Success';
			    	break;
		  		}
			case "paste":
			    {
			    	//non secure paste checking
			    	$checkInitialIfExistPaste = checkInitialInConnectionForPaste($db, $comparisonTable, $initial_symptom_id);
		            if($checkInitialIfExistPaste == 0){
		            	$ns_value = '0';
						$symptomUpdateQueryInitialPaste="UPDATE $comparisonTable SET non_secure_paste = NULLIF('".$ns_value."', '') WHERE symptom_id = '".$initial_symptom_id."' AND `is_initial_symptom`= '1'";
						$db->query($symptomUpdateQueryInitialPaste);
		            }
		            //non secure connect checking
		            $checkInitialIfExistConnect = checkInitialInConnectionForConnect($db, $comparisonTable, $initial_symptom_id);
		            if($checkInitialIfExistConnect == 0){
		            	$ns_value = '0';
						$symptomUpdateQueryInitialConnect="UPDATE $comparisonTable SET non_secure_connect = NULLIF('".$ns_value."', '') WHERE symptom_id = '".$initial_symptom_id."' AND `is_initial_symptom`= '1'";
						$db->query($symptomUpdateQueryInitialConnect);
		            }
			    	$deleteExistingQuery="DELETE FROM $comparisonSavedDataTable WHERE comparing_symptom_id = '".$comparing_symptom_id."' AND initial_symptom_id = '".$initial_symptom_id."'";
		            $db->query($deleteExistingQuery);
		  			$insertData="INSERT INTO $comparisonSavedDataTable (comparing_symptom_id, initial_symptom_id, connection_type, matched_percentage, highlighted_comparing_symptom_en, highlighted_comparing_symptom_de, highlighted_initial_symptom_en, highlighted_initial_symptom_de, comparison_language, comparing_quelle_code, initial_quelle_code, comparing_year, comparing_symptom_de, comparing_symptom_en, comparing_quelle_original_language, initial_quelle_original_language, comparing_quelle_id, initial_year, initial_symptom_de, initial_symptom_en, initial_quelle_id) VALUES ($comparing_symptom_id, $initial_symptom_id, NULLIF('".$type."', ''), $matched_percentage, NULLIF('".$symptomTextEn."', ''), NULLIF('".$symptomTextDe."', ''), NULLIF('".$connectedSymptomTextEn."', ''), NULLIF('".$connectedSymptomTextDe."', ''), '".$comparison_language."', '".$comparing_quelle_code."', '".$initial_quelle_code."',NULLIF('".$comparing_year."', ''),NULLIF('".$comparing_symptom_de."', ''),NULLIF('".$comparing_symptom_en."', ''),NULLIF('".$comparing_quelle_original_language."', ''),NULLIF('".$initial_quelle_original_language."', ''),NULLIF('".$comparing_quelle_id."', ''),NULLIF('".$initial_year."', ''),NULLIF('".$initial_symptom_de."', ''),NULLIF('".$initial_symptom_en."', ''),NULLIF('".$initial_quelle_id."', ''))";
					$db->query($insertData);

					//updating marking in the comparison table
					markingUpdation($db,$comparisonTable,"1",$initial_symptom_id);
					$status = 'success';
					$message = 'Success';
			    	break;
		  		}
			case "swap":
			    {
			    	$deleteExistingQuery="DELETE FROM $comparisonSavedDataTable WHERE swap = '".$symptom."'";
		            $db->query($deleteExistingQuery);
		  			$insertData="INSERT INTO $comparisonSavedDataTable (swap) VALUES (NULLIF('".$symptom."', ''))";
					$db->query($insertData);
					$status = 'success';
					$message = 'Success';
			    	break;
		  		}
			default:
			    break;
		}
	} catch (Exception $e) {
	    $status = 'error';
	    $message = 'Something went wrong';
	}
	echo json_encode( array( 'status' => $status, 'result_data' => $data, 'message' => $insertData) ); 
	exit;
?>