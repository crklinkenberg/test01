<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	include '../api/mainCall.php';
?>
<?php
	$masterId = (isset($_GET['mid']) AND $_GET['mid'] != "") ? $_GET['mid'] : ""; 
	if($masterId == ""){
		header('Location: '.$baseUrl);
		exit();
	}
?>
<?php
$_SESSION['current_page'] = $actual_link;
// $baseUrl = 'http://www.newrepertory.com/comparenew/';
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Symptoms</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="<?php echo $absoluteUrl;?>plugins/jasny-bootstrap/jasny-bootstrap.min.css">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="<?php echo $absoluteUrl;?>plugins/bootstrap/dist/css/bootstrap.min.css">
  <!-- dropify -->
  <link rel="stylesheet" type="text/css" href="<?php echo $absoluteUrl;?>plugins/dropify/css/dropify.min.css">
  <!-- Font Awesome -->
  <!-- <link rel="stylesheet" href="<?php echo $absoluteUrl;?>plugins/font-awesome/css/font-awesome.min.css"> -->
  <link rel="stylesheet" href="<?php echo $absoluteUrl;?>plugins/font-awesome/css/fontawesome-all.min.css">
  <!-- <link rel="stylesheet" href="<?php echo $baseUrl;?>plugins/font-awesome/css/fontawesome-all.min.css"> -->
  <!-- Ionicons -->
  <link rel="stylesheet" href="<?php echo $absoluteUrl;?>plugins/Ionicons/css/ionicons.min.css">
  <link rel="stylesheet" href="<?php echo $absoluteUrl;?>assets/css/skins/_all-skins.min.css">
  <!-- Jquery UI-->
  <link rel="stylesheet" href="<?php echo $absoluteUrl;?>plugins/jquery-ui/themes/base/jquery-ui.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="<?php echo $absoluteUrl;?>plugins/datatables.net-bs/css/dataTables.bootstrap.min.css">
  <!-- sweet alert 2 -->
  <link rel="stylesheet" type="text/css" href="<?php echo $absoluteUrl;?>plugins/sweetalert2/sweetalert2.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="<?php echo $absoluteUrl;?>plugins/select2/dist/css/select2.min.css">
  <!-- Theme style -->
  <!-- <link rel="stylesheet" href="<?php echo $absoluteUrl;?>assets/css/AdminLTE.min.css"> -->
  <!-- custom css -->
  <link rel="stylesheet" href="<?php echo $absoluteUrl;?>assets/css/custom.css">
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  <?php if(preg_match("/quellen/", $actual_link) || preg_match("/zeitschriften/", $actual_link)) {
  ?>
  <link rel="stylesheet" href="<?php echo $absoluteUrl;?>assets/css/custom-datepicker.css">
  <?php } ?>
  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
  <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12 symptoms-container">
            <?php
                $quellen_value = "";
                $importedLanguage = "";
                $quelleResult = mysqli_query($db,"SELECT quelle.quelle_id, quelle.code, quelle.titel, quelle.jahr, quelle.band, quelle.nummer, quelle.auflage, quelle.quelle_type_id, quelle.autor_or_herausgeber as bucher_autor_or_herausgeber, autor.suchname as zeitschriften_autor_suchname, autor.vorname as zeitschriften_autor_vorname, autor.nachname as zeitschriften_autor_nachname, quelle_import_master.importing_language FROM quelle JOIN quelle_import_master ON quelle.quelle_id = quelle_import_master.quelle_id LEFT JOIN quelle_autor ON quelle.quelle_id = quelle_autor.quelle_id LEFT JOIN autor ON quelle_autor.autor_id = autor.autor_id WHERE quelle_import_master.id = '".$masterId."' ORDER BY quelle.quelle_type_id ASC");
                if(mysqli_num_rows($quelleResult) > 0){
                    $quelleRow = mysqli_fetch_assoc($quelleResult);

                    $importedLanguage = $quelleRow['importing_language'];
                    $quellen_value = $quelleRow['code'];
                    if($quelleRow['quelle_type_id'] != 3){
                        // if(!empty($quelleRow['jahr'])) $quellen_value .= ', '.$quelleRow['jahr'];
                        // if(!empty($quelleRow['titel'])) $quellen_value .= ', '.$quelleRow['titel'];
                        // if($quelleRow['quelle_type_id'] == 1){
                        // 	if(!empty($quelleRow['bucher_autor_or_herausgeber'])) $quellen_value .= ', '.$quelleRow['bucher_autor_or_herausgeber'];
                        // }else if($quelleRow['quelle_type_id'] == 2){
                        // 	if(!empty($quelleRow['zeitschriften_autor_suchname']) ) 
                        // 		$zeitschriften_autor = $quelleRow['zeitschriften_autor_suchname']; 
                        // 	else 
                        // 		$zeitschriften_autor = $quelleRow['zeitschriften_autor_vorname'].' '.$quelleRow['zeitschriften_autor_nachname'];
                        // 	if(!empty($zeitschriften_autor)) $quellen_value .= ', '.$zeitschriften_autor;
                        // }

                        $quellen_value = (!empty($quelleRow['titel'])) ? $quelleRow['titel'] : "";
                        if($quelleRow['quelle_type_id'] == 1){
                            if(!empty($quelleRow['bucher_autor_or_herausgeber'])) $quellen_value .= ', '.$quelleRow['bucher_autor_or_herausgeber'];
                        }else if($quelleRow['quelle_type_id'] == 2){
                            if(!empty($quelleRow['zeitschriften_autor_suchname']) ) 
                                $zeitschriften_autor = $quelleRow['zeitschriften_autor_suchname']; 
                            else if($quelleRow['zeitschriften_autor_vorname'] != "" AND $quelleRow['zeitschriften_autor_nachname'] != "") 
                                $zeitschriften_autor = $quelleRow['zeitschriften_autor_vorname'].' '.$quelleRow['zeitschriften_autor_nachname'];
                            else
                                $zeitschriften_autor = "";
                            if(!empty($zeitschriften_autor)) $quellen_value .= ', '.$zeitschriften_autor;
                        }
                        if(!empty($quelleRow['jahr'])) $quellen_value .= ', '.$quelleRow['jahr'];

                        /*if(!empty($quelleRow['jahr'])) $quellen_value .= ' '.$quelleRow['jahr'];
                        if(!empty($quelleRow['band'])) $quellen_value .= ', Band: '.$quelleRow['band'];
                        if(!empty($quelleRow['nummer'])) $quellen_value .= ', Nr.: '. $quelleRow['nummer'];
                        if(!empty($quelleRow['auflage'])) $quellen_value .= ', Auflage: '. $quelleRow['auflage'];*/
                    }
                    // if(!empty($quelleRow['titel']))
                    // 	$quellen_value = $quelleRow['titel'];
                    // else
                    // 	$quellen_value = rtrim(trim($quellen_value),",");
                    $quellen_value = rtrim(trim($quellen_value),",");
                }
            ?>
            <h2>Symptoms of Source:  <?php echo $quellen_value; ?></h2>   
            <div class="spacer"></div>    
            <ul class="head-checkbox-panel-before-table">
                <li><label>Open translations </label></li>
                <li>
                    <label class="checkbox-inline">
                            <input class="show-all-translation" name="show_all_translation" id="show_all_translation" type="checkbox" value="1">All
                    </label>
                </li>
            </ul>  
            <div class="">          
                <table class="table full-symptom-details-table table-bordered table-sticky-head table-hover">
                    <thead>
                        <tr>
                            <th style="width:2%;">Symp- tom No (@N)</th>
                            <th style="width:2%;">Page (@S)</th>
                            <th style="width: 8%;">Imported symptom</th>
                            <th style="width: 8%;">Original symptom</th>
                            <!-- <th style="width: 8%;">Converted symptom</th> -->
                            <th style="width: 8%;">Converted symptom</th>
                            <th style="width: 8%;">Graduation (@G)</th>
                            <th style="width: 2%;">Edit Synonym</th>
                            <th style="width: 2%;">Add Synonym</th>
                            <th style="width: 6%;">Synonyms</th>
                            <th style="width: 4%;">Bracketed part</th>
                            <th style="width: 3%;">Time (@Z)</th>
                            <th style="width: 3%;">Prover (@P)</th>
                            <th style="width: 4%;">Reference (@L)</th>
                            <th>Remedy(@A)</th>
                            <th>Symptom Type</th>
                            <th>Symptom Of Different Remedy(@AT/@TA)</th>
                            <th>Footnote (@F)</th>
                            <th>Symptom edit comment</th>
                            <th>Hint (@V)</th>
                            <th>Chapter (@K)</th>
                            <th>Comment (@C)</th> <!-- Kommentar -->
                            <th>Ambiguities (@U)</th> <!-- Unklarheiten -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php                                                                   
                            $result = mysqli_query($db,"SELECT * FROM quelle_import_test WHERE master_id = '".$masterId."' ORDER BY id ASC"); 
                            while($row = mysqli_fetch_array($result)){   
                                $originSourceYear = "";
                                $originSourceLanguage = "";
                                $originQuelleResult = mysqli_query($db,"SELECT quelle.jahr, quelle.quelle_type_id, quelle.sprache FROM quelle WHERE quelle.quelle_id = '".$row['original_quelle_id']."'");
                                if(mysqli_num_rows($originQuelleResult) > 0){
                                    $originQuelleRow = mysqli_fetch_assoc($originQuelleResult);
                                    $originSourceYear = $originQuelleRow['jahr'];
                                    if($originQuelleRow['sprache'] == "deutsch")
                                        $originSourceLanguage = "de";
                                    else if($originQuelleRow['sprache'] == "englisch") 
                                        $originSourceLanguage = "en";
                                }
                                if($originSourceLanguage != ""){
                                    $importedSymptom = ""; 
                                    $originalSymptom = ""; 
                                    $originalSymptom_de = ($row['BeschreibungOriginal_de'] != "") ? convertSymptomToOriginal($row['BeschreibungOriginal_de'], $row['original_quelle_id'], $row['arznei_id']) : ""; 
                                    $originalSymptom_en = ($row['BeschreibungOriginal_en'] != "") ? convertSymptomToOriginal($row['BeschreibungOriginal_en'], $row['original_quelle_id'], $row['arznei_id']) : ""; 

                                    $convertedSymptom = ""; 
                                    $convertedSymptomFull = ""; 
                                    // [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
                                    // convertTheSymptom($row['searchable_text_de'], $row['original_quelle_id'], $row['arznei_id'], 0)
                                    $convertedSymptom_de = ($row['searchable_text_de'] != "") ? convertTheSymptom($row['searchable_text_de'], $row['original_quelle_id'], $row['arznei_id'], 0, 0, $row['id'], $row['original_symptom_id']) : ""; 
                                    $convertedSymptom_en = ($row['searchable_text_en'] != "") ? convertTheSymptom($row['searchable_text_en'], $row['original_quelle_id'], $row['arznei_id'], 0, 0, $row['id'], $row['original_symptom_id']) : ""; 

                                    $convertedSymptomFull_de = ($row['BeschreibungFull_de'] != "") ? convertTheSymptom($row['BeschreibungFull_de'], $row['original_quelle_id'], $row['arznei_id'], 0, 0, $row['id'], $row['original_symptom_id']) : ""; 
                                    $convertedSymptomFull_en = ($row['BeschreibungFull_en'] != "") ? convertTheSymptom($row['BeschreibungFull_en'], $row['original_quelle_id'], $row['arznei_id'], 0, 0, $row['id'], $row['original_symptom_id']) : "";

                                    $bracketedPart = "";
                                    $time = "";

                                    $graduation = "";
                                    // [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
                                    // [5th parameter(Optional)] $includeGrade values (0 = Gragde number will not include, 1 = Will include Grade number)
                                    // convertTheSymptom($row['searchable_text_de'], $row['original_quelle_id'], $row['arznei_id'], 0, 1)
                                    $graduation_de = ($row['searchable_text_de'] != "") ? convertTheSymptom($row['searchable_text_de'], $row['original_quelle_id'], $row['arznei_id'], 0, 1, $row['id'], $row['original_symptom_id']) : ""; 
                                    $graduation_en = ($row['searchable_text_en'] != "") ? convertTheSymptom($row['searchable_text_en'], $row['original_quelle_id'], $row['arznei_id'], 0, 1, $row['id'], $row['original_symptom_id']) : "";

                                    if($originSourceLanguage == "en"){
                                        // If original source/quelle language is "en"
                                        if($importedLanguage == "de"){
                                            $importedSymptom = ($row['Beschreibung_en'] != "") ? '<div class="table-symptom-cnr table-symptom-en table-original-symptom table-symptom-hidden hidden">'.$row['Beschreibung_en'].'</div>' : "";
                                            $importedSymptom .= ($row['Beschreibung_de'] != "") ? '<div class="table-symptom-cnr table-symptom-de">'.$row['Beschreibung_de'].'</div>' : "";

                                            $originalSymptom = ($originalSymptom_en != "") ? '<div class="table-symptom-cnr table-symptom-en table-original-symptom table-symptom-hidden hidden">'.$originalSymptom_en.'</div>' : "";
                                            $originalSymptom .= ($originalSymptom_de != "") ? '<div class="table-symptom-cnr table-symptom-de">'.$originalSymptom_de.'</div>' : "";

                                            $convertedSymptom = ($convertedSymptom_en != "") ? '<div class="table-symptom-cnr table-symptom-en table-original-symptom table-symptom-hidden hidden">'.$convertedSymptom_en.'</div>' : "";
                                            $convertedSymptom .= ($convertedSymptom_de != "") ? '<div class="table-symptom-cnr table-symptom-de">'.$convertedSymptom_de.'</div>' : "";

                                            $convertedSymptomFull = ($convertedSymptomFull_en != "") ? '<div class="table-symptom-cnr table-symptom-en table-original-symptom table-symptom-hidden hidden">'.$convertedSymptomFull_en.'</div>' : "";
                                            $convertedSymptomFull .= ($convertedSymptomFull_de != "") ? '<div class="table-symptom-cnr table-symptom-de">'.$convertedSymptomFull_de.'</div>' : "";

                                            $bracketedPart = ($row['bracketedString_en'] != "") ? '<div class="table-symptom-cnr table-symptom-en table-original-symptom table-symptom-hidden hidden">'.$row['bracketedString_en'].'</div>' : "";
                                            $bracketedPart .= ($row['bracketedString_de'] != "") ? '<div class="table-symptom-cnr table-symptom-de">'.$row['bracketedString_de'].'</div>' : "";

                                            $graduation = ($graduation_en != "") ? '<div class="table-symptom-cnr table-symptom-en table-original-symptom table-symptom-hidden hidden">'.$graduation_en.'</div>' : "";
                                            $graduation .= ($graduation_de != "") ? '<div class="table-symptom-cnr table-symptom-de">'.$graduation_de.'</div>' : "";

                                            $time = $row['timeString_de'];
                                        }
                                        else
                                        {
                                            $importedSymptom = ($row['Beschreibung_en'] != "") ? '<div class="table-symptom-cnr table-symptom-en table-original-symptom">'.$row['Beschreibung_en'].'</div>' : "";
                                            $importedSymptom .= ($row['Beschreibung_de'] != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de hidden">'.$row['Beschreibung_de'].'</div>' : "";

                                            $originalSymptom = ($originalSymptom_en != "") ? '<div class="table-symptom-cnr table-symptom-en table-original-symptom">'.$originalSymptom_en.'</div>' : "";
                                            $originalSymptom .= ($originalSymptom_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de hidden">'.$originalSymptom_de.'</div>' : "";

                                            $convertedSymptom = ($convertedSymptom_en != "") ? '<div class="table-symptom-cnr table-symptom-en table-original-symptom">'.$convertedSymptom_en.'</div>' : "";
                                            $convertedSymptom .= ($convertedSymptom_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de hidden">'.$convertedSymptom_de.'</div>' : "";

                                            $convertedSymptomFull = ($convertedSymptomFull_en != "") ? '<div class="table-symptom-cnr table-symptom-en table-original-symptom">'.$convertedSymptomFull_en.'</div>' : "";
                                            $convertedSymptomFull .= ($convertedSymptomFull_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de hidden">'.$convertedSymptomFull_de.'</div>' : "";

                                            $bracketedPart = ($row['bracketedString_en'] != "") ? '<div class="table-symptom-cnr table-symptom-en table-original-symptom">'.$row['bracketedString_en'].'</div>' : "";
                                            $bracketedPart .= ($row['bracketedString_de'] != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de hidden">'.$row['bracketedString_de'].'</div>' : "";

                                            $graduation = ($graduation_en != "") ? '<div class="table-symptom-cnr table-symptom-en table-original-symptom">'.$graduation_en.'</div>' : "";
                                            $graduation .= ($graduation_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de hidden">'.$graduation_de.'</div>' : "";

                                            $time = $row['timeString_en'];
                                        }
                                        // $bracketedPart = $row['bracketedString_en'];
                                        // $time = '<div class="table-symptom-cnr table-symptom-visible table-symptom-en table-original-symptom">'.$row['timeString_en'].'</div>';
                                        // $time .= '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de hidden">'.$row['timeString_de'].'</div>';
                                        // $time = $row['timeString_en'];	
                                    } else {
                                        // Else original source/quelle language is "de"
                                        if($importedLanguage == "en"){
                                            $importedSymptom = ($row['Beschreibung_de'] != "") ? '<div class="table-symptom-cnr table-symptom-de table-original-symptom table-symptom-hidden hidden">'.$row['Beschreibung_de'].'</div>' : "";
                                            $importedSymptom .= ($row['Beschreibung_en'] != "") ? '<div class="table-symptom-cnr table-symptom-en">'.$row['Beschreibung_en'].'</div>' : "";

                                            $originalSymptom = ($originalSymptom_de != "") ? '<div class="table-symptom-cnr table-symptom-de table-original-symptom table-symptom-hidden hidden">'.$originalSymptom_de.'</div>' : "";
                                            $originalSymptom .= ($originalSymptom_en != "") ? '<div class="table-symptom-cnr table-symptom-en">'.$originalSymptom_en.'</div>' : "";

                                            $convertedSymptom = ($convertedSymptom_de != "") ? '<div class="table-symptom-cnr table-symptom-de table-original-symptom table-symptom-hidden hidden">'.$convertedSymptom_de.'</div>' : "";
                                            $convertedSymptom .= ($convertedSymptom_en != "") ? '<div class="table-symptom-cnr table-symptom-en">'.$convertedSymptom_en.'</div>' : "";

                                            $convertedSymptomFull = ($convertedSymptomFull_de != "") ? '<div class="table-symptom-cnr table-symptom-de table-original-symptom table-symptom-hidden hidden">'.$convertedSymptomFull_de.'</div>' : "";
                                            $convertedSymptomFull .= ($convertedSymptomFull_en != "") ? '<div class="table-symptom-cnr table-symptom-en">'.$convertedSymptomFull_en.'</div>' : "";

                                            $bracketedPart = ($row['bracketedString_de'] != "") ? '<div class="table-symptom-cnr table-symptom-de table-original-symptom table-symptom-hidden hidden">'.$row['bracketedString_de'].'</div>' : "";
                                            $bracketedPart .= ($row['bracketedString_en'] != "") ? '<div class="table-symptom-cnr table-symptom-en">'.$row['bracketedString_en'].'</div>' : "";

                                            $graduation = ($graduation_de != "") ? '<div class="table-symptom-cnr table-symptom-de table-original-symptom table-symptom-hidden hidden">'.$graduation_de.'</div>' : "";
                                            $graduation .= ($graduation_en != "") ? '<div class="table-symptom-cnr table-symptom-en">'.$graduation_en.'</div>' : "";

                                            $time = $row['timeString_en'];
                                        }
                                        else
                                        {
                                            $importedSymptom = ($row['Beschreibung_de'] != "") ? '<div class="table-symptom-cnr table-symptom-de table-original-symptom">'.$row['Beschreibung_de'].'</div>' : "";
                                            $importedSymptom .= ($row['Beschreibung_en'] != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en hidden">'.$row['Beschreibung_en'].'</div>' : "";

                                            $originalSymptom = ($originalSymptom_de != "") ? '<div class="table-symptom-cnr table-symptom-de table-original-symptom">'.$originalSymptom_de.'</div>' : "";
                                            $originalSymptom .= ($originalSymptom_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en hidden">'.$originalSymptom_en.'</div>' : "";

                                            $convertedSymptom = ($convertedSymptom_de != "") ? '<div class="table-symptom-cnr table-symptom-de table-original-symptom">'.$convertedSymptom_de.'</div>' : "";
                                            $convertedSymptom .= ($convertedSymptom_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en hidden">'.$convertedSymptom_en.'</div>' : "";

                                            $convertedSymptomFull = ($convertedSymptomFull_de != "") ? '<div class="table-symptom-cnr table-symptom-de table-original-symptom">'.$convertedSymptomFull_de.'</div>' : "";
                                            $convertedSymptomFull .= ($convertedSymptomFull_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en hidden">'.$convertedSymptomFull_en.'</div>' : "";

                                            $bracketedPart = ($row['bracketedString_de'] != "") ? '<div class="table-symptom-cnr table-symptom-de table-original-symptom">'.$row['bracketedString_de'].'</div>' : "";
                                            $bracketedPart .= ($row['bracketedString_en'] != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en hidden">'.$row['bracketedString_en'].'</div>' : "";

                                            $graduation = ($graduation_de != "") ? '<div class="table-symptom-cnr table-symptom-de table-original-symptom">'.$graduation_de.'</div>' : "";
                                            $graduation .= ($graduation_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en hidden">'.$graduation_en.'</div>' : "";

                                            $time = $row['timeString_de'];
                                        }
                                        // $bracketedPart = $row['bracketedString_de'];
                                        // $time = '<div class="table-symptom-cnr table-symptom-visible table-symptom-de table-original-symptom">'.$row['timeString_de'].'</div>';
                                        // $time .= '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en hidden">'.$row['timeString_en'].'</div>';
                                        // $time = $row['timeString_de'];	
                                    }


                                    $resultData['synonym_word'] = displayFormateOfSynonym($row['synonym_word']);
                                    $resultData['strict_synonym'] = displayFormateOfSynonym($row['strict_synonym']);
                                    $resultData['synonym_partial_1'] = displayFormateOfSynonym($row['synonym_partial_1']);
                                    $resultData['synonym_partial_2'] = displayFormateOfSynonym($row['synonym_partial_2']);
                                    $resultData['synonym_general'] = displayFormateOfSynonym($row['synonym_general']);
                                    $resultData['synonym_minor'] = displayFormateOfSynonym($row['synonym_minor']);
                                    $resultData['synonym_nn'] = displayFormateOfSynonym($row['synonym_nn']);
                                    $synonymTxt = "";
                                    if($resultData['synonym_word'] != "")
                                        $synonymTxt .= $resultData['synonym_word']."<br>";
                                    if($resultData['strict_synonym'] != "")
                                        $synonymTxt .= $resultData['strict_synonym']."<br>";
                                    if($resultData['synonym_partial_1'] != "")
                                        $synonymTxt .= $resultData['synonym_partial_1']."<br>";
                                    if($resultData['synonym_partial_2'] != "")
                                        $synonymTxt .= $resultData['synonym_partial_2']."<br>";
                                    if($resultData['synonym_general'] != "")
                                        $synonymTxt .= $resultData['synonym_general']."<br>";
                                    if($resultData['synonym_minor'] != "")
                                        $synonymTxt .= $resultData['synonym_minor']."<br>";
                                    if($resultData['synonym_nn'] != "")
                                        $synonymTxt .= $resultData['synonym_nn']."<br>";

                                    // collecting symptom type info
                                    $symptomType = "";
                                    $querySympTypeInfo = mysqli_query($db,"SELECT symptom_type_for_whole FROM quelle_symptom_settings WHERE quelle_id =".$row['original_quelle_id']);
                                    if(mysqli_num_rows($querySympTypeInfo) > 0){
                                        $rowSympTypeInfo = mysqli_fetch_assoc($querySympTypeInfo);
                                        $symptomType = (isset($rowSympTypeInfo['symptom_type_for_whole']) AND $rowSympTypeInfo['symptom_type_for_whole'] != "") ? $rowSympTypeInfo['symptom_type_for_whole'] : "";
                                    }

                                    $symptomTypeResult = mysqli_query($db, "SELECT * FROM symptom_type_setting WHERE symptom_id = '".$row['id']."'");
                                    if(mysqli_num_rows($symptomTypeResult) > 0){
                                        $symptomTypeRow = mysqli_fetch_assoc($symptomTypeResult);
                                    }
                                    $symptomType = (isset($symptomTypeRow['symptom_type']) and $symptomTypeRow['symptom_type'] != "") ? $symptomTypeRow['symptom_type'] : $symptomType;
                                ?>
                                    <tr>
                                        <td><?=$row['Symptomnummer']?></td>
                                        <td>
                                            <?php
                                                if($row['SeiteOriginalVon'] == $row['SeiteOriginalBis'])
                                                    echo $row['SeiteOriginalVon'];
                                                else
                                                    echo $row['SeiteOriginalVon']."-".$row['SeiteOriginalBis']
                                            ?>
                                        </td>
                                        <td><?php echo $importedSymptom; ?></td>
                                        <td><?php echo $originalSymptom; ?></td>
                                        <!--<td><?php //echo $convertedSymptom; ?></td>-->
                                        <td><?php echo $convertedSymptomFull; ?></td>
                                        <?php /*<td><?=$row['Graduierung']?></td>*/ ?>
                                        <td><?php echo $graduation; ?></td>
                                        <td class="text-center">
                                            <a class="edit-symptom-btn" title="Edit Synonym" data-quelle-id="<?php echo $row['quelle_id']; ?>" data-symptom-id="<?php echo $row['id']; ?>" data-arznei-id="<?php echo $row['arznei_id']; ?>" data-quelle-import-master-id="<?php echo $row['master_id']; ?>" href="javascript:void(0)" style="font-size:25px;"><i class="fas fa-pen-square"></i></a>
                                        </td>
                                        <td class="text-center"><a class="add-symptom-synonym-btn" title="Add Synonym" data-quelle-id="<?php echo $row['quelle_id']; ?>" data-arznei-id="<?php echo $row['arznei_id']; ?>" data-quelle-import-master-id="<?php echo $row['master_id']; ?>" href="javascript:void(0)" style="font-size:25px;"><i class="far fa-plus-square"></i></a></td>
                                        <td><?php echo $synonymTxt; ?></td>
                                        <td><?php echo $bracketedPart; ?></td>
                                        <td><?php echo $time;?></td>
                                        <td>
                                            <?php
                                                $pruStr = "";
                                                $prueferResult = mysqli_query($db,"SELECT pruefer.pruefer_id, pruefer.suchname, pruefer.vorname, pruefer.nachname FROM symptom_pruefer JOIN pruefer ON symptom_pruefer.pruefer_id	= pruefer.pruefer_id WHERE symptom_pruefer.symptom_id = '".$row['id']."'");
                                                while($prueferRow = mysqli_fetch_array($prueferResult)){
                                                    if($prueferRow['suchname'] != "")
                                                        $pruStr .= $prueferRow['suchname'].", ";
                                                    else
                                                        $pruStr .= $prueferRow['vorname']." ".$prueferRow['nachname'].", ";
                                                }
                                                $pruStr =rtrim($pruStr, ", ");
                                                echo $pruStr;
                                            ?>
                                        </td>
                                        <td><?php echo trim(str_replace('No Author,', '', $row['EntnommenAus'])); ?></td>
                                        <td>
                                        	<?php
                                        		$rmdStr = "";
                                                $remedyResult = mysqli_query($db,"SELECT arznei.titel FROM symptom_remedy JOIN arznei ON symptom_remedy.remedy_id = arznei.arznei_id WHERE symptom_remedy.symptom_id = '".$row['id']."'");
                                                while($remedyRow = mysqli_fetch_array($remedyResult)){
                                                    if($remedyRow['titel'] != "")
                                                        $rmdStr .= $remedyRow['titel'].", ";
                                                }
                                                $rmdStr =rtrim($rmdStr, ", ");
                                                echo $rmdStr;
                                        	?>                                        	
                                        </td>
                                        <td><?=$symptomType?></td>
                                        <td><?=($row['symptom_of_different_remedy'] != "" AND $row['symptom_of_different_remedy'] != "null") ? $row['symptom_of_different_remedy'] : ""?></td>
                                        <td><?=$row['Fussnote']?></td>
                                        <td><?=($row['symptom_edit_comment'] != "" AND $row['symptom_edit_comment'] != "null") ? $row['symptom_edit_comment'] : ""?></td>
                                        <td><?=$row['Verweiss']?></td>
                                        <td><?=$row['BereichID']?></td>
                                        <td><?=$row['Kommentar']?></td>
                                        <td><?=$row['Unklarheiten']?></td>
                                    </tr>
                                <?php
                                }
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
            
</div>
<!-- Add synonym modal start -->
<div class="modal fade" id="addSynonymModal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="add_symptom_synonym_form" name="add_symptom_synonym_form" action="" method="POST">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Add Synonym</h4>
                </div>
                <div id="add_synonym_container" class="modal-body">
                    <div id="add_synonym_modal_loader" class="form-group text-center">
                        <span class="loading-msg">Loading informations please wait <img src="assets/img/loader.gif" alt="Loading..."></span>
                        <span class="error-msg"></span>
                    </div>
                    <div class="add-synonym-content">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="language">Language*</label>&nbsp;<span id="synonym_language_error" class="error-text text-danger"></span>
                                    <select id="synonym_language" name="synonym_language" class="form-control">
                                        <option value="">Select</option>
                                        <option value="en">English</option>
                                        <option value="de">German</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="strict_synonym">Striktes Synonym*</label>&nbsp;<span id="strict_synonym_error" class="error-text text-danger"></span>
                                    <input type="text" class="form-control" id="strict_synonym" name="strict_synonym" required>
                                    <small>Fügen Sie kommagetrennte Werte hinzu(i.e.: <b>ache, aching, agony</b>)</small>
                                </div>
                                <div class="form-group">
                                    <label for="synonym_partial_2">Partielles Synonym II</label><span class="error-text"></span>
                                    <input type="text" class="form-control" id="synonym_partial_2" name="synonym_partial_2">
                                    <small>Fügen Sie kommagetrennte Werte hinzu(i.e.: <b>ache, aching, agony</b>)</small>
                                </div>
                                <div class="form-group">
                                    <label for="synonym_minor">Hyponym (Unterbegriff)</label><span class="error-text"></span>
                                    <input type="text" class="form-control" id="synonym_minor" name="synonym_minor">
                                    <small>Fügen Sie kommagetrennte Werte hinzu(i.e.: <b>ache, aching, agony</b>)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="word">Wort*</label>&nbsp;<span id="word_error" class="error-text text-danger"></span>
                                    <input type="text" class="form-control" id="word" name="word" required>
                                </div>
                                <div class="form-group">
                                    <label for="synonym_partial_1">Partielles Synonym I</label><span class="error-text"></span>
                                    <input type="text" class="form-control" id="synonym_partial_1" name="synonym_partial_1">
                                    <small>Fügen Sie kommagetrennte Werte hinzu(i.e.: <b>ache, aching, agony</b>)</small>
                                </div>
                                <div class="form-group">
                                    <label for="synonym_general">Hyperonym (Oberbegriff)</label><span class="error-text"></span>
                                    <input type="text" class="form-control" id="synonym_general" name="synonym_general">
                                    <small>Fügen Sie kommagetrennte Werte hinzu(i.e.: <b>ache, aching, agony</b>)</small>
                                </div>
                                <div class="form-group">
                                    <label for="synonym_nn">Synonym NN</label><span class="error-text"></span>
                                    <input type="text" class="form-control" id="synonym_nn" name="synonym_nn">
                                    <small>Fügen Sie kommagetrennte Werte hinzu(i.e.: <b>ache, aching, agony</b>)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="modal_quelle_id" id="modal_quelle_id">
                    <input type="hidden" name="modal_arznei_id" id="modal_arznei_id">
                    <input type="hidden" name="modal_quelle_import_master_id" id="modal_quelle_import_master_id">
                    <button type="button" class="btn btn-primary symptom-synonym-modal-submit-btn">Submit</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Add synonym modal end -->
<!-- Add symptom edit modal start -->
<div class="modal fade" id="editSymptomModal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="edit_symptom_synonym_form" name="edit_symptom_synonym_form" action="" method="POST">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Edit Synonym</h4>
                </div>
                <div id="edit_synonym_container" class="modal-body">
                    <div id="edit_synonym_modal_loader" class="form-group text-center">
                        <span class="loading-msg">Loading informations please wait <img src="assets/img/loader.gif" alt="Loading..."></span>
                        <span class="error-msg text-danger"></span>
                    </div>
                    <div class="edit-synonym-content">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="symptom_type">Symptom type</label><span class="error-text"></span>
                                            <select name="symptom_type" id="symptom_type" class="form-control">
                                                <option value="">Select</option>
                                                <option value="proving">Proving symptom</option>
                                                <option value="intoxication">Intoxication</option>
                                                <option value="clinical">Clinical symptom</option>
                                                <option value="proving_intoxication_clinical_not_defined">Proving symptom / Intoxication / Clinical symptom not clearly defined</option>
                                                <option value="characteristic">Characteristic symptom</option>
                                                <option value="characteristic_not_defined">Characteristic symptom not clearly identified / defined</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6"></div>
                                </div>
                                <hr>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div><b>Symptom version</b></div>
                                <label class="radio-inline"><input type="radio" name="symptom_version" value="original" checked>Edit original and converted version.</label>
                                <label class="radio-inline"><input type="radio" name="symptom_version" value="converted">Edit only converted version.</label>
                                <div class="spacer"></div>
                            </div>
                        </div>
                        <div id="symptom_edit_de_container" class="row">
                            <div class="col-sm-12">
                                <div><b>Symptom(de)</b></div>
                                <p><small>Original version of the symptom is given below.</small></p>
                            </div>
                            <div class="col-sm-12">
                                <textarea id="symptom_edit_de" name="symptom_edit_de" class="texteditor-small" aria-hidden="true"></textarea>
                                <span class="symptom-edit-de-error error-text text-danger"></span>
                                <div class="spacer"></div>
                            </div>
                        </div>
                        <div id="symptom_edit_en_container" class="row">
                            <div class="col-sm-12">
                                <div><b>Symptom(en)</b></div>
                                <p><small>Original version of the symptom is given below.</small></p>
                            </div>
                            <div class="col-sm-12">
                                <textarea id="symptom_edit_en" name="symptom_edit_en" class="texteditor-small" aria-hidden="true"></textarea>
                                <span class="symptom-edit-en-error error-text text-danger"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="modal_symptom_edit_symptom_id" id="modal_symptom_edit_symptom_id">
                    <input type="hidden" name="modal_symptom_edit_quelle_id" id="modal_symptom_edit_quelle_id">
                    <input type="hidden" name="modal_symptom_edit_arznei_id" id="modal_symptom_edit_arznei_id">
                    <input type="hidden" name="modal_symptom_edit_quelle_import_master_id" id="modal_symptom_edit_quelle_import_master_id">
                    <button type="button" class="btn btn-primary symptom-edit-modal-submit-btn">Submit</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Add symptom edit modal end -->
<!-- jQuery 3 -->
<script src="<?php echo $absoluteUrl;?>plugins/jquery/dist/jquery.min.js"></script>

<!-- jQuery UI 1.11.4 -->
<script src="<?php echo $absoluteUrl;?>plugins/jquery-ui/jquery-ui.min.js"></script>
<script>
	var absoluteUrl = "<?php echo $absoluteUrl;?>";
	var baseApiURL = "<?php echo $baseApiURL;?>";
	var token = "<?php echo $_SESSION['access_token']; ?>";
</script>
<!-- Tinymce -->
<script src="<?php echo $absoluteUrl;?>plugins/tinymce/jquery.tinymce.min.js"></script>
<script src="<?php echo $absoluteUrl;?>plugins/tinymce/jquery.tinymce.config.js"></script>
<script src="<?php echo $absoluteUrl;?>plugins/tinymce/tinymce.min.js"></script>
<script src="<?php echo $absoluteUrl;?>plugins/jasny-bootstrap/jasny-bootstrap.min.js"></script>

<script src="<?php echo $absoluteUrl;?>plugins/jquery-ui/ui/i18n/datepicker-de.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button);
</script>
<!-- Bootstrap 3.3.7 -->
<script src="<?php echo $absoluteUrl;?>plugins/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- Dropify -->
<script src="<?php echo $absoluteUrl;?>plugins/dropify/js/dropify.min.js"></script>
<!-- DataTables -->
<script src="<?php echo $absoluteUrl;?>plugins/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo $absoluteUrl;?>plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<?php if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 3 ) { ?>
<script src="<?php echo $absoluteUrl;?>assets/js/dataTablesConfigPublic.js"></script>
<?php  } ?>
<?php if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 1 || $_SESSION['user_type'] == 2 )) { ?>
<script src="<?php echo $absoluteUrl;?>assets/js/dataTablesConfig.js"></script>
<?php  } ?>
<!-- sweet alert 2 -->
<script src="<?php echo $absoluteUrl;?>plugins/sweetalert2/sweetalert2.min.js"></script>
<!-- Select2 -->
<script src="<?php echo $absoluteUrl;?>plugins/select2/dist/js/select2.full.min.js"></script>
<!-- Select2 custom search box placeholder -->
<script src="<?php echo $absoluteUrl;?>assets/js/select2-custom-search-box-placeholder.js"></script>
<!-- FastClick -->
<script src="<?php echo $absoluteUrl;?>plugins/fastclick/lib/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="<?php echo $absoluteUrl;?>assets/js/adminlte.min.js"></script>
<!-- Jquery validation plugin -->
<script src="<?php echo $absoluteUrl;?>plugins/jquery.validate.min.js"></script>
<!-- custom common js -->
<script src="<?php echo $absoluteUrl;?>assets/js/common.js"></script>
<!-- Advance Search custom js -->
<script src="<?php echo $absoluteUrl;?>assets/js/advanceSearch.js"></script>
<script src="<?php echo $absoluteUrl;?>assets/js/modernizr.js"></script>
<!-- Custom form validation -->
<script src="<?php echo $absoluteUrl;?>assets/js/formValidation.js"></script>
<!-- Ajax blockUI -->
<script src="<?php echo $absoluteUrl;?>plugins/jquery.blockUI.js"></script>
<!-- sweet alert message popup-->
<script src="<?php echo $absoluteUrl;?>/assets/js/alertMessage.js"></script>
<!-- Ajax form submit -->
<script src="<?php echo $absoluteUrl;?>assets/js/ajaxFormSubmit.js"></script>
<!-- Quelle Gradings page js -->
<script src="<?php echo $absoluteUrl;?>assets/js/quelleSettings.js"></script>
<script src="<?php echo $absoluteUrl;?>assets/js/globalGradingSetting.js"></script>
<!--Any error type pop up  -->
<?php if(isset($error)) { ?>
	<script> 
		var errorMessage = '<?php echo $error;?>';
		errorMessagePopUp( errorMessage ); 
	</script>
<?php } ?>
<script>
    $('body').on( 'change', '#show_all_translation', function(e) {
        var action = "";
        var is_data_found = 0;

        if($(this).prop("checked") == true) {
            action = "check";
        }else{
            action = "uncheck";
        }

        if(action == "check"){
            $(".table-symptom-hidden").removeClass('hidden');
            $('.table-original-symptom').addClass('table-original-symptom-bg');
        }else if(action == "uncheck"){
            $(".table-symptom-hidden").addClass('hidden');
            $('.table-original-symptom').removeClass('table-original-symptom-bg');
        }
    });

    // Edit symptom popup on symptom verson change
    /*$(document).on('change', 'input[type=radio][name=symptom_version]', function(){
        $("#edit_synonym_modal_loader .loading-msg").removeClass('hidden');
        $("#edit_synonym_modal_loader .error-msg").html('');
        if($("#edit_synonym_modal_loader").hasClass('hidden'))
            $("#edit_synonym_modal_loader").removeClass('hidden');
        $('.symptom-edit-modal-submit-btn').prop('disabled', true);
        var symptomId = $("#modal_symptom_edit_symptom_id").val();
        var symptomVersion = $(this).attr("value");
        if(symptomId != ""){
            $.ajax({
                type: 'POST',
                url: 'get-editable-symptoms.php',
                data: {
                    symptom_id: symptomId
                },
                dataType: "json",
                success: function( response ) {
                    console.log(response);
                    var converted_symptom_full_de = (typeof(response.converted_symptom_full_de) != "undefined" && response.converted_symptom_full_de !== null && response.converted_symptom_full_de != "") ? response.converted_symptom_full_de : "";
                    var converted_symptom_full_en = (typeof(response.converted_symptom_full_en) != "undefined" && response.converted_symptom_full_en !== null && response.converted_symptom_full_en != "") ? response.converted_symptom_full_en : "";
                    var original_symptom_de = (typeof(response.original_symptom_de) != "undefined" && response.original_symptom_de !== null && response.original_symptom_de != "") ? response.original_symptom_de : "";
                    var original_symptom_en = (typeof(response.original_symptom_en) != "undefined" && response.original_symptom_en !== null && response.original_symptom_en != "") ? response.original_symptom_en : "";
                    if(symptomVersion == "original") {
                        $("#symptom_edit_de").val(original_symptom_de);
                        $("#symptom_edit_en").val(original_symptom_en);
                    } else if (symptomVersion == "converted") {
                        $("#symptom_edit_de").val(converted_symptom_full_de);
                        $("#symptom_edit_en").val(converted_symptom_full_en);
                    }
                    $('.symptom-edit-modal-submit-btn').prop('disabled', false);
                    if(!$("#edit_synonym_modal_loader .loading-msg").hasClass('hidden'))
                        $("#edit_synonym_modal_loader .loading-msg").addClass('hidden');
                    $("#edit_synonym_modal_loader .error-msg").html('');
                    if(!$("#edit_synonym_modal_loader").hasClass('hidden'))
                        $("#edit_synonym_modal_loader").addClass('hidden');
                }
            }).fail(function (response) {
                $('.symptom-edit-modal-submit-btn').prop('disabled', false);
                if(!$("#edit_synonym_modal_loader .loading-msg").hasClass('hidden'))
                    $("#edit_synonym_modal_loader .loading-msg").addClass('hidden');
                $("#edit_synonym_modal_loader .error-msg").html('Something went wrong!');
                if($("#edit_synonym_modal_loader").hasClass('hidden'))
                    $("#edit_synonym_modal_loader").removeClass('hidden');
                $('#editSymptomModal').animate({
                    scrollTop: $(".modal-header").offset().top
                }, 1000);
            });
        }else{
            $('.symptom-edit-modal-submit-btn').prop('disabled', false);
            if(!$("#edit_synonym_modal_loader .loading-msg").hasClass('hidden'))
                $("#edit_synonym_modal_loader .loading-msg").addClass('hidden');
            $("#edit_synonym_modal_loader .error-msg").html('Required data not found.');
            if($("#edit_synonym_modal_loader").hasClass('hidden'))
                $("#edit_synonym_modal_loader").removeClass('hidden');
            $('#editSymptomModal').animate({
                scrollTop: $(".modal-header").offset().top
            }, 1000);
        }
    });*/

    // Edit symptom
    $('body').on('click','.edit-symptom-btn', function(e){
        $("#edit_synonym_modal_loader .loading-msg").removeClass('hidden');
        $("#edit_synonym_modal_loader .error-msg").html('');
        if($("#edit_synonym_modal_loader").hasClass('hidden'))
            $("#edit_synonym_modal_loader").removeClass('hidden');
        $('.symptom-edit-modal-submit-btn').prop('disabled', true);
        $('#edit_symptom_synonym_form')[0].reset();
        var symptomId =  $(this).attr("data-symptom-id");
        var quelleId =  $(this).attr("data-quelle-id");
        var arzneiId =  $(this).attr("data-arznei-id");
        var mId =  $(this).attr("data-quelle-import-master-id");
        var symptomVersion = $('input[name="symptom_version"]:checked').val();
        $("#modal_symptom_edit_symptom_id").val(symptomId);
        $("#modal_symptom_edit_quelle_id").val(quelleId);
        $("#modal_symptom_edit_arznei_id").val(arzneiId);
        $("#modal_symptom_edit_quelle_import_master_id").val(mId);
        $("#editSymptomModal").modal('show');

        $.ajax({
            type: 'POST',
            url: 'get-editable-symptoms.php',
            data: {
                symptom_id: symptomId
            },
            dataType: "json",
            success: function( response ) {
                console.log(response);
                var converted_symptom_full_de = (typeof(response.converted_symptom_full_de) != "undefined" && response.converted_symptom_full_de !== null && response.converted_symptom_full_de != "") ? response.converted_symptom_full_de : "";
                var converted_symptom_full_en = (typeof(response.converted_symptom_full_en) != "undefined" && response.converted_symptom_full_en !== null && response.converted_symptom_full_en != "") ? response.converted_symptom_full_en : "";
                var original_symptom_de = (typeof(response.original_symptom_de) != "undefined" && response.original_symptom_de !== null && response.original_symptom_de != "") ? response.original_symptom_de : "";
                var original_symptom_en = (typeof(response.original_symptom_en) != "undefined" && response.original_symptom_en !== null && response.original_symptom_en != "") ? response.original_symptom_en : "";
                // var symptom_type = (typeof(response.symptom_type) != "undefined" && response.symptom_type !== null && response.symptom_type != "") ? response.symptom_type : "";
                // Symptom type
                (typeof response.symptom_type !== 'undefined' && response.symptom_type !== null && response.symptom_type != "") ? $("#symptom_type option[value='"+response.symptom_type+"']").prop('selected', true) : $("#symptom_type option[value='']").prop('selected', true);
                if(symptomVersion == "original") {
                    $("#symptom_edit_de").val(original_symptom_de);
                    $("#symptom_edit_en").val(original_symptom_en);
                } else if (symptomVersion == "converted") {
                    $("#symptom_edit_de").val(converted_symptom_full_de);
                    $("#symptom_edit_en").val(converted_symptom_full_en);
                }
                $('.symptom-edit-modal-submit-btn').prop('disabled', false);
                if(!$("#edit_synonym_modal_loader .loading-msg").hasClass('hidden'))
                    $("#edit_synonym_modal_loader .loading-msg").addClass('hidden');
                $("#edit_synonym_modal_loader .error-msg").html('');
                if(!$("#edit_synonym_modal_loader").hasClass('hidden'))
                    $("#edit_synonym_modal_loader").addClass('hidden');
            }
        }).fail(function (response) {
            $('.symptom-edit-modal-submit-btn').prop('disabled', false);
            if(!$("#edit_synonym_modal_loader .loading-msg").hasClass('hidden'))
                $("#edit_synonym_modal_loader .loading-msg").addClass('hidden');
            $("#edit_synonym_modal_loader .error-msg").html('Something went wrong!');
            if($("#edit_synonym_modal_loader").hasClass('hidden'))
                $("#edit_synonym_modal_loader").removeClass('hidden');
            $('#editSymptomModal').animate({
                scrollTop: $(".modal-header").offset().top
            }, 1000);
        });
    });

    // Edit symptom submit
    $('body').on( 'click', '.symptom-edit-modal-submit-btn', function(e) {
        $("#edit_synonym_modal_loader .loading-msg").removeClass('hidden');
        $("#edit_synonym_modal_loader .error-msg").html('');
        if($("#edit_synonym_modal_loader").hasClass('hidden'))
            $("#edit_synonym_modal_loader").removeClass('hidden');
        $('.symptom-edit-modal-submit-btn').prop('disabled', true);
        var modal_symptom_id = $("#modal_symptom_edit_symptom_id").val();
        var modal_quelle_id = $("#modal_symptom_edit_quelle_id").val();
        var modal_arznei_id = $("#modal_symptom_edit_arznei_id").val();
        var modal_quelle_import_master_id = $("#modal_symptom_edit_quelle_import_master_id").val();
        var symptom_edit_de = $("#symptom_edit_de").val();
        var symptom_edit_en = $("#symptom_edit_en").val();
        var error_count = 0;
        if(symptom_edit_de == "" && symptom_edit_en == "") {
            error_count++;
        }
        if(modal_symptom_id == "" || modal_quelle_id == "" || modal_arznei_id == "" || modal_quelle_import_master_id == ""){
            error_count++;
        }

        if(error_count != 0) {
            $('.symptom-edit-modal-submit-btn').prop('disabled', false);
            if(!$("#edit_synonym_modal_loader .loading-msg").hasClass('hidden'))
                $("#edit_synonym_modal_loader .loading-msg").addClass('hidden');
            $("#edit_synonym_modal_loader .error-msg").html('Required data not found.');
            if($("#edit_synonym_modal_loader").hasClass('hidden'))
                $("#edit_synonym_modal_loader").removeClass('hidden');
            $('#editSymptomModal').animate({
                scrollTop: $(".modal-header").offset().top
            }, 1000);
            return false;
        } else {
            $('.symptom-edit-modal-submit-btn').prop('disabled', true);
            var data = $("#edit_symptom_synonym_form").serialize();
            $.ajax({
                type: 'POST',
                url: 'edit-original-or-converted-symptom.php',
                data: {
                    form: data
                },
                dataType: "json",
                success: function( response ) {
                    console.log(response);
                    if(response.status == "success"){
                        $('.symptom-edit-modal-submit-btn').prop('disabled', false);
                        if(!$("#edit_synonym_modal_loader .loading-msg").hasClass('hidden'))
                            $("#edit_synonym_modal_loader .loading-msg").addClass('hidden');
                        $("#edit_synonym_modal_loader .error-msg").html(response.message);
                        if($("#edit_synonym_modal_loader").hasClass('hidden'))
                            $("#edit_synonym_modal_loader").removeClass('hidden');
                        $('#editSymptomModal').animate({
                            scrollTop: $(".modal-header").offset().top
                        }, 1000);
                        setTimeout(function () {
                                $("#editSymptomModal").modal('hide');
                                location.reload();
                        }, 2000);
                        
                    }else{
                        $('.symptom-edit-modal-submit-btn').prop('disabled', false);
                        if(!$("#edit_synonym_modal_loader .loading-msg").hasClass('hidden'))
                            $("#edit_synonym_modal_loader .loading-msg").addClass('hidden');
                        $("#edit_synonym_modal_loader .error-msg").html(response.message);
                        if($("#edit_synonym_modal_loader").hasClass('hidden'))
                            $("#edit_synonym_modal_loader").removeClass('hidden');
                        $('#editSymptomModal').animate({
                            scrollTop: $(".modal-header").offset().top
                        }, 1000);
                    }
                }
            }).fail(function (response) {
                $('.symptom-edit-modal-submit-btn').prop('disabled', false);
                if(!$("#edit_synonym_modal_loader .loading-msg").hasClass('hidden'))
                    $("#edit_synonym_modal_loader .loading-msg").addClass('hidden');
                $("#edit_synonym_modal_loader .error-msg").html('Something went wrong!');
                if($("#edit_synonym_modal_loader").hasClass('hidden'))
                    $("#edit_synonym_modal_loader").removeClass('hidden');
                $('#editSymptomModal').animate({
                    scrollTop: $(".modal-header").offset().top
                }, 1000);
            });
        }
    });

    // Add synonym
    $('body').on('click','.add-symptom-synonym-btn', function(e){
        $("#add_synonym_modal_loader .loading-msg").removeClass('hidden');
        $("#add_synonym_modal_loader .error-msg").html('');
        if(!$("#add_synonym_modal_loader").hasClass('hidden'))
            $("#add_synonym_modal_loader").addClass('hidden');
        $("#synonym_language_error").html("");
        $("#word_error").html("");
        $("#strict_synonym_error").html("");
        $('#add_symptom_synonym_form')[0].reset();
        var quelleId =  $(this).attr("data-quelle-id");
        var arzneiId =  $(this).attr("data-arznei-id");
        var mId =  $(this).attr("data-quelle-import-master-id");
        $("#modal_quelle_id").val(quelleId);
        $("#modal_arznei_id").val(arzneiId);
        $("#modal_quelle_import_master_id").val(mId);
        $("#addSynonymModal").modal('show');
    });

    $('body').on( 'click', '.symptom-synonym-modal-submit-btn', function(e) {
        $("#add_synonym_modal_loader .loading-msg").removeClass('hidden');
        $("#add_synonym_modal_loader .error-msg").html('');
        if($("#add_synonym_modal_loader").hasClass('hidden'))
            $("#add_synonym_modal_loader").removeClass('hidden');

        var synonym_language = $("#synonym_language").val();
        var word = $("#word").val();
        var strict_synonym = $("#strict_synonym").val();
        var modal_quelle_id = $("#modal_quelle_id").val();
        var modal_arznei_id = $("#modal_arznei_id").val();
        var modal_quelle_import_master_id = $("#modal_quelle_import_master_id").val();
        var error_count = 0;
        if(synonym_language == ""){
            $("#synonym_language_error").html("This field is required");
            error_count++;
        }else{
            $("#synonym_language_error").html("");
        }
        if(word == ""){
            $("#word_error").html("This field is required");
            error_count++;
        }else{
            $("#word_error").html("");
        }
        if(strict_synonym == ""){
            $("#strict_synonym_error").html("This field is required");
            error_count++;
        }else{
            $("#strict_synonym_error").html("");
        }
        if(modal_quelle_id == "" || modal_arznei_id == "" || modal_quelle_import_master_id == ""){
            error_count++;
        }

        if(error_count != 0){
            if(!$("#add_synonym_modal_loader .loading-msg").hasClass('hidden'))
                $("#add_synonym_modal_loader .loading-msg").addClass('hidden');
            $("#add_synonym_modal_loader .error-msg").html('Required data not found.');
            if($("#add_synonym_modal_loader").hasClass('hidden'))
                $("#add_synonym_modal_loader").removeClass('hidden');
            $('#addSynonymModal').animate({
                scrollTop: $(".modal-header").offset().top
            }, 1000);
            return false;
        } else {
            $('.symptom-synonym-modal-submit-btn').prop('disabled', true);
            var data = $("#add_symptom_synonym_form").serialize();
            $.ajax({
                type: 'POST',
                url: 'add-symptom-synonym.php',
                data: {
                    form: data
                },
                dataType: "json",
                success: function( response ) {
                    console.log(response);
                    if(response.status == "success"){
                        $('.symptom-synonym-modal-submit-btn').prop('disabled', false);
                        $('#add_symptom_synonym_form')[0].reset();
                        if(!$("#add_synonym_modal_loader .loading-msg").hasClass('hidden'))
                            $("#add_synonym_modal_loader .loading-msg").addClass('hidden');
                        $("#add_synonym_modal_loader .error-msg").html(response.message);
                        if($("#add_synonym_modal_loader").hasClass('hidden'))
                            $("#add_synonym_modal_loader").removeClass('hidden');
                        $('#addSynonymModal').animate({
                            scrollTop: $(".modal-header").offset().top
                        }, 1000);
                    }else{
                        $('.symptom-synonym-modal-submit-btn').prop('disabled', false);
                        if(!$("#add_synonym_modal_loader .loading-msg").hasClass('hidden'))
                            $("#add_synonym_modal_loader .loading-msg").addClass('hidden');
                        $("#add_synonym_modal_loader .error-msg").html(response.message);
                        if($("#add_synonym_modal_loader").hasClass('hidden'))
                            $("#add_synonym_modal_loader").removeClass('hidden');
                        $('#addSynonymModal').animate({
                            scrollTop: $(".modal-header").offset().top
                        }, 1000);
                    }
                }
            }).fail(function (response) {
                $('.symptom-synonym-modal-submit-btn').prop('disabled', false);
                if(!$("#add_synonym_modal_loader .loading-msg").hasClass('hidden'))
                    $("#add_synonym_modal_loader .loading-msg").addClass('hidden');
                $("#add_synonym_modal_loader .error-msg").html('Something went wrong!');
                if($("#add_synonym_modal_loader").hasClass('hidden'))
                    $("#add_synonym_modal_loader").removeClass('hidden');
                $('#addSynonymModal').animate({
                    scrollTop: $(".modal-header").offset().top
                }, 1000);
            });
        }
    });
</script>
</body>
</html>

