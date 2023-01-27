<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Updating the saved comparison name
	*/
?>
<?php  
	$resultData = array();
	$status = 'error';
	$message = 'Could not perform the action.';
	try {
		$comparison_name = (isset($_POST['comparison_name']) AND $_POST['comparison_name'] != "") ? trim($_POST['comparison_name']) : null;
		$id = (isset($_POST['id']) AND $_POST['id'] != "") ? $_POST['id'] : null;
		$existing_comparison_name = (isset($_POST['existing_comparison_name']) AND $_POST['existing_comparison_name'] != "") ? trim($_POST['existing_comparison_name']) : null;
		if($id != "" AND $comparison_name != "")
		{
			$totalSymptomQuery = mysqli_query($db,"SELECT id, comparison_name FROM pre_comparison_master_data WHERE comparison_name = '".$comparison_name."' AND id != '".$id."'");
			if(mysqli_num_rows($totalSymptomQuery) > 0){
				$status = 'error';
				$message = 'This name is already used.';
			}
			else
			{
				$updComparisonNameQuery = "UPDATE pre_comparison_master_data SET comparison_name = '".$comparison_name."' WHERE id = ".$id;
            	$db->query($updComparisonNameQuery);
            	$status = 'success';
		    	$message = 'Updated successfully';
			}
		}
	} catch (Exception $e) {
	    $status = 'error';
	    $message = 'Something went wrong, please try again!';
	}

	echo json_encode( array( 'status' => $status, 'result_data' => $resultData, 'message' => $message) ); 
	exit;
?>