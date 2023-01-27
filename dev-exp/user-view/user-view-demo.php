<?php  
	//$baseUrl = 'http://www.newrepertory.com/comparenew/';
	$baseUrl = 'http://www.newrepertory.com/dev-exp/';

	$settingsApplied = (isset($_GET['settingsApplied']) AND $_GET['settingsApplied'] != "") ? $_GET['settingsApplied'] : 0;
	$symInfoRadio = (isset($_GET['infoRadioVal']) AND $_GET['infoRadioVal'] != "") ? $_GET['infoRadioVal'] : 'metaDataInfoOff';
	$symHistoryRadio = (isset($_GET['historyRadioVal']) AND $_GET['historyRadioVal'] != "") ? $_GET['historyRadioVal'] : 'metaDataHistoryOff';

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>User View Demo</title>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="assets/css/bootstrap.min.css">
	<!-- Font Awesome -->
  	<link rel="stylesheet" href="../plugins/font-awesome/css/fontawesome-all.min.css">
	<!-- custom -->
  	<link rel="stylesheet" href="assets/css/user-view.css">
  	<style type="text/css">
  		.minimizeSidebar{
  			width: 3%;
  			transition: width 0.5s;
  		}
  		.maximizeSidebar{
  			transition: width 0.5s;
  		}
  		.hidden{
  			display: none;
  		}
  		.nav-item{
  			font-size: small;
  		}
  		.toggleSidebar{
  			padding: 1px;
  		}
  		.navbarIconModified{
  			width: 1em;
  		}
  	</style>
</head>
<body>
	<div class="container-fluid fullHeight">
		<div class="row fullHeight">
			<div class="col-sm-2 sidebarBackground">
				<?php include'side-dashboard.php'; ?>
			</div>
			<div class="col gx-0">
				<nav class="navbar navbar-light bg-light">
				  <div class="container-fluid">
				    <button class="navbar-toggler toggleSidebar" type="button" data-bs-toggle="collapse" data-bs-target="#navbarToggleExternalContent" aria-controls="navbarToggleExternalContent" aria-expanded="false" aria-label="Toggle navigation">
				      <span class="navbar-toggler-icon navbarIconModified"></span>
				    </button>
				  </div>
				</nav>
				<div class="container">
					<div class="row upperContent"></div>
					<div class="card mainContent">
						<div class="card-body">
							<div class="row settingsBox">
							<input type="hidden" name="settingsApplied" id="settingsApplied" value="<?php echo $settingsApplied; ?>">
								<?php 
									if($settingsApplied == 1){
										$infoHtml = '';
										$historyHtml = '';
										$historySymptom = '';
										if($symInfoRadio=='metaDataInfoOn') 
											$infoHtml = ' <i class="fas fa-info-circle symptom-info"></i>'; 
										if($symHistoryRadio=='metaDataHistoryOn') {
											$historyHtml = ' <i class="fas fa-history"></i>';
											$historySymptom = '
													<div class="symptom-history">In the eyes a strong pressure.</div>
													<div class="symptom-history">Eyes with pressure.</div>';
										}
										echo '
											<form action="'.$baseUrl.'user-view/user-view-demo.php" id="settingsForm" method="GET"> 
												<input type="hidden" name="infoRadioVal" id="infoRadioVal" value="metaDataInfoOn">
												<input type="hidden" name="historyRadioVal" id="historyRadioVal" value="metaDataHistoryOn">
											</form>
											<div id="symptomDisplay">
												<div class="symptom-row heading">
													<div class="symptom heading text-center">Symptom</div>
												</div>
												<div class="symptom-row">
													<div class="symptom">Pressure in the eyes. '.$infoHtml.$historyHtml.'</div>'.$historySymptom.'
												</div>
												<div class="symptom-row">
													<div class="symptom">Muddled in the head, as if from loss of sleep.</div>
												</div>
											</div>
										';
									}else{
										echo '
											<div class="col-md-4">
												<h4 id="settingsHeading">Please select settings.</h4>
												<form action="'.$baseUrl.'user-view/user-view-demo.php" id="settingsForm" method="GET"> 
												<input type="hidden" name="infoRadioVal" id="infoRadioVal" value="metaDataInfoOn">
												<input type="hidden" name="historyRadioVal" id="historyRadioVal" value="metaDataHistoryOn">
													<div class="mb-3" id="formContent"></div>
												</form>
											</div>
											<div class="col-md-8"></div>
										';
									} 
								?>
							</div>
						</div>
					</div>
				</div>			
			</div>
		</div>
	</div>
	<div>
	</div>
	<!-- Latest compiled and minified JavaScript -->
	<script type="text/javascript" src="assets/js/jquery-3.6.1.min.js"></script>
	<script type="text/javascript" src="assets/js/bootstrap.min.js"></script>
	<script src="assets/js/user-view.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			$('.toggleSidebar').on('click',function(){
				if($('.sidebarBackground').hasClass('minimizeSidebar')){
					$('.sidebarBackground').removeClass('minimizeSidebar');
					$('.logoSidebar').removeClass('hidden');
					$('.sidebarBackground').addClass('maximizeSidebar');
				}else{
					$('.sidebarBackground').addClass('minimizeSidebar');
					$('.logoSidebar').addClass('hidden');
				}
			});
		});
	</script>
</body>
</html>