<!-- BEGIN SIDEBAR -->
<div id="sidebar" class="nav-collapse collapse">
    <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
    <div class="sidebar-toggler hidden-phone"></div>
    <!-- BEGIN SIDEBAR TOGGLER BUTTON -->

    <!-- BEGIN RESPONSIVE QUICK SEARCH FORM -->
    <div class="navbar-inverse">
        <form class="navbar-search visible-phone">
            <input type="text" class="search-query" placeholder="Search" />
        </form>
    </div>
    <!-- END RESPONSIVE QUICK SEARCH FORM -->
    <!-- BEGIN SIDEBAR MENU -->
    <ul class="sidebar-menu">
       
        <li class="has-sub <?php if(strpos($_SERVER['REQUEST_URI'],'admin/dashboard') || strpos($_SERVER['REQUEST_URI'],'admin/brand') || strpos($_SERVER['REQUEST_URI'],'admin/manufacturer') || strpos($_SERVER['REQUEST_URI'],'admin/category') || strpos($_SERVER['REQUEST_URI'],'admin/product_list') || strpos($_SERVER['REQUEST_URI'],'admin/product_url_list') || strpos($_SERVER['REQUEST_URI'],'admin/products') || strpos($_SERVER['REQUEST_URI'],'admin/view_all_products') || strpos($_SERVER['REQUEST_URI'],'admin/bulk_upload')) { echo 'active'; } ?>">
            <a href="javascript:;" class="">
                <span class="icon-box"> <i class="icon-dashboard"></i></span> Management
                <span class="arrow"></span>
            </a>
            <ul class="sub">
            <li class="<?php if(strpos($_SERVER['REQUEST_URI'],'admin/category')) { echo 'active'; } ?>"><a class="" href="<?php echo admin_url();?>category">Category</a></li>
            <li class="<?php if(strpos($_SERVER['REQUEST_URI'],'admin/brand')) { echo 'active'; } ?>"><a class="" href="<?php echo admin_url();?>brand">Brand</a></li>
            <li class="<?php if(strpos($_SERVER['REQUEST_URI'],'admin/manufacturer')) { echo 'active'; } ?>"><a class="" href="<?php echo admin_url();?>manufacturer">Manufacturer</a></li>
            <li class="<?php if(strpos($_SERVER['REQUEST_URI'],'admin/dashboard')) { echo 'active'; } ?>"><a class="" href="<?php echo admin_url();?>dashboard">Dashboard</a></li>
            <li class="<?php if(strpos($_SERVER['REQUEST_URI'],'admin/view_all_products')) { echo 'active'; } ?>"><a class="" href="<?php echo admin_url();?>view_all_products">View All Products</a></li>
            <li class="<?php if(strpos($_SERVER['REQUEST_URI'],'admin/bulk_upload')) { echo 'active'; } ?>"><a class="" href="<?php echo admin_url();?>bulk_upload">Bulk upload</a></li>
            </ul>

        </li>
       

        <li class="has-sub">
            <a href="javascript:;" class="">
                <span class="icon-box"><i class="icon-cogs"></i></span> Your account  
                <span class="arrow"></span>
            </a>
            <ul class="sub">
                <li><a class="" href="<?php admin_url();?>profile">My profile</a></li>
                
             </ul>
        </li>
		<li><a href="logout" class=""><span class="icon-box"><i class="icon-user"></i></span> Logout</a></li>
    </ul>
    <!-- END SIDEBAR MENU -->
</div>
<!-- END SIDEBAR -->
