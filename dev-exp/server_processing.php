<?php
	include '../config/route.php';
	include 'sub-section-config.php';

	$returnArray = array();
	$array1 = array();
	$totalSymptoms = 0;
	$matchedSymptomIds = array();
	$cutOff = 10;
	$runningInitialSymptomId = "";
	// $infoHtml = '<div class="info"><ul class="info-linkage-group"><li><a class="btn symptom-info-btn" title="info" href="javascript:void(0)"><i class="fas fa-info-circle"></i></a></li><li><a class="btn symptom-edit-btn" title="Symptom edit" href="javascript:void(0)"><i class="fas fa-pencil-alt"></i></a></li><li><a class="btn symptom-comment-btn" title="comment" href="javascript:void(0)"><i class="fas fa-comment-alt"></i></a></li><li><a class="btn symptom-footnote-btn" title="footnote" href="javascript:void(0)"><i class="fas fa-sticky-note"></i></a></li><li><a class="btn symptom-translation-btn" title="translation" href="javascript:void(0)">T</a></li></ul></div>';
	$infoHtml = '<div class="info">
					<ul class="info-linkage-group">			
						<li>				
							<a class="btn symptom-info-btn" title="info" href="javascript:void(0)"><i class="fas fa-info-circle"></i></a>
						</li>			
						<li>				
							<a class="btn symptom-edit-btn" title="Symptom edit" href="javascript:void(0)"><i class="fas fa-pencil-alt"></i></a>			
						</li>			
						<li>				
							<a class="btn symptom-comment-btn" title="comment" href="javascript:void(0)"><i class="fas fa-comment-alt"></i></a>	
						</li>			
						<li>				
							<a class="btn symptom-footnote-btn" title="footnote" href="javascript:void(0)"><i class="fas fa-sticky-note"></i></a>			
						</li>			
						<li>				
							<a class="btn symptom-translation-btn" title="translation" href="javascript:void(0)">T</a>			
						</li>	
					</ul>
				</div>';
	$command = '<div class="command">
					<ul class="command-group">			
						<li>				
							<a class="btn symptom-soft-connect-btn" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a>			
						</li>			
						<li>				
							<a class="btn symptom-connect-btn connect" href="javascript:void(0)" title="connect"><i class="fas fa-link"></i></a>			
						</li>	
						<li>				
							<a class="btn symptom-connect-edit-btn" href="javascript:void(0)" title="Connect edit">CE</a>
						</li>			
						<li>				
							<a class="btn symptom-soft-paste-btn" href="javascript:void(0)" title="Non secure paste"><i class="fas fa-exclamation-triangle"></i></a>			
						</li>			
						<li>				
							<a class="btn symptom-paste-btn" href="javascript:void(0)" title="Paste">P</a>			
						</li>			
						<li>				
							<a class="btn symptom-paste-edit-btn" href="javascript:void(0)" title="Paste edit">PE</a>			
						</li>			
						<li>				
							<a class="btn symptom-swap-connect-btn" href="javascript:void(0)" title="Swap connect"><i class="fas fa-recycle"></i></a>
						</li>		
					</ul>
				</div>';


	$symptomResult = mysqli_query($db,"SELECT * FROM comparison_small WHERE (matched_percentage IS NULL OR matched_percentage >= ".$cutOff.")");
	if(mysqli_num_rows($symptomResult) > 0){
		while($symRow = mysqli_fetch_array($symptomResult)){
			$totalSymptoms++;
			if($symRow['is_initial_symptom'] == '1'){
				// Initial symptom
				$runningInitialSymptomId = $symRow['symptom_id'];

				$array1[] = array($symRow['quelle_code'], $symRow['symptom'], "", $infoHtml, '<div class="command"><ul class="command-group"></ul></div>');
			}else{
				// Comparing symptom
				array_push($matchedSymptomIds, $symRow['symptom_id']);

				$array1[] = array($symRow['quelle_code'], $symRow['symptom'], $symRow['matched_percentage'], $infoHtml, $command);
			}
		}
	}

	// Remaning un-match comparative symptoms
	if(!empty($matchedSymptomIds)){
		$matchedSymptomIdsString = implode(',', array_unique($matchedSymptomIds));
		$restOfComparingSymptomResult = mysqli_query($db,"SELECT quelle_import_test.id, quelle_import_test.quelle_code, quelle_import_test.BeschreibungPlain_de as remaining_symptom FROM quelle_import_test JOIN quelle_import_master ON quelle_import_test.master_id = quelle_import_master.id LEFT JOIN quelle ON quelle_import_test.quelle_id = quelle.quelle_id WHERE quelle_import_test.quelle_id = 250 AND quelle_import_test.id NOT IN (".$matchedSymptomIdsString.")");
		if(mysqli_num_rows($restOfComparingSymptomResult) > 0)
		{
			while($restOfComparingSymptomRow = mysqli_fetch_array($restOfComparingSymptomResult)){
				$totalSymptoms++;
				$array1[] = array($restOfComparingSymptomRow['quelle_code'], $restOfComparingSymptomRow['remaining_symptom'], "", $infoHtml, '<div class="command"><ul class="command-group"></ul></div>');
			}
		}
	}



	$array['draw']  = 1;
	$array['recordsTotal']  = $totalSymptoms;
	$array['recordsFiltered']  = $totalSymptoms;
	$array['data']  = $array1;

	echo json_encode($array);
?>