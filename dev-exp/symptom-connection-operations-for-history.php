<?php
include '../config/route.php';
include 'sub-section-config.php';

$singleConnectionInitialCheckArray = (isset($_POST['singleConnectionInitialCheck']) AND $_POST['singleConnectionInitialCheck'] != "") ? $_POST['singleConnectionInitialCheck'] : "";
$singleConnectionComparativeCheck = (isset($_POST['singleConnectionComparativeCheck']) AND $_POST['singleConnectionComparativeCheck'] != "") ? $_POST['singleConnectionComparativeCheck'] : "";
$combinedConnectionIntialsCheckArray = (isset($_POST['combinedConnectionIntialsCheck']) AND $_POST['combinedConnectionIntialsCheck'] != "") ? $_POST['combinedConnectionIntialsCheck'] : "";
$combinedConnectionComparativeCheckArray = (isset($_POST['combinedConnectionComparativeCheck']) AND $_POST['combinedConnectionComparativeCheck'] != "") ? $_POST['combinedConnectionComparativeCheck'] : "";
$symptomType = (isset($_POST['symptomType']) AND $_POST['symptomType'] != "") ? $_POST['symptomType'] : "";
// Getting main comparison data array from session
$comparisonTableDataArr = (isset($_SESSION['comparison_table_data_for_history']) AND !empty($_SESSION['comparison_table_data_for_history'])) ? $_SESSION['comparison_table_data_for_history'] : array(); 
// Comparison table don't exist in DB then the session data and other required data empty. 
$comparisonTable = (isset($comparisonTableDataArr['comparison_table']) AND $comparisonTableDataArr['comparison_table'] != "") ? $comparisonTableDataArr['comparison_table'] : ""; 
$comparisonSavedDataTable = $comparisonTable."_connections";

$arrayToSend = array();
$connectionSortingArrayCE = array();
$connectionSortingArrayConnect= array();
$connectionSortingArrayPE = array();
$connectionSortingArrayPaste = array();
$latestIdResult = array();

