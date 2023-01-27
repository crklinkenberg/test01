<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Exporting or Downloading Quelle/Souce
	*/
	// (A) LOAD PHPWORD
	require "../vendor/autoload.php";
	// Initialize class 
	$pw = new \PhpOffice\PhpWord\PhpWord();
	$masterId = (isset($_GET['mid']) AND $_GET['mid'] != "") ? $_GET['mid'] : ""; 
	$lang = (isset($_GET['lang']) AND $_GET['lang'] != "") ? $_GET['lang'] : ""; 
	if($masterId == "" OR $lang == ""){
		header('Location: '.$baseUrl);
		exit();
	}
	
	$sourceTitle = "";
	$comparisonTableName = "";

	$quelleResult = mysqli_query($db,"SELECT quelle.quelle_id, quelle.code, quelle.titel, quelle.jahr, quelle.band, quelle.nummer, quelle.auflage, quelle.quelle_type_id, quelle.autor_or_herausgeber as bucher_autor_or_herausgeber, autor.suchname as zeitschriften_autor_suchname, autor.vorname as zeitschriften_autor_vorname, autor.nachname as zeitschriften_autor_nachname, pre_comparison_master_data.table_name AS comparison_table_name, pre_comparison_master_data.comparison_name FROM quelle JOIN quelle_import_master ON quelle.quelle_id = quelle_import_master.quelle_id LEFT JOIN quelle_autor ON quelle.quelle_id = quelle_autor.quelle_id LEFT JOIN autor ON quelle_autor.autor_id = autor.autor_id LEFT JOIN pre_comparison_master_data ON quelle.quelle_id = pre_comparison_master_data.quelle_id WHERE quelle_import_master.id = '".$masterId."'");
	if(mysqli_num_rows($quelleResult) > 0){
		$quelleRow = mysqli_fetch_assoc($quelleResult);

		$sourceTitle = $quelleRow['code'];
		if($quelleRow['quelle_type_id'] != 3){
			if(!empty($quelleRow['jahr'])){
				if (mb_strpos($sourceTitle, $quelleRow['jahr']) === false)
				{
					$sourceTitle .= ', '.$quelleRow['jahr'];
  				}
			}
			if(!empty($quelleRow['titel'])) $sourceTitle .= ', '.$quelleRow['titel'];
			if($quelleRow['quelle_type_id'] == 1){
				if(!empty($quelleRow['bucher_autor_or_herausgeber'])) $sourceTitle .= ', '.$quelleRow['bucher_autor_or_herausgeber'];
			}else if($quelleRow['quelle_type_id'] == 2){
				if(!empty($quelleRow['zeitschriften_autor_suchname']) ) 
					$zeitschriften_autor = $quelleRow['zeitschriften_autor_suchname']; 
				else 
					$zeitschriften_autor = $quelleRow['zeitschriften_autor_vorname'].' '.$quelleRow['zeitschriften_autor_nachname'];
				if(!empty($zeitschriften_autor)) $sourceTitle .= ', '.$zeitschriften_autor;
			}
			/*if(!empty($quelleRow['jahr'])) $sourceTitle .= ' '.$quelleRow['jahr'];
			if(!empty($quelleRow['band'])) $sourceTitle .= ', Band: '.$quelleRow['band'];
			if(!empty($quelleRow['nummer'])) $sourceTitle .= ', Nr.: '. $quelleRow['nummer'];
			if(!empty($quelleRow['auflage'])) $sourceTitle .= ', Auflage: '. $quelleRow['auflage'];*/
		}
		$sourceTitle = rtrim(trim($sourceTitle),",");

		$comparisonTableName = ($quelleRow['comparison_table_name'] != "") ? $quelleRow['comparison_table_name'] : "";
		if($comparisonTableName != "")
			$sourceTitle = ($quelleRow['comparison_name'] != "") ? $quelleRow['comparison_name'] : "";
	}
	if($sourceTitle != "")
	{
		$fileName = preg_replace('#[^0-9a-zA-Z]+$#', '', $sourceTitle ); // Removing unwanted character from the ending of string 
		$fileName = ($fileName != "") ? $fileName.".docx" : "Source-document.docx";
		$htmlContent = '<h1 style="font-size: 32pt; font-weight: bold; color: #f00;">'.$sourceTitle.'</h1>';
		if($comparisonTableName != ""){
			$comparisonTableCompleted = $comparisonTableName."_completed";
			$checkIfComparisonCompleteTableExist = mysqli_query($db, "SHOW TABLES LIKE '".$comparisonTableCompleted."'");
			if(mysqli_num_rows($checkIfComparisonCompleteTableExist) != 0){
				$result = mysqli_query($db,"SELECT * FROM ".$comparisonTableCompleted);
			}else{
				echo "Could not start the download, required data not found.";
				exit;
			}
		}
		else
			$result = mysqli_query($db,"SELECT * FROM quelle_import_test WHERE master_id = '".$masterId."' ORDER BY id ASC");

		// $i = 0;
		while($row = mysqli_fetch_array($result)){
			$symptom = (isset($row['Beschreibung_'.$lang]) AND $row['Beschreibung_'.$lang] != "") ? $row['Beschreibung_'.$lang] : "";
			if($symptom != ""){
				// if($i == 562){
					// echo htmlentities($symptom)."<br>";
					$symp = formatSymptomForDownload($symptom);
					// echo htmlentities($symp); exit;
					$htmlContent .= '<p>'.$symp.'</p>';
				// }
			}
			// $i++;
			// if($i >= 563)
			// 	break;

		}
		// (B) ADD HTML CONTENT
		$section = $pw->addSection();
		\PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlContent, false, false);

		// (C) SAVE TO DOCX ON SERVER
		// $pw->save($fileName, "Word2007");

		// (D) OR FORCE DOWNLOAD
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment;filename=\"".$fileName."\"");
		$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($pw, "Word2007");
		$objWriter->save("php://output");
	}else{
		header('Location: '.$baseUrl);
		exit();
	}

	include 'includes/php-foot-includes.php';
?>