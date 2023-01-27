<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Checking duplicate name while user tries to change a saved comparison name 
	*/
?>
<?php  
	$status = "";
	$isValidName = 0;
	try 
	{
		$comparison_name = (isset($_POST['comparison_name']) AND $_POST['comparison_name'] != "") ? trim($_POST['comparison_name']) : null;
		if($comparison_name != ""){
			$totalSymptomQuery = mysqli_query($db,"SELECT quelle_id FROM quelle WHERE code = '".$comparison_name."' OR titel = '".$comparison_name."'");
			if(mysqli_num_rows($totalSymptomQuery) > 0){
				$isValidName = 0;
				$status = "success";
			}
			else{
				$isValidName = 1;
				$status = "success";
			}
		}else{
			$isValidName = 0;
			$status = "success";
		}
	} catch (Exception $e) {
	    $status = 'error';
	}


	echo json_encode( array( 'status' => $status, 'is_valid_name' => $isValidName) ); 
	exit;
?>
<?php
	include 'includes/php-foot-includes.php';
?>