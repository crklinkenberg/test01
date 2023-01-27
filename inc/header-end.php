
</head>
<body class="hold-transition skin-black sidebar-mini">
  <div class="wrapper">
    <header class="main-header cd-main-header animate-search">
      <!-- Logo -->
      <a href="<?php echo $absoluteUrl;?>" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini"><b>S</b>C</span>
        <!-- logo for regular state and mobile devices -->
        <div class="logo-lg"><img class="img-responsive" src="<?php echo $absoluteUrl;?>assets/img/logo-big.png" alt="logo"></div>
      </a>
      <!-- Header Navbar: style can be found in header.less -->
      <nav class="navbar navbar-static-top cd-main-nav-wrapper">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
          <span class="sr-only">Toggle navigation</span>
        </a>
        <div class="navbar-custom-menu">
          <ul class="nav navbar-nav reset-datatable-state">
            <!-- User Account: style can be found in dropdown.less -->
            <li class="last-login"><a><?php if(isset($_SESSION['last_login_at']) && (!empty($_SESSION['last_login_at']))) echo 'Last login '.$_SESSION['last_login_at'];?></a></li>
            <li class="dropdown">
              <a class="dropdown-toggle" href="#" id="kuntoDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <?php if(isset($_SESSION['username']) && (!empty($_SESSION['username']))) echo ucfirst($_SESSION['username']); else 'Not available';?>. &nbsp;<i class="fa fa-caret-down"></i>
              </a>
              <ul class="dropdown-menu kuntoDropdown" aria-labelledby="kuntoDropdown">
                  <li><a class="dropdown-item" href="<?php echo $absoluteUrl;?>profil"><i class="fa fa-user"></i> Profil</a></li>
                  <li><a class="dropdown-item" href="<?php echo $absoluteUrl;?>api/logout.php"><i class="fa fa-sign-out"></i> Abmelden</a></li>
              </ul>
          </li>
          </ul>
        </div>
      </nav>
      <div id="search" class="cd-main-search">
        <form>
          <div class="search-input">
            <input type="search" placeholder="Suche...">
          </div>

          <a class="close"></a>
        </form>
      </div> <!-- .cd-main-search -->
    </header>