<?php
$_SESSION['current_page'] = $actual_link;
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Symcom | <?php echo $admin; ?></title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="<?php echo $absoluteUrl;?>plugins/jasny-bootstrap/jasny-bootstrap.min.css">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="<?php echo $absoluteUrl;?>plugins/bootstrap/dist/css/bootstrap.min.css">
  <!-- dropify -->
  <link rel="stylesheet" type="text/css" href="<?php echo $absoluteUrl;?>plugins/dropify/css/dropify.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?php echo $absoluteUrl;?>plugins/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?php echo $baseUrl;?>plugins/font-awesome/css/fontawesome-all.min.css">
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
  <link rel="stylesheet" href="<?php echo $baseUrl;?>plugins/select2/dist/css/select2.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?php echo $absoluteUrl;?>assets/css/AdminLTE.min.css">
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
  
  