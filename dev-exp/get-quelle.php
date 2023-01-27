<?php
	include '../config/route.php';
    include 'sub-section-config.php';
	/*
	* Fetching sources/quelle of a particular arznei 
	*/
?>
<?php
	$sourceArray = array();
	if(isset($_POST['arznei_id']) AND $_POST['arznei_id'] != ""){
        $arzneiId = $_POST['arznei_id'];
        $finalArzneiArray = array();
        $finalResultSelection = mysqli_query($db,"SELECT comparison_name, arznei_id, quelle_id FROM pre_comparison_master_data WHERE arznei_id=$arzneiId");
        while($finalResultSelectionRow = mysqli_fetch_array($finalResultSelection)){
            $finalArznei = $finalResultSelectionRow['arznei_id'];
            $finalQuelleId = $finalResultSelectionRow['quelle_id'];
            $finalComparisonName = $finalResultSelectionRow['comparison_name'];

            $data['arznei_id'] = $finalArznei;
            $data['quelle_id'] = $finalQuelleId;
            $data['comparison_name'] = $finalComparisonName;
            $sourceArray[] = $data;
        }		
	}

	echo json_encode( $sourceArray ); 
	exit;
?>