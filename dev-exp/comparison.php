<?php
    include '../lang/GermanWords.php';
    include '../config/route.php';
    include 'sub-section-config.php';
    include '../api/mainCall.php';
?>
<?php
    //new comparison button
    if(isset($_POST['new_comparison_btn']))
    {
        $_SESSION['comparison_table_data'] = array();
        header("Location: ".$baseUrl."comparison.php");
        die();
    }
    $globalStopWords = array();
    $globalStopWords = getStopWords();
    $totalCount = 0;
    $debugArrayFinal = array();

    //flag to control comment and footnote modals
    $finalView = 0;
    if(isset($_GET['comid']) AND $_GET['comid'] != ""){
        $checkIfExist = mysqli_query($db,"SELECT * FROM pre_comparison_master_data  WHERE id = '".$_GET['comid']."'");
        if(mysqli_num_rows($checkIfExist) != 0){
            $existingTableRow = mysqli_fetch_assoc($checkIfExist);
            $arzneiId = (isset($existingTableRow['arznei_id']) AND $existingTableRow['arznei_id'] != "") ? $existingTableRow['arznei_id'] : "";
            $initialSourceId = (isset($existingTableRow['initial_source']) AND $existingTableRow['initial_source'] != "") ? $existingTableRow['initial_source'] : "";
            $comparingSourceIds = (isset($existingTableRow['comparing_sources']) AND !empty($existingTableRow['comparing_sources'])) ? $existingTableRow['comparing_sources'] : array();
            if(!empty($comparingSourceIds) AND !is_array($comparingSourceIds))
                $comparingSourceIds = explode(",", $comparingSourceIds);
            $comparingSourcesInsertString = (!empty($comparingSourceIds)) ? implode(",", $comparingSourceIds) : "";
            $similarityRate = (isset($existingTableRow['similarity_rate']) AND $existingTableRow['similarity_rate'] != "") ? $existingTableRow['similarity_rate'] : 20;
            $comparisonOption = (isset($existingTableRow['comparison_option']) AND $existingTableRow['comparison_option'] != "") ? $existingTableRow['comparison_option'] : 1;
            $comparisonLanguage = (isset($existingTableRow['comparison_language']) AND $existingTableRow['comparison_language'] != "") ? $existingTableRow['comparison_language'] : "";
            $per_page_initial_symptom_number = (isset($existingTableRow['per_page_initial_symptom_number']) AND $existingTableRow['per_page_initial_symptom_number'] != "") ? $existingTableRow['per_page_initial_symptom_number'] : 5;
            $comparisonTable = (isset($existingTableRow['table_name']) AND $existingTableRow['table_name'] != "") ? $existingTableRow['table_name'] : "";
            // Comparison only initials table name
            $comparisonOnlyInitialTable = $comparisonTable."_initials";

            // Storing comparison table data in sesssion Start
            $_SESSION['comparison_table_data'] = array();
            $tempData = array();
            $tempData['arznei_id'] = $arzneiId;
            $tempData['initial_source'] = $initialSourceId;
            $tempData['comparing_sources'] = $comparingSourceIds;
            $tempData['similarity_rate'] = $similarityRate;
            $tempData['comparison_option'] = $comparisonOption;
            $tempData['comparison_language'] = $comparisonLanguage;
            $tempData['comparison_table'] = $comparisonTable;
            $tempData['comparison_only_initial_table'] = $comparisonOnlyInitialTable;
            $tempData['per_page_initial_symptom_number'] = $per_page_initial_symptom_number;
            $tempData['is_opened_a_saved_comparison'] = 1;

            $_SESSION['comparison_table_data'] = $tempData;
            // Storing comparison table data in sesssion End
        }
    }

    //role of supervisor or editor. editor=1 | supervisor=2
    $role = 1;

    //editing of non secure
    $editingNs = (isset($_GET['ns_editing']) AND $_GET['ns_editing'] != "") ? $_GET['ns_editing'] : 0;
    // Getting main comparison data array from session
    $comparisonTableDataArr = (isset($_SESSION['comparison_table_data']) AND !empty($_SESSION['comparison_table_data'])) ? $_SESSION['comparison_table_data'] : array(); 

    $is_opened_a_saved_comparison = (isset($comparisonTableDataArr['is_opened_a_saved_comparison']) AND !empty($comparisonTableDataArr['is_opened_a_saved_comparison'])) ? $comparisonTableDataArr['is_opened_a_saved_comparison'] : "";

    // Comparison table don't exist in DB then the session data and other required data empty. 
    $comparisonTable = (isset($comparisonTableDataArr['comparison_table']) AND $comparisonTableDataArr['comparison_table'] != "") ? $comparisonTableDataArr['comparison_table'] : ""; 

    if($comparisonTable != ""){
        $checkIfComparisonTableExist = $db->prepare("SHOW TABLES LIKE '".$comparisonTable."'");
        $checkIfComparisonTableExist->execute();
        $checkIfComparisonTableExist->store_result();
        if($checkIfComparisonTableExist->num_rows == 0){
            $_SESSION['comparison_table_data'] = array();
            $comparisonTable = "";
            $comparisonTableDataArr = array();
        }
    }

    $savedComparisonComparingSourceIdsCommaSeparated = "";
    $comparisonTableStatus = "";
    $showProgressMsgForTable = "";
    $error_msg = array();

    //unmarked initials checked
    $unmarkedInitialsCheck = 0;
    $unmarkedInitialsCheck = (isset($_GET['unmarked_initials_check']) AND $_GET['unmarked_initials_check'] != "") ? $_GET['unmarked_initials_check'] : 0;


    $arzneiId = (isset($comparisonTableDataArr['arznei_id']) AND $comparisonTableDataArr['arznei_id'] != "") ? $comparisonTableDataArr['arznei_id'] : "";
    $initialSourceId = (isset($comparisonTableDataArr['initial_source']) AND $comparisonTableDataArr['initial_source'] != "") ? $comparisonTableDataArr['initial_source'] : "";
    $comparingSourceIds = (isset($comparisonTableDataArr['comparing_sources']) AND !empty($comparisonTableDataArr['comparing_sources'])) ? $comparisonTableDataArr['comparing_sources'] : array();
    if(!empty($comparingSourceIds) AND !is_array($comparingSourceIds))
        $comparingSourceIds = explode(",", $comparingSourceIds);
    $comparingSourcesInsertString = (!empty($comparingSourceIds)) ? implode(",", $comparingSourceIds) : "";

    // Fetching all involved source Ids(for combined/saved sources)
    $allInvolvedSourcesIdsOfComparingSourceArr = array();
    if(!empty($comparingSourceIds)){
        $returnedIds = getAllComparedSourceIdsFromNewTable($comparingSourceIds);
        if(!empty($returnedIds)){
            foreach ($returnedIds as $IdVal) {
                if(!in_array($IdVal, $allInvolvedSourcesIdsOfComparingSourceArr))
                    array_push($allInvolvedSourcesIdsOfComparingSourceArr, $IdVal);
            }
        }	
    }
    $allInvolvedSourcesIdsOfComparingSource = (!empty($allInvolvedSourcesIdsOfComparingSourceArr)) ? implode(',', $allInvolvedSourcesIdsOfComparingSourceArr) : "";


    $similarityRate = (isset($comparisonTableDataArr['similarity_rate']) AND $comparisonTableDataArr['similarity_rate'] != "") ? $comparisonTableDataArr['similarity_rate'] : 20;
    $comparisonOption = (isset($comparisonTableDataArr['comparison_option']) AND $comparisonTableDataArr['comparison_option'] != "") ? $comparisonTableDataArr['comparison_option'] : 1;
    $comparisonLanguage = (isset($comparisonTableDataArr['comparison_language']) AND $comparisonTableDataArr['comparison_language'] != "") ? $comparisonTableDataArr['comparison_language'] : "";
    $comparisonTable = (isset($comparisonTableDataArr['comparison_table']) AND $comparisonTableDataArr['comparison_table'] != "") ? $comparisonTableDataArr['comparison_table'] : "";
    $perPageInitialSymptomNumber = (isset($comparisonTableDataArr['per_page_initial_symptom_number']) AND $comparisonTableDataArr['per_page_initial_symptom_number'] != "") ? $comparisonTableDataArr['per_page_initial_symptom_number'] : 5;
    $comparisonSavedDataTable = (isset($comparisonTableDataArr['comparison_table']) AND $comparisonTableDataArr['comparison_table'] != "") ? $comparisonTableDataArr['comparison_table']."_connections" : "";
    // checking if table insertion is complete
    if($comparisonTable != ""){
        $checkIfExist = $db->prepare("SELECT id, table_name, status FROM pre_comparison_master_data  WHERE table_name = ?");
        $checkIfExist->bind_param("s",$comparisonTable);
        $checkIfExist->execute();
        $checkIfExist->store_result();
        $checkIfExist->bind_result($masterDataID,$masterDataName, $masterDataStatus);
        $checkIfExist->fetch();
        if($checkIfExist->num_rows != 0){
            $comparisonTableStatus = $masterDataStatus;
            if($comparisonTableStatus == "processing")
                $showProgressMsgForTable = $masterDataName;
        }

        //Array Declaration for saved connection loading
        $singleConnectionsInitials = array();
        $singleConnectionsComparative = array();
        $combinedConnectionsInitials = array();
        $combinedConnectionsComparative = array();

        //comments, footnote and translations on page load
        $dataOnLoad = mysqli_query($db, "SELECT `id` as symptom_id,`Kommentar`,`Fussnote`,`searchable_text_de`,`searchable_text_en`,`BeschreibungFull_de`,`BeschreibungFull_en` FROM quelle_import_test WHERE `arznei_id`=$arzneiId");
        if(mysqli_num_rows($dataOnLoad) > 0){
            while($onLoadFetchedData = mysqli_fetch_array($dataOnLoad)){
                $commentsString = "";
                $footnoteString = "";
                $translationDataString = "";
                $symptomString_de = "";
                $symptomString_en = "";

                if($onLoadFetchedData['Kommentar'] != "")
                    $commentsString .= $onLoadFetchedData['symptom_id']; 
                if($commentsString != "")
                        $commentsDataArray[] = "'".$commentsString."'"; 
                if($onLoadFetchedData['Fussnote'] != "")
                    $footnoteString .= $onLoadFetchedData['symptom_id']; 
                if($footnoteString != "")
                        $footnoteDataArray[] = "'".$footnoteString."'"; 

                if($comparisonOption == 1){
                    $symptomString_de =  ($onLoadFetchedData['searchable_text_de'] != "") ? $onLoadFetchedData['searchable_text_de'] : "";
                    $symptomString_en =  ($onLoadFetchedData['searchable_text_en'] != "") ? $onLoadFetchedData['searchable_text_en'] : "";
                }else{
                    $symptomString_de =  ($onLoadFetchedData['BeschreibungFull_de'] != "") ? $onLoadFetchedData['BeschreibungFull_de'] : "";
                    $symptomString_en =  ($onLoadFetchedData['BeschreibungFull_en'] != "") ? $onLoadFetchedData['BeschreibungFull_en'] : "";
                }
                if($comparisonLanguage=="en")
                {
                    if($symptomString_de != "")
                        $translationDataString .= $onLoadFetchedData['symptom_id'];
                }
                else
                {
                    if($symptomString_en != "")
                        $translationDataString .= $onLoadFetchedData['symptom_id'];
                }
                
                if($translationDataString != "")
                    $translationDataArray[] = "'".$translationDataString."'";

            }
        }

        //general non secure on load
        $genNsOnLoadQuery = mysqli_query($db, "SELECT symptom_id FROM $comparisonTable WHERE `is_initial_symptom`= '1' AND `gen_ns` = '1'");
        if(mysqli_num_rows($genNsOnLoadQuery) > 0){
            while($genNsOnLoadData = mysqli_fetch_array($genNsOnLoadQuery)){
                $genNsOnLoadString = "";
                $genNsOnLoadString .= $genNsOnLoadData['symptom_id'];
                if($genNsOnLoadString != "")
                    $genNsOnLoadArray[] = "'".$genNsOnLoadString."'";  
            }
        }
    }

    $genNsOnLoad = (!empty($genNsOnLoadArray)) ? implode(',', $genNsOnLoadArray) : "";
    $commentsOnLoad = (!empty($commentsDataArray)) ? implode(',', $commentsDataArray) : "";
    $footnoteOnLoad = (!empty($footnoteDataArray)) ? implode(',', $footnoteDataArray) : "";
    $translations = (!empty($translationDataArray)) ? implode(',', $translationDataArray) : "";

    $savedSwapArray = array();
    $savedConnectionsComparativeIdsArray = array();
    $savedSortedIdsArray = array();
    $pastedIdInfoArray = array();
    $dataPastedId = array();
    $comparisonDataInfo = array();
    $savedPasteEditIdArray = array();
    $savedConnectIdArray = array();
    $savedNonSecureIdArray = array();
    $savedSwappedIdArray = array();
    $swapComparingIdsArray = array();
    $swapInitialIdsArray = array();
    $savedNonSecureInitialIdArray = array();
    $markSymptomIds = array();
    $zeroComparativeIds = array();
    $unmarkedSymptomsArray = array();
    $customizedInitialArray = array();
    if($comparisonSavedDataTable != ""){
        
        //Fetching Connection Arrays
        $saveTableCheck = mysqli_query($db,"SHOW TABLES LIKE '".$comparisonSavedDataTable."'");
        if(mysqli_num_rows($saveTableCheck) > 0){
            $savedConnectionResult = mysqli_query($db, "SELECT `comparing_symptom_id`,`initial_symptom_id`,`connection_type`,`ns_connect`,`ns_paste`,`is_earlier_connection` FROM $comparisonSavedDataTable");
            if(mysqli_num_rows($savedConnectionResult) > 0){
                $count=0;
                while($savedData = mysqli_fetch_array($savedConnectionResult)){
                    //PasteEdit Symptoms are inserted separatly.
                    if($savedData['connection_type']=='PE')
                        array_push($savedPasteEditIdArray,$savedData['comparing_symptom_id']);

                    //paste and paste edit ids are taken in a separate array for sorting 
                    if($savedData['connection_type']=='paste' OR $savedData['connection_type']=='PE'){
                        if($savedData['is_earlier_connection']=='0'){
                            array_push($savedConnectionsComparativeIdsArray,$savedData['comparing_symptom_id']);
                        }

                        $pastedIdInfoArray['initial_symptom_id'] = $savedData['initial_symptom_id'];
                        $pastedIdInfoArray['comparing_symptom_id'] = $savedData['comparing_symptom_id'];
                        $pastedIdInfoArray['is_earlier_connection'] = $savedData['is_earlier_connection'];
                        array_push($dataPastedId,$pastedIdInfoArray);
                    }

                    //Connect and connect edit Symptoms are inserted separatly.
                    if($savedData['connection_type']=='CE' || $savedData['connection_type']=='connect'){
                        $savedConnectIdArray[$count]['initial_symptom_id'] = $savedData['initial_symptom_id'];
                        $savedConnectIdArray[$count]['comparing_symptom_id'] = $savedData['comparing_symptom_id'];
                        $savedConnectIdArray[$count]['connection_type'] = $savedData['connection_type'];
                        $count++;
                    }

                    //Non secure connect and paste are inserted separatly.
                    if($savedData['ns_connect']=='1' || $savedData['ns_paste']=='1'){
                        array_push($savedNonSecureIdArray,$savedData['comparing_symptom_id']);
                        array_push($savedNonSecureInitialIdArray,$savedData['initial_symptom_id']);
                    }

                    //Swap connections are inserted separatly.
                    if($savedData['connection_type']=='swap' || $savedData['connection_type']=='swapCE'){
                        $savedSwappedIdArray[$count]['initial_symptom_id'] = $savedData['initial_symptom_id'];
                        $savedSwappedIdArray[$count]['comparing_symptom_id'] = $savedData['comparing_symptom_id'];
                        $savedSwappedIdArray[$count]['connection_type'] = $savedData['connection_type'];
                        array_push($swapComparingIdsArray,$savedData['comparing_symptom_id']);
                        array_push($swapInitialIdsArray,$savedData['initial_symptom_id']);
                        $count++;
                    }		
                }
                $savedPasteEditIdArray = (isset($savedPasteEditIdArray) AND !empty($savedPasteEditIdArray)) ? array_unique($savedPasteEditIdArray) : "";
                $savedNonSecureIdArray = (isset($savedNonSecureIdArray) AND !empty($savedNonSecureIdArray)) ? array_unique($savedNonSecureIdArray) : "";
                $savedNonSecureInitialIdArray = (isset($savedNonSecureInitialIdArray) AND !empty($savedNonSecureInitialIdArray)) ? array_unique($savedNonSecureInitialIdArray) : "";
            }
        }
    }
    // Saving the comparison start
    if(isset($_POST['save_comparison']) AND $comparisonTable != "")
    {	
        $temp_unmark_check= (isset($_POST['temp_unmark_check']) AND $_POST['temp_unmark_check'] != "") ? $_POST['temp_unmark_check'] : "1";
        $similarity_rate_save= (isset($_POST['similarity_rate_save']) AND $_POST['similarity_rate_save'] != "") ? $_POST['similarity_rate_save'] : "";

        if($temp_unmark_check != 0){
            $unmarkedInitialsCheck = $temp_unmark_check;
        }else{
            if($role == 1){
                //editor
                $PCMInfoQuery = $db->query("SELECT quelle_id FROM pre_comparison_master_data WHERE table_name = '".$comparisonTable."'");
                if($PCMInfoQuery->num_rows > 0){
                    $PCMInfoData = mysqli_fetch_assoc($PCMInfoQuery);
                    if($PCMInfoData['quelle_id'] != ""){
                        // 0 = Initial stage when compared(Blue), 1 = State when user saved comparison(Yellow), 2 = State when admin approved the saved comparison(Green)
                        $updateComparisonData = "UPDATE pre_comparison_master_data SET comparison_save_status = 1 WHERE table_name = '".$comparisonTable."'";
                        $db->query($updateComparisonData);

                        $updateQuelleData = "UPDATE quelle SET comparison_save_status = 1 WHERE quelle_id = '".$PCMInfoData['quelle_id']."'";
                        $db->query($updateQuelleData);
                        // Clearing the session comparison data, as it has been saved.
                        $_SESSION['comparison_table_data'] = array();
                    }
                }
            }else{
                //supervisor
                if($similarity_rate_save!= ""){
                    $totalUnmarkedNoComparatives = unmarkedSymptoms($db, $comparisonTable, $similarity_rate_save,1);
                    if($totalUnmarkedNoComparatives == 0){
                        //rearranging the symptoms as per order
                        $savedSortedIdsArray = rearrangeSymptomsInOrder($db,$comparisonTable,$savedConnectionsComparativeIdsArray,$dataPastedId,$swapComparingIdsArray,$swapInitialIdsArray);
                        //ids are then selected and kept for insertion in completed table 
                        if(!empty($savedSortedIdsArray)){
                            foreach($savedSortedIdsArray as $id){
                                $comparisonTableResult = mysqli_query($db, "SELECT * FROM $comparisonTable WHERE symptom_id = $id");
                                $comparisonTableSortedData = mysqli_fetch_assoc($comparisonTableResult);
                                array_push($comparisonDataInfo, $comparisonTableSortedData);
                            }
                        }

                        if(!empty($savedSortedIdsArray)){
                            // Creating Comparasion Completed table
                            $comparisonTableCompleted = $comparisonTable."_completed";
                            $checkIfComparisonCompleteTableExist = mysqli_query($db, "SHOW TABLES LIKE '".$comparisonTableCompleted."'");
                            if(mysqli_num_rows($checkIfComparisonCompleteTableExist) == 0){
                                $createComparasionCompleteTable = "CREATE TABLE $comparisonTableCompleted ( 
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
                                                    is_excluded_in_comparison TINYINT(1) NULL DEFAULT 0 COMMENT 'Checking is symptom excluded in the comparison process',
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
                                                    chapter VARCHAR(255) NULL DEFAULT NULL,
                                                    PRIMARY KEY (id),
                                                    INDEX (symptom_id),
                                                    INDEX (chapter)
                                                ) ENGINE = InnoDB DEFAULT CHARSET = utf8";
                                mysqli_query($db, $createComparasionCompleteTable);
                            }else{
                                $truncateComparisonCompleted="TRUNCATE TABLE ".$comparisonTableCompleted;
                                $db->query($truncateComparisonCompleted);
                            }

                            $quelleIdArray = array();
                            $PCMInfoQuery = $db->query("SELECT quelle_id FROM pre_comparison_master_data WHERE table_name = '".$comparisonTable."'");
                            if($PCMInfoQuery->num_rows > 0){
                                $PCMInfoData = mysqli_fetch_assoc($PCMInfoQuery);
                            }
                            $newQuelleIdForCompleteTable = (isset($PCMInfoData['quelle_id']) AND $PCMInfoData['quelle_id'] != "") ? $PCMInfoData['quelle_id'] : ""; 

                            if($newQuelleIdForCompleteTable != ""){
                                //insertion in completed table
                                foreach($comparisonDataInfo as $comparisonTableData){
                                    $updateInitialArr['SeiteOriginalVon'] = mysqli_real_escape_string($db, $comparisonTableData['SeiteOriginalVon']);
                                    $updateInitialArr['SeiteOriginalBis'] = mysqli_real_escape_string($db, $comparisonTableData['SeiteOriginalBis']);
                                    $updateInitialArr['final_version_en'] = mysqli_real_escape_string($db, $comparisonTableData['final_version_en']);
                                    $updateInitialArr['final_version_de'] = mysqli_real_escape_string($db, $comparisonTableData['final_version_de']);
                                    $updateInitialArr['Beschreibung_de'] = mysqli_real_escape_string($db, $comparisonTableData['Beschreibung_de']);
                                    $updateInitialArr['Beschreibung_en'] = mysqli_real_escape_string($db, $comparisonTableData['Beschreibung_en']);
                                    $updateInitialArr['BeschreibungOriginal_de'] = mysqli_real_escape_string($db, $comparisonTableData['BeschreibungOriginal_de']);
                                    $updateInitialArr['BeschreibungOriginal_en'] = mysqli_real_escape_string($db, $comparisonTableData['BeschreibungOriginal_en']);
                                    $updateInitialArr['BeschreibungFull_de'] = mysqli_real_escape_string($db, $comparisonTableData['BeschreibungFull_de']);
                                    $updateInitialArr['BeschreibungFull_en'] = mysqli_real_escape_string($db, $comparisonTableData['BeschreibungFull_en']);
                                    $updateInitialArr['BeschreibungPlain_de'] = mysqli_real_escape_string($db, $comparisonTableData['BeschreibungPlain_de']);
                                    $updateInitialArr['BeschreibungPlain_en'] = mysqli_real_escape_string($db, $comparisonTableData['BeschreibungPlain_en']);
                                    $updateInitialArr['searchable_text_de'] = mysqli_real_escape_string($db, $comparisonTableData['searchable_text_de']);
                                    $updateInitialArr['searchable_text_en'] = mysqli_real_escape_string($db, $comparisonTableData['searchable_text_en']);
                                    $updateInitialArr['is_excluded_in_comparison'] = mysqli_real_escape_string($db, $comparisonTableData['is_excluded_in_comparison']);

                                    $insertComparisonCompleted="INSERT INTO $comparisonTableCompleted (symptom_id, is_initial_symptom, quelle_code, quelle_titel, quelle_type_id, quelle_jahr, quelle_band, quelle_auflage, quelle_autor_or_herausgeber, arznei_id, quelle_id, original_quelle_id, Symptomnummer, SeiteOriginalVon, SeiteOriginalBis, final_version_de, final_version_en, Beschreibung_de, Beschreibung_en, BeschreibungOriginal_de, BeschreibungOriginal_en, BeschreibungFull_de, BeschreibungFull_en, BeschreibungPlain_de, BeschreibungPlain_en, searchable_text_de, searchable_text_en, bracketedString_de, bracketedString_en, timeString_de, timeString_en, initial_source_original_language, Fussnote, EntnommenAus, Verweiss, BereichID, Kommentar, Unklarheiten, Remedy, symptom_of_different_remedy, subChapter, subSubChapter, synonym_word, strict_synonym, synonym_partial_1, synonym_partial_2, synonym_general, synonym_minor, synonym_nn, comparison_matched_synonyms, symptom_edit_comment, is_excluded_in_comparison, is_final_version_available, matched_percentage, ersteller_datum) VALUES (NULLIF('".$comparisonTableData['symptom_id']."', ''), NULLIF('".$comparisonTableData['is_initial_symptom']."', ''), NULLIF('".$comparisonTableData['quelle_code']."', ''), NULLIF('".$comparisonTableData['quelle_titel']."', ''), NULLIF('".$comparisonTableData['quelle_type_id']."', ''), NULLIF('".$comparisonTableData['quelle_jahr']."', ''), NULLIF('".$comparisonTableData['quelle_band']."', ''), NULLIF('".$comparisonTableData['quelle_auflage']."', ''), NULLIF('".$comparisonTableData['quelle_autor_or_herausgeber']."', ''), NULLIF('".$comparisonTableData['arznei_id']."', ''), NULLIF('".$newQuelleIdForCompleteTable."', ''), NULLIF('".$comparisonTableData['original_quelle_id']."', ''), NULLIF('".$comparisonTableData['Symptomnummer']."', ''), NULLIF('".$updateInitialArr['SeiteOriginalVon']."', ''), NULLIF('".$updateInitialArr['SeiteOriginalBis']."', ''), NULLIF('".$updateInitialArr['final_version_de']."', ''), NULLIF('".$updateInitialArr['final_version_en']."', ''), NULLIF('".$updateInitialArr['Beschreibung_de']."', ''), NULLIF('".$updateInitialArr['Beschreibung_en']."', ''), NULLIF('".$updateInitialArr['BeschreibungOriginal_de']."', ''), NULLIF('".$updateInitialArr['BeschreibungOriginal_en']."', ''), NULLIF('".$updateInitialArr['BeschreibungFull_de']."', ''), NULLIF('".$updateInitialArr['BeschreibungFull_en']."', ''), NULLIF('".$updateInitialArr['BeschreibungPlain_de']."', ''), NULLIF('".$updateInitialArr['BeschreibungPlain_en']."', ''), NULLIF('".$updateInitialArr['searchable_text_de']."', ''), NULLIF('".$updateInitialArr['searchable_text_en']."', ''), NULLIF('".$comparisonTableData['bracketedString_de']."', ''), NULLIF('".$comparisonTableData['bracketedString_en']."', ''), NULLIF('".$comparisonTableData['timeString_de']."', ''), NULLIF('".$comparisonTableData['timeString_en']."', ''), NULLIF('".$comparisonTableData['initial_source_original_language']."', ''), NULLIF('".$comparisonTableData['Fussnote']."', ''), NULLIF('".$comparisonTableData['EntnommenAus']."', ''), NULLIF('".$comparisonTableData['Verweiss']."', ''), NULLIF('".$comparisonTableData['BereichID']."', ''), NULLIF('".$comparisonTableData['Kommentar']."', ''), NULLIF('".$comparisonTableData['Unklarheiten']."', ''), NULLIF('".$comparisonTableData['Remedy']."', ''), NULLIF('".$comparisonTableData['symptom_of_different_remedy']."', ''), NULLIF('".$comparisonTableData['subChapter']."', ''), NULLIF('".$comparisonTableData['subSubChapter']."', ''), NULLIF('".$comparisonTableData['synonym_word']."', ''), NULLIF('".$comparisonTableData['strict_synonym']."', ''), NULLIF('".$comparisonTableData['synonym_partial_1']."', ''), NULLIF('".$comparisonTableData['synonym_partial_2']."', ''), NULLIF('".$comparisonTableData['synonym_general']."', ''), NULLIF('".$comparisonTableData['synonym_minor']."', ''), NULLIF('".$comparisonTableData['synonym_nn']."', ''), NULLIF('".$comparisonTableData['comparison_matched_synonyms']."', ''), NULLIF('".$comparisonTableData['symptom_edit_comment']."', ''), NULLIF('".$comparisonTableData['is_excluded_in_comparison']."', ''), NULLIF('".$comparisonTableData['is_final_version_available']."', ''), NULLIF('".$comparisonTableData['matched_percentage']."', ''), NULLIF('".$date."', ''))";
                                        $db->query($insertComparisonCompleted);
                                }
                                
                                //Paste Edit Symptoms are updated 
                                if(!empty($savedPasteEditIdArray))
                                {
                                    foreach ($savedPasteEditIdArray as $index => $savePasteEditId) {
                                        $query1 = "SELECT * FROM $comparisonTable WHERE symptom_id=$savePasteEditId AND is_final_version_available=1";
                                        $comparisonTablePasteEditResult = mysqli_query($db, $query1);
                                        if(mysqli_num_rows($comparisonTablePasteEditResult) > 0){
                                            while($row = mysqli_fetch_array($comparisonTablePasteEditResult)){
                                                $fv_symptom_de = mysqli_real_escape_string($db, $row['final_version_de']);
                                                $fv_symptom_en = mysqli_real_escape_string($db, $row['final_version_en']);
                                                $is_final_version_available = $row['is_final_version_available'];
                                                //Updating the symptom row in completed table
                                                $updateRow = "UPDATE $comparisonTableCompleted SET final_version_de = NULLIF('".$fv_symptom_de."', ''), final_version_en = NULLIF('".$fv_symptom_en."', ''), is_final_version_available = '".$is_final_version_available."' WHERE symptom_id = '".$savePasteEditId."'";
                                                $updateRes = $db->query($updateRow);
                                            }

                                        }

                                    }
                                }
                                
                                //Connect and connect edit symptoms are updated
                                if(!empty($savedConnectIdArray))
                                {
                                    foreach ($savedConnectIdArray as $index => $saveConnectIdRow) {
                                        $connectInitialId = $saveConnectIdRow['initial_symptom_id'];
                                        $connectComparativeId = $saveConnectIdRow['comparing_symptom_id'];
                                        $connect_connection_type = $saveConnectIdRow['connection_type'];
                                        $connectionValue = '1';
                                        if($connect_connection_type == 'CE'){
                                            $query1 = "SELECT * FROM $comparisonTable WHERE symptom_id=$connectInitialId AND is_final_version_available=1";
                                            $comparisonTableCeResult = mysqli_query($db, $query1);
                                            if(mysqli_num_rows($comparisonTableCeResult) > 0){
                                                while($row = mysqli_fetch_array($comparisonTableCeResult)){
                                                    $fv_symptom_de = mysqli_real_escape_string($db, $row['final_version_de']);
                                                    $fv_symptom_en = mysqli_real_escape_string($db, $row['final_version_en']);
                                                    //Updating the symptom row in completed table
                                                    $updateRow = "UPDATE $comparisonTableCompleted SET final_version_de = NULLIF('".$fv_symptom_de."', ''),final_version_en = NULLIF('".$fv_symptom_en."', ''),is_final_version_available = 1 WHERE symptom_id = '".$connectInitialId."'";
                                                    $updateRes = $db->query($updateRow);
                                                }
                                                //Updating the symptom row in completed table
                                                $updateRow2 = "UPDATE $comparisonTableCompleted SET connection = NULLIF('".$connectionValue."', '') WHERE symptom_id = '".$connectComparativeId."'";
                                                $updateRes2 = $db->query($updateRow2);
                                            }
                                        }else{
                                            //if connection type is connect
                                            $updateRow2 = "UPDATE $comparisonTableCompleted SET connection = NULLIF('".$connectionValue."', '') WHERE symptom_id = '".$connectComparativeId."'";
                                            $updateRes2 = $db->query($updateRow2);
                                        }

                                    }
                                }

                                //Swap Symptoms are updated 
                                if(!empty($savedSwappedIdArray))
                                {
                                    foreach ($savedSwappedIdArray as $index => $saveSwappedIdRow) {
                                        $swappedInitialId = $saveSwappedIdRow['initial_symptom_id'];
                                        $swappedComparativeId = $saveSwappedIdRow['comparing_symptom_id'];
                                        $swap_connection_type = $saveSwappedIdRow['connection_type'];
                                        $connectionValue = '1';
                                        if($swap_connection_type == 'swap'){
                                            $query1 = "SELECT * FROM $comparisonTable WHERE symptom_id=$swappedInitialId AND swap=1";
                                            $query2 = "SELECT * FROM $comparisonTable WHERE symptom_id=$swappedComparativeId AND initial_symptom_id = $swappedInitialId AND swap=1";
                                            $comparisonTableSwappedResult = mysqli_query($db, $query1);
                                            $comparisonTableSwappedResult2 = mysqli_query($db, $query2);
                                            if(mysqli_num_rows($comparisonTableSwappedResult) > 0){
                                                while($row = mysqli_fetch_array($comparisonTableSwappedResult)){
                                                    $fv_symptom_de = mysqli_real_escape_string($db, $row['swap_value_de']);
                                                    $fv_symptom_en = mysqli_real_escape_string($db, $row['swap_value_en']);
                                                    $swap = $row['swap'];
                                                    //Updating the symptom row in completed table
                                                    $updateRow = "UPDATE $comparisonTableCompleted SET swap='1',swap_value_de = NULLIF('".$fv_symptom_de."', ''), swap_value_en = NULLIF('".$fv_symptom_en."', ''), swap = '".$swap."' WHERE symptom_id = '".$swappedInitialId."'";
                                                    $updateRes = $db->query($updateRow);
                                                }
                                            }

                                            if(mysqli_num_rows($comparisonTableSwappedResult2) > 0){
                                                while($row = mysqli_fetch_array($comparisonTableSwappedResult2)){
                                                    $fv_symptom_de = mysqli_real_escape_string($db, $row['swap_value_de']);
                                                    $fv_symptom_en = mysqli_real_escape_string($db, $row['swap_value_en']);
                                                    $swap = $row['swap'];
                                                    //Updating the symptom row in completed table
                                                    $updateRow2 = "UPDATE $comparisonTableCompleted SET connection = NULLIF('".$connectionValue."', ''),swap='1',swap_value_en = NULLIF('".$fv_symptom_en."', ''),swap_value_de = NULLIF('".$fv_symptom_de."', '') WHERE symptom_id = '".$swappedComparativeId."'";
                                                    $updateRes2 = $db->query($updateRow2);
                                                }
                                            }
                                        }else{
                                            $query1 = "SELECT * FROM $comparisonTable WHERE symptom_id=$swappedInitialId AND swap_ce=1";
                                            $query2 = "SELECT * FROM $comparisonTable WHERE symptom_id=$swappedComparativeId AND initial_symptom_id = $swappedInitialId AND swap_ce=1";
                                            $comparisonTableSwappedResult = mysqli_query($db, $query1);
                                            $comparisonTableSwappedResult2 = mysqli_query($db, $query2);
                                            if(mysqli_num_rows($comparisonTableSwappedResult) > 0){
                                                while($row = mysqli_fetch_array($comparisonTableSwappedResult)){
                                                    $fv_symptom_de = mysqli_real_escape_string($db, $row['swap_value_ce_de']);
                                                    $fv_symptom_en = mysqli_real_escape_string($db, $row['swap_value_ce_en']);
                                                    $swap_ce = $row['swap_ce'];
                                                    //Updating the symptom row in completed table
                                                    $updateRow = "UPDATE $comparisonTableCompleted SET swap_ce='1',swap_value_ce_de = NULLIF('".$fv_symptom_de."', ''), swap_value_ce_en = NULLIF('".$fv_symptom_en."', '') WHERE symptom_id = '".$swappedInitialId."'";
                                                    $updateRes = $db->query($updateRow);
                                                }
                                            }
                                            if(mysqli_num_rows($comparisonTableSwappedResult2) > 0){
                                                while($row = mysqli_fetch_array($comparisonTableSwappedResult2)){
                                                    $fv_symptom_de = mysqli_real_escape_string($db, $row['swap_value_ce_de']);
                                                    $fv_symptom_en = mysqli_real_escape_string($db, $row['swap_value_ce_en']);
                                                    $swap_ce = $row['swap_ce'];
                                                    //Updating the symptom row in completed table
                                                    $updateRow2 = "UPDATE $comparisonTableCompleted SET connection = NULLIF('".$connectionValue."', ''),swap_ce='1', swap_value_ce_de = NULLIF('".$fv_symptom_de."', ''), swap_value_ce_en = NULLIF('".$fv_symptom_en."', '') WHERE symptom_id = '".$swappedComparativeId."'";
                                                    $updateRes2 = $db->query($updateRow2);
                                                }
                                            }
                                        }

                                    }
                                }
                                    
                                $PCMInfoQuery = $db->query("SELECT quelle_id FROM pre_comparison_master_data WHERE table_name = '".$comparisonTable."'");
                                if($PCMInfoQuery->num_rows > 0){
                                    $PCMInfoData = mysqli_fetch_assoc($PCMInfoQuery);
                                    if($PCMInfoData['quelle_id'] != ""){
                                        // 0 = Initial stage when compared(Blue), 1 = State when user saved comparison(Yellow), 2 = State when admin approved the saved comparison(Green), 3 when supervisor is working in a comparison(orange)
                                        $updateComparisonData = "UPDATE pre_comparison_master_data SET comparison_save_status = 2, final_view = '1' WHERE table_name = '".$comparisonTable."'";
                                        $db->query($updateComparisonData);

                                        $updateQuelleData = "UPDATE quelle SET comparison_save_status = 2 WHERE quelle_id = '".$PCMInfoData['quelle_id']."'";
                                        $db->query($updateQuelleData);
                                        // Clearing the session comparison data, as it has been saved.
                                        $_SESSION['comparison_table_data'] = array();
                                    }
                                }

                                //Creation of json file for chapter assignment starts
                                include 'additions/chapter-structure.php';
                                function chapterHeadingEdits($string){
                                    $string = $string."Var";
                                    $stringExplode = explode(" ", $string);
                                    $stringExplodeSize = count($stringExplode);
                                    $count = 0;
                                    $headingFinal = "";
                                    while($count < $stringExplodeSize){
                                        $headingFinal = $headingFinal.ucfirst($stringExplode[$count]);
                                        $count++;
                                    }
                                    return $headingFinal;
                                }
                                $masterChapterArray = array();
                                $fileName = $comparisonTable."_chapter_data.json";
                                foreach($chapters as $mainChapter => $innerChapter){
                                    $mainHeadName = chapterHeadingEdits($mainChapter);
                                    $masterChapterArray += [$mainHeadName => array()];
                            
                                    foreach($innerChapter as $innerChapterName => $subChapters){
                                        $innerHeadName = chapterHeadingEdits($innerChapterName);
                                        $masterChapterArray += [$innerHeadName => array()];
                            
                                        foreach($subChapters as $subChapterName){
                                            $subHeadName = chapterHeadingEdits($subChapterName);
                                            $masterChapterArray += [$subHeadName => array()];
                            
                                        }
                                    }
                                } 
                                $toSent = json_encode($masterChapterArray);
                                $file = file_put_contents("chapter-data/".$fileName, $toSent, FILE_APPEND);
                                //Creation of json file for chapter assignment ends
                            }
                        }
                    }
                }
            }
            header("Location: ".$baseUrl."materia-medica.php");
            die();
        }
    }
    // Saving the comparison end
    //Custom listing of initials
    if(isset($_POST['custom_listing']) AND $comparisonTable != "")
    {
        if(isset($_POST['ns_to_sent']) AND $_POST['ns_to_sent'] != "")
            $unmarkedInitialsCheck= (isset($_POST['ns_to_sent']) AND $_POST['ns_to_sent'] != "") ? $_POST['ns_to_sent'] : "0";

        if(isset($_POST['gen_ns_to_sent']) AND $_POST['gen_ns_to_sent'] != "")
            $unmarkedInitialsCheck= (isset($_POST['gen_ns_to_sent']) AND $_POST['gen_ns_to_sent'] != "") ? $_POST['gen_ns_to_sent'] : "0";

        if(isset($_POST['ns_editing']) AND $_POST['ns_editing'] != "")
            $editingNs= (isset($_POST['ns_editing']) AND $_POST['ns_editing'] != "") ? $_POST['ns_editing'] : "0";
    }

    // Show all Connection and translation parameter
    $openIniTrans = (isset($_GET['open_ini_trans']) AND $_GET['open_ini_trans'] != "") ? $_GET['open_ini_trans'] : "";
    $openComTrans = (isset($_GET['open_com_trans']) AND $_GET['open_com_trans'] != "") ? $_GET['open_com_trans'] : "";
    $openConn = (isset($_GET['open_conn']) AND $_GET['open_conn'] != "") ? $_GET['open_conn'] : "";
    $scroll = (isset($_GET['scroll']) AND $_GET['scroll'] != "") ? $_GET['scroll'] : "";
    //Pagination 
    $page = (isset($_GET['page']) AND $_GET['page'] != "") ? $_GET['page'] : 1;//Pages
    $perPageInitialSymptomNumber = (isset($_GET['per_page_initial_symptom_number']) AND $_GET['per_page_initial_symptom_number'] != "") ? $_GET['per_page_initial_symptom_number'] : $perPageInitialSymptomNumber;//How many Initial Ids per page

    if($comparisonTable != ""){
        if($db->query("DESCRIBE $comparisonTable"))
        {
            //updating color to orange in materia medica when supervisor is working in the comparison
            if($role == 2){
                $PCMInfoQuery = $db->query("SELECT quelle_id FROM pre_comparison_master_data WHERE table_name = '".$comparisonTable."'");
                if($PCMInfoQuery->num_rows > 0){
                    $PCMInfoData = mysqli_fetch_assoc($PCMInfoQuery);
                    if($PCMInfoData['quelle_id'] != ""){
                        // 0 = Initial stage when compared(Blue), 1 = State when user saved comparison(Yellow), 2 = State when admin approved the saved comparison(Green), 3 when supervisor is working in a comparison(orange)
                        $updateComparisonData = "UPDATE pre_comparison_master_data SET comparison_save_status = 3 WHERE table_name = '".$comparisonTable."'";
                        $db->query($updateComparisonData);

                        $updateQuelleData = "UPDATE quelle SET comparison_save_status = 3 WHERE quelle_id = '".$PCMInfoData['quelle_id']."'";
                        $db->query($updateQuelleData);
                    }
                }
            }
            switch($unmarkedInitialsCheck){
                case '1':{
                    $customizedInitialArray = markingInitialArray($db,$comparisonTable,$similarityRate);
                    $count = count($customizedInitialArray);
                    break;
                }
                case '2':{
                    $customizedInitialArray = generalNsArray($db,$comparisonTable);
                    $count = count($customizedInitialArray);
                    break;
                }
                case '3':{
                    $customizedInitialArray = connectNsArray($db,$comparisonTable);
                    $count = count($customizedInitialArray);
                    break;
                }
                case '4':{
                    $customizedInitialArray = pastetNsArray($db,$comparisonTable);
                    $count = count($customizedInitialArray);
                    break;
                }
                case '5':{
                    $customizedInitialArray = customNsListings($db,$comparisonTable);
                    $count = count($customizedInitialArray);
                    break;
                }
                default:{
                    $count = mysqli_query($db, "SELECT `symptom_id` FROM $comparisonTable WHERE is_initial_symptom = '1' AND connection = '0'");
                    break;
                }
            }
            if($unmarkedInitialsCheck != 0){
                $totalCount = $count;
            }else{
                if(mysqli_num_rows($count) > 0){
                    $totalCount = mysqli_num_rows($count);
                }
            }
            if($totalCount == 0){
                header("Location: ".$baseUrl."comparison.php");
                die();
            }else{
                $totalPage = ceil($totalCount/$perPageInitialSymptomNumber);
                $startFrom = ($page-1) * $perPageInitialSymptomNumber; 
            }
        }
    }
    //restricting initial symptoms per page to "1" when matched percentage is "0"
    if($similarityRate == 0)
        $perPageInitialSymptomNumber = 1;
