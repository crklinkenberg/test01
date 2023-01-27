<?php
	include '../config/route.php';
	include 'sub-section-config.php';
?>
<?php  
	if(isset($_POST['upd_submit_hidden_eng']) AND $_POST['upd_submit_hidden_eng'] == "Update"){
		$stopwordsResult = mysqli_query($db,"SELECT * FROM stop_words WHERE language = 'english'");
		while($stopwordsRow = mysqli_fetch_array($stopwordsResult)){
			$name = $stopwordsRow['id'];
			$active = (isset($_POST[$name]) AND $_POST[$name] != "") ? $_POST[$name] : 0;
			$updateQuery="UPDATE stop_words SET active = '".$active."' WHERE id = '".$stopwordsRow['id']."'";
			$db->query($updateQuery);
		}
		header('Location: '.$baseUrl.'stop-words.php#english');
		exit();
	}

	if(isset($_POST['upd_submit_hidden_de']) AND $_POST['upd_submit_hidden_de'] == "Update"){
		$stopwordsResult = mysqli_query($db,"SELECT * FROM stop_words WHERE language = 'german'");
		while($stopwordsRow = mysqli_fetch_array($stopwordsResult)){
			$name = $stopwordsRow['id'];
			$active = (isset($_POST[$name]) AND $_POST[$name] != "") ? $_POST[$name] : 0;
			$updateQuery="UPDATE stop_words SET active = '".$active."' WHERE id = '".$stopwordsRow['id']."'";
			$db->query($updateQuery);
		}
		header('Location: '.$baseUrl.'stop-words.php#german');
		exit();
	}
	if(isset($_POST['add_submit_hidden']) AND $_POST['add_submit_hidden'] == "Save"){
		$urlPram = "stop-words.php";
		if(isset($_POST['stop_word']) AND $_POST['stop_word'] != ""){
			$stopWord = mysqli_real_escape_string($db, trim($_POST['stop_word']));
			$language = (isset($_POST['language']) AND $_POST['language'] != "") ? mysqli_real_escape_string($db, trim($_POST['language'])) : 'english';
			$stopwordsResult = mysqli_query($db,"SELECT id FROM stop_words WHERE name = '".$stopWord."'");
			if(mysqli_num_rows($stopwordsResult) == 0){
				$insertQuery="INSERT INTO stop_words (name, language) VALUES (NULLIF('".$stopWord."', ''), '".$language."')";
				$db->query($insertQuery);
				$urlPram .= '#'.$language;
			}
			else{
				$urlPram .= "?error=1#".$language;
			}
		}
		header('Location: '.$baseUrl.$urlPram);
		exit();
	}
	

	// $stopwords = array("a", "about", "above", "above", "across", "after", "afterwards", "again", "against", "all", "almost", "alone", "along", "already", "also","although","always","am","among", "amongst", "amoungst", "amount",  "an", "and", "another", "any","anyhow","anyone","anything","anyway", "anywhere", "are", "around", "as",  "at", "back","be","became", "because","become","becomes", "becoming", "been", "before", "beforehand", "behind", "being", "below", "beside", "besides", "between", "beyond", "bill", "both", "bottom","but", "by", "call", "can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de", "describe", "detail", "do", "done", "down", "due", "during", "each", "eg", "eight", "either", "eleven","else", "elsewhere", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front", "full", "further", "get", "give", "go", "had", "has", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "him", "himself", "his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed", "interest", "into", "is", "it", "its", "itself", "keep", "last", "latter", "latterly", "least", "less", "ltd", "made", "many", "may", "me", "meanwhile", "might", "mill", "mine", "more", "moreover", "most", "mostly", "move", "much", "must", "my", "myself", "name", "namely", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "on", "once", "one", "only", "onto", "or", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own","part", "per", "perhaps", "please", "put", "rather", "re", "same", "see", "seem", "seemed", "seeming", "seems", "serious", "several", "she", "should", "show", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "such", "system", "take", "ten", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "un", "under", "until", "up", "upon", "us", "very", "via", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "will", "with", "within", "without", "would", "yet", "you", "your", "yours", "yourself", "yourselves", "the");
	// foreach ($stopwords as $key => $value) {
	// 	$stopWord = mysqli_real_escape_string($db, trim($value));
	// 	$stopwordsResult = mysqli_query($db,"SELECT id FROM stop_words WHERE name LIKE '".$stopWord."'");
	// 	if(mysqli_num_rows($stopwordsResult) == 0){
	// 		$insertQuery="INSERT INTO stop_words (name) VALUES (NULLIF('".$stopWord."', ''))";
	// 		$db->query($insertQuery);
	// 	}
	// }

	// $germanStopwords = array("a","ab","aber","ach","acht","achte","achten","achter","achtes","ag","alle","allein","allem","allen","aller","allerdings","alles","allgemeinen","als","also","am","an","ander","andere","anderem","anderen","anderer","anderes","anderm","andern","anderr","anders","au","auch","auf","aus","ausser","ausserdem","außer","außerdem","b","bald","bei","beide","beiden","beim","beispiel","bekannt","bereits","besonders","besser","besten","bin","bis","bisher","bist","c","d","d.h","da","dabei","dadurch","dafür","dagegen","daher","dahin","dahinter","damals","damit","danach","daneben","dank","dann","daran","darauf","daraus","darf","darfst","darin","darum","darunter","darüber","das","dasein","daselbst","dass","dasselbe","davon","davor","dazu","dazwischen","daß","dein","deine","deinem","deinen","deiner","deines","dem","dementsprechend","demgegenüber","demgemäss","demgemäß","demselben","demzufolge","den","denen","denn","denselben","der","deren","derer","derjenige","derjenigen","dermassen","dermaßen","derselbe","derselben","des","deshalb","desselben","dessen","deswegen","dich","die","diejenige","diejenigen","dies","diese","dieselbe","dieselben","diesem","diesen","dieser","dieses","dir","doch","dort","drei","drin","dritte","dritten","dritter","drittes","du","durch","durchaus","durfte","durften","dürfen","dürft","e","eben","ebenso","ehrlich","ei","ei,","eigen","eigene","eigenen","eigener","eigenes","ein","einander","eine","einem","einen","einer","eines","einig","einige","einigem","einigen","einiger","einiges","einmal","eins","elf","en","ende","endlich","entweder","er","ernst","erst","erste","ersten","erster","erstes","es","etwa","etwas","euch","euer","eure","eurem","euren","eurer","eures","f","folgende","früher","fünf","fünfte","fünften","fünfter","fünftes","für","g","gab","ganz","ganze","ganzen","ganzer","ganzes","gar","gedurft","gegen","gegenüber","gehabt","gehen","geht","gekannt","gekonnt","gemacht","gemocht","gemusst","genug","gerade","gern","gesagt","geschweige","gewesen","gewollt","geworden","gibt","ging","gleich","gott","gross","grosse","grossen","grosser","grosses","groß","große","großen","großer","großes","gut","gute","guter","gutes","h","hab","habe","haben","habt","hast","hat","hatte","hatten","hattest","hattet","heisst","her","heute","hier","hin","hinter","hoch","hätte","hätten","i","ich","ihm","ihn","ihnen","ihr","ihre","ihrem","ihren","ihrer","ihres","im","immer","in","indem","infolgedessen","ins","irgend","ist","j","ja","jahr","jahre","jahren","je","jede","jedem","jeden","jeder","jedermann","jedermanns","jedes","jedoch","jemand","jemandem","jemanden","jene","jenem","jenen","jener","jenes","jetzt","k","kam","kann","kannst","kaum","kein","keine","keinem","keinen","keiner","keines","kleine","kleinen","kleiner","kleines","kommen","kommt","konnte","konnten","kurz","können","könnt","könnte","l","lang","lange","leicht","leide","lieber","los","m","machen","macht","machte","mag","magst","mahn","mal","man","manche","manchem","manchen","mancher","manches","mann","mehr","mein","meine","meinem","meinen","meiner","meines","mensch","menschen","mich","mir","mit","mittel","mochte","mochten","morgen","muss","musst","musste","mussten","muß","mußt","möchte","mögen","möglich","mögt","müssen","müsst","müßt","n","na","nach","nachdem","nahm","natürlich","neben","nein","neue","neuen","neun","neunte","neunten","neunter","neuntes","nicht","nichts","nie","niemand","niemandem","niemanden","noch","nun","nur","o","ob","oben","oder","offen","oft","ohne","ordnung","p","q","r","recht","rechte","rechten","rechter","rechtes","richtig","rund","s","sa","sache","sagt","sagte","sah","satt","schlecht","schluss","schon","sechs","sechste","sechsten","sechster","sechstes","sehr","sei","seid","seien","sein","seine","seinem","seinen","seiner","seines","seit","seitdem","selbst","sich","sie","sieben","siebente","siebenten","siebenter","siebentes","sind","so","solang","solche","solchem","solchen","solcher","solches","soll","sollen","sollst","sollt","sollte","sollten","sondern","sonst","soweit","sowie","später","startseite","statt","steht","suche","t","tag","tage","tagen","tat","teil","tel","tritt","trotzdem","tun","u","uhr","um","und","und?","uns","unse","unsem","unsen","unser","unsere","unserer","unses","unter","v","vergangenen","viel","viele","vielem","vielen","vielleicht","vier","vierte","vierten","vierter","viertes","vom","von","vor","w","wahr?","wann","war","waren","warst","wart","warum","was","weg","wegen","weil","weit","weiter","weitere","weiteren","weiteres","welche","welchem","welchen","welcher","welches","wem","wen","wenig","wenige","weniger","weniges","wenigstens","wenn","wer","werde","werden","werdet","weshalb","wessen","wie","wieder","wieso","will","willst","wir","wird","wirklich","wirst","wissen","wo","woher","wohin","wohl","wollen","wollt","wollte","wollten","worden","wurde","wurden","während","währenddem","währenddessen","wäre","würde","würden","x","y","z","z.b","zehn","zehnte","zehnten","zehnter","zehntes","zeit","zu","zuerst","zugleich","zum","zunächst","zur","zurück","zusammen","zwanzig","zwar","zwei","zweite","zweiten","zweiter","zweites","zwischen","zwölf","über","überhaupt","übrigens");
	// foreach ($germanStopwords as $key => $value) {
	// 	$stopWord = trim(mysqli_real_escape_string($db, trim($value)));
	// 	$stopwordsResult = mysqli_query($db,"SELECT id FROM stop_words WHERE name = '".$stopWord."'");
	// 	if(mysqli_num_rows($stopwordsResult) == 0){
	// 		$insertQuery="INSERT INTO stop_words (name, language) VALUES (NULLIF('".$stopWord."', ''), 'german')";
	// 		$db->query($insertQuery);
	// 	}
	// }
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Stop words</title>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<!-- Font Awesome -->
  	<link rel="stylesheet" href="plugins/font-awesome/css/fontawesome-all.min.css">
  	<!-- Select2 -->
  	<link rel="stylesheet" href="plugins/select2/dist/css/select2.min.css">
  	<!-- custom -->
  	<link rel="stylesheet" href="assets/css/custom.css">