//latest ID fetch
$latestIdResult = latestId($comparisonSavedDataTable,$db);
switch($symptomType){
	case "initial":{
		if($singleConnectionInitialCheckArray != ""){
			foreach ($singleConnectionInitialCheckArray as $key => $value) {
				$symptomConnectionCollectionQuery = mysqli_query($db, "SELECT * FROM $comparisonSavedDataTable WHERE `initial_symptom_id` = $value");
				if(mysqli_num_rows($symptomConnectionCollectionQuery) > 0){
					while($row = mysqli_fetch_array($symptomConnectionCollectionQuery)){
						
						$comparing_symptom_id = $row['comparing_symptom_id'];
						$initial_symptom_id = $row['initial_symptom_id'];
						$matched_percentage = $row['matched_percentage'];
						$comparing_quelle_id = $row['comparing_quelle_id'];
						$initial_quelle_id = $row['initial_quelle_id'];
						$highlighted_comparing_symptom_en = $row['highlighted_comparing_symptom_en'];
						$highlighted_comparing_symptom_de = $row['highlighted_comparing_symptom_de'];
						$highlighted_initial_symptom_en = $row['highlighted_initial_symptom_en'];
						$highlighted_initial_symptom_de = $row['highlighted_initial_symptom_de'];
						$comparison_language = $row['comparison_language'];
						$comparing_quelle_code = $row['comparing_quelle_code'];
						$initial_quelle_code = $row['initial_quelle_code'];
						$comparing_year = $row['comparing_year'];
						$comparing_symptom_de = $row['comparing_symptom_de'];
						$comparing_symptom_en = $row['comparing_symptom_en'];
						$comparing_quelle_original_language = $row['comparing_quelle_original_language'];
						$initial_quelle_original_language = $row['initial_quelle_original_language'];
						$initial_year = $row['initial_year'];
						$initial_symptom_de = $row['initial_symptom_de'];
						$initial_symptom_en = $row['initial_symptom_en'];
						$non_encoded_comparing_symptom_de = ($row['comparing_symptom_de'] != "") ? $row['comparing_symptom_de'] : "Translation is not available";
						$non_encoded_comparing_symptom_en = ($row['comparing_symptom_en'] != "") ? $row['comparing_symptom_en'] : "Translation is not available";
						$non_encoded_initial_symptom_de = ($row['initial_symptom_de'] != "") ? $row['initial_symptom_de'] : "Translation is not available";
						$non_encoded_initial_symptom_en = ($row['initial_symptom_en'] != "") ? $row['initial_symptom_en'] : "Translation is not available";
						$connection_type = $row['connection_type'];
						$free_flag = $row['free_flag'];

						//encoding to preserve integrity
						$comparing_symptom_de = base64_encode($comparing_symptom_de);
						$comparing_symptom_en = base64_encode($comparing_symptom_en);
						$initial_symptom_de = base64_encode($initial_symptom_de);
						$initial_symptom_en = base64_encode($initial_symptom_en);
						$is_earlier_connection = $row['is_earlier_connection'];

						$data = array(
							'comparing_symptom_id'=> $comparing_symptom_id,
							'initial_symptom_id'=> $initial_symptom_id,
							'matched_percentage'=> $matched_percentage,
							'comparing_quelle_id'=> $comparing_quelle_id,
							'initial_quelle_id'=> $initial_quelle_id,
							'highlighted_comparing_symptom_en'=> $highlighted_comparing_symptom_en,
							'highlighted_comparing_symptom_de'=> $highlighted_comparing_symptom_de,
							'highlighted_initial_symptom_en'=> $highlighted_initial_symptom_en,
							'highlighted_initial_symptom_de'=> $highlighted_initial_symptom_de,
							'comparison_language'=> $comparison_language,
							'comparing_quelle_code'=> $comparing_quelle_code,
							'initial_quelle_code'=> $initial_quelle_code,
							'comparing_year'=> $comparing_year,
							'comparing_symptom_de'=> $comparing_symptom_de,
							'comparing_symptom_en'=> $comparing_symptom_en,
							'comparing_quelle_original_language'=> $comparing_quelle_original_language,
							'initial_quelle_original_language'=> $initial_quelle_original_language,
							'initial_year'=> $initial_year,
							'initial_symptom_de'=> $initial_symptom_de,
							'initial_symptom_en'=> $initial_symptom_en,
							'non_encoded_comparing_symptom_de'=> $non_encoded_comparing_symptom_de,
							'non_encoded_comparing_symptom_en'=> $non_encoded_comparing_symptom_en,
							'non_encoded_initial_symptom_de'=> $non_encoded_initial_symptom_de,
							'non_encoded_initial_symptom_en'=> $non_encoded_initial_symptom_en,
							'connection_type'=> $connection_type,
							'is_earlier_connection'=> $is_earlier_connection,
							'free_flag'=> $free_flag

						);
						//Storing according to connection type
						if($data['connection_type']=='CE'){	
							array_push($connectionSortingArrayCE, $data);
						}else if($data['connection_type']=='connect'){
							array_push($connectionSortingArrayConnect, $data);
						}else if($data['connection_type']=='PE'){
							array_push($connectionSortingArrayPE, $data);
						}else{
							array_push($connectionSortingArrayPaste, $data);
						}

						//array_push($arrayToSend, $data);
					}
					$arrayToSendFinal = array_merge($connectionSortingArrayPaste,$connectionSortingArrayPE, $connectionSortingArrayConnect, $connectionSortingArrayCE);
					$arrayToSend = $arrayToSendFinal;

					//array_push($arrayToSend, $arrayToSendFinal);

					// array_push($arrayToSend, $connectionSortingArrayConnect);
					// array_push($arrayToSend, $connectionSortingArrayPE);
					// array_push($arrayToSend, $connectionSortingArrayPaste);


					//array_push($arrayToSend, $data);

				}
			}

			// if(!empty($arrayToSend)){
			// 	usort($arrayToSend, 'sortByOrder');
			// }
		}
		break;
	}
	case "comparative":{
		if($singleConnectionComparativeCheck != ""){
			foreach ($singleConnectionComparativeCheck as $key => $value) {
				$symptomConnectionCollectionQuery = mysqli_query($db, "SELECT * FROM $comparisonSavedDataTable WHERE `comparing_symptom_id` = $value");
				if(mysqli_num_rows($symptomConnectionCollectionQuery) > 0){
					while($row = mysqli_fetch_array($symptomConnectionCollectionQuery)){
						
						$comparing_symptom_id = $row['comparing_symptom_id'];
						$initial_symptom_id = $row['initial_symptom_id'];
						$matched_percentage = $row['matched_percentage'];
						$comparing_quelle_id = $row['comparing_quelle_id'];
						$initial_quelle_id = $row['initial_quelle_id'];
						$highlighted_comparing_symptom_en = $row['highlighted_comparing_symptom_en'];
						$highlighted_comparing_symptom_de = $row['highlighted_comparing_symptom_de'];
						$highlighted_initial_symptom_en = $row['highlighted_initial_symptom_en'];
						$highlighted_initial_symptom_de = $row['highlighted_initial_symptom_de'];
						$comparison_language = $row['comparison_language'];
						$comparing_quelle_code = $row['comparing_quelle_code'];
						$initial_quelle_code = $row['initial_quelle_code'];
						$comparing_year = $row['comparing_year'];
						$comparing_symptom_de = $row['comparing_symptom_de'];
						$comparing_symptom_en = $row['comparing_symptom_en'];
						$comparing_quelle_original_language = $row['comparing_quelle_original_language'];
						$initial_quelle_original_language = $row['initial_quelle_original_language'];
						$initial_year = $row['initial_year'];
						$initial_symptom_de = $row['initial_symptom_de'];
						$initial_symptom_en = $row['initial_symptom_en'];
						$non_encoded_comparing_symptom_de = ($row['comparing_symptom_de'] != "") ? $row['comparing_symptom_de'] : "Translation is not available";
						$non_encoded_comparing_symptom_en = ($row['comparing_symptom_en'] != "") ? $row['comparing_symptom_en'] : "Translation is not available";
						$non_encoded_initial_symptom_de = ($row['initial_symptom_de'] != "") ? $row['initial_symptom_de'] : "Translation is not available";
						$non_encoded_initial_symptom_en = ($row['initial_symptom_en'] != "") ? $row['initial_symptom_en'] : "Translation is not available";
						$connection_type = $row['connection_type'];
						$free_flag = $row['free_flag'];

						//encoding to preserve integrity
						$comparing_symptom_de = base64_encode($comparing_symptom_de);
						$comparing_symptom_en = base64_encode($comparing_symptom_en);
						$initial_symptom_de = base64_encode($initial_symptom_de);
						$initial_symptom_en = base64_encode($initial_symptom_en);

						$is_earlier_connection = $row['is_earlier_connection'];
						$data = array(
							'comparing_symptom_id'=> $comparing_symptom_id,
							'initial_symptom_id'=> $initial_symptom_id,
							'matched_percentage'=> $matched_percentage,
							'comparing_quelle_id'=> $comparing_quelle_id,
							'initial_quelle_id'=> $initial_quelle_id,
							'highlighted_comparing_symptom_en'=> $highlighted_comparing_symptom_en,
							'highlighted_comparing_symptom_de'=> $highlighted_comparing_symptom_de,
							'highlighted_initial_symptom_en'=> $highlighted_initial_symptom_en,
							'highlighted_initial_symptom_de'=> $highlighted_initial_symptom_de,
							'comparison_language'=> $comparison_language,
							'comparing_quelle_code'=> $comparing_quelle_code,
							'initial_quelle_code'=> $initial_quelle_code,
							'comparing_year'=> $comparing_year,
							'comparing_symptom_de'=> $comparing_symptom_de,
							'comparing_symptom_en'=> $comparing_symptom_en,
							'comparing_quelle_original_language'=> $comparing_quelle_original_language,
							'initial_quelle_original_language'=> $initial_quelle_original_language,
							'initial_year'=> $initial_year,
							'initial_symptom_de'=> $initial_symptom_de,
							'initial_symptom_en'=> $initial_symptom_en,
							'non_encoded_comparing_symptom_de'=> $non_encoded_comparing_symptom_de,
							'non_encoded_comparing_symptom_en'=> $non_encoded_comparing_symptom_en,
							'non_encoded_initial_symptom_de'=> $non_encoded_initial_symptom_de,
							'non_encoded_initial_symptom_en'=> $non_encoded_initial_symptom_en,
							'connection_type'=> $connection_type,
							'is_earlier_connection'=> $is_earlier_connection,
							'free_flag'=> $free_flag


						);

						array_push($arrayToSend, $data);
					}
				}
			}
		}
		break;
	}
	case "combined-initial":{
		if($combinedConnectionIntialsCheckArray != ""){
			foreach ($combinedConnectionIntialsCheckArray as $key => $value) {
				$symptomConnectionCollectionQuery = mysqli_query($db, "SELECT * FROM $comparisonSavedDataTable WHERE `initial_symptom_id` = $value OR `comparing_symptom_id` = $value ");
					if(mysqli_num_rows($symptomConnectionCollectionQuery) > 0){
						while($row = mysqli_fetch_array($symptomConnectionCollectionQuery)){
							
							$comparing_symptom_id = $row['comparing_symptom_id'];
							$initial_symptom_id = $row['initial_symptom_id'];
							$matched_percentage = $row['matched_percentage'];
							$comparing_quelle_id = $row['comparing_quelle_id'];
							$initial_quelle_id = $row['initial_quelle_id'];
							$highlighted_comparing_symptom_en = $row['highlighted_comparing_symptom_en'];
							$highlighted_comparing_symptom_de = $row['highlighted_comparing_symptom_de'];
							$highlighted_initial_symptom_en = $row['highlighted_initial_symptom_en'];
							$highlighted_initial_symptom_de = $row['highlighted_initial_symptom_de'];
							$comparison_language = $row['comparison_language'];
							$comparing_quelle_code = $row['comparing_quelle_code'];
							$initial_quelle_code = $row['initial_quelle_code'];
							$comparing_year = $row['comparing_year'];
							$comparing_symptom_de = $row['comparing_symptom_de'];
							$comparing_symptom_en = $row['comparing_symptom_en'];
							$comparing_quelle_original_language = $row['comparing_quelle_original_language'];
							$initial_quelle_original_language = $row['initial_quelle_original_language'];
							$initial_year = $row['initial_year'];
							$initial_symptom_de = $row['initial_symptom_de'];
							$initial_symptom_en = $row['initial_symptom_en'];
							$non_encoded_comparing_symptom_de = ($row['comparing_symptom_de'] != "") ? $row['comparing_symptom_de'] : "Translation is not available";
							$non_encoded_comparing_symptom_en = ($row['comparing_symptom_en'] != "") ? $row['comparing_symptom_en'] : "Translation is not available";
							$non_encoded_initial_symptom_de = ($row['initial_symptom_de'] != "") ? $row['initial_symptom_de'] : "Translation is not available";
							$non_encoded_initial_symptom_en = ($row['initial_symptom_en'] != "") ? $row['initial_symptom_en'] : "Translation is not available";
							$connection_type = $row['connection_type'];
							$is_earlier_connection = $row['is_earlier_connection'];
							$free_flag = $row['free_flag'];
							//This value is taken for sorting according to year
							$year = $row['comparing_year'];
							if($connection_type == "PE" && $is_earlier_connection == "1"){
								$year = $row['initial_year'];
							}


							//encoding to preserve integrity
							$comparing_symptom_de = base64_encode($comparing_symptom_de);
							$comparing_symptom_en = base64_encode($comparing_symptom_en);
							$initial_symptom_de = base64_encode($initial_symptom_de);
							$initial_symptom_en = base64_encode($initial_symptom_en);

							$data = array(
								'comparing_symptom_id'=> $comparing_symptom_id,
								'initial_symptom_id'=> $initial_symptom_id,
								'matched_percentage'=> $matched_percentage,
								'comparing_quelle_id'=> $comparing_quelle_id,
								'initial_quelle_id'=> $initial_quelle_id,
								'highlighted_comparing_symptom_en'=> $highlighted_comparing_symptom_en,
								'highlighted_comparing_symptom_de'=> $highlighted_comparing_symptom_de,
								'highlighted_initial_symptom_en'=> $highlighted_initial_symptom_en,
								'highlighted_initial_symptom_de'=> $highlighted_initial_symptom_de,
								'comparison_language'=> $comparison_language,
								'comparing_quelle_code'=> $comparing_quelle_code,
								'initial_quelle_code'=> $initial_quelle_code,
								'comparing_year'=> $comparing_year,
								'comparing_symptom_de'=> $comparing_symptom_de,
								'comparing_symptom_en'=> $comparing_symptom_en,
								'comparing_quelle_original_language'=> $comparing_quelle_original_language,
								'initial_quelle_original_language'=> $initial_quelle_original_language,
								'initial_year'=> $initial_year,
								'initial_symptom_de'=> $initial_symptom_de,
								'initial_symptom_en'=> $initial_symptom_en,
								'non_encoded_comparing_symptom_de'=> $non_encoded_comparing_symptom_de,
								'non_encoded_comparing_symptom_en'=> $non_encoded_comparing_symptom_en,
								'non_encoded_initial_symptom_de'=> $non_encoded_initial_symptom_de,
								'non_encoded_initial_symptom_en'=> $non_encoded_initial_symptom_en,
								'connection_type'=> $connection_type,
								'is_earlier_connection'=> $is_earlier_connection,
								'free_flag'=> $free_flag,
								'year'=> $year

							);

							array_push($arrayToSend, $data);
							$arrayToSend= my_array_unique($arrayToSend);
						}
					}
			}

			if(!empty($arrayToSend)){
				usort($arrayToSend, 'sortByOrderModified');
			}
		}
		break;
		
	}
	case "combined-comparative":{
		if($combinedConnectionComparativeCheckArray != ""){
			foreach ($combinedConnectionComparativeCheckArray as $key => $value) {
				$symptomConnectionCollectionQuery = mysqli_query($db, "SELECT * FROM $comparisonSavedDataTable WHERE  `initial_symptom_id`=$value OR `comparing_symptom_id` = $value ");
				if(mysqli_num_rows($symptomConnectionCollectionQuery) > 0){
					while($row = mysqli_fetch_array($symptomConnectionCollectionQuery)){
						
						$comparing_symptom_id = $row['comparing_symptom_id'];
						$initial_symptom_id = $row['initial_symptom_id'];
						$matched_percentage = $row['matched_percentage'];
						$comparing_quelle_id = $row['comparing_quelle_id'];
						$initial_quelle_id = $row['initial_quelle_id'];
						$highlighted_comparing_symptom_en = $row['highlighted_comparing_symptom_en'];
						$highlighted_comparing_symptom_de = $row['highlighted_comparing_symptom_de'];
						$highlighted_initial_symptom_en = $row['highlighted_initial_symptom_en'];
						$highlighted_initial_symptom_de = $row['highlighted_initial_symptom_de'];
						$comparison_language = $row['comparison_language'];
						$comparing_quelle_code = $row['comparing_quelle_code'];
						$initial_quelle_code = $row['initial_quelle_code'];
						$comparing_year = $row['comparing_year'];
						$comparing_symptom_de = $row['comparing_symptom_de'];
						$comparing_symptom_en = $row['comparing_symptom_en'];
						$comparing_quelle_original_language = $row['comparing_quelle_original_language'];
						$initial_quelle_original_language = $row['initial_quelle_original_language'];
						$initial_year = $row['initial_year'];
						$initial_symptom_de = $row['initial_symptom_de'];
						$initial_symptom_en = $row['initial_symptom_en'];
						$non_encoded_comparing_symptom_de = ($row['comparing_symptom_de'] != "") ? $row['comparing_symptom_de'] : "Translation is not available";
						$non_encoded_comparing_symptom_en = ($row['comparing_symptom_en'] != "") ? $row['comparing_symptom_en'] : "Translation is not available";
						$non_encoded_initial_symptom_de = ($row['initial_symptom_de'] != "") ? $row['initial_symptom_de'] : "Translation is not available";
						$non_encoded_initial_symptom_en = ($row['initial_symptom_en'] != "") ? $row['initial_symptom_en'] : "Translation is not available";
						$connection_type = $row['connection_type'];
						$is_earlier_connection = $row['is_earlier_connection'];
						$free_flag = $row['free_flag'];

						//encoding to preserve integrity
						$comparing_symptom_de = base64_encode($comparing_symptom_de);
						$comparing_symptom_en = base64_encode($comparing_symptom_en);
						$initial_symptom_de = base64_encode($initial_symptom_de);
						$initial_symptom_en = base64_encode($initial_symptom_en);

						$data = array(
							'comparing_symptom_id'=> $comparing_symptom_id,
							'initial_symptom_id'=> $initial_symptom_id,
							'matched_percentage'=> $matched_percentage,
							'comparing_quelle_id'=> $comparing_quelle_id,
							'initial_quelle_id'=> $initial_quelle_id,
							'highlighted_comparing_symptom_en'=> $highlighted_comparing_symptom_en,
							'highlighted_comparing_symptom_de'=> $highlighted_comparing_symptom_de,
							'highlighted_initial_symptom_en'=> $highlighted_initial_symptom_en,
							'highlighted_initial_symptom_de'=> $highlighted_initial_symptom_de,
							'comparison_language'=> $comparison_language,
							'comparing_quelle_code'=> $comparing_quelle_code,
							'initial_quelle_code'=> $initial_quelle_code,
							'comparing_year'=> $comparing_year,
							'comparing_symptom_de'=> $comparing_symptom_de,
							'comparing_symptom_en'=> $comparing_symptom_en,
							'comparing_quelle_original_language'=> $comparing_quelle_original_language,
							'initial_quelle_original_language'=> $initial_quelle_original_language,
							'initial_year'=> $initial_year,
							'initial_symptom_de'=> $initial_symptom_de,
							'initial_symptom_en'=> $initial_symptom_en,
							'non_encoded_comparing_symptom_de'=> $non_encoded_comparing_symptom_de,
							'non_encoded_comparing_symptom_en'=> $non_encoded_comparing_symptom_en,
							'non_encoded_initial_symptom_de'=> $non_encoded_initial_symptom_de,
							'non_encoded_initial_symptom_en'=> $non_encoded_initial_symptom_en,
							'connection_type'=> $connection_type,
							'is_earlier_connection'=> $is_earlier_connection,
							'free_flag'=> $free_flag


						);

						array_push($arrayToSend, $data);
					}
				}
			}
		}
		break;
	}
	default:
	   break;
}

