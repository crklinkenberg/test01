<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Saving the uesr's edit of the symptom or performing any other action provided by user e.g. Later, Skip, etc.
	*/
?>
<?php
/*
* Error meanings
*
* popup_error = 3
* Meaning = Provided source text already exist in the main symptoms table or temp symptom table.
* Message = Source already exist in main symptoms or in incomplete source imports.
*
* popup_error = 4
* Meaning = Provided source text contain very few character to update.
* Message = Source text contain very few characters, Could not update!
*
* popup_error = 5
* Meaning = MySQl Transacation error
* Message = Something went wrong Could not save the data. Please retry! 
*
*/

if(isset($_POST['symptom_edit_save']) AND $_POST['symptom_edit_save'] == "Save"){
	try {
	    // First of all, let's begin a transaction
	    $db->begin_transaction();

	    /* Applying program logic in the string STRAT */
	    $parameterString = "";
	    if(isset($_POST['symptom_text']) AND $_POST['symptom_text'] != ""){
	    	$editComment = (isset($_POST['symptom_edit_comment']) AND $_POST['symptom_edit_comment'] != "") ? $_POST['symptom_edit_comment'] : null;
	    	$symptom_text = base64_encode($_POST['symptom_text']);
	    	$returnResult = repopulateDataOnSymptomEditOrReset($_POST['symptom_id'], $_POST['master_id'], $symptom_text, $editComment);
	    	if(isset($returnResult['status']) AND !empty($returnResult['status'])){
	    		if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
					$parameterString = "?master=".$_POST['master_id'];
	    		if(isset($returnResult['error_code']) AND $returnResult['error_code'] != 0){
	    			if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
						$parameterString = "?master=".$_POST['master_id']."&popup_error=".$returnResult['error_code'];
	    		}
	    	}
	    }

	    // If we arrive here, it means that no exception was thrown
	    // i.e. no query has failed, and we can commit the transaction
	    $db->commit();

		header('Location: '.$baseUrl.$parameterString);
		exit();
	} catch (Exception $e) {
	    // An exception has been thrown
	    // We must rollback the transaction
	    $db->rollback();
	    header('Location: '.$baseUrl.'?error=1');
		exit();
	}
	
}else if(isset($_POST['symptom_edit_cancel']) AND $_POST['symptom_edit_cancel'] == "Cancel"){
	try {
	    // First of all, let's begin a transaction
	    $db->begin_transaction();

	    $parameterString = "";
		$symptomUpdateQuery="UPDATE temp_quelle_import_test SET symptom_edit_priority = 0 WHERE id = '".$_POST['symptom_id']."'";
		$db->query($symptomUpdateQuery);

	    // If we arrive here, it means that no exception was thrown
	    // i.e. no query has failed, and we can commit the transaction
	    $db->commit();

	    if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
			$parameterString = "?master=".$_POST['master_id'];

		header('Location: '.$baseUrl.$parameterString);
		exit();
	} catch (Exception $e) {
	    // An exception has been thrown
	    // We must rollback the transaction
	    $db->rollback();
	    header('Location: '.$baseUrl.'?error=1');
		exit();
	}
}else if(isset($_POST['edit_symptom']) AND $_POST['edit_symptom'] == "Edit"){
	try {
	    // First of all, let's begin a transaction
	    $db->begin_transaction();

	    $parameterString = "";
		$symptomUpdateQuery="UPDATE temp_quelle_import_test SET symptom_edit_priority = 12 WHERE id = '".$_POST['symptom_id']."'";
		$db->query($symptomUpdateQuery);

	    // If we arrive here, it means that no exception was thrown
	    // i.e. no query has failed, and we can commit the transaction
	    $db->commit();

	    if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
			$parameterString = "?master=".$_POST['master_id'];

		header('Location: '.$baseUrl.$parameterString);
		exit();
	} catch (Exception $e) {
	    // An exception has been thrown
	    // We must rollback the transaction
	    $db->rollback();
	    header('Location: '.$baseUrl.'?error=1');
		exit();
	}
}
?>