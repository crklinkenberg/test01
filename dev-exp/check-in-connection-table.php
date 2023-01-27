<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Updating the comment or Foot note of a particular symptom 
	*/
?>
<?php  
	$status = '';
	$message = '';
	$comparisonTableName = (isset($_POST['comparison_table_name']) AND $_POST['comparison_table_name'] != "") ? $_POST['comparison_table_name'] : null;
	$param = (isset($_POST['param']) AND $_POST['param'] != "") ? $_POST['param'] : null;

	try {
		if($comparisonTableName != "" && $comparisonTableName != ""){
			$returnValue = 0;
			switch ($param) {
				case '1':{
					$connectionsTable = $comparisonTableName."_connections";
					$query = "SELECT id FROM $connectionsTable  WHERE ns_connect = '1'";
					$initialsCheckAllResult = mysqli_query($db, $query);
					if(mysqli_num_rows($initialsCheckAllResult) > 0){
						$returnValue = 1;
					}
					break;
				}

				case '2':{
					$connectionsTable = $comparisonTableName."_connections";
					$query = "SELECT id FROM $connectionsTable  WHERE ns_paste = '1'";
					$initialsCheckAllResult = mysqli_query($db, $query);
					if(mysqli_num_rows($initialsCheckAllResult) > 0){
						$returnValue = 1;
					}
					break;
				}

				case '3':{
					$query = "SELECT id FROM $comparisonTableName  WHERE gen_ns = '1'";
					$initialsCheckAllResult = mysqli_query($db, $query);
					if(mysqli_num_rows($initialsCheckAllResult) > 0){
						$returnValue = 1;
					}
					break;
				}
				
				default:{
					//nothing
					break;
				}
			}
					
			$status = "update success";
			$message = "success";
		}
	} catch (Exception $e) {
	    $status = 'error';
	    $message = 'Exception error';
	}

	echo json_encode( array( 'status' => $status, 'message' => $message, 'returnValue' => $returnValue) ); 
	exit;
?>
<?php
	include 'includes/php-foot-includes.php';
?>