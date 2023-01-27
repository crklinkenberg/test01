<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Performs a particular action from below options depending on the user's input on the question form of the quelle import process 
	*/
?>
<?php
if(isset($_POST['no']) AND $_POST['no'] == "No"){
	try {
	    $db->begin_transaction();

	    $parameterString = "";
		$symptomUpdateQuery="UPDATE temp_quelle_import_test SET reference_priority = 0, reference_with_no_author_priority = 0 WHERE id = '".$_POST['symptom_id']."'";
		$db->query($symptomUpdateQuery);

		$deleteTempReferenceQuery = "DELETE FROM temp_reference WHERE symptom_id = '".$_POST['symptom_id']."'";
		$db->query($deleteTempReferenceQuery);

		$deleteTempSymptomReferenceQuery = "DELETE FROM temp_symptom_reference WHERE symptom_id = '".$_POST['symptom_id']."'";
		$db->query($deleteTempSymptomReferenceQuery);

	    $db->commit();

	    if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
			$parameterString = "?master=".$_POST['master_id'];

		header('Location: '.$baseUrl.$parameterString);
		exit();

	} catch (Exception $e) {
	    $db->rollback();
	    header('Location: '.$baseUrl.'?error=1');
		exit();
	}
}
else if(isset($_POST['yes']) AND $_POST['yes'] == "Yes"){
	try {
	    $db->begin_transaction();

	    $parameterString = "";

	    if(isset($_POST['suggested_reference']) AND is_array($_POST['suggested_reference']) AND !empty($_POST['suggested_reference'])){

	    	$EntnommenAus = "";
	    	$tempEntnommenAusArray = array();
	    	$deleteTempReferenceQuery = "DELETE FROM temp_reference WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempReferenceQuery);

			$deleteTempSymptomReferenceQuery = "DELETE FROM temp_symptom_reference WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempSymptomReferenceQuery);

	    	foreach ($_POST['suggested_reference'] as $refKey => $refVal) {
	    		$referenceQuery = "INSERT INTO temp_symptom_reference (symptom_id, reference_id) VALUES ('".$_POST['symptom_id']."', '".$refVal."')";
	    		$db->query($referenceQuery);

	    		// Storing in temp_approved_reference for searching in case of a. a. O.
	    		$approval_string = base64_decode($_POST['approval_string']);
	    		$tempApprovedReferenceQuery = "INSERT INTO temp_approved_reference (master_id, symptom_id, reference_id, approval_string) VALUES ('".$_POST['master_id']."', '".$_POST['symptom_id']."', '".$refVal."', NULLIF('".$approval_string."', ''))";
	    		$db->query($tempApprovedReferenceQuery);

	    		$refResult = mysqli_query($db, "SELECT full_reference FROM reference WHERE reference_id = '".$refVal."'");
				$refRowCount = mysqli_num_rows($refResult);
				if( $refRowCount > 0){
					$refRow = mysqli_fetch_assoc($refResult);
					$tempEntnommenAusArray [] = trim($refRow['full_reference']);
				}
	    	}

	    	if(!empty($tempEntnommenAusArray))
	    		$EntnommenAus = join ( "\n", $tempEntnommenAusArray );

	    	$EntnommenAus = mysqli_real_escape_string($db, $EntnommenAus);
	    	$symptomUpdateQuery="UPDATE temp_quelle_import_test SET part_of_symptom_priority = 0, remedy_priority = 0, pruefer_priority = 0, reference_with_no_author_priority = 0, remedy_with_symptom_priority = 0, more_than_one_tag_string_priority = 0, aao_hyphen_priority = 0, hyphen_pruefer_priority = 0, hyphen_reference_priority = 0, reference_priority = 0, direct_order_priority = 0, need_approval = 0, EntnommenAus = '".$EntnommenAus."' WHERE id = '".$_POST['symptom_id']."'";
			$db->query($symptomUpdateQuery);
	    	
	    	// Deleteing the temp Remedys because they are no longer need if it is approved as Part Of Symptom.
			$deleteTempRemedyQuery="DELETE FROM temp_remedy WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempRemedyQuery);

			// Deleteing the temp Pruefers because they are no longer need if it is approved as Part Of Symptom.
			$deleteTempPrueferQuery = "DELETE FROM temp_pruefer WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempPrueferQuery);

			$deleteTempSymptomPrueferQuery = "DELETE FROM temp_symptom_pruefer WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempSymptomPrueferQuery);

	    }
	    else
	    {
	    	$deleteTempReferenceQuery = "DELETE FROM temp_reference WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempReferenceQuery);

			$deleteTempSymptomReferenceQuery = "DELETE FROM temp_symptom_reference WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempSymptomReferenceQuery);

			$referenceApprovalString = trim(base64_decode($_POST['approval_string']));
			$fullReferenceInArray = explode(",", $referenceApprovalString);
			if(count($fullReferenceInArray) >= 2){
				$referenceAutor = trim($fullReferenceInArray[0]);
	    		array_shift($fullReferenceInArray);
	    		$referenceTxt = rtrim(implode(",", $fullReferenceInArray), ",");
			}else{
				$referenceAutor = "No Author";
				$referenceTxt = $referenceApprovalString;
			}

			$referenceApprovalString = mysqli_real_escape_string($db, $referenceApprovalString);
			$referenceAutor = mysqli_real_escape_string($db, $referenceAutor);
			$referenceTxt = mysqli_real_escape_string($db, $referenceTxt);
		    $referenceInsertQuery = "INSERT INTO reference (full_reference, autor, reference, ersteller_datum) VALUES ('".$referenceApprovalString."', '".$referenceAutor."', '".$referenceTxt."', '".$date."')";
			$db->query($referenceInsertQuery);
			$newReferenceId = mysqli_insert_id($db);
			
			$referenceQuery = "INSERT INTO temp_symptom_reference (symptom_id, reference_id) VALUES ('".$_POST['symptom_id']."', '".$newReferenceId."')";
	    	$db->query($referenceQuery);

	    	// Storing in temp_approved_reference for searching in case of a. a. O.
	    	$approval_string = base64_decode($_POST['approval_string']);
    		$tempApprovedReferenceQuery = "INSERT INTO temp_approved_reference (master_id, symptom_id, reference_id, approval_string) VALUES ('".$_POST['master_id']."', '".$_POST['symptom_id']."', '".$newReferenceId."', NULLIF('".$approval_string."', ''))";
    		$db->query($tempApprovedReferenceQuery);

		    
			$symptomUpdateQuery="UPDATE temp_quelle_import_test SET part_of_symptom_priority = 0, remedy_priority = 0, pruefer_priority = 0, reference_with_no_author_priority = 0, remedy_with_symptom_priority = 0, more_than_one_tag_string_priority = 0, aao_hyphen_priority = 0, hyphen_pruefer_priority = 0, hyphen_reference_priority = 0, reference_priority = 0, direct_order_priority = 0, need_approval = 0, EntnommenAus = '".$referenceApprovalString."' WHERE id = '".$_POST['symptom_id']."'";
			$db->query($symptomUpdateQuery);

			// Deleteing the temp Remedys because they are no longer need if it is approved as Part Of Symptom.
			$deleteTempRemedyQuery="DELETE FROM temp_remedy WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempRemedyQuery);

			// Deleteing the temp Pruefers because they are no longer need if it is approved as Part Of Symptom.
			$deleteTempPrueferQuery = "DELETE FROM temp_pruefer WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempPrueferQuery);

			$deleteTempSymptomPrueferQuery = "DELETE FROM temp_symptom_pruefer WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempSymptomPrueferQuery);
	    }


		// If there is no more unskipped and unapproved data left than we will make the skipped data as unskipped.
		$unSkippedResult = mysqli_query($db, "SELECT * FROM temp_quelle_import_test Where need_approval = 1 AND is_skipped = 0 AND master_id = '".$_POST['master_id']."'");
		$unSkippedRowCount = mysqli_num_rows($unSkippedResult);
		if( $unSkippedRowCount > 0){
			if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
				$parameterString = "?master=".$_POST['master_id'];
		}else{
			// In this case the rediraction URL will be - Location: $baseUrl 
			$makeUnskippedQuery="UPDATE temp_quelle_import_test SET is_skipped = 0 WHERE master_id = '".$_POST['master_id']."'";
			$db->query($makeUnskippedQuery);

			$leftToApproveResult = mysqli_query($db, "SELECT * FROM temp_quelle_import_test Where need_approval = 1 AND master_id = '".$_POST['master_id']."'");
			$leftToApproveRowCount = mysqli_num_rows($leftToApproveResult);
			if( $leftToApproveRowCount == 0){
				if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
					$parameterString = "?master=".$_POST['master_id'];
			}
		}

	    $db->commit();

		header('Location: '.$baseUrl.$parameterString);
		exit();
	} catch (Exception $e) {
	    $db->rollback();
	    header('Location: '.$baseUrl.'?error=1');
		exit();
	}
	
}else if(isset($_POST['do']) AND $_POST['do'] == "DO"){
	try {
	    $db->begin_transaction();

	    $parameterString = "";
		$symptomUpdateQuery="UPDATE temp_quelle_import_test SET direct_order_priority = 11 WHERE id = '".$_POST['symptom_id']."'";
		$db->query($symptomUpdateQuery);

		// Deleteing the temp Remedys because they are no longer need if it is taking Direct Order.
		$deleteTempRemedyQuery="DELETE FROM temp_remedy WHERE symptom_id = '".$_POST['symptom_id']."'";
		$db->query($deleteTempRemedyQuery);

		// Deleteing the temp Pruefers because they are no longer need if it is taking Direct Order.
		$deleteTempPrueferQuery = "DELETE FROM temp_pruefer WHERE symptom_id = '".$_POST['symptom_id']."'";
		$db->query($deleteTempPrueferQuery);

		$deleteTempSymptomPrueferQuery = "DELETE FROM temp_symptom_pruefer WHERE symptom_id = '".$_POST['symptom_id']."'";
		$db->query($deleteTempSymptomPrueferQuery);

		$deleteTempReferenceQuery = "DELETE FROM temp_reference WHERE symptom_id = '".$_POST['symptom_id']."'";
		$db->query($deleteTempReferenceQuery);

		$deleteTempSymptomReferenceQuery = "DELETE FROM temp_symptom_reference WHERE symptom_id = '".$_POST['symptom_id']."'";
		$db->query($deleteTempSymptomReferenceQuery);

	    $db->commit();

	    if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
			$parameterString = "?master=".$_POST['master_id'];

		header('Location: '.$baseUrl.$parameterString);
		exit();
	} catch (Exception $e) {
	    $db->rollback();
	    header('Location: '.$baseUrl.'?error=1');
		exit();
	}
	
}else if(isset($_POST['later']) AND $_POST['later'] == "Later"){
	try {
	    $db->begin_transaction();

	    $parameterString = "";
		$symptomUpdateQuery="UPDATE temp_quelle_import_test SET is_skipped = 1, is_rechecked = 0 WHERE id = '".$_POST['symptom_id']."'";
		$db->query($symptomUpdateQuery);

		// If there is no more unskipped and unapproved data left than we will make the skipped data as unskipped.
		$unSkippedResult = mysqli_query($db, "SELECT * FROM temp_quelle_import_test Where need_approval = 1 AND is_skipped = 0 AND master_id = '".$_POST['master_id']."'");
		$unSkippedRowCount = mysqli_num_rows($unSkippedResult);
		if( $unSkippedRowCount > 0){
			if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
				$parameterString = "?master=".$_POST['master_id'];
		}else{
			// In this case the rediraction URL will be - Location: $baseUrl 
			$makeUnskippedQuery="UPDATE temp_quelle_import_test SET is_skipped = 0 WHERE master_id = '".$_POST['master_id']."'";
			$db->query($makeUnskippedQuery);

			$leftToApproveResult = mysqli_query($db, "SELECT * FROM temp_quelle_import_test Where need_approval = 1 AND master_id = '".$_POST['master_id']."'");
			$leftToApproveRowCount = mysqli_num_rows($leftToApproveResult);
			if( $leftToApproveRowCount == 0){
				if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
					$parameterString = "?master=".$_POST['master_id'];
			}
		}

	    $db->commit();

		header('Location: '.$baseUrl.$parameterString);
		exit();
	} catch (Exception $e) {
	    $db->rollback();
	    header('Location: '.$baseUrl.'?error=1');
		exit();
	}
}else if(isset($_POST['reset_current']) AND $_POST['reset_current'] == "Reset"){
	try {
	    $db->begin_transaction();

	    $parameterString = "";
	    if($_POST['is_pre_defined_tags_approval'] == 1){
	    	$unApprovedResult = mysqli_query($db, "SELECT * FROM temp_quelle_import_test WHERE id = '".$_POST['symptom_id']."'");
			if( mysqli_num_rows($unApprovedResult) > 0){
				$unApprovedRow = mysqli_fetch_assoc($unApprovedResult);

				$tagParameter = "";
				if($unApprovedRow['pruefer_priority'] != 0)
					$tagParameter = "pruefer";
				else if($unApprovedRow['reference_priority'] != 0)
					$tagParameter = "reference";
				else if($unApprovedRow['more_than_one_tag_string_priority'] != 0)
					$tagParameter = "multitag";

				ruleReimplementation($_POST['symptom_id'], $_POST['approval_string'], $_POST['master_id'], $_POST['approval_for'], $_POST['is_pre_defined_tags_approval'], $tagParameter);
			}
	    }
	    else{
	    	repopulateDataOnSymptomEditOrReset($_POST['symptom_id'], $_POST['master_id'], $_POST['full_symptom_string'], NULL);
	    }
	    	

		// If there is no more unskipped and unapproved data left than we will make the skipped data as unskipped.
		$unSkippedResult = mysqli_query($db, "SELECT id FROM temp_quelle_import_test Where need_approval = 1 AND is_skipped = 0 AND master_id = '".$_POST['master_id']."'");
		$unSkippedRowCount = mysqli_num_rows($unSkippedResult);
		if( $unSkippedRowCount > 0){
			if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
				$parameterString = "?master=".$_POST['master_id'];
		}else{
			// In this case the rediraction URL will be - Location: $baseUrl 
			$makeUnskippedQuery="UPDATE temp_quelle_import_test SET is_skipped = 0 WHERE master_id = '".$_POST['master_id']."'";
			$db->query($makeUnskippedQuery);

			$leftToApproveResult = mysqli_query($db, "SELECT id FROM temp_quelle_import_test Where need_approval = 1 AND master_id = '".$_POST['master_id']."'");
			$leftToApproveRowCount = mysqli_num_rows($leftToApproveResult);
			if( $leftToApproveRowCount == 0){
				if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
					$parameterString = "?master=".$_POST['master_id'];
			}
		}

	    $db->commit();

		header('Location: '.$baseUrl.$parameterString);
		exit();
	} catch (Exception $e) {
	    $db->rollback();
	    header('Location: '.$baseUrl.'?error=1');
		exit();
	}
}else if(isset($_POST['edit_symptom']) AND $_POST['edit_symptom'] == "Edit"){
	try {
	    $db->begin_transaction();

	    $parameterString = "";
		$symptomUpdateQuery="UPDATE temp_quelle_import_test SET symptom_edit_priority = 12 WHERE id = '".$_POST['symptom_id']."'";
		$db->query($symptomUpdateQuery);

	    $db->commit();

	    if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
			$parameterString = "?master=".$_POST['master_id'];

		header('Location: '.$baseUrl.$parameterString);
		exit();
	} catch (Exception $e) {
	    $db->rollback();
	    header('Location: '.$baseUrl.'?error=1');
		exit();
	}
}
?>