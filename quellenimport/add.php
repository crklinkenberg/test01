<?php
include '../lang/GermanWords.php';
include '../config/route.php';
include '../api/quellenimport.php';
include '../inc/header.php';
include '../inc/sidebar.php';
?>
 <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Neuer Quellenimport
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $absoluteUrl;?>"><i class="fa fa-dashboard"></i> <?php echo $home; ?></a></li>
        <li class=""> <a href="<?php echo $absoluteUrl;?>quelleimport">Quellenimport</a></li>
        <li class="active"> Neuer Quellenimport</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- Small boxes (Stat box) -->
		<div class="row">
			<div class="col-md-12">
				<div class="box box-success">
		            <div class="box-header with-border">
		            	<h3 class="box-title">Hinweis</h3>
						<div class="box-tools pull-right">
							<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
						</div>
					</div>
            		<!-- /.box-header -->
            		<div class="box-body" style="">
              			<div class="row">
                
			                <div class="col-md-12">
			                	<p>Format / Steuerzeichen für den Text:</p>
			                  	<ul>
									<!-- <li>@A: Arznei</li> -->
									<li>@K: Kapitel (z.B. Kopf, Bauch...)</li>
									<li>@S: Seite (Von oder "Von"-"Bis" z.B. "12" oder "13-14")</li>
									<li>@N: Symptom-Nr. Diese kann auch als Prefix aus dem Symptomtext entnommen werden. Wenn "0" angegeben, dann werden keine Symptom-Nr. vergeben</li>
									<li>@P: Prüfer (Kürzel). Diese können auch aus dem Symptomtext aus "[]" bzw. "()"entnommen werden</li>
									<li>@L: Literaturquelle(n) für "Entnommen aus"</li>
									<li>@C: Kommentar</li>
									<li>@U: Unklarheiten</li>
									<li>@F: Fusszeile</li>
									<li>@V: Verweis (bekannte Erwähnung in Quelle)</li>
									<li>@G: Graduierung</li>
									<li>{(}{Symptom-Nr.{.}{ }}{)}Symptomtext{[Prüfer]}</li>
								</ul>
			                </div>
		                	<!-- /.col -->
		              	</div>
              			<!-- /.row -->
            		</div>
	            <!-- ./box-body -->
	          	</div>
				<div class="box box-success">
		            <div class="box-header with-border">
		              <p>Die mit * gekennzeichneten Felder sind Pflichtfelder</p>
		            </div>
		            <!-- /.box-header -->
		            <!-- form start -->
		            <form id="addQuelleimportForm" class="" data-action="" data-source="" autocomplete="off">
		              <div class="box-body">
		              	<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="arznei_id">Arznei*</label><span class="error-text"></span>
									<select id="arznei_id" class="form-control" name="arznei_id">
										<option value="">Arznei wählen</option>
								        <?php foreach ($arzneiSelectBox as $key => $arznei) { ?>
										<option value="<?php echo $arznei['arznei_id'];?>"><?php echo $arznei['titel'];  ?></option>
										<?php } ?>
								    </select>
								</div>
								<div class="form-group">
									<label for="quelle_id">Quelle*</label><span class="error-text"></span>
									<select id="quelle_id" class="form-control quelle_id" name="quelle_id">
										<option value="">Quelle wählen</option>
										<?php foreach ($quellenSelectBox as $key => $quelle) { ?>
											<option value="<?php echo $quelle['quelle_id'];?>"><?php 
												$quellen_value = null;
												$quellen_value = $quellen_value.$quelle['code'];
												if(!empty($quelle['jahr'])) $quellen_value .= ' '.$quelle['jahr'];
												if(!empty($quelle['band'])) $quellen_value .= ', Band: '.$quelle['band'];
												if(!empty($quelle['nummer'])) $quellen_value .= ', Nr.: '. $quelle['nummer'];
												if(!empty($quelle['auflage'])) $quellen_value .= ', Auflage: '. $quelle['auflage'];
												 echo $quellen_value; ?></option>
										<?php } ?>
								    </select>
								</div>
								<div class="form-group">
									<label for="arznei">Quelle für 1:1 Zuordnung</label>
									<select id="arznei" class="form-control quelle_id" name="arznei_id">
										<option value="">Quelle für 1:1 Zuordnung wählen</option>
								        <?php foreach ($quellenSelectBox as $key => $quelle) { ?>
											<option value="<?php echo $quelle['quelle_id'];?>"><?php 
												$quellen_value = null;
												$quellen_value = $quellen_value.$quelle['code'];
												if(!empty($quelle['jahr'])) $quellen_value .= ' '.$quelle['jahr'];
												if(!empty($quelle['band'])) $quellen_value .= ', Band: '.$quelle['band'];
												if(!empty($quelle['nummer'])) $quellen_value .= ', Nr.: '. $quelle['nummer'];
												if(!empty($quelle['auflage'])) $quellen_value .= ', Auflage: '. $quelle['auflage'];
												 echo $quellen_value; ?></option>
										<?php } ?>
								    </select>
								</div>
								<div class="form-group">
									<label for="pruefer_id">Prüfer</label>
									<select id="pruefer_id" class="select2 form-control" multiple="multiple" data-placeholder="Prüfer wählen" name="pruefer_id[]">
								        <?php foreach ($prueferSelectBox as $key => $pruefer) { ?>
										<option value="<?php echo $pruefer['pruefer_id'];?>"><?php echo $pruefer['suchname']; ?></option>
										<?php } ?>
								    </select>
								</div>
								<div class="form-group">
									<label for="symptomtext">Symptomtext*</label><span class="error-text"></span>
									<textarea id="symptomtext" name="symptomtext" class="texteditor textInMce" aria-hidden="true" required></textarea>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="kommentar">Kommentar</label>
									<textarea id="kommentar" name="kommentar" class="texteditor" aria-hidden="true"></textarea>
								</div>
								<div class="form-group">
									<label for="unklarheiten">Unklarheiten</label>
									<textarea id="unklarheiten" name="unklarheiten" class="texteditor" aria-hidden="true"></textarea>
								</div>
							</div>
						</div>
		              </div>
		              <!-- /.box-body -->

		              <div class="box-footer">
		                <button class="btn btn-success" type="submit" id="saveFormBtn">Speichern</button>
						<a class="btn btn-default" href="<?php echo $absoluteUrl;?>stammdaten/arzneien/" id="cancelBtn">Abbrechen</a>
						<button type="reset" id="reset" class="sr-only"></button>
						<a href="<?php echo $absoluteUrl;?>stammdaten/arzneien/" class="pull-right btn btn-primary" style="background: #000;">Zurück</a>
		              </div>
		            </form>
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
<script type="text/javascript">
	$('#arznei_id').change(function() {
		var quelleSelectbox = $('.quelle_id');
		var arznei_id = $(this).val();
		if(arznei_id != '' ) 
		{
			var url = baseApiURL+'arznei/view?arznei_id='+arznei_id;
			var request = $.ajax({
				            type: 'GET',
				            url: url,
				            headers: {
						       "Authorization" : "Bearer "+token
						    },
				            //data: new FormData(this),
				            contentType: false,
				            cache: false,
				            processData:false,
				            beforeSend:function() {
								//$.blockUI({ message: '<h4><i class="fa fa-refresh fa-spin"></i> Einen Augenblick...</h4>' }); 

							},
							complete:function(jqXHR, status){
								//$.unblockUI();
							}
				        });
				        request.done(function(response) {
				        	var responseData = null;
							try {
								responseData = JSON.parse(response); 
							} catch (e) {
								responseData = response;
							}
							var status = responseData.status;
							switch (status) { 
								case 1: 
									console.log(responseData.message);
									break;
								case 2:
									//console.log(responseData.content.data);
									quelleSelectbox.each(function() {
										$(this).children('option:not(:first)').remove();
									})
									var options = '';
									for(let key in responseData.content.data.quelle) { 
										options += `<option value="${responseData.content.data.quelle[key]['quelle_id']}">${responseData.content.data.quelle[key]['titel']}</option>`;
									}
									if(options != '') quelleSelectbox.removeAttr('disabled'); else quelleSelectbox.attr('disabled', 'disabled');
									quelleSelectbox.append(options);
									break;
								case 3:
									var errorMessage = '';
									for(let key in responseData.content) { 
										errorMessage += `<p>${responseData.content[key]}</p>`;
									}
									swal({
										type: 'error',
										title: 'Hoppla...',
										html: errorMessage,
									}); 
									console.log(errorMessage);
									console.log(responseData.message);
									break;
								case 4: 
									var errorMessage = responseData.message;
									swal({
										type: 'error',
										title: 'Hoppla...',
										html: errorMessage,
									});
									console.log(responseData.message);
									break;
								case 5: 
									var errorMessage = responseData.message;
									swal({
										type: 'error',
										title: 'Hoppla...',
										html: errorMessage,
									});
									console.log(responseData.message);
									break;
								case 6: 
									var errorMessage = responseData.message;
									swal({
										type: 'error',
										title: 'Hoppla...',
										html: errorMessage,
									});
									console.log(responseData.message);
									break;
								default:
									console.log('Unexpected errors');
							}
						});

						request.fail(function(jqXHR, textStatus) {
							var errorData = null;
							try {
							  errorData = JSON.parse(jqXHR); 
							} catch (e) {
							  errorData = jqXHR;
							}
							swal({
								type: 'error',
								title: 'Hoppla...',
								html: errorData,
							});
							console.log("Error : "+errorData);
						});
		} else {
			quelleSelectbox.attr('disabled', 'disabled');
		}
	})
</script>