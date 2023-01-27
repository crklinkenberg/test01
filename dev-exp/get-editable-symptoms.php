<?php
	include '../config/route.php';
	include 'sub-section-config.php';

$symptom_id = (isset($_POST['symptom_id']) AND $_POST['symptom_id'] != "") ? $_POST['symptom_id'] : "";
$returnData = array();
$symptomResult = mysqli_query($db, "SELECT * FROM quelle_import_test WHERE id = ".$symptom_id);
if(mysqli_num_rows($symptomResult) > 0){
	$row = mysqli_fetch_assoc($symptomResult);

	$originalSymptom_de = ($row['BeschreibungOriginal_de'] != "") ? convertSymptomToOriginal($row['BeschreibungOriginal_de'], $row['original_quelle_id'], $row['arznei_id']) : ""; 
	$originalSymptom_en = ($row['BeschreibungOriginal_en'] != "") ? convertSymptomToOriginal($row['BeschreibungOriginal_en'], $row['original_quelle_id'], $row['arznei_id']) : ""; 
	$convertedSymptomFull_de = ($row['BeschreibungFull_de'] != "") ? convertTheSymptom($row['BeschreibungFull_de'], $row['original_quelle_id'], $row['arznei_id'], 0, 0, $row['id'], $row['original_symptom_id']) : ""; 
	$convertedSymptomFull_en = ($row['BeschreibungFull_en'] != "") ? convertTheSymptom($row['BeschreibungFull_en'], $row['original_quelle_id'], $row['arznei_id'], 0, 0, $row['id'], $row['original_symptom_id']) : ""; 

	$symptomType = "";
	$querySympTypeInfo = mysqli_query($db,"SELECT symptom_type_for_whole FROM quelle_symptom_settings WHERE quelle_id =".$row['original_quelle_id']);
	if(mysqli_num_rows($querySympTypeInfo) > 0){
		$rowSympTypeInfo = mysqli_fetch_assoc($querySympTypeInfo);
		$symptomType = (isset($rowSympTypeInfo['symptom_type_for_whole']) AND $rowSympTypeInfo['symptom_type_for_whole'] != "") ? $rowSympTypeInfo['symptom_type_for_whole'] : "";
	}

	$symptomTypeResult = mysqli_query($db, "SELECT * FROM symptom_type_setting WHERE symptom_id = '".$symptom_id."'");
	if(mysqli_num_rows($symptomTypeResult) > 0){
		$symptomTypeRow = mysqli_fetch_assoc($symptomTypeResult);
	}
	$symptom_type= (isset($symptomTypeRow['symptom_type']) and $symptomTypeRow['symptom_type'] != "")? $symptomTypeRow['symptom_type'] : $symptomType;

	$returnData = array(
			'original_symptom_de'=> $originalSymptom_de,
			'original_symptom_en'=> $originalSymptom_en,
			'converted_symptom_full_de'=> $convertedSymptomFull_de,
			'converted_symptom_full_en'=> $convertedSymptomFull_en,
			'symptom_type'=> $symptom_type
		);
}

echo json_encode($returnData); 
?>