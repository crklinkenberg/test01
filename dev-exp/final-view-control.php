<?php 
	include '../config/route.php';
	include 'sub-section-config.php';
	$status = '';
	$message = '';
	$resultData = array();
	$dbUserFinalView = "alegra_new_repertory_user_final_view";
	$checkIfComparisonCompleteTableExist = 1;
	$checkIfComparisonConnectionTableExist = 1;
	$created = 1;
	$pre_comparison_id = (isset($_POST['pre_comparison_id']) AND $_POST['pre_comparison_id'] != "") ? $_POST['pre_comparison_id'] : "";
	try {
		if($pre_comparison_id != ""){
			$checkIfExist = mysqli_query($db,"SELECT * FROM pre_comparison_master_data  WHERE id = '".$pre_comparison_id."'");
			if(mysqli_num_rows($checkIfExist) != 0){
				$existingTableRow = mysqli_fetch_assoc($checkIfExist);
				$comparisonTable = (isset($existingTableRow['table_name']) AND $existingTableRow['table_name'] != "") ? $existingTableRow['table_name'] : "";
			}

			$completedTable = $comparisonTable."_completed";
			$connectionTable = $comparisonTable."_connections";
			//completed Table
			$searchTableQuery = "SELECT '".$completedTable."' FROM information_schema.tables WHERE table_schema = '".$dbUserFinalView."' AND table_name = '".$completedTable."' LIMIT 1";
			//$searchTableQuery = "DESCRIBE $dbUserFinalView.$completedTable";
			$checkIfComparisonCompleteTableExist = mysqli_query($db, $searchTableQuery);
			if(mysqli_num_rows($checkIfComparisonCompleteTableExist) == 0){
				$createComparasionCompleteTable = "CREATE TABLE $dbUserFinalView.$completedTable ( 
									id INT NOT NULL AUTO_INCREMENT,
									symptom_id INT NULL DEFAULT NULL,
									initial_symptom_id INT NULL DEFAULT NULL,
									is_initial_symptom ENUM ('0','1') DEFAULT '0',
									quelle_code VARCHAR(100) NULL DEFAULT NULL,
									quelle_titel VARCHAR(255) NULL DEFAULT NULL,
									quelle_type_id INT NULL DEFAULT NULL COMMENT '1 = Bücher or Quelle, 2 = Zeitschriften, 3 = Saved comparison quelle',
									quelle_jahr VARCHAR(50) NULL DEFAULT NULL COMMENT 'Year',
									quelle_band VARCHAR(10) NULL DEFAULT NULL,
									quelle_auflage VARCHAR(50) NULL DEFAULT NULL COMMENT 'edition',
									quelle_autor_or_herausgeber VARCHAR(50) NULL DEFAULT NULL COMMENT 'Author or edition',
									arznei_id INT NULL DEFAULT NULL,
									quelle_id INT NULL DEFAULT NULL,
									original_quelle_id INT NULL DEFAULT NULL,
									Symptomnummer INT NULL DEFAULT NULL,
									SeiteOriginalVon INT NULL DEFAULT NULL,
									SeiteOriginalBis INT NULL DEFAULT NULL,
									final_version_de TEXT DEFAULT NULL,
									final_version_en TEXT DEFAULT NULL,
									Beschreibung_de TEXT DEFAULT NULL COMMENT 'The whole symptom string is kept here as it is found in the doc without any modifications',
									Beschreibung_en TEXT DEFAULT NULL,
									BeschreibungOriginal_de TEXT DEFAULT NULL COMMENT 'The whole symptom string is kept here with applicable modifications (This string will remain unchange after import)',
									BeschreibungOriginal_en TEXT DEFAULT NULL,
									BeschreibungFull_de TEXT DEFAULT NULL COMMENT 'The whole symptom string is kept here with applicable modifications (This string will be use in operations, and this can get change by edit operation)',
									BeschreibungFull_en TEXT DEFAULT NULL,
									BeschreibungPlain_de TEXT DEFAULT NULL COMMENT 'The whole symptom string is kept here as plain means without any html tags',
									BeschreibungPlain_en TEXT DEFAULT NULL,
									searchable_text_de TEXT DEFAULT NULL COMMENT 'This is the searchable symptom string used in comparison. Only symptom string is kept here excluding other data pruefer, remedy, time data etc.',
									searchable_text_en TEXT DEFAULT NULL,
									bracketedString_de VARCHAR(255) NULL DEFAULT NULL,
									bracketedString_en VARCHAR(255) NULL DEFAULT NULL,
									timeString_de VARCHAR(255) NULL DEFAULT NULL,
									timeString_en VARCHAR(255) NULL DEFAULT NULL,
									initial_source_original_language VARCHAR(50) NULL DEFAULT NULL,
									comparing_source_original_language VARCHAR(50) NULL DEFAULT NULL,
									Fussnote VARCHAR(255) NULL DEFAULT NULL COMMENT 'Footnote',
									EntnommenAus VARCHAR(255) NULL DEFAULT NULL COMMENT 'Full literature reference text',
									Verweiss VARCHAR(255) NULL DEFAULT NULL,
									BereichID VARCHAR(255) NULL DEFAULT NULL,
									Kommentar TEXT DEFAULT NULL,
									Unklarheiten TEXT DEFAULT NULL,
									Remedy VARCHAR(255) NULL DEFAULT NULL,
									symptom_of_different_remedy VARCHAR(255) NULL DEFAULT NULL,
									subChapter VARCHAR(255) NULL DEFAULT NULL,
									subSubChapter VARCHAR(255) NULL DEFAULT NULL,
									synonym_word VARCHAR(1200) NULL DEFAULT NULL,
									strict_synonym VARCHAR(1200) NULL DEFAULT NULL,
									synonym_partial_1 VARCHAR(1200) NULL DEFAULT NULL,
									synonym_partial_2 VARCHAR(1200) NULL DEFAULT NULL,
									synonym_general VARCHAR(1200) NULL DEFAULT NULL,
									synonym_minor VARCHAR(1200) NULL DEFAULT NULL,
									synonym_nn VARCHAR(1200) NULL DEFAULT NULL,
									comparison_matched_synonyms VARCHAR(1200) NULL DEFAULT NULL,
									symptom_edit_comment VARCHAR(255) NULL DEFAULT NULL,
									is_final_version_available TINYINT(1) NULL DEFAULT 0 COMMENT '0 = No, 1 = Connect edit, 2 = Paste edit',
									-- symptom TEXT DEFAULT NULL,
									matched_percentage INT NULL DEFAULT NULL,
									connected_with TEXT DEFAULT NULL,
									pasted_with TEXT DEFAULT NULL,
									non_secure_connect ENUM ('0','1') DEFAULT '0',
									non_secure_paste ENUM ('0','1') DEFAULT '0',
									connect_edited INT NULL DEFAULT NULL,
									paste_edited INT NULL DEFAULT NULL,
									swap INT NULL DEFAULT NULL,
									swap_value_en TEXT DEFAULT NULL,
									swap_value_de TEXT DEFAULT NULL,
									swap_ce INT NULL DEFAULT NULL,
									swap_value_ce_en TEXT DEFAULT NULL,
									swap_value_ce_de TEXT DEFAULT NULL,
									marked ENUM ('0','1') DEFAULT '0',
									connection ENUM ('0','1') DEFAULT '0',
									ip_address VARCHAR(255) NULL DEFAULT NULL,
									stand TIMESTAMP NULL DEFAULT NULL COMMENT 'updated_at',
									bearbeiter_id INT NULL DEFAULT NULL COMMENT 'editor_id',
									ersteller_datum TIMESTAMP NULL DEFAULT NULL COMMENT 'created_at',
									ersteller_id INT NULL DEFAULT NULL COMMENT 'creator_id',
									PRIMARY KEY (id),
									INDEX (symptom_id)
								) ENGINE = InnoDB DEFAULT CHARSET = utf8";
				mysqli_query($db, $createComparasionCompleteTable);
				$copyToUserDbQuery = "INSERT INTO $dbUserFinalView.$completedTable SELECT * from $dbName.$completedTable";
				$db->query($copyToUserDbQuery);
			}else{
				$truncateComparisonCompleted="DROP TABLE $dbUserFinalView.$completedTable";
				$db->query($truncateComparisonCompleted);
			}
			//connections Table
			$searchConnectionTableQuery = "SELECT '".$connectionTable."' FROM information_schema.tables WHERE table_schema = '".$dbUserFinalView."' AND table_name = '".$connectionTable."' LIMIT 1";
			//$searchConnectionTableQuery = "DESCRIBE $dbUserFinalView.$connectionTable";
			$checkIfComparisonConnectionTableExist = mysqli_query($db, $searchConnectionTableQuery);
			if(mysqli_num_rows($checkIfComparisonConnectionTableExist) == 0){
				$createComparasionConnectionTable = "CREATE TABLE $dbUserFinalView.$connectionTable ( 
									id INT NOT NULL AUTO_INCREMENT,
									initial_symptom_id INT NULL DEFAULT NULL,
									comparing_symptom_id INT NULL DEFAULT NULL,
									connection_type ENUM ('connect', 'paste', 'CE', 'PE', 'swap','swapCE') DEFAULT NULL COMMENT 'connect, paste, CE, PE, swap for connect swap, swapCE for connect edit swap',
									matched_percentage INT NOT NULL,
									ns_connect ENUM ('0','1') DEFAULT '0' COMMENT '1 = Yes, 0 = No',
									ns_paste ENUM ('0','1') DEFAULT '0' COMMENT '1 = Yes, 0 = No',
									ns_connect_comment VARCHAR(255) NULL DEFAULT NULL,
									ns_paste_comment VARCHAR(255) NULL DEFAULT NULL,
									initial_quelle_id INT NULL DEFAULT NULL,
									comparing_quelle_id INT NULL DEFAULT NULL,
									initial_quelle_code VARCHAR(100) NULL DEFAULT NULL,
									comparing_quelle_code VARCHAR(100) NULL DEFAULT NULL,
									initial_quelle_original_language VARCHAR(20) NULL DEFAULT NULL,
									comparing_quelle_original_language VARCHAR(20) NULL DEFAULT NULL,
									highlighted_initial_symptom_de TEXT DEFAULT NULL,
									highlighted_initial_symptom_en TEXT DEFAULT NULL,
									highlighted_comparing_symptom_de TEXT DEFAULT NULL,
									highlighted_comparing_symptom_en TEXT DEFAULT NULL,
									initial_symptom_de TEXT DEFAULT NULL,
									initial_symptom_en TEXT DEFAULT NULL,
									comparing_symptom_de TEXT DEFAULT NULL,
									comparing_symptom_en TEXT DEFAULT NULL,
									comparison_language VARCHAR(20) NULL DEFAULT NULL COMMENT 'de or en',
									initial_year VARCHAR(50) NULL DEFAULT NULL COMMENT 'initial source year',
									comparing_year VARCHAR(50) NULL DEFAULT NULL COMMENT 'comparing source year',
									is_earlier_connection ENUM ('0','1') DEFAULT '0' COMMENT '1 = Yes, 0 = No',
									free_flag ENUM ('0','1') DEFAULT '0' COMMENT '1 = Yes, 0 = No',
									PRIMARY KEY (id),
									INDEX (initial_symptom_id),
									INDEX (comparing_symptom_id),
									INDEX (initial_quelle_id),
									INDEX (comparing_quelle_id)
								) ENGINE = InnoDB DEFAULT CHARSET = utf8";
				mysqli_query($db, $createComparasionConnectionTable);
				$copyConnectionToUserDbQuery = "INSERT INTO $dbUserFinalView.$connectionTable SELECT * from $dbName.$connectionTable";
				$db->query($copyConnectionToUserDbQuery);
				$updateComparisonData = "UPDATE pre_comparison_master_data SET rmm = '1' WHERE table_name = '".$comparisonTable."'";
				$db->query($updateComparisonData);
			}else{
				$truncateComparisonConnection="DROP TABLE $dbUserFinalView.$connectionTable";
				$db->query($truncateComparisonConnection);
				$updateComparisonData = "UPDATE pre_comparison_master_data SET rmm = '0' WHERE table_name = '".$comparisonTable."'";
				$db->query($updateComparisonData);
				$created = 0;
			}
			$status = 'success';
	    	$message = 'ok';
		}
	}catch (Exception $e) {
	    $status = 'error';
	    $message = 'Exception error';
	}

	echo json_encode( array( 'status' => $status, 'message' => $message, 'created' => $created) ); 
	exit;
?>