?>
<?php
    include '../inc/header.php';
    include '../inc/sidebar.php';
?>
<!-- custom -->
<link rel="stylesheet" href="assets/css/custom-temp.css">
<!-- new comparison table style -->
<link rel="stylesheet" href="assets/css/new-comparison-table-style.css">
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
	    <h1>Comparison</h1>
	    <ol class="breadcrumb">
	    	<li><a href="<?php echo $absoluteUrl;?>"><i class="fa fa-dashboard"></i> <?php echo $home; ?></a></li>
	    	<li class="active">Comparison</li>
	    </ol>
	</section>

  	<!-- Main content -->
  	<section class="content">
    <!-- Small boxes (Stat box) -->
		<div class="row">
			<div class="col-md-12">
				<div class="box box-success">
					<?php //if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 1 || $_SESSION['user_type'] == 2)) { ?>
            		<!-- <div class="box-header with-border">
		              	<h3 class="box-title">
		              		<a href="<#" class="btn btn-success"><i class="fa fa-plus"></i> &nbsp; Add</a>
		              	</h3>
	            	</div> -->
			     	<?php  //} ?>
		    		<!-- /.box-header -->
		    		<div class="box-body">
                        <div id="comparison_table_overlay" class="hidden">
                            <div class="overlayBody">
                                <p>Our record shows that you have not compared these two sources before.</p>
                                <p>Please wait while we process the comparison.</p>
                                <img width="25px" src="../assets/img/loader.gif" alt="Loading...">
                            </div>
                        </div>
                        <div id="comparison_loader" class="hidden">
                            <div class="overlayLoaderBody">
                                <p>Please wait. Comparison is loading.. <img src="../assets/img/loader.gif" alt="Loading..."></p>
                            </div>
                        </div>
                        <div id="common_small_loader" class="hidden">
                            <div class="overlayLoaderBody">
                                <p>Please wait. Data is loading.. <img src="../assets/img/loader.gif" alt="Loading..."></p>
                            </div>
                        </div>
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-sm-12">
                                    <ul class="comparison-navigation-ul">
                                        <li class="pull-right">
                                            <form action="" method="POST">
                                                <button type="submit" class="btn btn-success" name="new_comparison_btn">New Comparison</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="row">
                                <!-- Search and comparison tab html -->
                                <?php include 'search-and-comparison-tab-html.php'; ?>
                                <!-- Search and comparison tab html -->
                            </div>
                            <div class="row">
                                <div class="col-sm-2">	
                                </div>
                                <div class="col-sm-4"></div>
                                <div class="col-sm-6">
                                    <div class="row">
                                        <div class="col-sm-6">
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="row control-panel-btns">
                                                <div class="col-md-3">
                                                    <form id="saveSubmitGenNs" action="<?php echo $baseUrl?>comparison.php" method="POST">
                                                        <input type="hidden" name="custom_listing">
                                                        <input type="hidden" name="gen_ns_to_sent" id="gen_ns_to_sent" value="2">
                                                        <input type="hidden" name="ns_editing" id="ns_editing" value="1">
                                                        <button type="button" class="btn gen-ns-btn <?php if($unmarkedInitialsCheck == 2) echo "ns-g-active"?>">G-NS</button>
                                                    </form>
                                                </div>
                                                <div class="col-md-3">
                                                    <form id="saveSubmitNs" action="<?php echo $baseUrl?>comparison.php?open_conn=1" method="POST">
                                                        <input type="hidden" name="custom_listing">
                                                        <input type="hidden" name="ns_to_sent" id="ns_to_sent" value="3">
                                                        <input type="hidden" name="ns_editing" id="ns_editing" value="1">
                                                        <button type="button" class="btn ns-normal-btn <?php if($unmarkedInitialsCheck == 3) echo "ns-c-active"?>">NS-C</button>
                                                    </form>
                                                </div>
                                                <div class="col-md-3">
                                                    <form id="saveSubmitNsP" action="<?php echo $baseUrl?>comparison.php?open_conn=1" method="POST">
                                                        <input type="hidden" name="custom_listing">
                                                        <input type="hidden" name="ns_to_sent" id="ns_to_sent" value="4">
                                                        <input type="hidden" name="ns_editing" id="ns_editing" value="1">
                                                        <button type="button" class="btn ns-normal-btn-p <?php if($unmarkedInitialsCheck == 4) echo "ns-p-active"?>">NS-P</button>
                                                    </form>
                                                </div>
                                                <div class="col-md-3">
                                                    <form id="saveSubmitNsF" action="<?php echo $baseUrl?>comparison.php?open_conn=1" method="POST">
                                                        <input type="hidden" name="custom_listing">
                                                        <input type="hidden" name="ns_to_sent" id="ns_to_sent" value="5">
                                                        <input type="hidden" name="ns_editing" id="ns_editing" value="1">
                                                        <button type="button" class="btn ns-normal-btn-f <?php if($unmarkedInitialsCheck == 5) echo "ns-f-active"?>">NS-F</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>							
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label class="control-label" style="font-size: 12px">Number of initial symptom/page: <span class="required">*</span></label>
                                        <select class="form-control" name="per_page_initial_symptom_number" id="per_page_initial_symptom_number">
                                            <?php
                                                $j=1; 
                                                while($j<=50) { ?>
                                                <option <?php if($perPageInitialSymptomNumber == $j) { echo 'selected'; } ?> value="<?php echo $j; ?>"><?php echo $j; ?></option>
                                            <?php
                                                    $j =$j+1;
                                                    if($j>2){
                                                        $j = $j+2;
                                                    }
                                                    if($j>5){
                                                        $j= $j+2;
                                                    }
                                                } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-10">
                                    <div class="row">
                                        <div class="col-sm-11">
                                            <ul class="show-all-checkbox-container pull-right">
                                                <li>
                                                    <label class="checkbox-show-all">
                                                    <input class="all-initial-translation" name="all_initial_translation" id="all_initial_translation" type="checkbox" value="1">  Show all initial translations</label>
                                                </li>
                                                <li>
                                                    <label class="checkbox-show-all">
                                                    <input class="all-comparative-translation" name="all_comparative_translation" id="all_comparative_translation" type="checkbox" value="1">  Show all comparative translations</label>
                                                </li>
                                                <li>
                                                    <label class="checkbox-show-all">
                                                    <input class="all-connections" name="all_connections" id="all_connections_display" type="checkbox" value="1">  Show all connections</label>
                                                </li>
                                            </ul>
                                            <input type="hidden" name="open_conn_hidden_param" id="open_conn_hidden_param" value="<?php echo $openConn; ?>">
                                            <input type="hidden" name="open_ini_trans_hidden_param" id="open_ini_trans_hidden_param" value="<?php echo $openIniTrans; ?>">
                                            <input type="hidden" name="open_com_trans_hidden_param" id="open_com_trans_hidden_param" value="<?php echo $openComTrans; ?>">
                                        </div>
                                        <div class="col-sm-1">
                                            <form id="saveSubmit" action="<?php echo $baseUrl.'comparison.php';?>" method="POST">
                                                <input type="hidden" name="save_comparison" value="1">
                                                <input type="hidden" name="temp_unmark_check" id="temp_unmark_check" value="1">
                                                <input type="hidden" name="similarity_rate_save" id="similarity_rate_save" value="<?php echo $similarityRate;?>">
                                                <button type="button" class="btn comparison-table-save-btn">SAVE</button>
                                            </form>
                                        </div>
                                    </div>	
                                </div>
                            </div>

                            <div class="row">
                                <?php 
                                    switch($unmarkedInitialsCheck){
                                        case '1':{
                                            ?>
                                            <div class= "col-sm-12">
                                                <div style="text-align: center; background-color: #F1FB3A; height: 40px;">
                                                    <h4 style="padding-top: 10px;">Showing unmarked symptoms</h4>
                                                </div>
                                            </div>
                                            <?php
                                            break;
                                        }
                                        case '2':{
                                            ?>
                                            <div class= "col-sm-12">
                                                <div style="text-align: center; background-color: #F1FB3A; height: 40px;">
                                                    <h4 style="padding-top: 10px;">General non secure symptoms</h4>
                                                </div>
                                                <input type="hidden" name="check_custom_ns" id="check_custom_ns" value="1">
                                            </div>
                                            <?php
                                            break;
                                        }
                                        case '3':{
                                            ?>
                                            <div class= "col-sm-12">
                                                <div style="text-align: center; background-color: #F1FB3A; height: 40px;">
                                                    <h4 style="padding-top: 10px;">Non secure connect symptoms</h4>
                                                </div>
                                                <input type="hidden" name="check_custom_ns" id="check_custom_ns" value="1">
                                            </div>
                                            <?php
                                            break;
                                        }
                                        case '4':{
                                            ?>
                                            <div class= "col-sm-12">
                                                <div style="text-align: center; background-color: #F1FB3A; height: 40px;">
                                                    <h4 style="padding-top: 10px;">Non secure paste symptoms</h4>
                                                </div>
                                                <input type="hidden" name="check_custom_ns" id="check_custom_ns" value="1">
                                            </div>
                                            <?php
                                            break;
                                        }
                                        default:{
                                            //normal display
                                            echo '
                                            <div class= "col-sm-12">
                                                <input type="hidden" name="check_custom_ns" id="check_custom_ns" value="0">
                                            </div>';
                                            break;
                                        }
                                    }
                                ?>
                                <div class="col-sm-12 sticky-head">
                                    <div class="symptom-row heading" id="A">
                                        <div class="source-code heading text-center">Source</div>
                                        <div class="symptom heading text-center">Symptom</div>
                                        <div class="percentage heading text-center">Match (%)</div>
                                        <div class="info heading text-center">INFO & LINKAGE</div>
                                        <div class="command heading text-center">Command</div>
                                    </div>
                                </div>
                                <div id="comparison_result_container" class="col-sm-12">
                                    <input type="hidden" name="comparison_table" id="comparison_table" value="<?php echo $comparisonTable; ?>">
                                    <input type="hidden" name="baseUrlOperation" id="baseUrlOperation" value="<?php echo $baseUrl; ?>">
                                    <input type="hidden" name="show_progress_msg_for_table" id="show_progress_msg_for_table" value="<?php echo $showProgressMsgForTable; ?>">
                                    <?php
                                        $totalSymptoms = 0;
                                        $translationSymptomsArray = array();
                                        if($comparisonTable != "" AND $comparisonTableStatus != "processing")
                                        {
                                            $matchedSymptomIds = array();
                                            $cutOff = $similarityRate;
                                            $runningInitialSymptomId = "";
                                            $runningInitialSymptomDe = "";
                                            $runningInitialSymptomEn = "";
                                            $runningInitialSymptom = "";

                                            //Pagination
                                            $initialCustomVal = initialCustomListingModified($unmarkedInitialsCheck, $db, $comparisonTable, $startFrom, $perPageInitialSymptomNumber, $customizedInitialArray);
                                            if(!empty($initialCustomVal)){
                                                //while($savedData = mysqli_fetch_array($initialCustomVal)){
                                                foreach($initialCustomVal as $key => $savedData){
                                                    $value = $savedData['symptom_id'];
                                                    $zeroComparativesValue = zeroComparativesCheck($db, $comparisonTable, $value, $cutOff);
                                                    //if ($unmarkedInitialsCheck == 0 || $zeroComparativesValue == 0){
                                                    if (!($unmarkedInitialsCheck == 1 && $zeroComparativesValue == 1)){
                                                        //Sending the id for searching saved connections
                                                        if($savedData['connection']=='0'){
                                                            if($savedData['quelle_type_id']==1 || $savedData['quelle_type_id']==2){
                                                                array_push($singleConnectionsInitials,$savedData['symptom_id']);
                                                            }
                                                            else{
                                                                array_push($combinedConnectionsInitials,$savedData['symptom_id']);
                                                            }
                                                        }
                                                            

                                                        // Selecting symptom string depending on comparison option that user selected
                                                        $symptomString_de = "";
                                                        $symptomString_en = "";
                                                        $isNonSymptomEditableConnecteionIni = 0;
                                                        if($savedData['swap_ce'] !=0){
                                                            $symptomString_de =  $savedData['swap_value_ce_de'];
                                                            $symptomString_en =  $savedData['swap_value_ce_en'];
                                                            $isNonSymptomEditableConnecteionIni = 1;
                                                        }else{
                                                            if($savedData['swap'] != 0){
                                                                $symptomString_de =  $savedData['swap_value_de'];
                                                                $symptomString_en =  $savedData['swap_value_en'];
                                                                $isNonSymptomEditableConnecteionIni = 1;
                                                            }else{
                                                                if($savedData['is_final_version_available'] != 0){
                                                                    $symptomString_de =  $savedData['final_version_de'];
                                                                    $symptomString_en =  $savedData['final_version_en'];
                                                                    $isNonSymptomEditableConnecteionIni = 1;
                                                                }else{
                                                                    if($comparisonOption == 1){
                                                                        $symptomString_de =  $savedData['searchable_text_de'];
                                                                        $symptomString_en =  $savedData['searchable_text_en'];
                                                                    }else{
                                                                        $symptomString_de =  $savedData['BeschreibungFull_de'];
                                                                        $symptomString_en =  $savedData['BeschreibungFull_en'];
                                                                    }
                                                                }
                                                            }
                                                        }
                                                        
                                                        // Chceking non symptom editable connections
                                                        $iniConnectionDataQuery = mysqli_query($db,"SELECT id FROM ".$comparisonTable."_connections WHERE (initial_symptom_id = '".$savedData['symptom_id']."' OR comparing_symptom_id = '".$savedData['symptom_id']."') AND (connection_type = 'CE' OR connection_type = 'PE' OR connection_type = 'swap' OR connection_type = 'swapCE')");
                                                        if(mysqli_num_rows($iniConnectionDataQuery) > 0){
                                                            $isNonSymptomEditableConnecteionIni = 1;
                                                        } 

                                                        $originalQuelleDataQuery = mysqli_query($db,"SELECT quelle_id FROM quelle_import_test WHERE id = '".$savedData['symptom_id']."' AND arznei_id = '".$savedData['arznei_id']."'");
                                                        if(mysqli_num_rows($originalQuelleDataQuery) > 0){
                                                            $originalQuelleData = mysqli_fetch_assoc($originalQuelleDataQuery);
                                                        }	
                                                        $originalQuelleIdForConversion = (isset($originalQuelleData['quelle_id']) AND $originalQuelleData['quelle_id'] != "") ? $originalQuelleData['quelle_id'] : "";
                                                        if($originalQuelleIdForConversion == "")
                                                            $originalQuelleIdForConversion = $savedData['quelle_id'];

                                                        if($symptomString_de != ""){
                                                            // Converting the symptoms to it's applicable format according to the settings to present it in front of the user
                                                            // [1st parameter] $symptom symptom string
                                                            // [2nd parameter] $originalQuelleId the quelle_id of the symptom, that the particular symptom originaly belongs. 
                                                            // [3rd parameter] $arzneiId arzneiId 
                                                            // [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
                                                            // [5th parameter] $includeGrade values (0 = Gragde number will not include, 1 = Will include Grade number)
                                                            // [6th parameter] $symptomId the symptom_id of the symptombelong
                                                            // [7th parameter(only if available)] $originalSymptomId the symptom_id of the symptom where he originally belongs.(where he fist created)
                                                            $symptomString_de = convertTheSymptom($symptomString_de, $originalQuelleIdForConversion, $savedData['arznei_id'], 0, 0, $savedData['symptom_id']);
                                                        }
                                                        if($symptomString_en != ""){
                                                            // Converting the symptoms to it's applicable format according to the settings to present it in front of the user
                                                            // [1st parameter] $symptom symptom string
                                                            // [2nd parameter] $originalQuelleId the quelle_id of the symptom, that the particular symptom originaly belongs. 
                                                            // [3rd parameter] $arzneiId arzneiId 
                                                            // [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
                                                            // [5th parameter] $includeGrade values (0 = Gragde number will not include, 1 = Will include Grade number)
                                                            // [6th parameter] $symptomId the symptom_id of the symptombelong
                                                            // [7th parameter(only if available)] $originalSymptomId the symptom_id of the symptom where he originally belongs.(where he fist created)
                                                            $symptomString_en = convertTheSymptom($symptomString_en, $originalQuelleIdForConversion, $savedData['arznei_id'], 0, 0, $savedData['symptom_id']);
                                                        }

                                                        // Displayable symptom string without highlighting
                                                        $symptomString_without_highlight_en = $symptomString_en;
                                                        $symptomString_without_highlight_de = $symptomString_de;

                                                        // Preparing Symptom string with available language divs
                                                        $displayingSymptomString = "";
                                                        if($comparisonLanguage == "en"){
                                                            $displayingSymptomString = $symptomString_en;
                                                            $translationSymptomsArray['row'.$savedData['symptom_id'].'_translated_symptom'] = ($symptomString_de != "") ? $symptomString_de : 'Translation is not available';
                                                        }
                                                        else
                                                        {
                                                            $displayingSymptomString = $symptomString_de;
                                                            $translationSymptomsArray['row'.$savedData['symptom_id'].'_translated_symptom'] = ($symptomString_en != "") ? $symptomString_en : 'Translation is not available';
                                                        }

                                                        if($savedData['quelle_type_id']==1 || $savedData['quelle_type_id']==2)
                                                        {
                                                            if($savedData['is_initial_symptom'] == '1'){
                                                                // Initial symptom
                                                                $runningInitialSymptomDe = $symptomString_without_highlight_de;
                                                                $runningInitialSymptomEn = $symptomString_without_highlight_en;
                                                                $runningInitialSymptomId = $savedData['symptom_id'];
                                                                $uniqueId = "row".$savedData['symptom_id'];
                                                                // storing initial symptom in $runningInitialSymptom for using while comparing with comparing symptom
                                                                $runningInitialSymptom = $displayingSymptomString;
                                                                ?>
                                                                <section id="<?php echo $savedData['symptom_id']; ?>"></section>
                                                                <div class="hidden">Dummy</div>
                                                                <div class="<?php echo $uniqueId; ?> symptom-row initial" id="row<?php echo $savedData['symptom_id']; ?>" data-year="<?php echo $savedData['quelle_jahr']; ?>" data-initial-symptom-de="<?php echo base64_encode($runningInitialSymptomDe); ?>" data-initial-symptom-en="<?php echo base64_encode($runningInitialSymptomEn); ?>" data-comparing-symptom-de="" data-comparing-symptom-en="" data-source-original-language="<?php echo $savedData['initial_source_original_language']; ?>"data-quell-id="<?php echo $savedData['quelle_id']; ?>" data-quelle-type = "<?php echo $savedData['quelle_type_id']; ?>" data-is-non-symptom-editable-connection ="<?php echo $isNonSymptomEditableConnecteionIni; ?>">
                                                                    <div class="source-code"><?php echo $savedData['quelle_code']; ?></div>
                                                                    <div class="symptom"><?php echo $displayingSymptomString; ?></div>
                                                                    <div class="percentage">
                                                                        <input class="marking" name="marking" id="marking" type="checkbox" value="row<?php echo $savedData['symptom_id']; ?>" <?php if($savedData['marked']=='1') echo "checked"; ?>>
                                                                        <?php 
                                                                            if($savedData['marked']=='0')
                                                                                array_push($markSymptomIds,$savedData['symptom_id']);
                                                                            if($zeroComparativesValue == 1)
                                                                                array_push($zeroComparativeIds,$savedData['symptom_id']);
                                                                        ?>
                                                                    </div>
                                                                    <div class="info">
                                                                        <ul class="info-linkage-group">			
                                                                            <li>				
                                                                                <a class="symptom-info-btn" title="info" href="javascript:void(0)"><i class="fas fa-info-circle"></i></a>
                                                                            </li>			
                                                                            <li>				
                                                                                <a class="symptom-edit-btn" title="Symptom edit" href="javascript:void(0)"><i class="fas fa-pencil-alt"></i></a>			
                                                                            </li>			
                                                                            <li>				
                                                                                <a class="symptom-comment-btn" title="comment" href="javascript:void(0)"><i class="fas fa-comment-alt"></i></a>	
                                                                            </li>			
                                                                            <li>				
                                                                                <a class="symptom-footnote-btn" title="footnote" href="javascript:void(0)"><i class="fas fa-sticky-note"></i></a>			
                                                                            </li>			
                                                                            <li>				
                                                                                <a class="symptom-translation-btn symptom-initial-translation-btn" title="translation" href="javascript:void(0)" data-unique-id="<?php echo $uniqueId; ?>">T</a>			
                                                                            </li>	
                                                                            <li>				
                                                                                <a class="symptom-search-btn" title="Search" href="javascript:void(0)"><i class="fas fa-search"></i></a>			
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                    <div class="command">
                                                                        <ul class="command-group">
                                                                            <li>
                                                                                <a class="gen-ns <?php if($savedData['gen_ns']=='1'){echo "active";} ?>" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a>
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                                <?php
                                                            }
                                                        }
                                                        else
                                                        {
                                                            if($savedData['connection']==0)
                                                            {
                                                                if($savedData['is_initial_symptom'] == '1'){
                                                                    // Initial symptom
                                                                    $runningInitialSymptomDe = $symptomString_without_highlight_de;
                                                                    $runningInitialSymptomEn = $symptomString_without_highlight_en;
                                                                    $runningInitialSymptomId = $savedData['symptom_id'];
                                                                    $uniqueId = "row".$savedData['symptom_id'];
                                                                    // storing initial symptom in $runningInitialSymptom for using while comparing with comparing symptom
                                                                    $runningInitialSymptom = $displayingSymptomString;
                                                                    ?>
                                                                    <section id="<?php echo $savedData['symptom_id']; ?>"></section>
                                                                    <div class="hidden">Dummy</div>
                                                                    <div class="<?php echo $uniqueId; ?> symptom-row initial" id="row<?php echo $savedData['symptom_id']; ?>" data-year="<?php echo $savedData['quelle_jahr']; ?>" data-initial-symptom-de="<?php echo base64_encode($runningInitialSymptomDe); ?>" data-initial-symptom-en="<?php echo base64_encode($runningInitialSymptomEn); ?>" data-comparing-symptom-de="" data-comparing-symptom-en="" data-source-original-language="<?php echo $savedData['initial_source_original_language']; ?>"data-quell-id="<?php echo $savedData['quelle_id']; ?>" data-quelle-type = "<?php echo $savedData['quelle_type_id']; ?>" data-is-non-symptom-editable-connection = "<?php echo $isNonSymptomEditableConnecteionIni; ?>">
                                                                        <div class="source-code"><?php echo $savedData['quelle_code']; ?></div>
                                                                        <div class="symptom"><?php echo $displayingSymptomString; ?></div>
                                                                        <div class="percentage">
                                                                            <input class="marking" name="marking" id="marking" type="checkbox" value="row<?php echo $savedData['symptom_id']; ?>" <?php if($savedData['marked']=='1') echo "checked"; ?>>
                                                                            <?php 
                                                                                if($savedData['marked']=='0')
                                                                                    array_push($markSymptomIds,$savedData['symptom_id']);
                                                                                if($zeroComparativesValue == 1)
                                                                                    array_push($zeroComparativeIds,$savedData['symptom_id']);
                                                                            ?>
                                                                        </div>
                                                                        <div class="info">
                                                                            <ul class="info-linkage-group">			
                                                                                <li>				
                                                                                    <a class="symptom-info-btn" title="info" href="javascript:void(0)"><i class="fas fa-info-circle"></i></a>
                                                                                </li>			
                                                                                <li>				
                                                                                    <a class="symptom-edit-btn" title="Symptom edit" href="javascript:void(0)"><i class="fas fa-pencil-alt"></i></a>			
                                                                                </li>			
                                                                                <li>				
                                                                                    <a class="symptom-comment-btn" title="comment" href="javascript:void(0)"><i class="fas fa-comment-alt"></i></a>	
                                                                                </li>			
                                                                                <li>				
                                                                                    <a class="symptom-footnote-btn" title="footnote" href="javascript:void(0)"><i class="fas fa-sticky-note"></i></a>			
                                                                                </li>			
                                                                                <li>				
                                                                                    <a class="symptom-translation-btn symptom-initial-translation-btn" title="translation" href="javascript:void(0)" data-unique-id="<?php echo $uniqueId; ?>">T</a>			
                                                                                </li>	
                                                                                <li>				
                                                                                    <a class="symptom-search-btn" title="Search" href="javascript:void(0)"><i class="fas fa-search"></i></a>			
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                        <div class="command">
                                                                            <ul class="command-group">
                                                                                <li>
                                                                                    <a class="gen-ns <?php if($savedData['gen_ns']=='1'){echo "active";} ?>" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a>
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                    <?php
                                                                }
                                                            }
                                                        }

                                                        // Collecting Synonyms of this Symptom START
                                                        $initialSymptomsAllSynonyms = array();
                                                        $wordSynonyms = array();
                                                        $strictSynonyms = array();
                                                        $partial1Synonyms = array();
                                                        $partial2Synonyms = array();
                                                        $generalSynonyms = array();
                                                        $minorSynonyms = array();
                                                        $nnSynonyms = array();
                                                        if(!empty($savedData['synonym_word'])){
                                                            $wordSynonyms = getAllOrganizeSynonyms($savedData['synonym_word']);
                                                            $wordSynonyms = (!empty($wordSynonyms)) ? $wordSynonyms : array(); 
                                                        }
                                                        if(!empty($savedData['strict_synonym'])){
                                                            $strictSynonyms = getAllOrganizeSynonyms($savedData['strict_synonym']);
                                                            $strictSynonyms = (!empty($strictSynonyms)) ? $strictSynonyms : array(); 
                                                        }
                                                        if(!empty($savedData['synonym_partial_1'])){
                                                            $partial1Synonyms = getAllOrganizeSynonyms($savedData['synonym_partial_1']);
                                                            $partial1Synonyms = (!empty($partial1Synonyms)) ? $partial1Synonyms : array(); 
                                                        }
                                                        if(!empty($savedData['synonym_partial_2'])){
                                                            $partial2Synonyms = getAllOrganizeSynonyms($savedData['synonym_partial_2']);
                                                            $partial2Synonyms = (!empty($partial2Synonyms)) ? $partial2Synonyms : array(); 
                                                        }
                                                        if(!empty($savedData['synonym_general'])){
                                                            $generalSynonyms = getAllOrganizeSynonyms($savedData['synonym_general']);
                                                            $generalSynonyms = (!empty($generalSynonyms)) ? $generalSynonyms : array(); 
                                                        }
                                                        if(!empty($savedData['synonym_minor'])){
                                                            $minorSynonyms = getAllOrganizeSynonyms($savedData['synonym_minor']);
                                                            $minorSynonyms = (!empty($minorSynonyms)) ? $minorSynonyms : array(); 
                                                        }
                                                        if(!empty($savedData['synonym_nn'])){
                                                            $nnSynonyms = getAllOrganizeSynonyms($savedData['synonym_nn']);
                                                            $nnSynonyms = (!empty($nnSynonyms)) ? $nnSynonyms : array(); 
                                                        }
                                                        $initialSymptomsAllSynonyms = array_merge($wordSynonyms, $strictSynonyms, $partial1Synonyms, $partial2Synonyms, $generalSynonyms, $minorSynonyms, $nnSynonyms);
                                                        // Collecting Synonyms of this Symptom END

                                                        $result = mysqli_query($db, "SELECT * FROM $comparisonTable WHERE initial_symptom_id = $value AND `matched_percentage`>=$cutOff AND `connection`='0' ORDER BY `matched_percentage` DESC");
                                                        if(mysqli_num_rows($result) > 0){
                                                            $output = array();
                                                            while($symRow = mysqli_fetch_array($result)){
                                                                $totalSymptoms++;

                                                                //Sending the id for searching saved connections
                                                                if($symRow['connection']=='0'){
                                                                    if($symRow['quelle_type_id']==1 || $symRow['quelle_type_id']==2){
                                                                        array_push($singleConnectionsComparative,$symRow['symptom_id']);
                                                                    }
                                                                    else{
                                                                        array_push($combinedConnectionsComparative,$symRow['symptom_id']);
                                                                    }
                                                                }
                                                                    

                                                                // Selecting symptom string depending on comparison option that user selected
                                                                $symptomString_de = "";
                                                                $symptomString_en = "";
                                                        
                                                                $isNonSymptomEditableConnecteionCom = 0;
                                                                if($symRow['swap_ce'] !=0){
                                                                    $symptomString_de =  $symRow['swap_value_ce_de'];
                                                                    $symptomString_en =  $symRow['swap_value_ce_en'];
                                                                    $isNonSymptomEditableConnecteionCom = 1;
                                                                }else{
                                                                    if($symRow['swap'] != 0){
                                                                        $symptomString_de =  $symRow['swap_value_de'];
                                                                        $symptomString_en =  $symRow['swap_value_en'];
                                                                        $isNonSymptomEditableConnecteionCom = 1;
                                                                    }else{
                                                                        if($symRow['is_final_version_available'] != 0){
                                                                            $symptomString_de =  $symRow['final_version_de'];
                                                                            $symptomString_en =  $symRow['final_version_en'];
                                                                            $isNonSymptomEditableConnecteionCom = 1;
                                                                        }else{
                                                                            if($comparisonOption == 1){
                                                                                $symptomString_de =  $symRow['searchable_text_de'];
                                                                                $symptomString_en =  $symRow['searchable_text_en'];
                                                                            }else{
                                                                                $symptomString_de =  $symRow['BeschreibungFull_de'];
                                                                                $symptomString_en =  $symRow['BeschreibungFull_en'];
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                // Chceking non symptom editable connections
                                                                $comConnectionDataQuery = mysqli_query($db,"SELECT id FROM ".$comparisonTable."_connections WHERE (initial_symptom_id = '".$symRow['symptom_id']."' OR comparing_symptom_id = '".$symRow['symptom_id']."') AND (connection_type = 'CE' OR connection_type = 'PE' OR connection_type = 'swap' OR connection_type = 'swapCE')");
                                                                if(mysqli_num_rows($comConnectionDataQuery) > 0){
                                                                    $isNonSymptomEditableConnecteionCom = 1;
                                                                } 

                                                                $debugArray = array();
                                                                $debugArray['symptomString_de'] = $symptomString_de;
                                                                $debugArray['symptomString_en'] = $symptomString_en;
                                                                $debugArrayFinal[] = $debugArray;
                                                            
                                                                $originalQuelleDataQuery = mysqli_query($db,"SELECT quelle_id FROM quelle_import_test WHERE id = '".$symRow['symptom_id']."' AND arznei_id = '".$symRow['arznei_id']."'");
                                                                if(mysqli_num_rows($originalQuelleDataQuery) > 0){
                                                                    $originalQuelleData = mysqli_fetch_assoc($originalQuelleDataQuery);
                                                                }	
                                                                $originalQuelleIdForConversion = (isset($originalQuelleData['quelle_id']) AND $originalQuelleData['quelle_id'] != "") ? $originalQuelleData['quelle_id'] : "";
                                                                if($originalQuelleIdForConversion == "")
                                                                    $originalQuelleIdForConversion = $symRow['quelle_id'];

                                                                if($symptomString_de != ""){
                                                                    // Converting the symptoms to it's applicable format according to the settings to present it in front of the user
                                                                    // [1st parameter] $symptom symptom string
                                                                    // [2nd parameter] $originalQuelleId the quelle_id of the symptom, that the particular symptom originaly belongs. 
                                                                    // [3rd parameter] $arzneiId arzneiId 
                                                                    // [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
                                                                    // [5th parameter] $includeGrade values (0 = Gragde number will not include, 1 = Will include Grade number)
                                                                    // [6th parameter] $symptomId the symptom_id of the symptombelong
                                                                    // [7th parameter(only if available)] $originalSymptomId the symptom_id of the symptom where he originally belongs.(where he fist created)
                                                                    $symptomString_de = convertTheSymptom($symptomString_de, $originalQuelleIdForConversion, $symRow['arznei_id'], 0, 0, $symRow['symptom_id']);
                                                                }
                                                                if($symptomString_en != ""){
                                                                    // Converting the symptoms to it's applicable format according to the settings to present it in front of the user
                                                                    // [1st parameter] $symptom symptom string
                                                                    // [2nd parameter] $originalQuelleId the quelle_id of the symptom, that the particular symptom originaly belongs. 
                                                                    // [3rd parameter] $arzneiId arzneiId 
                                                                    // [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
                                                                    // [5th parameter] $includeGrade values (0 = Gragde number will not include, 1 = Will include Grade number)
                                                                    // [6th parameter] $symptomId the symptom_id of the symptombelong
                                                                    // [7th parameter(only if available)] $originalSymptomId the symptom_id of the symptom where he originally belongs.(where he fist created)
                                                                    $symptomString_en = convertTheSymptom($symptomString_en, $originalQuelleIdForConversion, $symRow['arznei_id'], 0, 0, $symRow['symptom_id']);
                                                                }

                                                                // Displayable symptom string without highlighting
                                                                $symptomString_without_highlight_en = $symptomString_en;
                                                                $symptomString_without_highlight_de = $symptomString_de;

                                                                // Preparing Symptom string with available language divs
                                                                $displayingSymptomString = "";
                                                                if($comparisonLanguage == "en"){
                                                                    $displayingSymptomString = $symptomString_en;
                                                                    $translationSymptomsArray['row'.$runningInitialSymptomId."_".$symRow['symptom_id'].'_translated_symptom'] = ($symptomString_de != "") ? $symptomString_de : 'Translation is not available';
                                                                }
                                                                else
                                                                {
                                                                    $displayingSymptomString = $symptomString_de;
                                                                    $translationSymptomsArray['row'.$runningInitialSymptomId."_".$symRow['symptom_id'].'_translated_symptom'] = ($symptomString_en != "") ? $symptomString_en : 'Translation is not available';
                                                                }

                                                                {
                                                                    // Comparing symptom
                                                                    array_push($matchedSymptomIds, $symRow['symptom_id']);
                                                                    $uniqueId = "row".$runningInitialSymptomId."_".$symRow['symptom_id'];
                                                                    // For heighlighting match words
                                                                    // $compareResult = newComareSymptom($runningInitialSymptom, $displayingSymptomString);
                                                                    $compareResult = compareSymptomWithSynonyms($runningInitialSymptom, $displayingSymptomString, $globalStopWords, $initialSymptomsAllSynonyms);
                                                                    $highlightedComparingSymptom = (isset($compareResult['comparing_source_symptom_highlighted']) AND $compareResult['comparing_source_symptom_highlighted'] != "") ? $compareResult['comparing_source_symptom_highlighted'] : "";
                                                                    // updating $symptomString_en and $symptomString_de data with the heighlighted comparing symptom
                                                                    if($comparisonLanguage == "en")
                                                                        $symptomString_en = ($highlightedComparingSymptom != "") ? $highlightedComparingSymptom : $symptomString_en;
                                                                    else
                                                                        $symptomString_de = ($highlightedComparingSymptom != "") ? $highlightedComparingSymptom : $symptomString_de;
                                                                    ?>
                                                                    <div class="<?php echo $uniqueId; ?> symptom-row comparing" id="row<?php echo $runningInitialSymptomId."_".$symRow['symptom_id']; ?>" data-year="<?php echo $symRow['quelle_jahr']; ?>" data-initial-symptom-de="<?php echo base64_encode($runningInitialSymptomDe); ?>" data-initial-symptom-en="<?php echo base64_encode($runningInitialSymptomEn); ?>" data-comparing-symptom-de="<?php echo base64_encode($symptomString_without_highlight_de); ?>" data-comparing-symptom-en="<?php echo base64_encode($symptomString_without_highlight_en); ?>" data-source-original-language="<?php echo $symRow['comparing_source_original_language']; ?>" data-quell-id="<?php echo $symRow['quelle_id']; ?>" data-quelle-type = "<?php echo $symRow['quelle_type_id']; ?>" data-is-non-symptom-editable-connection = "<?php echo $isNonSymptomEditableConnecteionCom; ?>">
                                                                        <div class="source-code"><?php echo $symRow['quelle_code']; ?></div>
                                                                        <div class="symptom"><?php echo $highlightedComparingSymptom; ?></div>
                                                                        <div class="percentage"><?php echo $symRow['matched_percentage']; ?>%</div>
                                                                        <div class="info">
                                                                            <ul class="info-linkage-group">			
                                                                                <li>				
                                                                                    <a class="symptom-info-btn" title="info" href="javascript:void(0)"><i class="fas fa-info-circle"></i></a>
                                                                                </li>			
                                                                                <li>				
                                                                                    <a class="symptom-edit-btn" title="Symptom edit" href="javascript:void(0)"><i class="fas fa-pencil-alt"></i></a>			
                                                                                </li>			
                                                                                <li>				
                                                                                    <a class="symptom-comment-btn" title="comment" href="javascript:void(0)"><i class="fas fa-comment-alt"></i></a>	
                                                                                </li>			
                                                                                <li>				
                                                                                    <a class="symptom-footnote-btn" title="footnote" href="javascript:void(0)"><i class="fas fa-sticky-note"></i></a>			
                                                                                </li>			
                                                                                <li>				
                                                                                    <a class="symptom-translation-btn symptom-comparative-translation-btn" title="translation" href="javascript:void(0)" data-unique-id="<?php echo $uniqueId; ?>">T</a>
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                        <div class="command">
                                                                            <ul class="command-group">						
                                                                                <li>				
                                                                                    <a class="symptom-connect-btn connect" href="javascript:void(0)" title="connect"><i class="fas fa-link"></i></a>			
                                                                                </li>	
                                                                                <li>				
                                                                                    <a class="symptom-connect-edit-btn" href="javascript:void(0)" title="Connect edit">CE</a>
                                                                                </li>						
                                                                                <li>				
                                                                                    <a class="symptom-paste-btn" href="javascript:void(0)" title="Paste">P</a>			
                                                                                </li>			
                                                                                <li>				
                                                                                    <a class="symptom-paste-edit-btn" href="javascript:void(0)" title="Paste edit">PE</a>			
                                                                                </li>	
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                    <?php
                                                                }

                                                            }
                                                        }else{
                                                            array_push($unmarkedSymptomsArray,$value);
                                                        }
                                                    }
                                                    echo '<div class="hidden">Dummy</div>';
                                                }
                                                        
                                            }else{
                                                header("Location: ".$baseUrl."comparison.php?page=1&per_page_initial_symptom_number=".$perPageInitialSymptomNumber."&unmarked_initials_check=".$unmarkedInitialsCheck."&ns_editing=".$editingNs."&open_conn=1");
                                                die();
                                            }

                                            if($page==$totalPage && $unmarkedInitialsCheck==0)
                                            {
                                                ?>
                                                <div style="text-align: center; background-color: #F1FB3A;"><a href="<?php echo $baseUrl?>unmatched-symptoms.php?matched_percentage=<?php echo $cutOff?>&table=<?php echo $comparisonTable?>&comparison_option=<?php echo $comparisonOption?>&comparison_language=<?php echo $comparisonLanguage?>&arznei_id=<?php echo $arzneiId?>" class="btn"><h4>Show unmatched symptoms</h4></a></div>							
                                                <?php
                                            }

                                            // <!-- Pagination -->
                                            
                                            ?>
                                            <div class="text-center col-sm-12">
                                                <nav aria-label="Page navigation example">
                                                    <ul class="pagination">
                                                    <?php
                                                        //Advance Pagination
                                                        $pageDisplayToLeft = 3;
                                                        $pageDisplayToRight = 7;
                                                        $currentPage = $page;
                                                        $fixedInitialPage =1 ;
                                                        
                                                        if(($currentPage == $totalPage) && ($currentPage == 1))
                                                        {
                                                            echo '<li class="page-item"><a href="javascript:void(0)"></a></li>';
                                                        }
                                                        else
                                                        {
                                                            if($page>4)
                                                            {
                                                                ?>
                                                                    <li class="page-item">
                                                                    <a class="page-link  text-primary" href="comparison.php?page=<?php echo $fixedInitialPage;?>&per_page_initial_symptom_number=<?php echo $perPageInitialSymptomNumber?>&unmarked_initials_check=<?php echo $unmarkedInitialsCheck?>&ns_editing=<?php echo $editingNs?>">1</a>
                                                                    </li>
                                                                <?php
                                                            }

                                                            //Left Page
                                                            if(($currentPage - $pageDisplayToLeft) > 1) 
                                                            {
                                                                echo ' <li class="page-item"><a href="javascript:void(0)">...</a></li> ';
                                                            }
                                                            $pageDisplay = max(1, $currentPage - $pageDisplayToLeft);
                                                            $pageVarLeft0=$page;
                                                            $pageVarLeft = 0;
                                                            $decrementLeft = $perPageInitialSymptomNumber*($page-1);
                                                            
                                        
                                                            while($pageDisplay < $currentPage) 
                                                            {

                                                                $pageVarLeft = $pageDisplay* $perPageInitialSymptomNumber - $perPageInitialSymptomNumber;
                                                                ?>
                                                                    <li class="page-item"><a class="page-link  text-dark " href="comparison.php?page=<?php echo $pageDisplay;?>&per_page_initial_symptom_number=<?php echo $perPageInitialSymptomNumber?>&id_to_continue=<?php echo $pageVarLeft;?>&unmarked_initials_check=<?php echo $unmarkedInitialsCheck?>&ns_editing=<?php echo $editingNs?>"><?php echo $pageDisplay?></a></li>
                                                                <?php
                                                                $pageDisplay++;
                                                                $decrementLeft = $decrementLeft - $perPageInitialSymptomNumber;
                                                                $pageVarLeft0++;
                                                            }
                                                            ?>
                                                            <?php 
                                                                //Current Page
                                                                $currentPageVar=$perPageInitialSymptomNumber*$page - $perPageInitialSymptomNumber;
                                                                if($currentPage == $page)
                                                                {
                                                                    $pageClassStyle = "active";
                                                                }

                                                            ?>
                                                                <li class="page-item <?php echo $pageClassStyle?>"><a class="page-link  text-light bg-danger " href="comparison.php?page=<?php echo $page;?>&per_page_initial_symptom_number=<?php echo $perPageInitialSymptomNumber?>&id_to_continue=<?php echo $currentPageVar;?>&unmarked_initials_check=<?php echo $unmarkedInitialsCheck?>&ns_editing=<?php echo $editingNs?>"><?php echo $currentPage?></a></li>
                                                            <?php
                                                                //Right Page

                                                                $pageDisplay = min($totalPage, $currentPage + 1);
                                                                $pageVarRight0 = $page+1;
                                                            while($pageDisplay < min($currentPage + $pageDisplayToRight, $totalPage)) 
                                                            {
                                                                $pageVarRight = $pageVarRight0*$perPageInitialSymptomNumber - $perPageInitialSymptomNumber;   
                                                                ?>
                                                                    <li class="page-item"><a class="page-link  text-dark " href="comparison.php?page=<?php echo $pageDisplay;?>&per_page_initial_symptom_number=<?php echo $perPageInitialSymptomNumber?>&id_to_continue=<?php echo $pageVarRight;?>&unmarked_initials_check=<?php echo $unmarkedInitialsCheck?>&ns_editing=<?php echo $editingNs?>"><?php echo $pageDisplay?></a></li>
                                                                <?php
                                                                $pageDisplay++;
                                                                $pageVarRight0++;

                                                            }
                                                            if(($currentPage + $pageDisplayToRight) < $totalPage) 
                                                            {
                                                                echo '<li class="page-item"><a href="javascript:void(0)">...</a></li> ';
                                                            }

                                                            if($currentPage<$totalPage)
                                                            {
                                                                $lastPage = $totalPage*$perPageInitialSymptomNumber - $perPageInitialSymptomNumber;
                                                            ?>
                                                                <li class="page-item">
                                                                <a class="page-link  text-primary" href="comparison.php?page=<?php echo $totalPage;?>&per_page_initial_symptom_number=<?php echo $perPageInitialSymptomNumber?>&id_to_continue=<?php echo $lastPage;?>&unmarked_initials_check=<?php echo $unmarkedInitialsCheck?>&ns_editing=<?php echo $editingNs?>"><?php echo $totalPage?></a>
                                                                </li>
                                                            <?php
                                                            }	
                                                        }

                                                        $db->close();           
                                                    ?>
                                                    </ul>
                                                    <br><br><br>
                                                </nav>
                                            </div>
                                            <?php
                                        }
                                        else
                                        {
                                    ?>
                                        <div class="symptom-row text-center">
                                            <div class="full-length-row">No records found.</div>
                                        </div>
                                    <?php
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div id="loaderCEOverlay" class="hidden">
                            <div id="loaderCE" align="center" style="background: #EEE;">
                                <div>
                                    <p>Please Wait. Connect edit opeartion is going on.</p>
                                </div>
                                <img src="../assets/img/loader.gif">
                            </div>
                        </div>
                        <input type="hidden" name="no_of_initials_single" id="no_of_initials_single" value="0">
                        <input type="hidden" name="no_of_initials_combined" id="no_of_initials_combined" value="0">

                        <div id="mydialog" style="display: none" align="center">
                        
                        <?php
                            //Save connection array modification
                            $singleConnectionsComparative = (isset($singleConnectionsComparative) AND !empty($singleConnectionsComparative)) ? array_unique($singleConnectionsComparative) : "";
                            $singleConnectionsComparativeString = (!empty($singleConnectionsComparative)) ? implode(',', $singleConnectionsComparative) : "";
                            $singleConnectionsInitialsString = (!empty($singleConnectionsInitials)) ? implode(',', $singleConnectionsInitials) : "";
                            $combinedConnectionsIntialsString = (!empty($combinedConnectionsInitials)) ? implode(',', $combinedConnectionsInitials) : "";
                            $combinedConnectionsComparative = (isset($combinedConnectionsComparative) AND !empty($combinedConnectionsComparative)) ? array_unique($combinedConnectionsComparative) : "";
                            $combinedConnectionsComparativeString = (!empty($combinedConnectionsComparative)) ? implode(',', $combinedConnectionsComparative) : "";

                            //marking symptom ids
                            $markSymptomIdsArray = (isset($markSymptomIds) AND !empty($markSymptomIds)) ? array_unique($markSymptomIds) : "";
                            $markSymptomIdsArrayString = (!empty($markSymptomIdsArray)) ? implode(',', $markSymptomIdsArray) : "";

                            //zero comparative ids
                            $zeroComparativeIdsArray = (isset($zeroComparativeIds) AND !empty($zeroComparativeIds)) ? array_unique($zeroComparativeIds) : "";
                            $zeroComparativeIdsArrayString = (!empty($zeroComparativeIdsArray)) ? implode(',', $zeroComparativeIdsArray) : "";

                            //unmarked symptoms
                            $unmarkedSymptomsArrayString = (!empty($unmarkedSymptomsArray)) ? implode(',', $unmarkedSymptomsArray) : "";

                            //Non secure array modification
                            $savedNonSecureIdArrayString = (!empty($savedNonSecureIdArray)) ? implode(',', $savedNonSecureIdArray) : "";
                            $savedNonSecureInitialIdArrayString = (!empty($savedNonSecureInitialIdArray)) ? implode(',', $savedNonSecureInitialIdArray) : "";
                        ?>
                        </div>
                        <!-- Including Modals html START -->
                        <?php include 'includes/comparison-table-page-modals.php'; ?>
                        <!-- Including Modals html END -->
			        </div>
          			<!-- /.box-body -->
		    	</div>
			</div>
		</div>
	    <!-- /.row -->
  	</section>
  	<!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php
include '../inc/footer.php';
?>
<script src="assets/js/common.js"></script>
<script src="allIconFunctions2-jay.js"></script>
<script src="connect-saved.js"></script>
<script src="connection-function.js"></script>
<script src="connect.js"></script>
<script src="paste.js"></script>
<script src="paste-edit.js"></script>
<script src="connect-edit-exp.js"></script>
<script type="text/javascript">
    //marking ids in js array
    var markSymptomIdsFinalArray = [<?php echo $markSymptomIdsArrayString; ?>];
    var zeroComparativeIdsFinalArray = [<?php echo $zeroComparativeIdsArrayString; ?>];
    var isHistory = "";
    var baseUrlOperation = $("#baseUrlOperation").val();
    var translationArray = {}; 
    <?php foreach($translationSymptomsArray as $tranKey => $tranVal){ ?>
        translationArray["<?php echo $tranKey; ?>"] = "<?php echo $tranVal; ?>";
    <?php } ?>
</script>
<!-- If the compariosn not open from history section then add below js -->
<script src="assets/js/comparison-icons.js"></script>
<script src="symptom-icon-functions.js"></script>
<script src="non-secure-connect.js"></script>
<script type="text/javascript">
    //Enabling loader if comparison table exist
    var comparison_table_check = $("#comparison_table").val();
    if(comparison_table_check != ""){
        $('#comparison_loader').removeClass("hidden");
    }
    // For stoping recursive shell excution status checking function 
    var stopRecursiveCall = false;
    var comparison_table_name = '<?php echo $comparisonTable; ?>';
    var role = '<?php echo $role; ?>';
    var comparison_language = '<?php echo $comparisonLanguage; ?>';
    var scroll = '<?php echo $scroll; ?>';
    $(window).bind("load", function() {
        if(scroll != ""){
            $('html, body').animate({
                scrollTop: $("#"+scroll).offset().top
            }, 1000);
        }    
        var show_progress_msg_for_table = $("#show_progress_msg_for_table").val();
        if(show_progress_msg_for_table != ""){
            $("#comparison_table_overlay").removeClass('hidden');
            checkShellExecutionNew(show_progress_msg_for_table);
        }
        else{
            var open_ini_trans_hidden_param = $("#open_ini_trans_hidden_param").val();
            var open_com_trans_hidden_param = $("#open_com_trans_hidden_param").val();
            var open_conn_hidden_param = $("#open_conn_hidden_param").val();
            if(open_conn_hidden_param != "")
                $(".all-connections").click();
            if(open_ini_trans_hidden_param != "")
                $("#all_initial_translation").click();
            if(open_com_trans_hidden_param != "")
                $("#all_comparative_translation").click();
            $("#comparison_table_overlay").addClass('hidden');
        }
    });
    $('#arznei_id').select2({
        // options 
        searchInputPlaceholder: 'Search Remedy...'
    });
    // Defining Select2
    $('#initial_source').select2({
        // options 
        searchInputPlaceholder: 'Search Source...'
    });
    $('#comparing_sources').select2({
        // options 
        searchInputPlaceholder: 'Search Source...'
    });

    $('#search_sources').select2({
        searchInputPlaceholder: 'Search Source...',
    });
    // Fetching Quelle/Sources of the arznei
    $('#arznei_id').on('select2:select', function (e) {
        if(typeof(e.params.data.id) != "undefined" && e.params.data.id !== null){
            $("#initial_source").prop("disabled", true);
            $("#comparing_sources").prop("disabled", true);
            var request = $.ajax({
                url: "get_arznei_quelle.php",
                type: "POST",
                data: {arznei_id : e.params.data.id},
                dataType: "json"
            });
            request.done(function(responseData) {
                console.log(responseData);
                var resultData = null;
                try {
                    resultData = JSON.parse(responseData); 
                } catch (e) {
                    resultData = responseData;
                }
                var saved_initial_source_id = $("#saved_initial_source_id").val(); 
                var saved_comparing_source_ids = $("#saved_comparison_comparing_source_ids_comma_separated").val();
                var split_saved_comparing_source_ids = saved_comparing_source_ids.split(",");
                var initialSourceHtml = "";
                var comparingSourceHtml = "";
                // Initial source select box
                initialSourceHtml += '<select class="form-control save-data" name="initial_source" id="initial_source">';
                initialSourceHtml += '<option value="">Select</option>';
                var htmlComparisons = '<optgroup label="Comparisons">';
                var htmlSingleSources = '<optgroup label="Single sources">';
                var htmlComparisonsInner = ''; 
                var htmlSingleSourcesInner = '';
                // Comparing source select box
                comparingSourceHtml += '<select class="form-control save-data" name="comparing_sources[]" id="comparing_sources" multiple="multiple" data-placeholder="Search comparing source(s)">';
                comparingSourceHtml += '<option value="">Select</option>';
                var comHtmlComparisons = '<optgroup label="Comparisons">';
                var comHtmlSingleSources = '<optgroup label="Single sources">';
                var comHtmlComparisonsInner = ''; 
                var comHtmlSingleSourcesInner = '';
                $.each(resultData, function( key, value ) {
                    // Initial source select box
                    var selected = (saved_initial_source_id == value.quelle_id) ? 'selected' : '';
                    // Comparing source select box
                    var comSelected = (split_saved_comparing_source_ids.indexOf(value.quelle_id) !== -1) ? 'selected' : '';
                    if(value.quelle_type_id == 3){
                        htmlComparisonsInner += '<option data-is-symptoms-available-in-de='+value.is_symptoms_available_in_de+' data-is-symptoms-available-in-en='+value.is_symptoms_available_in_en+' data-is-synonyms-up-to-date='+value.is_synonyms_up_to_date+' '+selected+' value="'+value.quelle_id+'">'+value.source+'</option>';
                        comHtmlComparisonsInner += '<option data-is-symptoms-available-in-de='+value.is_symptoms_available_in_de+' data-is-symptoms-available-in-en='+value.is_symptoms_available_in_en+' data-is-synonyms-up-to-date='+value.is_synonyms_up_to_date+' '+comSelected+' value="'+value.quelle_id+'">'+value.source+'</option>';
                    } else {
                        htmlSingleSourcesInner += '<option data-is-symptoms-available-in-de='+value.is_symptoms_available_in_de+' data-is-symptoms-available-in-en='+value.is_symptoms_available_in_en+' data-is-synonyms-up-to-date='+value.is_synonyms_up_to_date+' '+selected+' value="'+value.quelle_id+'">'+value.source+'</option>';
                        comHtmlSingleSourcesInner += '<option data-is-symptoms-available-in-de='+value.is_symptoms_available_in_de+' data-is-symptoms-available-in-en='+value.is_symptoms_available_in_en+' data-is-synonyms-up-to-date='+value.is_synonyms_up_to_date+' '+comSelected+' value="'+value.quelle_id+'">'+value.source+'</option>';
                    }

                }) ;
                // Initial source select box
                if(htmlComparisonsInner == '')
                    htmlComparisons += '<option value="" disabled="disabled">None</option>';
                else
                    htmlComparisons += htmlComparisonsInner;
                if(htmlSingleSourcesInner == '')
                    htmlSingleSources += '<option value="" disabled="disabled">None</option>';
                else
                    htmlSingleSources += htmlSingleSourcesInner;
                // Comparing source select box
                if(comHtmlComparisonsInner == '')
                    comHtmlComparisons += '<option value="" disabled="disabled">None</option>';
                else
                    comHtmlComparisons += comHtmlComparisonsInner;
                if(comHtmlSingleSourcesInner == '')
                    comHtmlSingleSources += '<option value="" disabled="disabled">None</option>';
                else
                    comHtmlSingleSources += comHtmlSingleSourcesInner;
                // Initial source select box
                htmlComparisons += '</optgroup>';
                htmlSingleSources += '</optgroup>';
                initialSourceHtml += htmlComparisons+htmlSingleSources;
                initialSourceHtml += '</select>';
                initialSourceHtml += '<span class="error-text"></span>';
                $("#initial_source_cnr").html( initialSourceHtml );
                $('#initial_source').select2({
                    // options 
                    searchInputPlaceholder: 'Search Quelle...'
                });
                // Comparing source select box
                comHtmlComparisons += '</optgroup>';
                comHtmlSingleSources += '</optgroup>';
                comparingSourceHtml += comHtmlComparisons+comHtmlSingleSources;
                comparingSourceHtml += '</select>';
                comparingSourceHtml += '<span class="error-text"></span>';
                $("#comparing_source_cnr").html( comparingSourceHtml );
                $('#comparing_sources').select2({
                    // options 
                    searchInputPlaceholder: 'Search Quelle...'
                });
                $("#initial_source").prop("disabled", false);
                $("#comparing_sources").prop("disabled", false);
            });
            request.fail(function(jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
                $("#initial_source").prop("disabled", false);
                $("#comparing_sources").prop("disabled", false);
            });
        }
    });
    // Changing comparative source(selectbox) select options depending on initial source selection.  
    $(document).on('select2:select', '#initial_source', function(e){
        var arznei_id = $("#arznei_id").val();
        if((typeof(e.params.data.id) != "undefined" && e.params.data.id !== null) && arznei_id != ""){
            $("#comparing_sources").prop("disabled", true);
            var request = $.ajax({
                url: "get_comparing_quelle.php",
                type: "POST",
                data: {initial_source : e.params.data.id, arznei_id : arznei_id},
                dataType: "json"
            });
            request.done(function(responseData) {
                var resultData = null;
                try {
                    resultData = JSON.parse(responseData); 
                } catch (e) {
                    resultData = responseData;
                }
                var saved_comparing_source_ids = $("#saved_comparison_comparing_source_ids_comma_separated").val();
                var split_saved_comparing_source_ids = saved_comparing_source_ids.split(",");
                var comparingSourceHtml = "";
                // Comparing source select box
                comparingSourceHtml += '<select class="form-control save-data" name="comparing_sources[]" id="comparing_sources" multiple="multiple" data-placeholder="Search comparing source(s)">';
                comparingSourceHtml += '<option value="">Select</option>';
                var comHtmlComparisons = '<optgroup label="Comparisons">';
                var comHtmlSingleSources = '<optgroup label="Single sources">';
                var comHtmlComparisonsInner = '';
                var comHtmlSingleSourcesInner = '';
                $.each(resultData, function( key, value ) {
                    var conditionDisabled = "";
                    if(value.is_disabled == 1)
                        conditionDisabled = 'disabled="disabled"';
                    // Initial source select box
                    var selected = (saved_initial_source_id == value.quelle_id) ? 'selected' : '';
                    // Comparing source select box
                    var comSelected = '';
                    // conditionDisabled = ""; // I have to remove this line to make the disabled concept (comparing source will always be younger then initial source) active again
                    if(value.quelle_type_id == 3){
                        comHtmlComparisonsInner += '<option data-is-symptoms-available-in-de='+value.is_symptoms_available_in_de+' data-is-symptoms-available-in-en='+value.is_symptoms_available_in_en+' data-is-synonyms-up-to-date='+value.is_synonyms_up_to_date+' '+comSelected+' value="'+value.quelle_id+'" '+conditionDisabled+'>'+value.source+'</option>';
                    } else {
                        comHtmlSingleSourcesInner += '<option data-is-symptoms-available-in-de='+value.is_symptoms_available_in_de+' data-is-symptoms-available-in-en='+value.is_symptoms_available_in_en+' data-is-synonyms-up-to-date='+value.is_synonyms_up_to_date+' '+comSelected+' value="'+value.quelle_id+'" '+conditionDisabled+'>'+value.source+'</option>';
                    }  
                });
                // Comparing source select box
                if(comHtmlComparisonsInner == '')
                    comHtmlComparisons += '<option value="" disabled="disabled">None</option>';
                else
                    comHtmlComparisons += comHtmlComparisonsInner;
                if(comHtmlSingleSourcesInner == '')
                    comHtmlSingleSources += '<option value="" disabled="disabled">None</option>';
                else
                    comHtmlSingleSources += comHtmlSingleSourcesInner;
                // Comparing source select box
                comHtmlSingleSources += '</optgroup>';
                comHtmlComparisons += '</optgroup>';
                comparingSourceHtml += comHtmlComparisons+comHtmlSingleSources;
                comparingSourceHtml += '</select>';
                comparingSourceHtml += '<span class="error-text"></span>';
                $("#comparing_source_cnr").html( comparingSourceHtml );
                $('#comparing_sources').select2({
                    // options 
                    searchInputPlaceholder: 'Search Quelle...'
                });
                $("#comparing_sources").prop("disabled", false);
            });
            request.fail(function(jqXHR, textStatus) {
                $("#comparing_sources").prop("disabled", false);
                console.log("Request failed: " + textStatus);
            });
        }
    });
    $('body').on( 'submit', '#symptom_comparison_form', function(e) {
        e.preventDefault();
        var initial_source = $("#initial_source").val();
        var arznei_id = $("#arznei_id").val();
        var comparing_sources = $("#comparing_sources").val();
        var comparison_language = $("#comparison_language").val();
        var similarity_rate = $("#similarity_rate").val();
        var comparison_option = $("#comparison_option").val();
        var per_page_initial_symptom_number = $("#per_page_initial_symptom_number").val();
        var is_opened_a_saved_comparison = $("#is_opened_a_saved_comparison").val();
        var error_count = 0;
        if(arznei_id == ""){
            $("#arznei_id").next().next().html('Please select arznei');
            $("#arznei_id").next().next().addClass('text-danger');
            error_count++;
        }else{
            $("#arznei_id").next().next().html('');
            $("#arznei_id").next().next().removeClass('text-danger');
        }
        if(initial_source == ""){
            $("#initial_source").next().next().html('Please select initial source');
            $("#initial_source").next().next().addClass('text-danger');
            error_count++;
        }else{
            $("#initial_source").next().next().html('');
            $("#initial_source").next().next().removeClass('text-danger');
        }
        if(comparing_sources == ""){
            $("#comparing_sources").next().next().html('Please select comparing source');
            $("#comparing_sources").next().next().addClass('text-danger');
            error_count++;
        }else{
            $("#comparing_sources").next().next().html('');
            $("#comparing_sources").next().next().removeClass('text-danger');
        }
        if(comparison_language == ""){
            $("#comparison_language").next().html('Please select comparison language');
            $("#comparison_language").next().addClass('text-danger');
            error_count++;
        }else{
            $("#comparison_language").next().html('');
            $("#comparison_language").next().removeClass('text-danger');
        }
        if(error_count != 0){
            return false;
        }else{
            // Checking if selected initial and comparing sources are available in selecetd comparison language.
            var initialSourceLanguage = $("#initial_source").find(':selected').attr("data-is-symptoms-available-in-"+comparison_language);
            var comparingSourceLanguage = $("#comparing_sources option:selected").map(function() {
                return $(this).attr("data-is-symptoms-available-in-"+comparison_language);
            }).get();
            comparingSourceLanguage.push(initialSourceLanguage);
            // Checking if selected initial and comparing sources are up to date with synonym.
            var initialSourceSynonymUptoDate = $("#initial_source").find(':selected').attr("data-is-synonyms-up-to-date");
            var comparingSourceSynonymUptoDate = $("#comparing_sources option:selected").map(function() {
                return $(this).attr("data-is-synonyms-up-to-date");
            }).get();
            comparingSourceSynonymUptoDate.push(initialSourceSynonymUptoDate);
            if($.inArray("0", comparingSourceLanguage) !== -1){
                $("#global_msg_container").html('<p class="text-center">There is/are source(s) in the Initial source or in the Comparing source(s) which are not available in the language that you have selecetd to compare, Please check and try again!</p>');
                $("#globalMsgModal").modal('show');
                return false;
            }else if($.inArray("0", comparingSourceSynonymUptoDate) !== -1){
                $("#global_msg_container").html('<p class="text-center">The source(s) are not up to date with the synonyms, Please go to Materia Medica page and make the source(s) up to date with synonyms.</p>');
                $("#globalMsgModal").modal('show');
                return false;
            }else{
                // Showing the normal overlay loader here
                $("#comparison_loader").removeClass('hidden');

                var request = $.ajax({
                    url: "check-if-comparison-table-exist.php",
                    type: "POST",
                    data: {arznei_id : arznei_id, initial_source : initial_source, comparing_sources : comparing_sources, similarity_rate : similarity_rate, comparison_option : comparison_option, comparison_language : comparison_language, per_page_initial_symptom_number : per_page_initial_symptom_number, is_opened_a_saved_comparison : is_opened_a_saved_comparison},
                    dataType: "json"
                });

                request.done(function(responseData) {
                    var resultData = null;
                    try {
                        resultData = JSON.parse(responseData); 
                    } catch (e) {
                        resultData = responseData;
                    }
                    // console.log(resultData);
                    if(resultData.result_data.is_table_exist == 0){
                        $("#comparison_loader").addClass('hidden');
                        $("#comparison_table_overlay").removeClass('hidden');
                        stopRecursiveCall = false;
                        var dynamic_table_name = (typeof(resultData.result_data.dynamic_table_name) != "undefined" && resultData.result_data.dynamic_table_name !== null && resultData.result_data.dynamic_table_name != "") ? resultData.result_data.dynamic_table_name : "";
                        checkShellExecutionNew(dynamic_table_name);
                    }else{
                        window.location.href = "<?php echo $baseUrl?>comparison.php";
                    }
                });

                request.fail(function(jqXHR, textStatus) {
                    console.log("Request failed: " + textStatus);
                });
                
            }
        }
    });
    function checkShellExecutionNew(dynamicTableName = ""){
        // If cancell is true the recusrive function calling will stop.
        if (stopRecursiveCall) {
            return;
        }
        if(dynamicTableName != "")
        {
            var request = $.ajax({
                type: "POST",
                url: "check-shell-execution-new.php",
                dataType: "json",
                data: {
                    dynamic_table_name: dynamicTableName
                }
            });
            request.done(function(responseData) {
                var resultData = null;
                try {
                    resultData = JSON.parse(responseData); 
                } catch (e) {
                    resultData = responseData;
                }
                if(responseData.status == "success"){
                    if(responseData.result_data.script_status == "Complete"){
                        setTimeout(function(){
                            $("#comparison_table_overlay").addClass('hidden');
                            $("#comparison_loader").addClass('hidden');
                            window.location.href = "<?php echo $baseUrl; ?>comparison.php";
                        }, 2000);
                    }else{
                        console.log('Again');
                        setTimeout(function(){
                            checkShellExecutionNew(dynamicTableName);
                        }, 5000);
                    }
                }else{

                    $("#comparison_table_overlay").addClass('hidden');
                    $("#comparison_loader").addClass('hidden');
                    console.log("Not Successfull: " + responseData);
                    $("#global_msg_container").html(responseData.message);
                    $("#globalMsgModal").modal('show');
                }
            });
            request.fail(function(jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
                
                $("#comparison_table_overlay").addClass('hidden');
                $("#comparison_loader").addClass('hidden');
                $("#global_msg_container").html('<p class="text-center">Something went wrong!</p>');
                $("#globalMsgModal").modal('show');
            });
        }
    }
    function checkShellExecution(){
        // If cancell is true the recusrive function calling will stop.
        if (stopRecursiveCall) {
            return;
        } 
        var request = $.ajax({
            url: "check-shell-execution.php",
            type: "POST",
            dataType: "json"
        });
        request.done(function(responseData) {
            var resultData = null;
            try {
                resultData = JSON.parse(responseData); 
            } catch (e) {
                resultData = responseData;
            }
            console.log(responseData);
            if(responseData.status == "success"){
                if(responseData.result_data.script_status == "Complete"){
                    $("#comparison_table_overlay").addClass('hidden');
                    $("#comparison_loader").addClass('hidden');
                    // location.reload();
                    window.location.href = "<?php echo $baseUrl?>comparison.php";
                }else{
                    setTimeout(checkShellExecution, 5000);
                }
            }else{
                $("#comparison_table_overlay").addClass('hidden');
                $("#comparison_loader").addClass('hidden');
                console.log("Not Successfull: " + responseData);
                $("#global_msg_container").html(responseData.message);
                $("#globalMsgModal").modal('show');
            }
        });
        request.fail(function(jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
        });
    }
    // Closing the processing overlay clicking on that
    // $('body').on( 'click', '#comparison_table_overlay', function(e) {
    // 	stopRecursiveCall = true;
    // 	$(this).addClass('hidden');
    // });
    var swappedSymptoms = [];
    var connected_symptoms_saved = [];
    var connected_edited_symptoms_saved = [];
    var pasted_symptoms_saved = [];
    var pasted_edited_symptoms_saved = [];
    var connected_symptoms =[];
    var pasted_symptoms = [];
    //Paste Edit Variables
    var comparative_symptoms_original_pe =[];
    var comparative_symptoms_edited_pe =[];
    var initial_symptoms_connected_pe =[];
    var edited_comparative;
    var peComparativeId, peInitialId;
    var initialSymptomPE, comparativeSymptomPE;
    //Varibales for Connect Edit
    var cutoff_percentage = <?php echo $similarityRate;?>;
    var comparison_option = <?php echo $comparisonOption;?>;
    //console.log(cutoff_percentage);
    var initial_symptoms_original =[];
    var initial_symptoms_edited =[];
    var comparative_symptoms_connected =[];
    var ce_initials = {};
    var edited_initial;
    var ceComparativeId, ceInitialId;
    var initialSymptom, comparativeSymptom;
    var language, translation;
    language = "<?php echo $comparisonLanguage?>";
    var pass_through = 0;
    var swapped = false;	
    var swapped_modal_initial, swapped_modal_comparative;
    var latestIdArray;
    var latestInitialId="";
    var latestComparingId="";
    var markField;
    $(document).ready(function(){
        //Connection functions
        $.fn.connectEditFunction();
        $.fn.pasteFunction();
        $.fn.connectFunction();
        $.fn.pasteEditFunction();
        var singleConnectionComparativeCheck = [<?php echo $singleConnectionsComparativeString; ?>];
        var singleConnectionInitialCheck = [<?php echo $singleConnectionsInitialsString; ?>];
        var combinedConnectionIntialsCheck = [<?php echo $combinedConnectionsIntialsString; ?>];
        var combinedConnectionComparativeCheck = [<?php echo $combinedConnectionsComparativeString; ?>];
        //Sending ID's for saved connections check
        if(singleConnectionComparativeCheck.length > 0)
        {
            var symptomType = "comparative";
            $.ajax({
                async:false,
                type: "POST",
                url: "symptom-connection-operations.php",
                data: {
                    singleConnectionComparativeCheck:singleConnectionComparativeCheck,
                    symptomType: symptomType
                },
                dataType: "JSON",
                success: function(returnedData){
                    try {
                        resultData = JSON.parse(returnedData.result_data); 
                    } catch (e) {
                        resultData = returnedData.result_data;
                    }
                    for (var i=0; i<resultData.length; i++)
                    {
                        var source_type ="singleSourceComparative";
                        var comparing_symptom_id = resultData[i].comparing_symptom_id;
                        var initial_symptom_id = resultData[i].initial_symptom_id;
                        var matched_percentage = resultData[i].matched_percentage;
                        var comparing_quelle_id = resultData[i].comparing_quelle_id;
                        var initial_quelle_id = resultData[i].initial_quelle_id;
                        var highlighted_comparing_symptom_en = resultData[i].highlighted_comparing_symptom_en;
                        var highlighted_comparing_symptom_de = resultData[i].highlighted_comparing_symptom_de;
                        var highlighted_initial_symptom_en = resultData[i].highlighted_initial_symptom_en;
                        var highlighted_initial_symptom_de = resultData[i].highlighted_initial_symptom_de;
                        var comparison_language = resultData[i].comparison_language;
                        var comparing_quelle_code = resultData[i].comparing_quelle_code;
                        var initial_quelle_code = resultData[i].initial_quelle_code;
                        var comparing_year = resultData[i].comparing_year;
                        var comparing_symptom_de = resultData[i].comparing_symptom_de;
                        var comparing_symptom_en = resultData[i].comparing_symptom_en;
                        var comparing_quelle_original_language = resultData[i].comparing_quelle_original_language;
                        var initial_quelle_original_language = resultData[i].initial_quelle_original_language;
                        var initial_year = resultData[i].initial_year;
                        var initial_symptom_de = resultData[i].initial_symptom_de;
                        var initial_symptom_en = resultData[i].initial_symptom_en;
                        var non_encoded_comparing_symptom_de = resultData[i].non_encoded_comparing_symptom_de;
                        var non_encoded_comparing_symptom_en = resultData[i].non_encoded_comparing_symptom_en;
                        var non_encoded_initial_symptom_de = resultData[i].non_encoded_initial_symptom_de;
                        var non_encoded_initial_symptom_en = resultData[i].non_encoded_initial_symptom_en;
                        var connection_type = resultData[i].connection_type;
                        var is_earlier_connection = resultData[i].is_earlier_connection;
                        var free_flag = resultData[i].free_flag;
                        var connection_id = resultData[i].connection_id;
                        $.fn.connectSave(comparing_symptom_id, initial_symptom_id, matched_percentage, comparing_quelle_id,initial_quelle_id, highlighted_comparing_symptom_en, highlighted_comparing_symptom_de, highlighted_initial_symptom_en, highlighted_initial_symptom_de, comparison_language, comparing_quelle_code, initial_quelle_code, comparing_year, comparing_symptom_de, comparing_symptom_en, comparing_quelle_original_language,initial_quelle_original_language, initial_year, initial_symptom_de, initial_symptom_en,source_type,connection_type,is_earlier_connection,free_flag, connection_id);

                        // Translation Array
                        var tarnKeyCom = "row"+initial_symptom_id+"_"+comparing_symptom_id+"_translated_symptom";
                        var tarnKeyIni = "row"+initial_symptom_id+"_translated_symptom";
                        if(comparison_language == "de"){
                            // if(translationArray[tarnKeyCom] == undefined)
                                translationArray[tarnKeyCom] = non_encoded_comparing_symptom_en;
                            if(translationArray[tarnKeyIni] == undefined)
                                translationArray[tarnKeyIni] = non_encoded_initial_symptom_en;
                        }
                        else{
                            // if(translationArray[tarnKeyCom] == undefined)
                                translationArray[tarnKeyCom] = non_encoded_comparing_symptom_de;
                            if(translationArray[tarnKeyIni] == undefined)
                                translationArray[tarnKeyIni] = non_encoded_initial_symptom_de;
                        }

                    }
                },
                error: function(xhr, textStatus, error){
                    console.log(xhr.statusText);
                    console.log(textStatus);
                    console.log(error);
                }
            });
        }
        if(singleConnectionInitialCheck.length > 0)
        {
            $("#no_of_initials_single").val(singleConnectionInitialCheck.length);
            var symptomType = "initial";
            $.ajax({
                async:false,
                type: "POST",
                url: "symptom-connection-operations.php",
                data: {
                    singleConnectionInitialCheck:singleConnectionInitialCheck,
                    symptomType: symptomType
                },
                dataType: "JSON",
                success: function(returnedData){
                    try {
                        resultData = JSON.parse(returnedData.result_data); 
                        latestIdArray = JSON.parse(returnedData.latestIdResult); 
                    } catch (e) {
                        resultData = returnedData.result_data;
                        latestIdArray = returnedData.latestIdResult;
                    }
                    for (var i=0; i<resultData.length; i++)
                    {
                        var source_type ="singleSourceInitial";
                        var comparing_symptom_id = resultData[i].comparing_symptom_id;
                        var initial_symptom_id = resultData[i].initial_symptom_id;
                        var matched_percentage = resultData[i].matched_percentage;
                        var comparing_quelle_id = resultData[i].comparing_quelle_id;
                        var initial_quelle_id = resultData[i].initial_quelle_id;
                        var highlighted_comparing_symptom_en = resultData[i].highlighted_comparing_symptom_en;
                        var highlighted_comparing_symptom_de = resultData[i].highlighted_comparing_symptom_de;
                        var highlighted_initial_symptom_en = resultData[i].highlighted_initial_symptom_en;
                        var highlighted_initial_symptom_de = resultData[i].highlighted_initial_symptom_de;
                        var comparison_language = resultData[i].comparison_language;
                        var comparing_quelle_code = resultData[i].comparing_quelle_code;
                        var initial_quelle_code = resultData[i].initial_quelle_code;
                        var comparing_year = resultData[i].comparing_year;
                        var comparing_symptom_de = resultData[i].comparing_symptom_de;
                        var comparing_symptom_en = resultData[i].comparing_symptom_en;
                        var comparing_quelle_original_language = resultData[i].comparing_quelle_original_language;
                        var initial_quelle_original_language = resultData[i].initial_quelle_original_language;
                        var initial_year = resultData[i].initial_year;
                        var initial_symptom_de = resultData[i].initial_symptom_de;
                        var initial_symptom_en = resultData[i].initial_symptom_en;
                        var non_encoded_comparing_symptom_de = resultData[i].non_encoded_comparing_symptom_de;
                        var non_encoded_comparing_symptom_en = resultData[i].non_encoded_comparing_symptom_en;
                        var non_encoded_initial_symptom_de = resultData[i].non_encoded_initial_symptom_de;
                        var non_encoded_initial_symptom_en = resultData[i].non_encoded_initial_symptom_en;
                        var connection_type = resultData[i].connection_type;
                        var is_earlier_connection = resultData[i].is_earlier_connection;
                        var free_flag = resultData[i].free_flag;
                        var connection_id = resultData[i].connection_id;
                        $.fn.connectSave(comparing_symptom_id, initial_symptom_id, matched_percentage, comparing_quelle_id,initial_quelle_id, highlighted_comparing_symptom_en, highlighted_comparing_symptom_de, highlighted_initial_symptom_en, highlighted_initial_symptom_de, comparison_language, comparing_quelle_code, initial_quelle_code, comparing_year, comparing_symptom_de, comparing_symptom_en, comparing_quelle_original_language,initial_quelle_original_language, initial_year, initial_symptom_de, initial_symptom_en,source_type,connection_type,is_earlier_connection,free_flag, connection_id);

                        // Translation Array
                        var tarnKeyCom = "row"+initial_symptom_id+"_"+comparing_symptom_id+"_translated_symptom";
                        var tarnKeyIni = "row"+initial_symptom_id+"_translated_symptom";
                        if(comparison_language == "de"){
                            // if(translationArray[tarnKeyCom] == undefined)
                                translationArray[tarnKeyCom] = non_encoded_comparing_symptom_en;
                            if(translationArray[tarnKeyIni] == undefined)
                                translationArray[tarnKeyIni] = non_encoded_initial_symptom_en;
                        }
                        else{
                            // if(translationArray[tarnKeyCom] == undefined)
                                translationArray[tarnKeyCom] = non_encoded_comparing_symptom_de;
                            if(translationArray[tarnKeyIni] == undefined)
                                translationArray[tarnKeyIni] = non_encoded_initial_symptom_de;
                        }

                    }
                },
                error: function(xhr, textStatus, error){
                    console.log(xhr.statusText);
                    console.log(textStatus);
                    console.log(error);
                }
            });
        }
        if(combinedConnectionIntialsCheck.length > 0)
        {
            $("#no_of_initials_combined").val(combinedConnectionIntialsCheck.length);
            var symptomType = "combined-initial";
            $.ajax({
                async:false,
                type: "POST",
                url: "symptom-connection-operations.php",
                data: {
                    combinedConnectionIntialsCheck:combinedConnectionIntialsCheck,
                    symptomType: symptomType
                },
                dataType: "JSON",
                success: function(returnedData){
                    try {
                        resultData = JSON.parse(returnedData.result_data); 
                        latestIdArray = JSON.parse(returnedData.latestIdResult); 
                    } catch (e) {
                        resultData = returnedData.result_data;
                        latestIdArray = returnedData.latestIdResult;
                    }
                    for (var i=0; i<resultData.length; i++)
                    {
                        var source_type = "combinedSourceInitials";
                        var comparing_symptom_id = resultData[i].comparing_symptom_id;
                        var initial_symptom_id = resultData[i].initial_symptom_id;
                        var matched_percentage = resultData[i].matched_percentage;
                        var comparing_quelle_id = resultData[i].comparing_quelle_id;
                        var initial_quelle_id = resultData[i].initial_quelle_id;
                        var highlighted_comparing_symptom_en = resultData[i].highlighted_comparing_symptom_en;
                        var highlighted_comparing_symptom_de = resultData[i].highlighted_comparing_symptom_de;
                        var highlighted_initial_symptom_en = resultData[i].highlighted_initial_symptom_en;
                        var highlighted_initial_symptom_de = resultData[i].highlighted_initial_symptom_de;
                        var comparison_language = resultData[i].comparison_language;
                        var comparing_quelle_code = resultData[i].comparing_quelle_code;
                        var initial_quelle_code = resultData[i].initial_quelle_code;
                        var comparing_year = resultData[i].comparing_year;
                        var comparing_symptom_de = resultData[i].comparing_symptom_de;
                        var comparing_symptom_en = resultData[i].comparing_symptom_en;
                        var comparing_quelle_original_language = resultData[i].comparing_quelle_original_language;
                        var initial_quelle_original_language = resultData[i].initial_quelle_original_language;
                        var initial_year = resultData[i].initial_year;
                        var initial_symptom_de = resultData[i].initial_symptom_de;
                        var initial_symptom_en = resultData[i].initial_symptom_en;
                        var is_earlier_connection = resultData[i].is_earlier_connection;
                        var free_flag = resultData[i].free_flag;
                        var connection_id = resultData[i].connection_id;
                        var non_encoded_comparing_symptom_de = resultData[i].non_encoded_comparing_symptom_de;
                        var non_encoded_comparing_symptom_en = resultData[i].non_encoded_comparing_symptom_en;
                        var non_encoded_initial_symptom_de = resultData[i].non_encoded_initial_symptom_de;
                        var non_encoded_initial_symptom_en = resultData[i].non_encoded_initial_symptom_en;
                        var connection_type = resultData[i].connection_type;
                        $.fn.connectSave(comparing_symptom_id, initial_symptom_id, matched_percentage, comparing_quelle_id,initial_quelle_id, highlighted_comparing_symptom_en, highlighted_comparing_symptom_de, highlighted_initial_symptom_en, highlighted_initial_symptom_de, comparison_language, comparing_quelle_code, initial_quelle_code, comparing_year, comparing_symptom_de, comparing_symptom_en, comparing_quelle_original_language,initial_quelle_original_language, initial_year, initial_symptom_de, initial_symptom_en,source_type,connection_type,is_earlier_connection,free_flag, connection_id);

                        // Translation Array
                        var tarnKeyCom = "row"+initial_symptom_id+"_"+comparing_symptom_id+"_translated_symptom";
                        var tarnKeyIni = "row"+initial_symptom_id+"_translated_symptom";
                        if(comparison_language == "de"){
                            // if(translationArray[tarnKeyCom] == undefined)
                                translationArray[tarnKeyCom] = non_encoded_comparing_symptom_en;
                            if(translationArray[tarnKeyIni] == undefined)
                                translationArray[tarnKeyIni] = non_encoded_initial_symptom_en;
                        }
                        else{
                            // if(translationArray[tarnKeyCom] == undefined)
                                translationArray[tarnKeyCom] = non_encoded_comparing_symptom_de;
                            if(translationArray[tarnKeyIni] == undefined)
                                translationArray[tarnKeyIni] = non_encoded_initial_symptom_de;
                        }

                    }
                },
                error: function(xhr, textStatus, error){
                    console.log(xhr.statusText);
                    console.log(textStatus);
                    console.log(error);
                }
            });
        }
        if(combinedConnectionComparativeCheck.length > 0)
        {
            var symptomType = "combined-comparative";
            $.ajax({
                async:false,
                type: "POST",
                url: "symptom-connection-operations.php",
                data: {
                    combinedConnectionComparativeCheck:combinedConnectionComparativeCheck,
                    symptomType: symptomType
                },
                dataType: "JSON",
                success: function(returnedData){
                    try {
                        resultData = JSON.parse(returnedData.result_data); 
                    } catch (e) {
                        resultData = returnedData.result_data;
                    }
                    for (var i=0; i<resultData.length; i++)
                    {
                        var source_type = "combinedSourceComparative";
                        var comparing_symptom_id = resultData[i].comparing_symptom_id;
                        var initial_symptom_id = resultData[i].initial_symptom_id;
                        var matched_percentage = resultData[i].matched_percentage;
                        var comparing_quelle_id = resultData[i].comparing_quelle_id;
                        var initial_quelle_id = resultData[i].initial_quelle_id;
                        var highlighted_comparing_symptom_en = resultData[i].highlighted_comparing_symptom_en;
                        var highlighted_comparing_symptom_de = resultData[i].highlighted_comparing_symptom_de;
                        var highlighted_initial_symptom_en = resultData[i].highlighted_initial_symptom_en;
                        var highlighted_initial_symptom_de = resultData[i].highlighted_initial_symptom_de;
                        var comparison_language = resultData[i].comparison_language;
                        var comparing_quelle_code = resultData[i].comparing_quelle_code;
                        var initial_quelle_code = resultData[i].initial_quelle_code;
                        var comparing_year = resultData[i].comparing_year;
                        var comparing_symptom_de = resultData[i].comparing_symptom_de;
                        var comparing_symptom_en = resultData[i].comparing_symptom_en;
                        var comparing_quelle_original_language = resultData[i].comparing_quelle_original_language;
                        var initial_quelle_original_language = resultData[i].initial_quelle_original_language;
                        var initial_year = resultData[i].initial_year;
                        var initial_symptom_de = resultData[i].initial_symptom_de;
                        var initial_symptom_en = resultData[i].initial_symptom_en;
                        var non_encoded_comparing_symptom_de = resultData[i].non_encoded_comparing_symptom_de;
                        var non_encoded_comparing_symptom_en = resultData[i].non_encoded_comparing_symptom_en;
                        var non_encoded_initial_symptom_de = resultData[i].non_encoded_initial_symptom_de;
                        var non_encoded_initial_symptom_en = resultData[i].non_encoded_initial_symptom_en;
                        var connection_type = resultData[i].connection_type;
                        var is_earlier_connection = resultData[i].is_earlier_connection;
                        var free_flag = resultData[i].free_flag;
                        var connection_id = resultData[i].connection_id;
                        $.fn.connectSave(comparing_symptom_id, initial_symptom_id, matched_percentage, comparing_quelle_id,initial_quelle_id, highlighted_comparing_symptom_en, highlighted_comparing_symptom_de, highlighted_initial_symptom_en, highlighted_initial_symptom_de, comparison_language, comparing_quelle_code, initial_quelle_code, comparing_year, comparing_symptom_de, comparing_symptom_en, comparing_quelle_original_language,initial_quelle_original_language, initial_year, initial_symptom_de, initial_symptom_en,source_type,connection_type,is_earlier_connection,free_flag, connection_id);

                        // Translation Array
                        var tarnKeyCom = "row"+initial_symptom_id+"_"+comparing_symptom_id+"_translated_symptom";
                        var tarnKeyIni = "row"+initial_symptom_id+"_translated_symptom";
                        if(comparison_language == "de"){
                            // if(translationArray[tarnKeyCom] == undefined)
                                translationArray[tarnKeyCom] = non_encoded_comparing_symptom_en;
                            if(translationArray[tarnKeyIni] == undefined)
                                translationArray[tarnKeyIni] = non_encoded_initial_symptom_en;
                        }
                        else{
                            // if(translationArray[tarnKeyCom] == undefined)
                                translationArray[tarnKeyCom] = non_encoded_comparing_symptom_de;
                            if(translationArray[tarnKeyIni] == undefined)
                                translationArray[tarnKeyIni] = non_encoded_initial_symptom_de;
                        }

                    }
                },
                error: function(xhr, textStatus, error){
                    console.log(xhr.statusText);
                    console.log(textStatus);
                    console.log(error);
                }
            });
        }
        /////////////////////////Toggle Icons/////////////////////////
        $(document).on("click", ".toggleInitial", function (ev) {
            $(this).parents('div.initial').nextUntil(".initial").each(function()
            {
                if($(this).hasClass('comparativesConnectedCD'))
                    $(this).toggle();
                if($(this).hasClass('comparativesConnectedCE'))
                    $(this).toggle();
                if($(this).hasClass('comparativesConnectedPE'))
                    $(this).toggle();
                if($(this).hasClass('comparativesConnectedPASTE'))
                    $(this).toggle();
            });
            if($(this).find('i').hasClass('fa-plus'))
                $(this).find('i').removeClass('fa-plus').addClass('fa-minus')
            else
                $(this).find('i').removeClass('fa-minus').addClass('fa-plus')
        });
        $(document).on("click", ".toggleComparative", function (ev) {
            $(this).parents('div.comparing').nextUntil('.comparing').each(function(){
                if($(this).hasClass('initialsConnectedCD'))
                    $(this).toggle();
                if($(this).hasClass('initialsConnectedCE'))
                    $(this).toggle();
                if($(this).hasClass('initialsConnectedPE'))
                    $(this).toggle();
                if($(this).hasClass('initialsConnectedPASTE'))
                    $(this).toggle();
                if($(this).hasClass('comparativesConnectedPASTE'))
                    $(this).toggle();
            });
            
            if($(this).find('i').hasClass('fa-plus'))
                $(this).find('i').removeClass('fa-plus').addClass('fa-minus');
            else
                $(this).find('i').removeClass('fa-minus').addClass('fa-plus');
        });
        //opening the latest connections
        if(latestIdArray != null){
            latestInitialId = latestIdArray.initial_symptom_id;
            latestComparingId = latestIdArray.comparing_symptom_id;
            $('.row'+latestInitialId).each(function(){
                if($(this).find('.toggleInitial')){
                    $(this).find('.toggleInitial').trigger('click');
                }
            });	
        }
        /////////////////////////DELETE SYMPTOM WHEN DISCONNECT LINK IS CLICKED /////////////////////////
        $.fn.deleteSymptoms = function(initialSymp, comparativeSymp,operation){ 
            $.ajax({
                async:false,
                type: "POST",
                url: "connection-delete-script.php",
                data: "type=normal&initialSymptom="+initialSymp+"&symptom="+comparativeSymp+"&operation="+operation,
                dataType: "JSON",
                success: function(returnedData){
                    console.log(returnedData);
                },
                error: function(xhr, textStatus, error){
                    console.log(xhr.statusText);
                    console.log(textStatus);
                    console.log(error);
                }
            });
        }
        /////////////////////////SAVING WITH AJAX/////////////////////////
        $.fn.saveConnects = function(connect_type, comparativeId, initial_id_to_send, comparison_language,connected_percentage,  comparative_symptom_text, initial_symptom_text, comparing_quelle_code, initial_quelle_code, comparing_year, comparing_symptom_de, comparing_symptom_en, comparing_quelle_original_language, comparing_quelle_id, initial_year, initial_symptom_de, initial_symptom_en, initial_quelle_id, initial_quelle_original_language)
        {
            $.ajax({
                async:false,
                type: "POST",
                url: "connection-save-script.php",
                data:{
                    connect_type : connect_type,
                    comparing_symptom_id: comparativeId,
                    initial_symptom_id: initial_id_to_send,
                    comparison_language: comparison_language,
                    matched_percentage : connected_percentage,
                    initial_quelle_id : initial_quelle_id,
                    comparative_symptom_text: comparative_symptom_text,
                    initial_symptom_text: initial_symptom_text,
                    comparing_quelle_code: comparing_quelle_code,
                    initial_quelle_code: initial_quelle_code,
                    comparing_year : comparing_year,
                    comparing_symptom_de : comparing_symptom_de,
                    comparing_symptom_en : comparing_symptom_en,
                    comparing_quelle_original_language : comparing_quelle_original_language,
                    comparing_quelle_id : comparing_quelle_id,
                    initial_year : initial_year,
                    initial_symptom_de : initial_symptom_de,
                    initial_symptom_en : initial_symptom_en,
                    initial_quelle_id : initial_quelle_id,	
                    initial_quelle_original_language : initial_quelle_original_language	
                },
                dataType: "JSON",
                success: function(returnedData){
                },
                error: function(xhr, textStatus, error){
                    console.log(xhr.statusText);
                    console.log(textStatus);
                    console.log(error);
                }
            });
        }
        $.fn.saveConnectsEarlier = function(comparativeId, earlierConnectedId,initial_id_to_send, initial_symptom_text, initial_quelle_code, initial_year, initial_symptom_de, initial_symptom_en, initial_quelle_id, initial_quelle_original_language,comparison_language,type,free_flag,sub_connected_percentage)
        {
            $.ajax({
                async:false,
                type: "POST",
                url: "connection-save-script-earlier.php",
                data:{
                    comparing_symptom_id: comparativeId,
                    initial_symptom_id: initial_id_to_send,
                    earlier_symptom_id: earlierConnectedId,
                    initial_quelle_id : initial_quelle_id,
                    initial_symptom_text: initial_symptom_text,
                    initial_quelle_code: initial_quelle_code,
                    initial_year : initial_year,
                    initial_symptom_de : initial_symptom_de,
                    initial_symptom_en : initial_symptom_en,
                    initial_quelle_id : initial_quelle_id,	
                    initial_quelle_original_language : initial_quelle_original_language,
                    comparison_language : comparison_language,	
                    type : type,
                    free_flag : free_flag,	
                    sub_connected_percentage : sub_connected_percentage,
                    comparison_option : comparison_option
                },
                dataType: "JSON",
                success: function(returnedData){
                },
                error: function(xhr, textStatus, error){
                    console.log(xhr.statusText);
                    console.log(textStatus);
                    console.log(error);
                }
            });
        }
        ////////////////////////////END SAVING///////////////////////////
        //Comments on load function starts
        var comments_on_load = [<?php echo $commentsOnLoad; ?>];
        //console.log(comments_on_load);
        //non secure connection active icon load
        if(comments_on_load.length > 0)
        {
            comments_on_load.forEach((commentsLoad) => {
                $.fn.commentsOnLoadFn(commentsLoad);
            });
        }
        //Comments on load function ends
        //Footnote on load function starts
        var footnote_on_load = [<?php echo $footnoteOnLoad; ?>];
        //console.log(footnote_on_load);
        //non secure connection active icon load
        if(footnote_on_load.length > 0)
        {
            footnote_on_load.forEach((footnoteLoad) => {
                $.fn.footnoteOnLoadFn(footnoteLoad);
            });
        }
        //Footnote on load function ends
        //Translations on load function starts
        var translation_on_load = [<?php echo $translations; ?>];
        //console.log(translation_on_load);
        if(translation_on_load.length > 0)
        {
            translation_on_load.forEach((translationLoad) => {
                $.fn.translationOnLoadFn(translationLoad);
            });
        }
        //Translations on load function ends	
        //Non secure connections on load starts
        var non_secure_on_load = [<?php echo $savedNonSecureIdArrayString; ?>];
        var non_secure_on_load_initials = [<?php echo $savedNonSecureInitialIdArrayString; ?>];
        var nonSecureCount = 0;
        if(non_secure_on_load.length > 0)
        {
            non_secure_on_load.forEach((nonSecureLoad) => {
                var initialNS = non_secure_on_load_initials[nonSecureCount];
                $.fn.nonSecureOnLoad(nonSecureLoad,initialNS);
                nonSecureCount++;
            });
        }
        //Non secure connections on load ends
        //general ns on load starts
        var gen_ns_on_load = [<?php echo $genNsOnLoad; ?>];
        if(gen_ns_on_load.length > 0)
        {
            gen_ns_on_load.forEach((genNsLoad) => {
                $.fn.genNsOnLoadFn(genNsLoad);
            });
        }
        //general ns on load ends
        $(zeroComparativeIdsFinalArray).each(function(){
            var id = "#row"+this;
            if(!($(id).next("div.symptom-row").hasClass("comparing"))){
                $(id).find(".gen-ns").addClass("ns-disabled");
                $(id).find(".marking").attr("checked","1");
            }
        });
        //saving a comparison function
        function savingComparison(param){
            // if(param == 0){
            // 	$("#saveSubmit").submit();
            // }else{
                $("#temp_unmark_check").val(param);
                $("#saveSubmit").submit();
            // }
        }
        //save button alert
        $(".comparison-table-save-btn").on("click", function (x){
            confirmation = confirm("Do you really want to save this comparison?");
            if(confirmation == true){
                $.ajax({
                    async:false,
                    type: "POST",
                    url: "check-unmarked-symptoms.php",
                    data: {
                        similarityRate:cutoff_percentage,
                        role:role,
                        comparisonTableName:comparison_table_name
                    },
                    dataType: "JSON",
                    success: function(returnedData){
                        try {
                            resultData = JSON.parse(returnedData.result_data);  
                            returnType = JSON.parse(returnedData.returnType);  
                        } catch (e) {
                            resultData = returnedData.result_data;
                            returnType = returnedData.returnType;
                        }
                        switch(returnType){
                            case "unmarked":{
                                if(resultData > 0){
                                    confirmationUnmarked = confirm("There are "+resultData+" unmarked symptoms. Do you want to check it?");
                                    if(confirmationUnmarked == true){
                                        savingComparison(1);
                                    }
                                }else{
                                    savingComparison(0);
                                }
                            }break;
                            case "ns_connect":{
                                if(resultData > 0){
                                    confirmationUnmarked = confirm("There are "+resultData+" non secure connect symptoms. Do you want to check it?");
                                    if(confirmationUnmarked == true){
                                        $('.ns-normal-btn').click();
                                    }
                                }
                            }break;
                            case "ns_paste":{
                                if(resultData > 0){
                                    confirmationUnmarked = confirm("There are "+resultData+" non secure paste symptoms. Do you want to check it?");
                                    if(confirmationUnmarked == true){
                                        $('.ns-normal-btn-p').click();
                                    }
                                }
                            }break;
                            case "ns_general":{
                                if(resultData > 0){
                                    confirmationUnmarked = confirm("There are "+resultData+" non secure initial symptoms. Do you want to check it?");
                                    if(confirmationUnmarked == true){
                                        $('.gen-ns-btn').click();
                                    }
                                }
                            }break;
                            default:{
                                //final save
                                savingComparison(0);
                            }
                        }
                    },
                    error: function(xhr, textStatus, error){
                        console.log(xhr.statusText);
                        console.log(textStatus);
                        console.log(error);
                    }
                });
            }
        });
        //submission of non secure list
        $(".ns-normal-btn").on("click", function (x){
            if($(".ns-normal-btn").hasClass("ns-c-active")){
                window.location.href = "<?php echo $baseUrl?>comparison.php";
            }else{
                var checkingInConnection = checkInConnectionTable('1', comparison_table_name);
                if(checkingInConnection == 0)
                    alert("There are no non secure connections.");
                else
                    $("#saveSubmitNs").submit();
            }
        });
        $(".gen-ns-btn").on("click", function (x){
            if($(".gen-ns-btn").hasClass("ns-g-active")){
                window.location.href = "<?php echo $baseUrl?>comparison.php";
            }else{
                var checkingInConnection = checkInConnectionTable('3', comparison_table_name);
                if(checkingInConnection == 0)
                    alert("There are no general non secure connections.");
                else
                    $("#saveSubmitGenNs").submit();
            }
        });
        $(".ns-normal-btn-p").on("click", function (x){
            if($(".ns-normal-btn-p").hasClass("ns-p-active")){
                window.location.href = "<?php echo $baseUrl?>comparison.php";
            }else{
                var checkingInConnection = checkInConnectionTable('2', comparison_table_name);
                if(checkingInConnection == 0)
                    alert("There are no non secure paste connections.");
                else
                    $("#saveSubmitNsP").submit();
            }
        });
        $(".ns-normal-btn-f").on("click", function (x){
            if($(".ns-normal-btn-f").hasClass("ns-f-active")){
                window.location.href = "<?php echo $baseUrl?>comparison.php";
            }else{
                $("#saveSubmitNsF").submit();
            }
        });

    });//End document ready function
    //checking if the document is ready for hiding loader 
    $(function() {
        if(comparison_table_check != "")
            $('#comparison_loader').addClass("hidden");
    });
    $('body').on( 'click', '#all_initial_translation', function(e) {
        if($(this).prop("checked") == true) {
            $.when().done(function () {
                $("#common_small_loader").removeClass('hidden');
                $(".page-link").each(function(){
                    var href = $(this).prop('href');
                    var newHref = href+"&open_ini_trans=1";
                    $(this).attr("href", newHref);
                });
            }).then(function () {
                $(".symptom-initial-translation-btn").each(function(){
                    var uniqueId = $(this).attr("data-unique-id");
                    if (!$(this).parent().parent().parent().parent().children("div.symptom").find("div#translation_display_"+uniqueId).length){
                        $(this).click();
                    }
                }).promise().done( function(){ $("#common_small_loader").addClass('hidden'); } );
            });
        }else{
            $.when().done(function () {
                $("#common_small_loader").removeClass('hidden');
                $(".page-link").each(function(){
                    var href = $(this).prop('href');
                    var newHref = href.replace('&open_ini_trans=1','');
                    $(this).attr("href", newHref);
                });
            }).then(function () {
                $(".symptom-initial-translation-btn").each(function(){
                    var uniqueId = $(this).attr("data-unique-id");
                    if ($(this).parent().parent().parent().parent().children("div.symptom").find("div#translation_display_"+uniqueId).length){
                        $(this).click();
                    }
                }).promise().done( function(){ $("#common_small_loader").addClass('hidden'); } );
            });
        } 
    });
    $('body').on( 'click', '#all_comparative_translation', function(e) {
        if($(this).prop("checked") == true) {
            $.when().done(function () {
                $("#common_small_loader").removeClass('hidden');
                $(".page-link").each(function(){
                    var href = $(this).prop('href');
                    var newHref = href+"&open_com_trans=1";
                    $(this).attr("href", newHref);
                });
            }).then(function () {
                $(".symptom-comparative-translation-btn").each(function(){
                    var uniqueId = $(this).attr("data-unique-id");
                    if (!$(this).parent().parent().parent().parent().children("div.symptom").find("div#translation_display_"+uniqueId).length){
                        $(this).click();
                    }
                }).promise().done( function(){ $("#common_small_loader").addClass('hidden'); } );
            });  
        }else{
            $.when().done(function () {
                $("#common_small_loader").removeClass('hidden');
                $(".page-link").each(function(){
                    var href = $(this).prop('href');
                    var newHref = href.replace('&open_com_trans=1','');
                    $(this).attr("href", newHref);
                });
            }).then(function () {
                $(".symptom-comparative-translation-btn").each(function(){
                    var uniqueId = $(this).attr("data-unique-id");
                    if ($(this).parent().parent().parent().parent().children("div.symptom").find("div#translation_display_"+uniqueId).length){
                        $(this).click();
                    }
                }).promise().done( function(){ $("#common_small_loader").addClass('hidden'); } );
            });
        } 
    });
    $('body').on( 'click', '.symptom-translation-btn', function(e) {
        e.preventDefault();
        var uniqueId = $(this).attr("data-unique-id");
        var keyString = uniqueId+'_translated_symptom';
        var symptomTranslation = (typeof(translationArray[keyString]) != "undefined" && translationArray[keyString] !== null && translationArray[keyString] != "") ? translationArray[keyString] : "";
        if ($(this).parent().parent().parent().parent().children("div.symptom").find("div#translation_display_"+uniqueId).length){
            $(this).parent().parent().parent().parent().children("div.symptom").find("div#translation_display_"+uniqueId).remove();
        }else{
            if(symptomTranslation == "" || symptomTranslation == "Translation is not available"){
                $(this).parents('div#'+uniqueId).find("div.symptom").append('<div id="translation_display_'+uniqueId+'"></div>');
            }else{
                $(this).parents('div#'+uniqueId).find("div.symptom").append('<div id="translation_display_'+uniqueId+'" class="translated-symptom-div">'+symptomTranslation+'</div>');
            }
        }
    });
    $('body').on("click", ".all-connections", function (x){
        if($(this).prop("checked") == true) {
            $.when().done(function () {
                $("#common_small_loader").removeClass('hidden');
                $(".page-link").each(function(){
                    var href = $(this).prop('href');
                    var newHref = href+"&open_conn=1";
                    $(this).attr("href", newHref);
                });
            }).then(function () {
                $(".toggleInitial").each(function(){
                    if($(this).children('.fas').hasClass('fa-plus')){
                        $(this).click();
                    }
                }).promise().done( function(){ $("#common_small_loader").addClass('hidden'); } );
            });
        }else{
            $.when().done(function () {
                $("#common_small_loader").removeClass('hidden');
                $(".page-link").each(function(){
                    var href = $(this).prop('href');
                    var newHref = href.replace('&open_conn=1','');
                    $(this).attr("href", newHref);
                });
            }).then(function () {
                $(".toggleInitial").each(function(){
                    if($(this).children('.fas').hasClass('fa-minus')){
                        $(this).click();
                    }
                }).promise().done( function(){ $("#common_small_loader").addClass('hidden'); } );
            });
        }
    });
    //marking
    $('body').on("click", ".marking", function (x){
        var markedValue = "0";
        if($(this).prop("checked") == true) {
            markedValue = "1";
        }
        var initialSymptom = $(this).attr("value");
        initialSymptom = initialSymptom.replace("row","");
        $.ajax({
            async:false,
            type: "POST",
            url: "update-marking-symptoms.php",
            data: {
                initialSymptom:initialSymptom,
                markedValue:markedValue,
                comparisonTableName:comparison_table_name
            },
            dataType: "JSON",
            success: function(returnedData){
                try {
                    resultData = JSON.parse(returnedData.result_data); 
                } catch (e) {
                    resultData = returnedData.result_data;
                }
            },
            error: function(xhr, textStatus, error){
                console.log(xhr.statusText);
                console.log(textStatus);
                console.log(error);
            }
        });
    });
    //non secure radio
    $(document).on('change', 'input[type=radio][name=ns_radio]', function(){
        //var initialIdNs = $(this).parents('div#populated_nsc_note_data').find("#initial_id_nsc_note_modal").attr('value');
        var val = $(this).attr("value");
        var className = $(this).attr("class");
        if(className == "ns-confirm"){
            if(val == 0){
                $(this).attr("value","1");
                $(".ns-new").attr("value","0");
            }
        }else{
            if(val == 0){
                $(this).attr("value","1");
                $(".ns-confirm").attr("value","0");
            }
        }
    });
</script>