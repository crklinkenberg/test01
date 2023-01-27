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
		$symptomUpdateQuery="UPDATE temp_quelle_import_test SET remedy_priority = 0 WHERE id = '".$_POST['symptom_id']."'";
		$db->query($symptomUpdateQuery);

		$deleteTempRemedyQuery="DELETE FROM temp_remedy WHERE symptom_id = '".$_POST['symptom_id']."'";
		$db->query($deleteTempRemedyQuery);

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
	try {
	    $db->begin_transaction();
	    $isActionTaken = 0;
	    if(isset($_POST['suggested_remedy']) AND is_array($_POST['suggested_remedy']) AND !empty($_POST['suggested_remedy'])){
	    	$isActionTaken = 1;
	    	$deleteTempSymptomRemedyQuery = "DELETE FROM temp_remedy WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempSymptomRemedyQuery);
	    	foreach ($_POST['suggested_remedy'] as $remedyKey => $remedyVal) {
				// Inserting freshly, selected Pruefer
				$tempRemedyQuery = "INSERT INTO temp_remedy (symptom_id, main_remedy_id) VALUES ('".$_POST['symptom_id']."', '".$remedyVal."')";
				$db->query($tempRemedyQuery);
	    	}
			// $remedyString = rtrim(implode(", ", $_POST['suggested_remedy']), ", ");
			// $remedyString = mysqli_real_escape_string($db, $remedyString);
			$symptomUpdateQuery="UPDATE temp_quelle_import_test SET part_of_symptom_priority = 0, remedy_priority = 0, pruefer_priority = 0, reference_with_no_author_priority = 0, remedy_with_symptom_priority = 0, more_than_one_tag_string_priority = 0, aao_hyphen_priority = 0, hyphen_pruefer_priority = 0, hyphen_reference_priority = 0, reference_priority = 0, direct_order_priority = 0, need_approval = 0 WHERE id = '".$_POST['symptom_id']."'";
			$db->query($symptomUpdateQuery);

			// Deleteing the temp remedies because they are allready there in main Remedy table
			// $deleteTempRemedyQuery="DELETE FROM temp_remedy WHERE symptom_id = '".$_POST['symptom_id']."'";
			// $db->query($deleteTempRemedyQuery);

			// Deleteing the temp pruefers because they are no longer need if it is approved as Remedy.
			$deleteTempPrueferQuery = "DELETE FROM temp_pruefer WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempPrueferQuery);

			$deleteTempSymptomPrueferQuery = "DELETE FROM temp_symptom_pruefer WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempSymptomPrueferQuery);	

			$deleteTempReferenceQuery = "DELETE FROM temp_reference WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempReferenceQuery);

			$deleteTempSymptomReferenceQuery = "DELETE FROM temp_symptom_reference WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempSymptomReferenceQuery);
		}else if(isset($_POST['import_question_popup_remedy_ids']) AND is_array($_POST['import_question_popup_remedy_ids']) AND !empty($_POST['import_question_popup_remedy_ids'])){
			$isActionTaken = 1;
			$deleteTempSymptomRemedyQuery = "DELETE FROM temp_remedy WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempSymptomRemedyQuery);
	    	foreach ($_POST['import_question_popup_remedy_ids'] as $remedyKey => $remedyVal) {
				// Inserting freshly, selected Pruefer
				$tempRemedyQuery = "INSERT INTO temp_remedy (symptom_id, main_remedy_id) VALUES ('".$_POST['symptom_id']."', '".$remedyVal."')";
				$db->query($tempRemedyQuery);
	    	}

	    	// $remedyString = rtrim(implode(", ", $_POST['suggested_remedy']), ", ");
			// $remedyString = mysqli_real_escape_string($db, $remedyString);
			$symptomUpdateQuery="UPDATE temp_quelle_import_test SET part_of_symptom_priority = 0, remedy_priority = 0, pruefer_priority = 0, reference_with_no_author_priority = 0, remedy_with_symptom_priority = 0, more_than_one_tag_string_priority = 0, aao_hyphen_priority = 0, hyphen_pruefer_priority = 0, hyphen_reference_priority = 0, reference_priority = 0, direct_order_priority = 0, need_approval = 0 WHERE id = '".$_POST['symptom_id']."'";
			$db->query($symptomUpdateQuery);

			// Deleteing the temp remedies because they are allready there in main Remedy table
			// $deleteTempRemedyQuery="DELETE FROM temp_remedy WHERE symptom_id = '".$_POST['symptom_id']."'";
			// $db->query($deleteTempRemedyQuery);

			// Deleteing the temp pruefers because they are no longer need if it is approved as Remedy.
			$deleteTempPrueferQuery = "DELETE FROM temp_pruefer WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempPrueferQuery);

			$deleteTempSymptomPrueferQuery = "DELETE FROM temp_symptom_pruefer WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempSymptomPrueferQuery);	

			$deleteTempReferenceQuery = "DELETE FROM temp_reference WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempReferenceQuery);

			$deleteTempSymptomReferenceQuery = "DELETE FROM temp_symptom_reference WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempSymptomReferenceQuery);
		}else if(isset($_POST['remedy_title']) AND is_array($_POST['remedy_title']) AND !empty($_POST['remedy_title'])){
			if(isset($_POST['remedy_title'][0]) AND $_POST['remedy_title'][0] != ""){
				$isActionTaken = 1;
				$remedyInsertionString = ""; 
				$deleteTempRemedyQuery="DELETE FROM temp_remedy WHERE symptom_id = '".$_POST['symptom_id']."'";
				$db->query($deleteTempRemedyQuery);
				foreach ($_POST['remedy_title'] as $remedyKey => $remedyVal) {
					$remedyTitle = (isset($_POST['remedy_title'][$remedyKey]) AND $_POST['remedy_title'][$remedyKey] != "") ? mysqli_real_escape_string($db, trim($_POST['remedy_title'][$remedyKey])) : "";
					$remedyAbbreviations = (isset($_POST['remedy_abbreviations'][$remedyKey]) AND $_POST['remedy_abbreviations'][$remedyKey] != "") ? mysqli_real_escape_string($db, trim($_POST['remedy_abbreviations'][$remedyKey])) : "";
					if(isset($_POST['remedy_title'][$remedyKey]) AND $_POST['remedy_title'][$remedyKey] != "")
						$remedyInsertionString = trim($_POST['remedy_title'][$remedyKey]).", ";
					
					$checkRemedyResult = mysqli_query($db, "SELECT titel FROM arznei WHERE titel = '".$remedyTitle."'");
					$checkRemedyRowCount = mysqli_num_rows($checkRemedyResult);
					if( $checkRemedyRowCount == 0){
						$remedyQuery = "INSERT INTO arznei (titel, kuerzel, ersteller_datum) VALUES ('".$remedyTitle."', '".$remedyAbbreviations."', '".$date."')";
						$db->query($remedyQuery);
						$newRemedyId = mysqli_insert_id($db);

						// Inserting freshly, selected Pruefer
						$tempRemedyQuery = "INSERT INTO temp_remedy (symptom_id, main_remedy_id) VALUES ('".$_POST['symptom_id']."', '".$newRemedyId."')";
						$db->query($tempRemedyQuery);
					}
				}
				$remedyInsertionString = ($remedyInsertionString != "") ? rtrim($remedyInsertionString, ", ") : "";
				if($remedyInsertionString != ""){
					$remedyApprovalString = mysqli_real_escape_string($db, $remedyInsertionString);
					$symptomUpdateQuery="UPDATE temp_quelle_import_test SET part_of_symptom_priority = 0, remedy_priority = 0, pruefer_priority = 0, reference_with_no_author_priority = 0, remedy_with_symptom_priority = 0, more_than_one_tag_string_priority = 0, aao_hyphen_priority = 0, hyphen_pruefer_priority = 0, hyphen_reference_priority = 0, reference_priority = 0, direct_order_priority = 0, need_approval = 0 WHERE id = '".$_POST['symptom_id']."'";
					$db->query($symptomUpdateQuery);
				}

				// Deleteing the temp pruefers because they are no longer need if it is approved as Remedy.
				$deleteTempPrueferQuery = "DELETE FROM temp_pruefer WHERE symptom_id = '".$_POST['symptom_id']."'";
				$db->query($deleteTempPrueferQuery);

				$deleteTempSymptomPrueferQuery = "DELETE FROM temp_symptom_pruefer WHERE symptom_id = '".$_POST['symptom_id']."'";
				$db->query($deleteTempSymptomPrueferQuery);

				$deleteTempReferenceQuery = "DELETE FROM temp_reference WHERE symptom_id = '".$_POST['symptom_id']."'";
				$db->query($deleteTempReferenceQuery);

				$deleteTempSymptomReferenceQuery = "DELETE FROM temp_symptom_reference WHERE symptom_id = '".$_POST['symptom_id']."'";
				$db->query($deleteTempSymptomReferenceQuery);
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

		if($isActionTaken != 0) {
			// Update searchable text
			if(isset($_POST['approval_for']) AND $_POST['approval_for'] == 1){
				$workingApprovalString = base64_decode($_POST['middle_bracket_approval_string']);
				$unApprovedResult = mysqli_query($db, "SELECT searchable_text FROM temp_quelle_import_test WHERE id = '".$_POST['symptom_id']."'");
				if( mysqli_num_rows($unApprovedResult) > 0){
					$unApprovedRow = mysqli_fetch_assoc($unApprovedResult);
					$updateableSearchText = $unApprovedRow['searchable_text'];
					if(mb_strpos($updateableSearchText, $workingApprovalString) !== false){
						$updateableSearchText = str_replace($workingApprovalString, "", $updateableSearchText);
						// Removing blank parentheses
						$updateableSearchText = preg_replace('#\(\s*\)#', '', $updateableSearchText);
						$updateableSearchText = str_replace("()", "", $updateableSearchText);
						// Removing blank square brackets
						$updateableSearchText = preg_replace('#\[\s*\]#', '', $updateableSearchText);
						$updateableSearchText = str_replace("[]", "", $updateableSearchText);

						$updateableSearchText = mysqli_real_escape_string($db, $updateableSearchText);
						$searchTextUpdateQuery="UPDATE temp_quelle_import_test SET searchable_text = '".$updateableSearchText."' WHERE id = '".$_POST['symptom_id']."'";
						$db->query($searchTextUpdateQuery);
					}
				}
			}
		} else {
			$parameterString = "?master=".$_POST['master_id']."&popup_error=6"; 
		}

	    $db->commit();
		header('Location: '.$baseUrl.$parameterString);
		exit();
	} catch (Exception $e) {
	    $db->rollback();
	    header('Location: '.$baseUrl.'?error=1');
		exit();
	}
}
else if(isset($_POST['do']) AND $_POST['do'] == "DO"){
	try {
	    $db->begin_transaction();

	    $parameterString = "";

	    // Deleteing the temp remidies because they are no longer need if it is taking Direct Order.
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
}else if(isset($_POST['comma_separated_remedies_ok']) AND $_POST['comma_separated_remedies_ok'] == "Ok"){
	
	$parameterString = "";
	
	if(isset($_POST['remedies_comma_separated']) AND $_POST['remedies_comma_separated'] != ""){

		try {
		    $db->begin_transaction();

		    // Update searchable text
			if(isset($_POST['approval_for']) AND $_POST['approval_for'] == 1){
				$workingApprovalString = base64_decode($_POST['middle_bracket_approval_string']);
				$unApprovedResult = mysqli_query($db, "SELECT searchable_text FROM temp_quelle_import_test WHERE id = '".$_POST['symptom_id']."'");
				if( mysqli_num_rows($unApprovedResult) > 0){
					$unApprovedRow = mysqli_fetch_assoc($unApprovedResult);
					$updateableSearchText = $unApprovedRow['searchable_text'];
					if(mb_strpos($updateableSearchText, $workingApprovalString) !== false){
						$updateableSearchText = str_replace($workingApprovalString, "", $updateableSearchText);
						// Removing blank parentheses
						$updateableSearchText = preg_replace('#\(\s*\)#', '', $updateableSearchText);
						$updateableSearchText = str_replace("()", "", $updateableSearchText);
						// Removing blank square brackets
						$updateableSearchText = preg_replace('#\[\s*\]#', '', $updateableSearchText);
						$updateableSearchText = str_replace("[]", "", $updateableSearchText);

						$updateableSearchText = mysqli_real_escape_string($db, $updateableSearchText);
						$searchTextUpdateQuery="UPDATE temp_quelle_import_test SET searchable_text = '".$updateableSearchText."' WHERE id = '".$_POST['symptom_id']."'";
						$db->query($searchTextUpdateQuery);
					}
				}
			}

		    // Deleteing the temp pruefers because they are no longer need if it is taking this Direct Order.
		    $deleteTempRemedyQuery="DELETE FROM temp_remedy WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempRemedyQuery);
			
			$explodedValue = explode(",", $_POST['remedies_comma_separated']);
			foreach ($explodedValue as $remedyKey => $remedyVal) {
				if($remedyVal == "")
					continue;

				$cleanRemedy = trim($remedyVal);
				$cleanRemedyString = (mb_substr ( $cleanRemedy, mb_strlen ( $cleanRemedy ) - 1, 1 ) == '.') ? $cleanRemedy : $cleanRemedy.'.';
				$remedyReturnArr = lookupRemedy($cleanRemedyString);
				if(isset($remedyReturnArr['need_approval']) AND $remedyReturnArr['need_approval'] == 1){
					$cleanRemedy = mysqli_real_escape_string($db, $cleanRemedy);
					$remedyQuery = "INSERT INTO remedy (name, ersteller_datum) VALUES ('".$cleanRemedy."', '".$date."')";
					$db->query($remedyQuery);
				}else{
					$explodedValue[$remedyKey] = ($remedyReturnArr['data'][0]['name'] != "") ? $remedyReturnArr['data'][0]['name'] : "";
				}
			}

			
			$remedies_comma_separated = trim(implode(",", $explodedValue));
			//$remedies_comma_separated = trim($_POST['remedies_comma_separated']);
			$remedies_comma_separated = rtrim($remedies_comma_separated, ",");

			$remedies_comma_separated = mysqli_real_escape_string($db, $remedies_comma_separated);
			$symptomUpdateQuery="UPDATE temp_quelle_import_test SET Remedy = '".$remedies_comma_separated."', part_of_symptom_priority = 0, remedy_priority = 0, pruefer_priority = 0, reference_with_no_author_priority = 0, remedy_with_symptom_priority = 0, more_than_one_tag_string_priority = 0, aao_hyphen_priority = 0, hyphen_pruefer_priority = 0, hyphen_reference_priority = 0, reference_priority = 0, direct_order_priority = 0, need_approval = 0 WHERE id = '".$_POST['symptom_id']."'";
			$db->query($symptomUpdateQuery);

			// Deleteing the temp pruefers because they are no longer need if it is approved as Remedy.
			$deleteTempPrueferQuery = "DELETE FROM temp_pruefer WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempPrueferQuery);

			$deleteTempSymptomPrueferQuery = "DELETE FROM temp_symptom_pruefer WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempSymptomPrueferQuery);

			$deleteTempReferenceQuery = "DELETE FROM temp_reference WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempReferenceQuery);

			$deleteTempSymptomReferenceQuery = "DELETE FROM temp_symptom_reference WHERE symptom_id = '".$_POST['symptom_id']."'";
			$db->query($deleteTempSymptomReferenceQuery);

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
			$parameterString = "?master=".$_POST['master_id']."&popup_error=1";

		header('Location: '.$baseUrl.$parameterString);
		exit();
	}
}else if(isset($_POST['reset_current']) AND $_POST['reset_current'] == "Reset"){
	try {
	    $db->begin_transaction();

	    $parameterString = "";
	    if($_POST['is_pre_defined_tags_approval'] == 1){
	    	if($_POST['approval_for'] == 1)
				$workingApprovalString = $_POST['middle_bracket_approval_string'];
			else
				$workingApprovalString = $_POST['approval_string'];
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

				ruleReimplementation($_POST['symptom_id'], $workingApprovalString, $_POST['master_id'], $_POST['approval_for'], $_POST['is_pre_defined_tags_approval'], $tagParameter);
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