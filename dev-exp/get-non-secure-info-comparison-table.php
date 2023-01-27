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
		if(isset($_POST['symptom_id']) AND !empty($_POST['symptom_id']) AND isset($_POST['comparison_table_name']) AND !empty($_POST['comparison_table_name'])){	
			$symptomId = trim($_POST['symptom_id']);
			$initialId = trim($_POST['initial_id']);
			$comparisonTableName = (isset($_POST['comparison_table_name']) AND $_POST['comparison_table_name'] != "") ? trim($_POST['comparison_table_name']) : "";
			$connectionTableName = $comparisonTableName."_connections";
			
			$query = "SELECT `ns_connect`,`ns_paste`,`ns_connect_comment`,`ns_paste_comment` FROM `".$connectionTableName."` WHERE `initial_symptom_id`=".$initialId." AND `comparing_symptom_id` =".$symptomId."";
			$queryRes = mysqli_query($db,$query);
			if(mysqli_num_rows($queryRes) > 0){
				$row = mysqli_fetch_assoc($queryRes);

				
				$resultData['ns_connect'] = $row['ns_connect'];
				$resultData['ns_paste'] = $row['ns_paste'];
				$resultData['ns_connect_comment'] = $row['ns_connect_comment'];
				$resultData['ns_paste_comment'] = $row['ns_paste_comment'];
				
				$status = "success";
				$message = "success";
			}
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