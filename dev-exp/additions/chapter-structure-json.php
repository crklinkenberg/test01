<?php
    $chapters = [
        "Head" => [
            "Inner Head" => ["general", "brain", "sides", "temples", "forehead"],
            "Outer Head" => ["general outer", "brain outer", "sides outer", "temples outer", "forehead outer"]
        ],
        "Eyes" => [
            "Outer Eye" => ["lens", "hairs"],
            "Inner Eye" => ["cornea", "pupil"]
        ]
    ];

    $testArray = array(
        "Head" => array("apple", "pie")
    );

    $masterChapterArray = array();
    
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

    $fileName = "new.json";
    $error = 0;
    $startTxt = "{
        ";
    // $file = file_put_contents("../chapter-data/".$fileName, $startTxt, FILE_APPEND);
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
    $endTxt = "
    }";
    // $file = file_put_contents("../chapter-data/".$fileName, $endTxt, FILE_APPEND);
    echo "<pre>";
    print_r($masterChapterArray);
    echo "<br>";
    $toSent = json_encode($masterChapterArray);
    echo $toSent;
    $file = file_put_contents("../chapter-data/".$fileName, $toSent, FILE_APPEND);



?>