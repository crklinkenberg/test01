<?php 
include '../config/route.php';
include 'sub-section-config.php';

$savedConnections = (isset($_POST['savedConnections']) AND $_POST['savedConnections'] != "") ? $_POST['savedConnections'] : "";

// Getting main comparison data array from session
$comparisonTableDataArr = (isset($_SESSION['comparison_table_data']) AND !empty($_SESSION['comparison_table_data'])) ? $_SESSION['comparison_table_data'] : array(); 
// Comparison table don't exist in DB then the session data and other required data empty. 
$comparisonTable = (isset($comparisonTableDataArr['comparison_table']) AND $comparisonTableDataArr['comparison_table'] != "") ? $comparisonTableDataArr['comparison_table'] : ""; 
$comparisonSavedDataTable = $comparisonTable."_connections";
$arrayToSend = array();
if($savedConnections != ""){
	foreach ($savedConnections as $key => $value) {
		$savedConnectionsQuery = mysqli_query($db, "SELECT id,connection_type,is_earlier_connection FROM $comparisonSavedDataTable WHERE `comparing_symptom_id` = $value");
		if(mysqli_num_rows($savedConnectionsQuery) > 0){
			while($row = mysqli_fetch_array($savedConnectionsQuery)){
				if($row['is_earlier_connection']=='1')
				{
					$valueFromRow = $row['connection_type'];
					$connectionToCheck = array('paste','PE');
					if(!in_array($valueFromRow,$connectionToCheck))
					{
						$data = array(
							'symptom_id'=> $value,
							'message'=> 'Earler connection '.$row['connection_type']
						);
					}
					else
					{
						$data = array(
							'symptom_id'=> '',
							'message'=> 'Earler connection '.$row['connection_type']
						);
					}
				}
				else
				{
					$data = array(
						'symptom_id'=> $value,
						'message'=> 'Not Earler connection'

					);
				}
			}
			
			array_push($arrayToSend, $data);

		}
	}
}
echo json_encode( array('result_data' => $arrayToSend)); 

 ?>