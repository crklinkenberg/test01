<?php  
	//$baseUrl = 'http://www.newrepertory.com/comparenew/';
	$baseUrl = 'http://www.newrepertory.com/dev-exp/';

	$settingsApplied = (isset($_GET['settingsApplied']) AND $_GET['settingsApplied'] != "") ? $_GET['settingsApplied'] : 0;
	$symInfoRadio = (isset($_GET['infoRadioVal']) AND $_GET['infoRadioVal'] != "") ? $_GET['infoRadioVal'] : 'metaDataInfoOff';
	$symHistoryRadio = (isset($_GET['historyRadioVal']) AND $_GET['historyRadioVal'] != "") ? $_GET['historyRadioVal'] : 'metaDataHistoryOff';

	include 'header-2.php';
	include 'sidebar-2.php';
?>