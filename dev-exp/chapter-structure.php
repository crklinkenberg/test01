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

    //new code starts
    $myfile = fopen("additions/hello.txt", "w") or die("Unable to open file!");
    //$myfile = fopen("dynamic-chapter.php", "w") or die("Unable to open file!");
    $txt = "John Doe\n";
    fwrite($myfile, $txt);
    exit();
    //new code ends

    echo "<pre>";
    print_r($chapters);
    echo "------------";
    echo "------------";
    //{primary ##&##  primary more ##&## secondary ##&## tertiary}   
    //{head ##&## mouth ##&## inner head, outer head ##&## }   

    $mainChapterArray = array();
    $innerChapterArray = array();
    $subChapterArray = array();
    $chapterParentsArray = array();
    

    foreach($chapters as $mainChapter => $innerChapter){
        array_push($mainChapterArray, $mainChapter);
        foreach($innerChapter as $innerChapterName => $subChapters){
            array_push($innerChapterArray, $innerChapterName);
            foreach($subChapters as $subChapterName){
                $parentsLink = $innerChapterName.' , '.$mainChapter;
                array_push($chapterParentsArray, $parentsLink);
                array_push($subChapterArray, $subChapterName);
            }
        }
    }

    $subChapterArray = array_flip($subChapterArray);

    print_r($mainChapterArray);
    echo "------------";
    print_r($innerChapterArray);
    echo "------------";
    print_r($subChapterArray);
    echo "------------";
    print_r($chapterParentsArray);
?>