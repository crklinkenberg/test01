<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Fetching basic informations of a particular symptom
	*/
?>
<?php  
	$resultData = array();
	$status = '';
	$message = '';
	try {
		if(isset($_POST['initial_id']) AND !empty($_POST['initial_id']) AND isset($_POST['comparison_table_name']) AND !empty($_POST['comparison_table_name'])){	
			$initialId = trim($_POST['initial_id']);
			$comparisonTableName = (isset($_POST['comparison_table_name']) AND $_POST['comparison_table_name'] != "") ? trim($_POST['comparison_table_name']) : "";
			
			$query = "SELECT `gen_ns`,`gen_ns_comment` FROM `".$comparisonTableName."` WHERE `symptom_id`=".$initialId." AND `is_initial_symptom`= '1'";
			$queryRes = mysqli_query($db,$query);
			if(mysqli_num_rows($queryRes) > 0){
				$row = mysqli_fetch_assoc($queryRes);
				$resultData['gen_ns'] = $row['gen_ns'];
				$resultData['gen_ns_comment'] = $row['gen_ns_comment'];
			}
				$status = "success";
				$message = "success";
		}
	} catch (Exception $e) {
	    $status = 'error';
	    $message = 'Exception error';
	}


	echo json_encode( array( 'status' => $status, 'result_data' => $resultData, 'message' => $message, 'query' => $query) ); 
	exit;
?>
<?php
	include 'includes/php-foot-includes.php';
?>