function sortByOrder($a, $b) {
   return  $b['comparing_year'] - $a['comparing_year'];
}

function sortByOrderModified($a, $b) {
   return  $b['year'] - $a['year'];
}

function latestId($tableName,$db){
	$latestInitialId="";
	$latestComparingId="";
	$sendingArray = array();
	$latestIdQuery = mysqli_query($db, "SELECT `initial_symptom_id`,`comparing_symptom_id`,`is_earlier_connection` FROM $tableName ORDER BY ID DESC LIMIT 1");
	if(mysqli_num_rows($latestIdQuery) > 0){
		while($latestIdRow = mysqli_fetch_array($latestIdQuery)){
			$latestInitialId = $latestIdRow['initial_symptom_id'];
			$latestComparingId = $latestIdRow['comparing_symptom_id'];
			$latestConnection = $latestIdRow['is_earlier_connection'];
		}
		if($latestConnection == '0'){
			$sendingArray['initial_symptom_id'] =$latestInitialId;
			$sendingArray['comparing_symptom_id'] =$latestComparingId;
		}	
	}
	return $sendingArray;
}

function my_array_unique($array, $keep_key_assoc = false){
    $duplicate_keys = array();
    $tmp = array();       

    foreach ($array as $key => $val){
        // convert objects to arrays, in_array() does not support objects
        if (is_object($val))
            $val = (array)$val;

        if (!in_array($val, $tmp))
            $tmp[] = $val;
        else
            $duplicate_keys[] = $key;
    }

    foreach ($duplicate_keys as $key)
        unset($array[$key]);

    return $keep_key_assoc ? $array : array_values($array);
}

$returnData = array();
echo json_encode( array('result_data' => $arrayToSend,'arrayQueryFeb'=>$combinedConnectionIntialsCheckArray,'latestIdResult'=>$latestIdResult)); 
?>