<?php  

ini_set("max_execution_time",'80000');

	ini_set('memory_limit','-1');
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
<!-- BEGIN HEAD --><head>
	<meta charset="utf-8" />
	<title><?php echo $title; ?></title>
	<meta content="width=device-width, initial-scale=1.0" name="viewport" />
	<meta content="" name="description" />
	<meta content="" name="author" />
    <link rel="icon" type="image/png" href="<?php echo front_url();?>assets/img/fav.png" />
	<link href="<?php echo front_url();?>assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
	<link href="<?php echo front_url();?>assets/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
	<link href="<?php echo front_url();?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
	<link href="<?php echo css_url();?>style.css" rel="stylesheet" />
	<link href="<?php echo css_url();?>style_responsive.css" rel="stylesheet" />
	<link href="<?php echo css_url();?>style_default.css" rel="stylesheet" id="style_color" />

	<link href="<?php echo front_url();?>assets/fancybox/source/jquery.fancybox.css" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="<?php echo front_url();?>assets/uniform/css/uniform.default.css" />
	<link href="<?php echo front_url();?>assets/fullcalendar/fullcalendar/bootstrap-fullcalendar.css" rel="stylesheet" />
	<link href="<?php echo front_url();?>assets/jqvmap/jqvmap/jqvmap.css" media="screen" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="<?php echo front_url();?>assets/bootstrap-daterangepicker/daterangepicker.css" />
    
    <!--Define Custom Css-->
    <link href="<?php echo front_url();?>assets/style/lightbox.css" rel="stylesheet" type="text/css" />
	<!-- lightbox style -->
	<!-- alert box style -->
	<link rel="stylesheet/less" type="text/css" href="<?php echo front_url();?>assets/style/style_custom.css" />
    <!--<link href="../style/style_custom.css" rel="stylesheet" type="text/css" />-->
    <!--<link href="../style/bootstrap.min.css" rel="stylesheet" type="text/css" />-->
   
    <!--End Define Custom Css-->
	<style type="text/css">
    	#header .navbar-inner .nav > li { margin-right: 10px !important; }
		.watermark_small
		{
			background: url('') no-repeat scroll center center rgba(0, 0, 0, 0); width: 100%;
		}
		.watermark_medium
		{
			background: url('') no-repeat scroll center center rgba(0, 0, 0, 0); width: 100%;
		}
		.watermark_big
		{
			background: url('') no-repeat scroll center center rgba(0, 0, 0, 0); width: 100%;
		}
    </style>

</head>
<!-- END HEAD -->
    
	
<!-- BEGIN HEADER -->
<div id="header" class="navbar navbar-inverse navbar-fixed-top">
  <!-- BEGIN TOP NAVIGATION BAR -->
  <div class="navbar-inner">
    <div class="container-fluid">
    <!--LOGO & NAVIGATION BAR-->
    </div>
  </div>
  <!-- END TOP NAVIGATION BAR -->
</div>
<!-- END HEADER -->
<!-- BEGIN BODY -->
<body class="fixed-top yui-skin-sam">
	<!-- BEGIN HEADER -->
	<div id="header" class="navbar navbar-inverse navbar-fixed-top">
		<!-- BEGIN TOP NAVIGATION BAR -->
		<div class="navbar-inner">
			<div class="container-fluid">
				<!-- BEGIN LOGO -->
				<a class="brand_new" href="<?php admin_url();?>">
				    <img src="<?php echo front_url();?>assets/img/logo.png" alt="Data.vu" />
				</a>
				<!-- END LOGO -->
				<!-- BEGIN RESPONSIVE MENU TOGGLER -->
				<a class="btn btn-navbar collapsed" id="main_menu_trigger" data-toggle="collapse" data-target=".nav-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="arrow"></span>
				</a>
				<!-- END RESPONSIVE MENU TOGGLER -->
				
                    <!-- END  NOTIFICATION -->
                <div class="top-nav ">
                    <ul class="nav pull-right top-menu">
                        <!-- BEGIN SUPPORT -->
                        <!--<li class="dropdown mtop5">

                            <a class="dropdown-toggle element" data-placement="bottom" data-toggle="tooltip" href="#" data-original-title="Settings">
                                <i class="icon-cog"></i>
                            </a>
                        </li>-->
                        
                        
                        <!-- END SUPPORT -->
                        <!-- BEGIN USER LOGIN DROPDOWN -->
				        <li class="dropdown">
                             <div style="margin-top:3px">
							 	 
							 </div>
                        </li>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <img src="<?php echo images_url()?>avatar-mini.png" alt="" height="29" width="29">
                                <span class="username"><?php echo $username; ?></span>
                                <b class="caret"></b>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="profile"><i class="icon-user"></i> My Profile</a></li>								
                                <li><a href="logout"><i class="icon-key"></i> Logout</a></li>
                                
                            </ul>
                            
                        </li>
                       
                                                
                        <!-- END USER LOGIN DROPDOWN -->
                    </ul>
					<!-- END TOP NAVIGATION MENU -->
				</div>
			</div>
		</div>
		<!-- END TOP NAVIGATION BAR -->
	</div>
	<!-- END HEADER -->
	<!-- BEGIN CONTAINER -->
   	<form id="company_form_select_result" method="post" action="">  
   		<input type="hidden" name="user_company_list" id="user_company_list">
   	</form>
	<div id="container" class="row-fluid">		