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
		$symptomUpdateQuery="UPDATE temp_quelle_import_test SET hyphen_reference_priority = 0 WHERE id = '".$_POST['symptom_id']."'";
		$db->query($symptomUpdateQuery);

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

			$checkedReferenceString = "";
			$fullApprovalStringWhenHyphen = trim(base64_decode($_POST['full_approval_string_when_hyphen']));
	    	foreach ($_POST['suggested_reference'] as $refKey => $refVal) {
				$getReferenceNameResult = mysqli_query($db, "SELECT full_reference FROM reference WHERE reference_id = '".$refVal."'");
				if( mysqli_num_rows($getReferenceNameResult) > 0){
					$referenceData = mysqli_fetch_assoc($getReferenceNameResult);
					$checkedReferenceString = trim($referenceData['full_reference'])." - ";
				}
	    	}
	    	if($checkedReferenceString != ""){
	    		$checkedReferenceString = rtrim($checkedReferenceString, " - ");
		    	$approvalString = trim(base64_decode($_POST['approval_string']));
				$fullApprovalStringWhenHyphen = str_replace($approvalString, $checkedReferenceString, $fullApprovalStringWhenHyphen);
	    	}

	    	$fullApprovalStringWhenHyphenQuery = mysqli_real_escape_string($db, $fullApprovalStringWhenHyphen);
	    	$symptomUpdateQuery="UPDATE temp_quelle_import_test SET full_approval_string_when_hyphen = '".$fullApprovalStringWhenHyphenQuery."', hyphen_pruefer_priority = 0, hyphen_reference_priority = 0 WHERE id = '".$_POST['symptom_id']."'";
			$db->query($symptomUpdateQuery);

			$fullApprovalStringWhenHyphen = base64_encode($fullApprovalStringWhenHyphen);
	    	$reImplementRule = ruleReimplementation($_POST['symptom_id'], $fullApprovalStringWhenHyphen, $_POST['master_id'], $_POST['approval_for'], $_POST['is_pre_defined_tags_approval'], null);
	    }
	    else
	    {
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

			$referenceApprovalStringQuery = mysqli_real_escape_string($db, $referenceApprovalString);
			$referenceAutor = mysqli_real_escape_string($db, $referenceAutor);
			$referenceTxt = mysqli_real_escape_string($db, $referenceTxt);
		    $referenceInsertQuery = "INSERT INTO reference (full_reference, autor, reference, ersteller_datum) VALUES ('".$referenceApprovalStringQuery."', '".$referenceAutor."', '".$referenceTxt."', '".$date."')";
			$db->query($referenceInsertQuery);
			$newReferenceId = mysqli_insert_id($db);

			$fullApprovalStringWhenHyphen = trim(base64_decode($_POST['full_approval_string_when_hyphen']));
			$approvalString = trim(base64_decode($_POST['approval_string']));
			$fullApprovalStringWhenHyphen = str_replace($approvalString, $referenceApprovalString, $fullApprovalStringWhenHyphen);

			$fullApprovalStringWhenHyphenQuery = mysqli_real_escape_string($db, $fullApprovalStringWhenHyphen);
			$symptomUpdateQuery="UPDATE temp_quelle_import_test SET full_approval_string_when_hyphen = '".$fullApprovalStringWhenHyphenQuery."', hyphen_pruefer_priority = 0, hyphen_reference_priority = 0 WHERE id = '".$_POST['symptom_id']."'";
			$db->query($symptomUpdateQuery);

			$fullApprovalStringWhenHyphen = base64_encode($fullApprovalStringWhenHyphen);
			$reImplementRule = ruleReimplementation($_POST['symptom_id'], $fullApprovalStringWhenHyphen, $_POST['master_id'], $_POST['approval_for'], $_POST['is_pre_defined_tags_approval'], null);
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

				ruleReimplementation($_POST['symptom_id'], $_POST['full_approval_string_when_hyphen_unchanged'], $_POST['master_id'], $_POST['approval_for'], $_POST['is_pre_defined_tags_approval'], $tagParameter);
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