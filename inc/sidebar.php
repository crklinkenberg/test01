 <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- sidebar menu: : style can be found in sidebar.less -->
      <form action="#" method="get" class="sidebar-form mobile-show">
        <div class="input-group">
          <input type="text" name="q" class="form-control" placeholder="Suche..." autocomplete="off">
          <span class="input-group-btn">
                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
        </div>
      </form>
      <ul class="sidebar-menu" data-widget="tree">
        <li class="mobile-show"><a href="#search" class="cd-search-trigger">Advanced Search</a></li>
        <!-- <li class="<?php if(preg_match("/quellenimport/", $actual_link)) echo 'active'; ?>">
          <a href="<?php echo $absoluteUrl;?>quellenimport">
            <i class="fa fa-sign-in"></i>
            <span>Quellenimport</span>
          </a>
        </li> -->
        <!-- <li class="treeview">
          <a href="#">
            <i class="fa fa-line-chart"></i>
            <span>Quellenvergleich</span>
          </a>
        </li> -->
        <li class="<?php if(preg_match("/stammdaten/", $actual_link)) echo 'active'; ?> reset-datatable-state treeview"> 
          <a href="#">
            <i class="fa fa-database"></i>
            <span>Master data</span> <!-- Master Data -->
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="<?php if(preg_match("/quellen/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>stammdaten/quellen"><i class="far fa-circle fa-xs"></i> Books</a></li>
            <li class="<?php if(preg_match("/zeitschriften/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>stammdaten/zeitschriften"><i class="far fa-circle fa-xs"></i> Magazines</a></li>
            <li class="<?php if(preg_match("/arzneien/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>stammdaten/arzneien"><i class="far fa-circle fa-xs"></i> Medicines</a></li>
            <li class="<?php if(preg_match("/autoren/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>stammdaten/autoren"><i class="far fa-circle fa-xs"></i> Authors</a></li>
            <li class="<?php if(preg_match("/prufer/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>stammdaten/prufer"><i class="far fa-circle fa-xs"></i> Tester</a></li>
            <li class="<?php if(preg_match("/herkunft/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>stammdaten/herkunft"><i class="far fa-circle fa-xs"></i> Origin</a></li>
            <li class="<?php if(preg_match("/synonym/", $actual_link)) echo 'active'; ?> reset-datatable-state treeview">
              <a href="#">
                <i class="far fa-circle fa-xs"></i>
                <span>Synonyms</span>
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                <li class="<?php if(preg_match("/synonym-de/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>stammdaten/synonym-de"><i class="far fa-circle fa-xs"></i> Synonym DE</a></li>
                <li class="<?php if(preg_match("/synonym-en/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>stammdaten/synonym-en"><i class="far fa-circle fa-xs"></i> Synonym EN</a></li>
              </ul>
            </li>
            <li class="<?php if(preg_match("/verlage/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>stammdaten/verlage"><i class="far fa-circle fa-xs"></i> Publishers</a></li>
            <li class="<?php if(preg_match("/reference/", $actual_link)) echo 'active'; ?> reset-datatable-state treeview">
              <a href="#">
                <i class="far fa-circle fa-xs"></i>
                <span>Literature</span> <!-- Reference -->
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                <li class="<?php if(preg_match("/de/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>stammdaten/reference/de"><i class="far fa-circle fa-xs"></i> Literature DE</a></li>
                <?php /* <li class="<?php //if(preg_match("/reference/en/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>stammdaten/reference/en"><i class="far fa-circle fa-xs"></i> Reference EN</a></li> */ ?>
                <li class=""><a href="#"><i class="far fa-circle fa-xs"></i> Literature EN</a></li>
              </ul>
            </li>
          </ul>
        </li>
        <li class="<?php if(preg_match("/source-settings/", $actual_link)) echo 'active'; ?> reset-datatable-state treeview"> 
          <a href="#">
            <i class="fa fa-cog"></i>
            <span>Settings Sources</span> <!-- Master Data -->
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="<?php if(preg_match("/font/i", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>source-settings/quelle-settings/index-font"><i class="far fa-circle fa-xs"></i> Fonts</a></li>
            <li class="<?php if(preg_match("/symptom-type/i", $actual_link)) echo 'active';   ?>"><a href="<?php echo $absoluteUrl;?>source-settings/quelle-settings/index-symptom-type"><i class="far fa-circle fa-xs"></i> Symptom Type Setting</a></li>
            <li class="<?php if(preg_match("/global-grading-settings/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>source-settings/global-grading-settings"><i class="far fa-circle fa-xs"></i> Grade fonts</a></li> <!-- Global Grading Settings -->
          </ul>
          <li class="<?php if(preg_match("/import/", $actual_link)) echo 'active'; ?>">
            <!-- <a href="<?php //echo $absoluteUrl;?>import"> -->
            <a href="<?php echo $absoluteUrl;?>dev-exp">
              <i class="fa fa-sign-in-alt"></i>
              <span>Import</span>
            </a>
          </li>
          <li class="<?php if(preg_match("/comparison.php/i", $actual_link)) echo 'active'; ?>">
            <a href="<?php echo $absoluteUrl;?>dev-exp/comparison.php">
              <i class="fa fa-random"></i>
              <span>Comparison</span> <!-- Compare -->
            </a>
          </li>
          <li class="<?php if(preg_match("/materia-medica/i", $actual_link)) echo 'active'; ?>">
            <a href="<?php echo $absoluteUrl;?>dev-exp/materia-medica.php">
              <i class="fa fa-heartbeat"></i>
              <span>Materia medica</span>
            </a>
          </li>
          <li class="<?php if(preg_match("/history/i", $actual_link)) echo 'active'; ?>">
            <a href="<?php echo $absoluteUrl;?>dev-exp/history.php">
              <i class="fa fa-history"></i>
              <span>History</span>
            </a>
          </li>
          <?php if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 1 )) { ?>
          <li class="<?php if(preg_match("/einstellungen/", $actual_link)) echo 'active'; ?> treeview reset-datatable-state"> 
            <a href="#">
            <i class="fa fa-cog"></i>
              <span>User</span> <!-- user -->
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
              <li class="<?php if(preg_match("/benutzer/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>einstellungen/benutzer"><i class="far fa-circle fa-xs"></i> User</a></li>
            </ul>
          </li>
          <?php  } ?>
          <li class="">
            <a href="#">
              <i class="fa fa-comments"></i>
              <span>Contact / Imprint</span>
            </a>
          </li>
        </li>
      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>