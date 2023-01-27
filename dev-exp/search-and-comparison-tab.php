<div class="col-sm-12">
	<div class="fancy-collapse-panel">
        <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingOne">
                    <h4 class="panel-title">
                        <a class="collapsed" data-toggle="collapse" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">SEARCH</a>
                    </h4>
                </div>
                <div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
                  <form id="symptom_search_form" name="symptom_search_form" action="" method="POST">
                      <div id="search_container" class="panel-body">
                          <!-- Search fields Strat -->
                      	<div class="row">
							<div class="col-sm-8">
								<div class="form-group Text_form_group">
									<label class="search-label">Search</label>
									<input type="text" name="search_keyword" id="search_keyword" class="form-control" placeholder="Search for words or part of words, from one or multiple sources">
									<span class="error-text"></span>
								</div>
							</div>
							<div class="col-sm-4"></div>
						</div>
						<div class="row">
							<div class="col-sm-8">
								<div class="form-group Text_form_group">
									<label class="search-sources-label">Source(s)</label>
									<select class="form-control" name="search_sources[]" id="search_sources" multiple="multiple" data-placeholder="Select one or multiple sources">
							   			<option value="">Select</option>
							   			<?php 
							   				$html = ''; 
							   				$htmlComparisons = '<optgroup label="Comparisons">';
							   				$htmlSingleSources = '<optgroup label="Single sources">';
							   				$htmlComparisonsInner = '';
											$htmlSingleSourcesInner = '';
											
								   			$quelleResult = mysqli_query($db,"SELECT quelle.quelle_id, quelle.code, quelle.titel, quelle.jahr, quelle.band, quelle.nummer, quelle.auflage, quelle.quelle_type_id, quelle.autor_or_herausgeber as bucher_autor_or_herausgeber, autor.suchname as zeitschriften_autor_suchname, autor.vorname as zeitschriften_autor_vorname, autor.nachname as zeitschriften_autor_nachname FROM quelle LEFT JOIN quelle_autor ON quelle.quelle_id = quelle_autor.quelle_id LEFT JOIN autor ON quelle_autor.autor_id = autor.autor_id WHERE quelle.is_materia_medica = 1 ORDER BY quelle.quelle_type_id ASC");
											while($quelleRow = mysqli_fetch_array($quelleResult)){
												$quelleSymptomResultSearch = mysqli_query($db,"SELECT id FROM quelle_import_test WHERE quelle_id = '".$quelleRow['quelle_id']."'");
	    										if(mysqli_num_rows($quelleSymptomResultSearch) > 0){
	    											$quellen_value = $quelleRow['code'];
													if(!empty($quelleRow['jahr'])) $quellen_value .= ', '.$quelleRow['jahr'];
													if($quelleRow['code'] != $quelleRow['titel'])
														if(!empty($quelleRow['titel'])) $quellen_value .= ', '.$quelleRow['titel'];
													if($quelleRow['quelle_type_id'] == 1){
														if(!empty($quelleRow['bucher_autor_or_herausgeber'])) $quellen_value .= ', '.$quelleRow['bucher_autor_or_herausgeber'];
													}else if($quelleRow['quelle_type_id'] == 2){
														if(!empty($quelleRow['zeitschriften_autor_suchname']) ) 
															$zeitschriften_autor = $quelleRow['zeitschriften_autor_suchname']; 
														else 
															$zeitschriften_autor = $quelleRow['zeitschriften_autor_vorname'].' '.$quelleRow['zeitschriften_autor_nachname'];
														if(!empty($zeitschriften_autor)) $quellen_value .= ', '.$zeitschriften_autor;
													}
													if($quelleRow['quelle_type_id'] == 3)
														$htmlComparisonsInner .= '<option value="'.$quelleRow['quelle_id'].'">'.$quellen_value.'</option>';
													else
														$htmlSingleSourcesInner .= '<option value="'.$quelleRow['quelle_id'].'">'.$quellen_value.'</option>'; 
	    										}
											}
											if($htmlComparisonsInner == '')
												$htmlComparisons .= '<option value="" disabled="disabled">None</option>';
											else
												$htmlComparisons .= $htmlComparisonsInner;
											if($htmlSingleSourcesInner == '')
												$htmlSingleSources .= '<option value="" disabled="disabled">None</option>';
											else
												$htmlSingleSources .= $htmlSingleSourcesInner;
											
											$htmlSingleSources .= '</optgroup>';
											$htmlComparisons .= '</optgroup>';
											$html .= $htmlComparisons.$htmlSingleSources;
											echo $html;
								   		?>
							   		</select>
							   		<span class="error-text"></span>
								</div>
							</div>
							<div class="col-sm-4"></div>
						</div>
						<div class="form-group">
							<div class="spacer15"></div>
							<button type="submit" id="search_submit_btn" class="btn comparison-tab-submit-btn" type="button">SEARCH</button>
						</div>
						<!-- Search fields End -->
                      </div>
                  </form>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingTwo">
                    <h4 class="panel-title">
                        <a data-toggle="collapse" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">SOURCE COMPARISON</a>
                    </h4>
                </div>
                <div id="collapseTwo" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingTwo">
                  <form id="symptom_comparison_form" name="symptom_comparison_form" action="" method="POST">	
                      <div id="comparison_container" class="panel-body">
                      	<!-- Source comparison fields Strat -->
                      	<div class="row">
							<div class="col-sm-6">
								<div class="form-group Text_form_group">
									<label class="similarity-rate">Matched percentage(cut-off)<span class="required">*</span></label>
								   	<select class="form-control save-data" name="similarity_rate" id="similarity_rate">
								   		<?php
								   			$i=0;
								   			While($i <= 100)
											{
												?>
												<option <?php echo ($i == $similarityRate) ? 'selected' : ''; ?> value="<?php echo $i; ?>"><?php echo $i." %"; ?></option>
												<?php
											  $i = $i+5;
											}
								   		?>
								   	</select>
								   	<span class="error-text"></span>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group Text_form_group">
									<label class="language-label">Language<span class="required">*</span></label>
								   	<select class="form-control save-data" name="comparison_language" id="comparison_language">
								   		<option value="">Select</option>
								   		<option <?php echo (isset($comparisonLanguage) AND $comparisonLanguage == 'de') ? 'selected' : ''; ?> value="de">German</option>
								   		<option <?php echo (isset($comparisonLanguage) AND $comparisonLanguage == 'en') ? 'selected' : ''; ?> value="en">English</option>
								   	</select>	
								   	<span class="error-text"></span>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group Text_form_group">
									<label class="control-label">Arznei<span class="required">*</span></label>
								   	<select <?php if($scId != ""){ ?> readonly <?php } ?> class="form-control save-data" name="arznei_id" id="arznei_id">
								   		<option value="">Select</option>
								   		<?php
											$arzneiResult = mysqli_query($db,"SELECT arznei_id, titel FROM arznei");
											while($arzneiRow = mysqli_fetch_array($arzneiResult)){
												$selected = ($arzneiRow['arznei_id'] == $arzneiId) ? 'selected' : '';
												echo '<option '.$selected.' value="'.$arzneiRow['arznei_id'].'">'.$arzneiRow['titel'].'</option>';
											}
											// $db->close();
										?>
								   	</select>
								   	<span class="error-text"></span>	
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group Text_form_group">
									<label class="comparing-option-label">Comparison option</label>
									<select class="form-control save-data" name="comparison_option" id="comparison_option">
										<option value="1" <?php echo ($comparisonOption == 1) ? 'selected' : ''; ?>>Compare only symptoms</option>
										<option value="2" <?php echo ($comparisonOption == 2) ? 'selected' : ''; ?>>Compare whole symptom text</option>
									</select>
									<span class="error-text"></span>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group Text_form_group">
									<label class="control-label">Initial source<span class="required">*</span></label>
									<div id="initial_source_cnr">
									   	<select <?php if($scId != ""){ ?> readonly <?php } ?> class="form-control save-data" name="initial_source" id="initial_source">
									   		<option value="">Select</option>
									   	</select>
									   	<span class="error-text"></span>
									</div>	
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group Text_form_group">
									<label class="control-label">Comparing source(s)<span class="required">*</span></label>
									<div id="comparing_source_cnr">
										<select <?php if($scId != ""){ ?> readonly <?php } ?> class="form-control save-data" name="comparing_sources[]" id="comparing_sources" multiple="multiple" data-placeholder="Search comparing source(s)">
								   			<option value="">Select</option>
								   		</select>
								   		<span class="error-text"></span>
									</div>
								</div>
							</div>
						</div>
						
						<div class="form-group">
							<div class="spacer15"></div>
							<input type="hidden" name="comparison_name" id="comparison_name" value="<?php echo $comparisonName; ?>" class="form-control save-data" placeholder="Name of the comparison">
							<input type="hidden" name="scid" id="scid" value="<?php echo $scId; ?>">
							<input type="hidden" name="saved_arznei_id" id="saved_arznei_id" value="<?php echo $arzneiId; ?>">
							<input type="hidden" name="saved_initial_source_id" id="saved_initial_source_id" value="<?php echo $initialSourceId; ?>">
							<input type="hidden" name="saved_comparison_comparing_source_ids_comma_separated" id="saved_comparison_comparing_source_ids_comma_separated" value="<?php echo $savedComparisonComparingSourceIdsCommaSeparated; ?>">
							<input type="hidden" name="saved_comparison_quelle_id" id="saved_comparison_quelle_id" value="<?php echo $savedComparisonQuelleId; ?>">
							<button type="submit" id="compare_submit_btn" class="btn comparison-tab-submit-btn" >COMPARE</button>
							<span class="pull-right stop-word-link">
								<a class="stop-word-anchor-tag" title="Stop words" target="_blank" href="<?php echo $baseUrl."stop-words.php"; ?>">Click Here</a> to see the active stop words
							</span>
						</div>
                      	<!-- Source comparison fields end -->
                      </div>
                  </form>
                </div>
            </div>
        </div>
	</div>
	<div id="loader" class="form-group text-center hidden">
		Loading is not complete please wait <img src="../assets/img/loader.gif" alt="Loading...">
	</div>
</div>
