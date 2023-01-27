<?php
	include 'includes/php-head-includes-integrated.php';
	include 'additions/chapter-structure.php';

    echo "<pre>";
    print_r($chapters);
    $mainChapter = $chapters["Head"];
    $subChapter = $mainChapter["Inner Head"];
    print_r($subChapter);

    //Default values
    $message = "";
    $error =0;
    $symptom_id= 96620;
    $chapterFinalInput = "Brain ##&## Eyes ##&## Temples ##&## ";
    $completedTable = "comparison_table_289_252_250_de_completed";
    
    //Insertion query
    // $updateSymptom = "UPDATE $completedTable SET chapter = NULLIF('".$chapterFinalInput."', '') WHERE symptom_id = '".$symptom_id."'";
    // $updateRes = $db->query($updateSymptom);
    if (true) {
        $chapterWeights = explode("##&##",$chapterFinalInput);
        print_r($chapterWeights);
    }else{
        $error = 1;
        $message = "Error in updating data to mysql table";
    }

    //Error Handling
    if($error != 0 ){
        echo $message;
    }
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Chapter Assignment</title>
</head>
<body>
    <script src="<?php echo $baseUrl;?>plugins/jquery/jquery/jquery-3.3.1.js"></script>
</body>
</html>