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
		$symptomUpdateQuery="UPDATE temp_quelle_import_test SET hyphen_pruefer_priority = 0 WHERE id = '".$_POST['symptom_id']."'";
		$db->query($symptomUpdateQuery);

		$deleteTempSymptomPrueferQuery = "DELETE FROM temp_symptom_pruefer WHERE symptom_id = '".$_POST['symptom_id']."'";
		$db->query($deleteTempSymptomPrueferQuery);

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
	$parameterString = "";
	if(isset($_POST['suggested_pruefer']) AND $_POST['suggested_pruefer'] != ""){

		try {
		    $db->begin_transaction();

			$fullApprovalStringWhenHyphen = trim(base64_decode($_POST['full_approval_string_when_hyphen']));
			$getPrueferNameResult = mysqli_query($db, "SELECT suchname FROM pruefer WHERE pruefer_id = '".$_POST['suggested_pruefer']."'");
			if( mysqli_num_rows($getPrueferNameResult) > 0){
				$prueferData = mysqli_fetch_assoc($getPrueferNameResult);
				$approvalString = trim(base64_decode($_POST['approval_string']));
				$fullApprovalStringWhenHyphen = str_replace($approvalString, $prueferData['suchname'], $fullApprovalStringWhenHyphen);
			}

			$fullApprovalStringWhenHyphenQuery = mysqli_real_escape_string($db, $fullApprovalStringWhenHyphen);
			$symptomUpdateQuery="UPDATE temp_quelle_import_test SET full_approval_string_when_hyphen = '".$fullApprovalStringWhenHyphenQuery."', hyphen_pruefer_priority = 0, hyphen_reference_priority = 0 WHERE id = '".$_POST['symptom_id']."'";
			$db->query($symptomUpdateQuery);

			// Update main Pruefer kuerzel if it is not empty
			$checkedPrueferId = $_POST['suggested_pruefer'];
			if(isset($_POST['kuerzel_'.$checkedPrueferId]) AND $_POST['kuerzel_'.$checkedPrueferId] != ""){
				$_POST['kuerzel_'.$checkedPrueferId] = mysqli_real_escape_string($db, $_POST['kuerzel_'.$checkedPrueferId]);
				$updPrueferKuerzelQuery = "UPDATE pruefer SET kuerzel = '".$_POST['kuerzel_'.$checkedPrueferId]."', stand = '".$date."' WHERE pruefer_id = '".$checkedPrueferId."'";
				$db->query($updPrueferKuerzelQuery);
			}

			$fullApprovalStringWhenHyphen = base64_encode($fullApprovalStringWhenHyphen);
			$reImplementRule = ruleReimplementation($_POST['symptom_id'], $fullApprovalStringWhenHyphen, $_POST['master_id'], $_POST['approval_for'], $_POST['is_pre_defined_tags_approval'], null);

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
		
	}else{
		if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
		$parameterString = "?master=".$_POST['master_id']."&new-pruefer=1";

		header('Location: '.$baseUrl.$parameterString);
		exit();
	}
}else if(isset($_POST['do']) AND $_POST['do'] == "DO"){
	try {
	    $db->begin_transaction();

	    $parameterString = "";
		// Deleteing the temp remeds because they are no longer need if it is taking Direct Order.
		$deleteTempRemedyQuery="DELETE FROM temp_remedy WHERE symptom_id = '".$_POST['symptom_id']."'";
		$db->query($deleteTempRemedyQuery);

		// Deleteing the temp pruefers because they are no longer need if it is taking Direct Order.
		$deleteTempPrueferQuery = "DELETE FROM temp_pruefer WHERE symptom_id = '".$_POST['symptom_id']."'";
		$db->query($deleteTempPrueferQuery);

		$deleteTempSymptomPrueferQuery = "DELETE FROM temp_symptom_pruefer WHERE symptom_id = '".$_POST['symptom_id']."'";
		$db->query($deleteTempSymptomPrueferQuery);

		$deleteTempReferenceQuery = "DELETE FROM temp_reference WHERE symptom_id = '".$_POST['symptom_id']."'";
		$db->query($deleteTempReferenceQuery);

		$deleteTempSymptomReferenceQuery = "DELETE FROM temp_symptom_reference WHERE symptom_id = '".$_POST['symptom_id']."'";
		$db->query($deleteTempSymptomReferenceQuery);

		$symptomUpdateQuery="UPDATE temp_quelle_import_test SET direct_order_priority = 11 WHERE id = '".$_POST['symptom_id']."'";
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
	
}else if(isset($_POST['add_pruefer']) AND $_POST['add_pruefer'] == "Yes"){
	$parameterString = "";
	$geburtsdatum = (isset($_POST['geburtsdatum']) AND $_POST['geburtsdatum'] != "") ? "'".date('Y-m-d', strtotime($_POST['geburtsdatum']))."'" : 'null';
	$sterbedatum = (isset($_POST['sterbedatum']) AND $_POST['sterbedatum'] != "") ? "'".date('Y-m-d', strtotime($_POST['sterbedatum']))."'" : 'null';
	$suchname = (isset($_POST['suchname']) AND $_POST['suchname'] != "") ? $_POST['suchname'] : '';
	$kuerzel = (isset($_POST['kuerzel']) AND $_POST['kuerzel'] != "") ? $_POST['kuerzel'] : '';
	$prueferArray = array();
	try {
	    $db->begin_transaction();

	    // First check if Given pruefer already exit or not, If it Exist assign with the already existed one
	    $isPruferFoundInDb = 0;
	    if($suchname != ""){
	    	$suchnameString = trim($suchname);
	    	$suchnameString = (mb_substr ( $suchnameString, mb_strlen ( $suchnameString ) - 1, 1 ) == '.') ? $suchnameString : $suchnameString.'.';
	    	$prueferReturnArr = lookupPruefer($suchnameString);
			if(isset($prueferReturnArr['need_approval']) AND $prueferReturnArr['need_approval'] == 0){
				$isPruferFoundInDb = 1;
				$prueferArray = $prueferReturnArr['data'];
			}
	    }
	    if($isPruferFoundInDb == 0 AND $kuerzel != ""){
	    	if (mb_strpos($kuerzel, '|') !== false){
	    		$kuerzelArray = explode("|", $kuerzel);
	    		foreach ($kuerzelArray as $kueKey => $kueVal) {
	    			if($kueVal == "")
						continue;

					$kuerzelString = trim($kueVal);
					$kuerzelString = (mb_substr ( $kuerzelString, mb_strlen ( $kuerzelString ) - 1, 1 ) == '.') ? $kuerzelString : $kuerzelString.'.';
					$prueferReturnArr = lookupPruefer($kuerzelString);
					if(isset($prueferReturnArr['need_approval']) AND $prueferReturnArr['need_approval'] == 0){
						$isPruferFoundInDb = 1;
						$prueferArray = array();
						$prueferArray = $prueferReturnArr['data'];
						break;
					}
	    		}
	    	}else{
	    		$kuerzelString = trim($kuerzel);
	    		$kuerzelString = (mb_substr ( $kuerzelString, mb_strlen ( $kuerzelString ) - 1, 1 ) == '.') ? $kuerzelString : $kuerzelString.'.';
				$prueferReturnArr = lookupPruefer($kuerzelString);
				if(isset($prueferReturnArr['need_approval']) AND $prueferReturnArr['need_approval'] == 0){
					$isPruferFoundInDb = 1;
					$prueferArray = array();
					$prueferArray = $prueferReturnArr['data'];
				}
	    	} 
	    }

	    if($isPruferFoundInDb == 1 AND (isset($prueferArray[0]['pruefer_id']) AND $prueferArray[0]['pruefer_id'] != "")){
	    	$foundPrueferId = $prueferArray[0]['pruefer_id'];

			$fullApprovalStringWhenHyphen = trim(base64_decode($_POST['full_approval_string_when_hyphen']));
			$getPrueferNameResult = mysqli_query($db, "SELECT suchname FROM pruefer WHERE pruefer_id = '".$foundPrueferId."'");
			if( mysqli_num_rows($getPrueferNameResult) > 0){
				$prueferData = mysqli_fetch_assoc($getPrueferNameResult);
				$approvalString = trim(base64_decode($_POST['approval_string']));
				$fullApprovalStringWhenHyphen = str_replace($approvalString, $prueferData['suchname'], $fullApprovalStringWhenHyphen);
			}

			$fullApprovalStringWhenHyphenQuery = mysqli_real_escape_string($db, $fullApprovalStringWhenHyphen);
			$symptomUpdateQuery="UPDATE temp_quelle_import_test SET full_approval_string_when_hyphen = '".$fullApprovalStringWhenHyphenQuery."', hyphen_pruefer_priority = 0, hyphen_reference_priority = 0 WHERE id = '".$_POST['symptom_id']."'";
			$db->query($symptomUpdateQuery);

			$fullApprovalStringWhenHyphen = base64_encode($fullApprovalStringWhenHyphen); 
			$reImplementRule = ruleReimplementation($_POST['symptom_id'], $fullApprovalStringWhenHyphen, $_POST['master_id'], $_POST['approval_for'], $_POST['is_pre_defined_tags_approval'], null);
	    }else{

	    	$_POST['kuerzel'] = mysqli_real_escape_string($db, $_POST['kuerzel']);
	    	$_POST['suchname'] = mysqli_real_escape_string($db, $_POST['suchname']);
	    	$_POST['titel'] = mysqli_real_escape_string($db, $_POST['titel']);
	    	$_POST['vorname'] = mysqli_real_escape_string($db, $_POST['vorname']);
	    	$_POST['nachname'] = mysqli_real_escape_string($db, $_POST['nachname']);
	    	$_POST['kommentar'] = mysqli_real_escape_string($db, $_POST['kommentar']);
	    	// Inserting Pruefer in Pruefer Tbale
			$prueferQuery = "INSERT INTO pruefer (kuerzel, suchname, titel, vorname, nachname, geburtsdatum, sterbedatum, kommentar) VALUES ('".$_POST['kuerzel']."', '".$_POST['suchname']."', '".$_POST['titel']."', '".$_POST['vorname']."', '".$_POST['nachname']."', ".$geburtsdatum.", ".$sterbedatum.", '".$_POST['kommentar']."')";
			$db->query($prueferQuery);
			$mainPrueferId = mysqli_insert_id($db);
			if($mainPrueferId != ""){
				$fullApprovalStringWhenHyphen = trim(base64_decode($_POST['full_approval_string_when_hyphen']));
				$approvalString = trim(base64_decode($_POST['approval_string']));
				$fullApprovalStringWhenHyphen = str_replace($approvalString, $_POST['suchname'], $fullApprovalStringWhenHyphen);

				$fullApprovalStringWhenHyphenQuery = mysqli_real_escape_string($db, $fullApprovalStringWhenHyphen);
				$symptomUpdateQuery="UPDATE temp_quelle_import_test SET full_approval_string_when_hyphen = '".$fullApprovalStringWhenHyphenQuery."', hyphen_pruefer_priority = 0, hyphen_reference_priority = 0 WHERE id = '".$_POST['symptom_id']."'";
				$db->query($symptomUpdateQuery);

				$fullApprovalStringWhenHyphen = base64_encode($fullApprovalStringWhenHyphen);
				$reImplementRule = ruleReimplementation($_POST['symptom_id'], $fullApprovalStringWhenHyphen, $_POST['master_id'], $_POST['approval_for'], $_POST['is_pre_defined_tags_approval'], null);
			}
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