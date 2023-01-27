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
        <li class="mobile-show"><a href="#search" class="cd-search-trigger">Erweiterte Suche</a></li>
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
        <li class="">
          <a target="_blank" href="<?php echo $absoluteUrl;?>comparenew/materia-medica.php">
            <i class="fa fa-heartbeat"></i>
            <span>Materia medica</span>
          </a>
        </li>
        <li class="">
          <a target="_blank" href="<?php echo $absoluteUrl;?>comparenew/comparison.php">
            <i class="fa fa-random"></i>
            <span>Vergleich</span> <!-- Compare -->
          </a>
        </li>
        <li class="<?php if(preg_match("/stammdaten/", $actual_link)) echo 'active'; ?> reset-datatable-state treeview"> 
          <a href="#">
            <i class="fa fa-database"></i>
            <span>Stammdaten</span> <!-- Master Data -->
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="<?php if(preg_match("/quellen/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>stammdaten/quellen"><i class="fa fa-circle-o"></i> Bücher</a></li>
            <li class="<?php if(preg_match("/zeitschriften/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>stammdaten/zeitschriften"><i class="fa fa-circle-o"></i> Zeitschriften</a></li>
            <li class="<?php if(preg_match("/quelle-settings/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>stammdaten/quelle-settings"><i class="fa fa-circle-o"></i> Settings</a></li>
            <li class="<?php if(preg_match("/global-grading-settings/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>stammdaten/global-grading-settings"><i class="fa fa-circle-o"></i> Grade Schriftarten</a></li> <!-- Global Grading Settings -->
            <li class="<?php if(preg_match("/arzneien/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>stammdaten/arzneien"><i class="fa fa-circle-o"></i> Arzneien</a></li>
            <li class="<?php if(preg_match("/autoren/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>stammdaten/autoren"><i class="fa fa-circle-o"></i> Autoren</a></li>
            <li class="<?php if(preg_match("/prufer/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>stammdaten/prufer"><i class="fa fa-circle-o"></i> Prüfer</a></li>
            <li class="<?php if(preg_match("/herkunft/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>stammdaten/herkunft"><i class="fa fa-circle-o"></i> Herkunft</a></li>
            <li class="<?php if(preg_match("/synonym/", $actual_link)) echo 'active'; ?> reset-datatable-state treeview">
              <a href="#">
                <i class="fa fa-circle-o"></i>
                <span>Synonyms</span>
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                <li class="<?php if(preg_match("/synonym-de/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>stammdaten/synonym-de"><i class="fa fa-circle-o"></i> Synonym DE</a></li>
                <li class="<?php if(preg_match("/synonym-en/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>stammdaten/synonym-en"><i class="fa fa-circle-o"></i> Synonym EN</a></li>
              </ul>
            </li>
            <li class="<?php if(preg_match("/verlage/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>stammdaten/verlage"><i class="fa fa-circle-o"></i> Verlage</a></li>
            <li class="<?php if(preg_match("/reference/", $actual_link)) echo 'active'; ?> reset-datatable-state treeview">
              <a href="#">
                <i class="fa fa-circle-o"></i>
                <span>Literatur</span> <!-- Reference -->
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                <li class="<?php if(preg_match("/de/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>stammdaten/reference/de"><i class="fa fa-circle-o"></i> Literatur DE</a></li>
                <?php /* <li class="<?php //if(preg_match("/reference/en/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>stammdaten/reference/en"><i class="fa fa-circle-o"></i> Reference EN</a></li> */ ?>
                <li class=""><a href="#"><i class="fa fa-circle-o"></i> Literatur EN</a></li>
              </ul>
            </li>
          </ul>
        </li>
        <?php if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 1 )) { ?>
        <li class="<?php if(preg_match("/einstellungen/", $actual_link)) echo 'active'; ?> treeview reset-datatable-state"> 
          <a href="#">
          <i class="fa fa-cog"></i>
            <span>Benutzer</span> <!-- user -->
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="<?php if(preg_match("/benutzer/", $actual_link)) echo 'active'; ?>"><a href="<?php echo $absoluteUrl;?>einstellungen/benutzer"><i class="fa fa-circle-o"></i> Benutzer</a></li>
          </ul>
        </li>
        <?php  } ?>
        <li class="<?php if(preg_match("/import/", $actual_link)) echo 'active'; ?>">
          <a href="<?php echo $absoluteUrl;?>import">
            <i class="fa fa-sign-in"></i>
            <span>Import</span>
          </a>
        </li>
      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>