</head>
<body>
	<div class="container">
		<?php 
			if(isset($_GET['error'])){
				switch ($_GET['error']) {
				 	case 1:
				 		$err_msg = "This stop word is already added.";
				 		break;
				 	
				 	default:
				 		$err_msg = "";
				 		break;
				} 
		?>	
			<div class="row text-center"><span class="text-danger text-center"><strong><?php echo $err_msg; ?></strong></span></div>
			<div class="spacer"></div>
		<?php 
			} 
		?>
		<div id="loader" class="form-group text-center">
			Loading is not complete please wait <img src="assets/img/loader.gif" alt="Loading...">
		</div>
		<h2>Stop words</h2>
		<small>Checked words will be ignored in comparison, you can uncheck the words that you want to include in the comparison and click on the save button below</small>
		<div class="spacer"></div>
		<ul class="nav nav-tabs" id="myTab">
		    <li class="active"><a data-toggle="tab" href="#english">English</a></li>
		    <li><a data-toggle="tab" href="#german">German</a></li>
		</ul>
		<div class="tab-content">
		    <div id="english" class="tab-pane fade in active">
		      	<form id="english_stop_form" name="english_stop_form" action="" class="unclicable" method="POST">
					<h3>Add new <a href="javascript:void(0)" title="Add stop word" onclick="openModal('english')"><i class="far fa-plus-square"></i></a></h3>
					<div class="spacer"></div>
					<div class="row">
						<?php
							$stopwordsResult = mysqli_query($db,"SELECT * FROM stop_words where language = 'english' ORDER BY name ASC");
							$cnt = 1;
							$isClosed = 0;
							while($stopwordsRow = mysqli_fetch_array($stopwordsResult)){
								if($cnt == 1)
									echo '<ul class="col-sm-3">';
								?>
								<li><input type="checkbox" name="<?php echo $stopwordsRow['id']; ?>" value="1" <?php if($stopwordsRow['active'] == 1){ echo 'checked'; } ?>> <?php echo $stopwordsRow['name']; ?></li>
								<?php
								if($cnt == 80){
									echo '</ul>';
									$cnt = 1;
									$isClosed = 1;
								}else
									$cnt++;
							}
							if($isClosed == 0)
								echo '</ul>';
						?>
					</div>
					
					<div class="form-group text-center">
						<div class="spacer"></div>
						<input type="hidden" name="upd_submit_hidden_eng" value="Update">
						<button id="eng_submit_btn" class="btn btn-success" type="button" onclick="stopWordUpdEng()">Save</button>
					</div>
				</form>
		    </div>
		    <div id="german" class="tab-pane fade">
		      	<form id="german_stop_form" name="german_stop_form" action="" class="unclicable" method="POST">
					<h3>Add new <a href="javascript:void(0)" title="Add stop word" onclick="openModal('german')"><i class="far fa-plus-square"></i></a></h3>
					<div class="spacer"></div>
					<div class="row">
						<?php
							$stopwordsResult = mysqli_query($db,"SELECT * FROM stop_words where language = 'german' ORDER BY name ASC");
							$cnt = 1;
							$isClosed = 0;
							while($stopwordsRow = mysqli_fetch_array($stopwordsResult)){
								if($cnt == 1)
									echo '<ul class="col-sm-3">';
								?>
								<li><input type="checkbox" name="<?php echo $stopwordsRow['id']; ?>" value="1" <?php if($stopwordsRow['active'] == 1){ echo 'checked'; } ?>> <?php echo $stopwordsRow['name']; ?></li>
								<?php
								if($cnt == 80){
									echo '</ul>';
									$cnt = 1;
									$isClosed = 1;
								}else
									$cnt++;
							}
							if($isClosed == 0)
								echo '</ul>';
						?>
					</div>
					
					<div class="form-group text-center">
						<div class="spacer"></div>
						<input type="hidden" name="upd_submit_hidden_de" value="Update">
						<button id="de_submit_btn" class="btn btn-success" type="button" onclick="stopWordUpdDe()">Save</button>
					</div>
				</form>
		    </div>
		</div>
	</div>

	<!-- Modal -->
	<div class="modal fade" id="stopwordModal" role="dialog">
	    <div class="modal-dialog modal-md">
	    	<form name="add_stop_word_form" id="add_stop_word_form" method="POST">
	    		<div class="modal-content">
			        <div class="modal-header">
			          	<button type="button" class="close" data-dismiss="modal">&times;</button>
			          	<h4 class="modal-title">Add stop word</h4>
			        </div>
			        <div class="modal-body">
			          	<div class="row">
							<div class="col-sm-8 col-sm-offset-2">
								<div class="form-group Text_form_group">
									<label class="comparing-option-label">Stop word</label>
									<input type="text" class="form-control" name="stop_word" id="stop_word" placeholder="Stop word">
									<span class="error-text"></span>
								</div>
							</div>
						</div>
			        </div>
			        <div class="modal-footer">
			        	<input type="hidden" name="add_submit_hidden" value="Save">
			        	<input type="hidden" name="language" id="language" value="">
			          	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			          	<button type="button" class="btn btn-success" name="add_stop_word_btn" id="add_stop_word_btn" onclick="adStopWordEng()">Save</button>
			        </div>
			    </div>
	    	</form>
	    </div>
	</div>
	<script type="text/javascript" src="plugins/jquery/jquery/jquery-3.3.1.js"></script>
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	<!-- Select2 -->
	<script src="plugins/select2/dist/js/select2.full.min.js"></script>
	<script src="assets/js/select2-custom-search-box-placeholder.js"></script>
	<script type="text/javascript">
		$(window).bind("load", function() {
			console.log('loaded');
			$("#loader").addClass("hidden");
			$("#english_stop_form").removeClass('unclicable');
			$("#german_stop_form").removeClass('unclicable');
		});

		$('#myTab a').click(function(e) {
		  e.preventDefault();
		  $(this).tab('show');
		});

		// // store the currently selected tab in the hash value
		// $("ul.nav-tabs > li > a").on("shown.bs.tab", function(e) {
		//   var id = $(e.target).attr("href").substr(1);
		//   window.location.hash = id;
		// });

		// on load of the page: switch to the currently selected tab
		var hash = window.location.hash;
		$('#myTab a[href="' + hash + '"]').tab('show');
		$('html, body').animate({
            scrollTop: $("body").offset().top
        }, 1000);

		function openModal(language){
			$("#language").val(language);
			$("#stop_word").next().html('');
			$("#stop_word").next().removeClass('text-danger');
			$("#stopwordModal").modal('show');
		}

		function stopWordUpdEng(){
			$('#eng_submit_btn').prop('disabled', true);
			$("#loader").removeClass("hidden");
			$("#english_stop_form").addClass('unclicable');
			$("#english_stop_form").submit();
		}

		function stopWordUpdDe(){
			$('#de_submit_btn').prop('disabled', true);
			$("#loader").removeClass("hidden");
			$("#german_stop_form").addClass('unclicable');
			$("#german_stop_form").submit();
		}

		function adStopWordEng(){
			var stop_word = $("#stop_word").val();
			var error_count = 0;

			if(stop_word == ""){
				$("#stop_word").next().html('Required');
				$("#stop_word").next().addClass('text-danger');
				error_count++;
			}else{
				$("#stop_word").next().html('');
				$("#stop_word").next().removeClass('text-danger');
			}

			if(error_count == 0){
				$('#add_stop_word_btn').prop('disabled', true);
				$("#loader").removeClass("hidden");
				$("#add_stop_word_form").submit();
			}else{
				return false;
			}
		}
	</script>
</body>
</html>
<?php
	include 'includes/php-foot-includes.php';
?>