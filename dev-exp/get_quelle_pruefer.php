<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Fetching the pruefers depending on the quelle/Source.
	*/
?>
<?php
	$html = '<select class="form-control" name="pruefer_id[]" id="pruefer_id" multiple="multiple" data-placeholder="Search Prüfer...">';
	if(isset($_POST['quelle_id']) AND $_POST['quelle_id'] != ""){
		$preDefinedPruefer = array();
		$quellePrueferResult = mysqli_query($db,"SELECT quelle_pruefer.pruefer_id FROM quelle_pruefer WHERE quelle_pruefer.quelle_id = '".$_POST['quelle_id']."'");
		while($preDefinedPrueferRow = mysqli_fetch_array($quellePrueferResult)){
			$preDefinedPruefer[] = $preDefinedPrueferRow['pruefer_id'];
		}
		$prueferResult = mysqli_query($db,"SELECT pruefer_id, titel, vorname, nachname FROM pruefer");
		while($prueferRow = mysqli_fetch_array($prueferResult)){
			$prueferFullname = "";
			$prueferFullname .= ($prueferRow['titel'] != "") ? $prueferRow['titel']." " : "";
			$prueferFullname .= ($prueferRow['vorname'] != "") ? $prueferRow['vorname']." " : "";
			$prueferFullname .= ($prueferRow['nachname'] != "") ? $prueferRow['nachname'] : "";
			if(trim($prueferFullname) != ""){
				if(in_array($prueferRow['pruefer_id'], $preDefinedPruefer))
					$html .= '<option value="'.$prueferRow['pruefer_id'].'" selected>'.$prueferFullname.'</option>';
				else
					$html .= '<option value="'.$prueferRow['pruefer_id'].'">'.$prueferFullname.'</option>';
			}
		}
	}
	$html .= '</select>';
	$html .= '<span class="error-text"></span>';
	echo $html;
?>