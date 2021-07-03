<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>LazySuzy</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
        <link href="https://fonts.googleapis.com/css?family=Marcellus+SC" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo front_css_url();?>normalize.css">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.0/animate.min.css">

        <link rel="stylesheet" href="<?php echo front_css_url();?>ion.rangeSlider.css" />
        <link rel="stylesheet" href="<?php echo front_css_url();?>ion.rangeSlider.skinModern.css">
        <link rel="stylesheet" href="<?php echo front_css_url();?>product-gal.css">
    </head>
    <style type="text/css">
        #return-to-top {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #32C2CD;
        background: #32C2CD;
        width: 50px;
        height: 50px;
        display: block;
        text-decoration: none;
        -webkit-border-radius: 35px;
        -moz-border-radius: 35px;
        border-radius: 35px;
        display: none;
        -webkit-transition: all 0.3s linear;
        -moz-transition: all 0.3s ease;
        -ms-transition: all 0.3s ease;
        -o-transition: all 0.3s ease;
        transition: all 0.3s ease;
        }
        #return-to-top i {
            color: #fff;
            margin: 0;
            position: relative;
            left: 16px;
            top: 13px;
            font-size: 19px;
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            -ms-transition: all 0.3s ease;
            -o-transition: all 0.3s ease;
            transition: all 0.3s ease;
        }
        #return-to-top:hover {
            background: #32C2CD;
        }
        #return-to-top:hover i {
            color: #fff;
            top: 5px;
        }
    </style>

    <style type="text/css">
        #datatable table{ width:100% !important; text-align:center; }
        .yui-dt-paginator{text-align: center;}
        .modal-body{ text-align:center;}
        .yui-dt th, .yui-dt td{height:40px;}

        .modal.fade.in {
          top: 10%;
          left: 46%;
        }
        .dialog .modal {
          left: 38%!important;
          width:800px!important;
        }
         th {
          background: #D8D8DA !important;
          text-align: center !important;
          }

        .pagination {
            display: inline-block;
            float: right;
        }

        .pagination a {
            color: black;
            float: left;
            padding: 8px 16px;
            text-decoration: none;
            transition: background-color .3s;
            border: 1px solid #ddd;
        }

        .pagination a.active {
            background-color: #32C2CD;
            color: white;
            border: 1px solid #32C2CD;
        }

        .pagination a:hover:not(.active) {background-color: #ddd;}
    </style>
    <body>
        <!--
        [if lte IE 9]>
          <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
        <![endif]-->
        <section id="navbar">
            <nav class="navbar navbar-expand-lg sticky-top navbar-light bg-light">
                <div class="d-flex justify-content-between flex-nowrap contain-full">
                    <div class="flex-div">
                        <div class="d-flex justify-content-between contain-full">
                            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>
                            <a class="navbar-brand" href="<?php echo base_url();?>">
                                <img src="<?php echo front_images_url();?>logo/png/color_logo_with_background.png" alt="LazySuzy" id="logo-small">
                            </a>
                        </div>
                    </div>
                    <div class="flex-div d-none d-sm-block">
                        <form class="card card-sm ht-balance">
                            <div class="card-body row no-gutters align-items-center">
                                
                                <!--end of col-->
                                <div class="col">
                                    <input class="form-control form-control-lg form-control-borderless search-big-d" type="search" name="search_text" id="search_text" placeholder="Find your accent...">
                                </div>
                                <!--end of col-->
                                <div class="col-auto" id="new_table_yui_grid">
                                    <i class="fas fa-search h4 text-body"></i>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="flex-div">
                        <ul class="small-nav"> <!-- navbar-nav mr-auto float-right -->
                            <li class="nav-ite">
                                <a href="" class="nav-link user-ico dark small-text">
                                    About Us
                                </a>
                            </li>
                            <li class="nav-ite d-md-none d-sm-block d-xl-none d-lg-none">
                                <input type="search" class="expandable-search" placeholder="Find your Accent" >
                            </li>
                            <li class="nav-ite">
                                <a href="" class="nav-link user-ico dark">
                                    <i class="far fa-heart"></i>
                                </a>
                            </li>
                            <li class="nav-ite">
                                <a href="" class="nav-link user-ico dark">
                                    <i class="fas fa-user-circle"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </section>
        <section id="departments">

            <!-- New design images 26-11-18 mobile view -->
            <div class="container">
                <div class="row d-flex justify-content-between d-sm-none">
                    <div class="d-flex">
                        <nav class="navbar navbar-light navbar-expand-lg mainmenu">
                            <ul class="navbar-nav mr-auto">
                                <li class="dropdown">
                                    <a class="dropdown-toggle custm" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-angle-down"></i></a>
                                    <ul class="dropdown-menu custm lvl-one" aria-labelledby="navbarDropdown">
                                     

                                        <?php
                                        
                                        foreach($get_categories as $getcategories)
                                        {
                                            $dept = $getcategories['name'];

                                            $get_sub_categories = $this->db->query('select * from dsb_department where name="'.addslashes($dept).'" AND status=1 group by product_category')->result_array();
                                            
                                            
                                            if($get_sub_categories)
                                            {
                                                echo '<li class="dropdown">
                                                            <a class="dropdown-toggle custm" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.$getcategories['name'].'
                                                            <i class="fas fa-angle-right"></i>
                                                            </a>';
                                                            
                                                            echo '<ul class="dropdown-menu custm" aria-labelledby="navbarDropdown">';
                                                            
                                                            foreach($get_sub_categories as $sub_category)
                                                            {
                                                                if($sub_category['product_category'])
                                                                {
                                                                    ?>
                                                                    <li>
                                                                        <a onClick="sub_category_filter('<?php echo $sub_category["product_category"];?>');" href="javascript:void(0)"><?php echo $sub_category['product_category'];?>
                                                                        </a>
                                                                    </li>
                                                                    <?php
                                                                } 
                                                            }
                                                            echo '</ul>
                                                        </li>';
                                            }
                                            else
                                            {   
                                                ?>
                                                <li>
                                                    <a onClick="category_filter('<?php echo $getcategories["department_id"];?>');" href="javascript:void(0)"><?php echo $getcategories['name'];?>
                                                    </a>
                                                </li>
                                                <?php 
                                            }
                                        }

                                        ?>
                                        
                                    </ul>
                                </li>
                            </ul>
                        </nav>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb custm">
                                <li class="breadcrumb-item"><a href="<?php echo base_url();?>"><?php echo $current_department; ?></a></li>
                                <?php 
                                if($current_pro_category!='')
                                {
                                    ?>
                                    <li class="breadcrumb-item <?php if($current_pro_category!=''){ echo 'active'; }?>" aria-current="page"><?php echo $current_pro_category; ?></li>
                                    <?php 
                                }
                                ?>
                            </ol>
                        </nav>
                    </div>
                    <div class="filters-con">
                        <i class="fas fa-filter" id="call-filters"></i>
                    </div>
                </div>
            </div>
            <!-- End 26-11-18 -->

            <div class="container-fluid d-none d-sm-block">
                <div class="row">
                    <div class="col-11 offset-md-x">
                        <div class="departments-container-v2 d-flex flex-wrap justify-content-center">
                            <?php 
                            foreach($get_categories as $getcategories)
                            {
                                ?>
                                <div class="department-v2">
                                    <div class="department-text-v2">
                                        <a onClick="category_filter('<?php echo $getcategories['department_id'];?>');" href="javascript:void(0)" class="dept-link 
                                        <?php echo ($current_dept == $getcategories['department_id']) ? 'active' : '' ?>">
                                        <h2 class="dept-text-v2"><?php echo $getcategories['name'];?></h2>
                                        </a>
                                    </div>
                                </div>
                                <?php 
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-10 offset-md-x d-flex flex-wrap justify-content-between">
                        <div class="department-container-short d-flex flex-wrap justify-content-center">
                            <?php foreach($sub_categories as $subcategory)
                            {
                                ?>
                                <div class="short-dept">
                                    <a onClick="sub_category_filter('<?php echo $subcategory['product_category'];?>');" href="javascript:void(0)" class="short-dept-link  <?php echo ($current_pro_category == $subcategory['product_category']) ? 'active' : '' ?>">
                                    <p><?php echo $subcategory['product_category'];?></p></a>
                                </div>
                                <?php

                            } ?>
                        </div>
                        <div class="selector d-flex">
                            <label for="">Sort By </label>
                            <select class="cstm-select" name="sort_by" onChange="get_price_filter();" id='sort_by'>
                                <option value="0">Recommended</option>
                                <option <?php if($price_filter == 1){ echo 'selected=selected';}?> value="1">Price: Low to High</option>
                                <option <?php if($price_filter == 2){ echo 'selected=selected';}?> value="2">Price: High to Low</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-10 offset-md-x text-right">
                        <p id="result-num"><?php echo $total_records;?> Results</p>
                    </div>
                </div>
            </div>    

        </section>
        <section id="products">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-2 filter-fun animated d-none d-sm-none d-md-block">
                        <div class="filters">
                            <div class="row">
                                <div class="brand-filter-container col-6 col-md-12">
                                    <h6 class="filter-heading">Brands</h6>
                                    <ul class="filter-list">
                                        <?php 
                                        $site_id = $_GET['site'];
                                        $explode = explode(',',$site_id);
                                        foreach($allsitedetails as $site_details)
                                        {
                                            if(in_array($site_details->site_id,$explode))
                                                $checked = 'checked';
                                            else
                                                $checked = '';
                                            ?>
                                            <li>
                                                <label for="filter">
                                                    <input type="checkbox" id="pro_name_<?php echo $site_details->site_id;?>" onClick="get_filter();" name="pro_name" value="<?php echo $site_details->site_id;?>" class="filter-input" <?php echo $checked; ?>>
                                                    <?php echo $site_details->site_name;?>
                                                </label>
                                            </li>
                                            <?php 
                                        }
                                        ?>
                                    </ul>
                                </div>
                                <div class="brand-filter-container col-6 col-md-12">
                                    <h6 class="filter-heading">Price</h6>
                                    <div class="theme-ccff22">
                                        <input type="text" id="range" value="" name="range" />
                                    </div>
                                </div>
                            </div>    
                                <div class="brand-filter-container">
                                <a class="btn" style="color:white;background-color:#0c1c31;" href="<?php echo base_url();?>products">Clear filter</a>
                                </div>

                        </div>
                    </div>

                    <div class="col-md-10 pad-0">
                        <div class="ajax-load1 text-center" style="display: none; margin-top: 20%;">
                            <p>
                                <img src="<?php echo front_images_url();?>loader.gif">Loading More products
                            </p>
                        </div>
                        <div class="d-flex flex-wrap products-container postsList" id="post-data">
                            <?php 
                            if(!empty($result))
                            {   $i=1;
                                foreach($result as $product_details)
                                {
                                    if(strpos($product_details->product_url,'cb2') !== false) 
                                    {
                                      $pro_id = 'cb2';

                                    } 
                                    if(strpos($product_details->product_url,'pier1') !== false)
                                    {
                                      $pro_id = 'pier-1';
                                    }
                                    if(strpos($product_details->product_url,'potterybarn') !== false)
                                    {
                                      $pro_id = 'potterybarn';
                                    }

                                    if(strpos($product_details->images, '[US]') == true) 
                                    {
                                         
                                        $imagess = explode('[US]', $product_details->images);
                                        $images  = $imagess[0];
                                    }
                                    else
                                    {
                                        $images  = $product_details->images;
                                    }
                                    if($i >= 2)
                                        $bounce = 'bounce';
                                    else
                                        $bounce = '';
                                    ?>
                                    <div class="product-show">
                                        <div class="product-top-layer">
                                            <div class="micro-intra">
                                                <div class="d-flex justify-content-between flex-wrap">
                                                    <?php 

                                                    if(strpos($product_details->price,'INR') !== false) 
                                                    {
                                                        $price      = preg_replace("/[^0-9]/", "",$product_details->price);
                                                    } 
                                                    else
                                                    {
                                                      $price      =   $product_details->price;
                                                    }
                                                    if(strpos($product_details->was_price,'INR') !== false) 
                                                    {
                                                        $was_price  = preg_replace("/[^0-9]/", "",$product_details->was_price);
                                                    }
                                                    else
                                                    {
                                                      $was_price      =   $product_details->was_price;
                                                    }

                                                    if($was_price > $price)
                                                    {
                                                        ?>
                                                        <div>
                                                            <span class="price-tag">
                                                                <i class="fas fa-dollar-sign padd"><?php //echo $product_details->price;?></i>
                                                            </span>
                                                        </div>
                                                        <?php 
                                                    }
                                                    ?>

                                                    <div>
                                                        <div class="heart- <?php echo $bounce;?>" id="<?php echo $i; ?>">
                                                            <i class="far fa-heart product-heart animated" id="2"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <a href="<?php echo base_url();?>product_details_page/<?php echo $pro_id;?>/<?php echo $product_details->id;?>">
                                            <div class="product-img">
                                                <?php 
                                                if($pro_id == 'cb2') { $pro_id = 'CB2'; }
                                                if($pro_id == 'pier-1') { $pro_id = 'Pier-1'; }
                                                ?> 
                                                <?php 
                                                if($product_details->main_product_images == '')
                                                {
                                                    $new_images = front_images_url().'coming_soon.jpg'; 
                                                }
                                                else
                                                {
                                                    $new_images = "../".$pro_id."/main_product_images/".$product_details->main_product_images;
                                                }
                                                ?>
                                                <img src="<?php echo $new_images;?>" class="product-img-a">
                                                 
                                            </div>
                                            </a>
                                            <div class="product-bottom-layer">
                                                <div class="d-flex justify-content-between flex-wrap">
                                                    <div class="site">
                                                        <?php echo ucfirst($pro_id);?>
                                                    </div>
                                                    <div class="price d-flex flex-nowrap">
                                                        <?php 
                                                        if($was_price > $price)
                                                        {
                                                            ?>
                                                            <p class="price-discount">$<?php echo number_format($price); ?></p>
                                                            <p class="simple-price-over">$<?php echo number_format($was_price);?></p>
                                                            <?php 
                                                        }
                                                        else
                                                        {
                                                            ?>
                                                            <p>$<?php echo number_format($product_details->price);?></p>
                                                            <?php
                                                        }
                                                        ?> 
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="product-name">
                                            <p><?php echo $product_details->product_name;?></p>
                                        </div>
                                    </div>
                                    <?php 
                                    $i++;
                                }
                            }
                            else
                            {
                                echo '<div class="product-show" style="margin-left: 30%;margin-top: 20%;"><div class="product-top-layer"><p>Products not available</p></div></div>';
                            }
                            ?>
                        </div>
                    </div>
                </div><br>
                <?php 
                /*if(!empty($result))
                {
                    ?>
                     
                        <div class="row">
                            <div class="col-md-5">
                                <div class="pagination" id="php-pagination">
                                    <a id="first" onclick="pagination('first');" href="javascript:void(0)" class="active">First</a>
                                    <a id="prev" onclick="pagination('prev');" href="javascript:void(0)">← Prev </a>
                                    <a id="next" onclick="pagination('next');" href="javascript:void(0)"> Next →  </a>  
                                    <a id="last" onclick="pagination('last');" href="javascript:void(0)" class="active">Last</a>
                                </div>  
                                <div class="pagination" id="ajax-pagination" style="display: none;">
                                    <a onclick="previous_pagination()"> ← Prev </a>
                                    <a onclick="next_pagination()"> Next →  </a>
                                </div>  
                            </div>    
                            <div class="col-md-7" style="text-align: right;">
                                Total number of records <?php echo $total_records;?>
                            </div>
                        </div>  
                            
                    <?php 

                }*/
                ?>
                <div class="ajax-load text-center" style="display: none;">
                    <p>
                        <img src="<?php echo front_images_url();?>loader.gif">Loading More products
                    </p>
                </div>
                <a href="javascript:" id="return-to-top"><i class="fa fa-chevron-up" aria-hidden="true"></i></a>
                <input type="hidden" class="hidden_pagination" value="0">
            </div>
        </section><br><br>
        <?php 
        $price    = $this->input->get('price');
        $prices   = explode('-', $price);  
        $from     = $prices[0];
        $to       = $prices[1];
        if($from!=0)
        {
            $from = $from;
        }
        else
        {
            $from = 0;
        }
        if($to!='')
        {
            $to = $to;
        }
        else
        {
            $to = 5000;
        }

        $category    = $this->input->get('dept');
        $site_val    = $this->input->get('site'); 
        ?>
        <input type="hidden" name="from" id="from" value="<?php echo $from; ?>">
        <input type="hidden" name="to" id="to" value="<?php echo $to; ?>">
        <input type="hidden" name="check_val" id="check_val" value="<?php echo $site_val;?>">
        <input type="hidden" name="category" value="<?php echo $category;?>" id="category">
        <input type="hidden" name="tot_price" value="<?php echo $price;?>" id="tot_price">
        <input type="hidden" name="sub_pro_category" value="<?php echo $current_pro_category;?>" id="sub_pro_category">
        <input type="hidden" name="hid_sort_by" value="<?php echo $price_filter;?>" id="hid_sort_by">

        <input type="hidden" name="search_value" id="search_value" value="">

        <script src="<?php echo front_js_url();?>jquery.min.js"></script>
        <script src="<?php echo front_js_url();?>bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
        <script src="<?php echo front_js_url();?>ion.rangeSlider.min.js"></script>
        
        <script>

            function category_filter(type)
            {
                var category = type;     
                $("#category").val(category);  
                var checkedvalue = $('.filter-input:checked').map(function() {return this.value;}).get().join(',');
                if(checkedvalue!='')
                {
                    str1 = checkedvalue.replace( /,/g, "" );
                    var finals = str1.length;

                    if(finals > 0)
                    {
                        finals = "?site="+checkedvalue;
                    } 
                    else
                    {
                        finals = '?site=all';
                    }
                }
                else
                {
                    finals = '?site=all';
                }
                
                var from = $('#from').val();
                var to   = $('#to').val();
                

                if(from == 0 && to == 5000)
                {
                    var price = '';
                }
                else
                {
                    var price = "&price="+from+"-"+to;
                }

                var p_filter = $('#hid_sort_by').val();
                if(p_filter!='')
                {
                    var pricefilter = "&price_filter="+p_filter;
                }
                else
                {
                    var pricefilter = "";   
                }

                if(category!='')
                {
                    category = '&dept='+category;
                     $(".filter-input").prop("disabled",true);
                    window.location.assign("<?php echo base_url();?>products/"+finals+price+category+pricefilter);
                }
            }

            //24-11-18
            function sub_category_filter(type)
            {
                var sub_pro_category = type;     
                $("#sub_pro_category").val(sub_pro_category);  
                var checkedvalue = $('.filter-input:checked').map(function() {return this.value;}).get().join(',');
                if(checkedvalue!='')
                {
                    str1 = checkedvalue.replace( /,/g, "" );
                    var finals = str1.length;

                    if(finals > 0)
                    {
                        finals = "?site="+checkedvalue;
                    } 
                    else
                    {
                        finals = '?site=all';
                    }
                }
                else
                {
                    finals = '?site=all';
                }
                
                var from = $('#from').val();
                var to   = $('#to').val();
                var category = $('#category').val();
                if(from == 0 && to == 5000)
                {
                    var price = '';
                }
                else
                {
                    var price = "&price="+from+"-"+to;
                }

                var p_filter = $('#hid_sort_by').val();
                if(p_filter!='')
                {
                    var pricefilter = "&price_filter="+p_filter;
                }
                else
                {
                    var pricefilter = "";   
                }

                if(category!='')
                {
                    category = '&dept='+category;
                    if(sub_pro_category!='')
                    {
                        sub_pro_category = '&pro_category='+encodeURIComponent(sub_pro_category);
                         $(".filter-input").prop("disabled",true);
                        window.location.assign("<?php echo base_url();?>products/"+finals+price+category+sub_pro_category+pricefilter);                     
                    }    
                }
            }
            //24-11-18

            $(function() {

                var hearts = document.getElementsByClassName('product-heart');
                console.log(hearts.length);
                for( var i = 0; i < hearts.length; i++) {
                    hearts[i].addEventListener('click', function(e) {
                        e.preventDefault();
                        console.log(this);
                        this.classList.toggle('far');
                        this.classList.toggle('fas');
                        this.classList.toggle('heart-red');
                        this.classList.toggle('bounce');
                    })
                }

                /**
                 * Format a number as a string with commas separating the thousands.
                 * @param num - The number to be formatted (e.g. 10000)
                 * @return A string representing the formatted number (e.g. "10,000")
                 */
                var formatNumber = function(num) {
                    var array = num.toString().split('');
                    var index = -3;
                    while (array.length + index > 0) {
                        array.splice(index, 0, ',');
                        // Decrement by 4 since we just added another unit to the array.
                        index -= 4;
                    }
                    return array.join('');
                };

                $(".heart").on("click", function() {
                    $(this).toggleClass("is-active");
                });

                $(".expandable-search").on('focus', function(e) {
                    $('#logo-small').toggle('d-none') 
                })

                $(".expandable-search").on('blur', function(e) {
                    $('#logo-small').toggle('d-none')
                    $(this).val("")
                })
                $('.dropdown-menu a.dropdown-toggle').on('click', function(e) 
                {
                    if (!$(this).next().hasClass('show')) {
                    $(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
                    }
                      var $subMenu = $(this).next(".dropdown-menu");
                      $subMenu.toggleClass('show');

                      $(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function(e) {
                        $('.dropdown-submenu .show').removeClass("show");
                      });

                      return false;
                });
                $("#call-filters").on('click', function(e) {
                    //alert("ASd");
                    $(".filter-fun").toggleClass('d-none');
                    $(".filter-fun").toggleClass('flipInX');
                    //$(".filter-fun").toggleClass('flipOutX');

                })

                var from = '<?php echo $from; ?>';
                var to   = '<?php echo $to; ?>';

                $("#range").ionRangeSlider({
                    hide_min_max: true,
                    keyboard: true,
                    min: 0,
                    max: 5000,
                    from: from,
                    to: to,
                    type: 'double',
                    step: 1,
                    prefix: "$",
                });

                $("#range").on("change", function () 
                {
                    var $this = $(this),
                        from = $this.data("from"),
                        to = $this.data("to");
                         
                    
                    $('#from').val(from);
                    $('#to').val(to);

                     
                    var checkedvalue = $('.filter-input:checked').map(function() {return this.value;}).get().join(',');

                    str1 = checkedvalue.replace( /,/g, "" );
                    var final = str1.length;

                    if(final > 0)
                    {
                        final = checkedvalue;
                    } 
                    else
                    {
                        final = 'all';
                    }

                    var from = $('#from').val();
                    var to   = $('#to').val();
                    var category = $("#category").val();

                    var p_filter = $('#hid_sort_by').val();
                    
                    if(p_filter!='')
                    {
                        var pricefilter = "&price_filter="+p_filter;
                    }
                    else
                    {
                        var pricefilter = "";   
                    }

                    if(category!='')
                    {
                        category = "&dept="+category;
                    }
                    else
                    {
                        category = "";
                    }
                         
                    $(".filter-input").prop("disabled",true);
                    window.location.assign("<?php echo base_url();?>products/?site="+final+"&price="+from+"-"+to+category+pricefilter);
                });
            });
        </script>

        <script type='text/javascript'>

            function digits_count(n) {
              var count = 0;
              if (n >= 1) ++count;

              while (n / 10 >= 1) {
                n /= 10;
                ++count;
              }

              return count;
            }

            $(document).ready(function()
            {    
                $("#new_table_yui_grid").click(function()
                {
                    $('#php-pagination').hide();
                    $('#ajax-pagination').show();

                    var per_row         = $('.hidden_pagination').val();
                    var product_name    = jQuery('#search_text').val();

                    var pagno           = per_row;

                    $("#search_value").val(product_name);

                    $.ajax({
                        url: '<?php echo base_url();?>load_all_Record', 
                        type: "POST",             
                        data: 'pagno='+pagno+'&product_name='+encodeURI(product_name)+'&type=filter', 
                        dataType: 'json',     
                        beforeSend: function()
                        {
                            $('.ajax-load1').show();
                            $('.postsList').empty();
                        },
                        success: function(response) 
                        {
                            $('.ajax-load1').hide();
                            //$('#pagination').html(response.pagination);
                            newcreateTable(response.result,response.row,response.total_rows);  
                        }

                    });
                });

                // Create table list
                function newcreateTable(result,sno, total_rows)
                {
                    sno = Number(sno);
                    total_rows = Number(total_rows);
                    var tot_records = total_rows+' Results';
                    $('#result-num').html(tot_records);
                    $('.postsList').empty();
                       
                    if(result == '')
                    { 
                        var tr = "<center>No matching Records found</center>";
                        $('.postsList').append(tr);
                    }
                    else
                    {
                        for(index in result)
                        {
                            var id               = result[index].id;
                            var product_name     = result[index].product_name;
                            var images           = result[index].images;
                            var model_code       = result[index].model_code;
                            var color            = result[index].color;
                            var product_url      = result[index].product_url;
                            var product_category = result[index].product_category;
                            var price            = result[index].price;
                            var main_product_images = result[index].main_product_images;
                            var was_price        = result[index].was_price;
                            sno+=1;

                            if( product_url.indexOf('cb2') >= 0){
                              var pro_id = 'CB2';
                              var sitename = 'cb2';
                            }
                             if( product_url.indexOf('pier1') >= 0){
                              var pro_id = 'Pier-1';
                                var sitename = 'pier1';
                            }
                             if( product_url.indexOf('potterybarn') >= 0){
                              var pro_id = 'potterybarn';
                              var sitename = 'potterybarn';
                            }

                            
                            images ='<a href="<?php echo base_url();?>product_details_page/'+sitename+'/'+id+'"><div class="product-img"><img src="../'+pro_id+'/main_product_images/'+main_product_images+'" class="product-img-a"></div></a>';

                            if(was_price > price)
                            {
                                var price_tag = '<div><span class="price-tag"><i class="fas fa-dollar-sign padd"></i></span></div>';

                                var price     = '<p class="price-discount">$'+price+'</p>';
                                var was_price = '<p class="simple-price-over">$'+was_price+'</p>';
                            }
                            else
                            {
                                var price_tag = '';
                                var price     = '<p>$'+price+'</p>';
                            }


                            var tr='<div class="product-show"><div class="product-top-layer"><div class="micro-intra"><div class="d-flex justify-content-between flex-wrap">'+price_tag+'<div><div class="heart- bounce" id="'+sno+'"><i class="far fa-heart product-heart animated" id="2"></i></div></div></div></div>'+images+'<div class="product-bottom-layer"><div class="d-flex justify-content-between flex-wrap"><div class="site">'+sitename+'</div><div class="price d-flex flex-nowrap">'+price+'</div></div></div></div><div class="product-name"><p>'+product_name+'</p></div></div>';
                            $('.postsList').append(tr);
                        }
                    }  
                }
            });
        </script>

        <script type="text/javascript">
            function get_price_filter()
            {
                var sort_by     = $('#sort_by').val();
                var checkedvalue= $('.filter-input:checked').map(function() {return this.value;}).get().join(',');
                var str1        = checkedvalue.replace( /,/g, "" );
                var final       = str1.length; 
                var from        = $('#from').val();
                var to          = $('#to').val();

                $("#hid_sort_by").val(sort_by); 

                if(final > 0)
                {
                    final = checkedvalue;
                } 
                else
                {
                    final = 'all';
                }

                var category = $("#category").val();

                var sub_pro_category = $("#sub_pro_category").val();
                if(sub_pro_category!='')
                {
                    var sub_category = '&pro_category='+sub_pro_category;
                }
                else
                {
                    var sub_category = '';
                }


                if(from !=0)
                {
                    if(category!='')
                    {
                        category = "&dept="+category; 

                        $(".filter-input").prop("disabled",true);
                        if(sort_by != 0)
                        {
                           window.location.assign("<?php echo base_url();?>products/?site="+final+"&price="+from+"-"+to+category+"&price_filter="+sort_by+sub_category);  
                        }
                       
                    }
                    else
                    {
                        $(".filter-input").prop("disabled",true);
                        if(sort_by != 0)
                        {
                            window.location.assign("<?php echo base_url();?>products/?site="+final+"&price="+from+"-"+to+"&price_filter="+sort_by+sub_category);
                        }    
                    }
                } 
                else
                {
                    if(category!='')
                    {   
                        if(sort_by != 0)
                        {
                            category = "&dept="+category; 
                            sort_by  = "&price_filter="+sort_by;
                            $(".filter-input").prop("disabled",true);
                            window.location.assign("<?php echo base_url();?>products/?site="+final+category+sort_by+sub_category);       
                        }    
                    }
                    else
                    {
                        if(sort_by != 0)
                        {   
                            sort_by  = "&price_filter="+sort_by;
                            $(".filter-input").prop("disabled",true);
                            window.location.assign("<?php echo base_url();?>products/?site="+final+sort_by+sub_category);  
                        }        
                    }
                }    
            }

            function get_filter()
            {
                var checkedvalue = $('.filter-input:checked').map(function() {return this.value;}).get().join(',');

                str1 = checkedvalue.replace( /,/g, "" );
                var final = str1.length; 
                $("#check_val").val(checkedvalue);
                var from = $('#from').val();
                var to   = $('#to').val();
                 
                if(final > 0)
                {
                    final = checkedvalue;
                } 
                else
                {
                    final = 'all';
                }

                var category = $("#category").val();


                var p_filter = $('#hid_sort_by').val();
                if(p_filter!='')
                {
                    var pricefilter = "&price_filter="+p_filter;
                }
                else
                {
                    var pricefilter = '';
                }

                if(from !=0)
                {
                    if(category!='')
                    {
                        category = "&dept="+category; 
                         $(".filter-input").prop("disabled",true);
                        window.location.assign("<?php echo base_url();?>products/?site="+final+"&price="+from+"-"+to+category+pricefilter);
                    }
                    else
                    {
                         $(".filter-input").prop("disabled",true);
                        window.location.assign("<?php echo base_url();?>products/?site="+final+"&price="+from+"-"+to+pricefilter);
                    }
                } 
                else
                {
                    if(category!='')
                    {   
                        category = "&dept="+category; 
                         $(".filter-input").prop("disabled",true);
                        window.location.assign("<?php echo base_url();?>products/?site="+final+category+pricefilter);       
                    }
                    else
                    {
                        $(".filter-input").prop("disabled",true);
                        window.location.assign("<?php echo base_url();?>products/?site="+final+pricefilter);          
                    }
                }   
            }

        </script>

        <script type="text/javascript">
 
            $('#return-to-top').click(function() {      // When arrow is clicked
                $('body,html').animate({
                    scrollTop : 0                       // Scroll to top of body
                }, 500);
            });

            $(document.body).on('touchmove', onScroll); // for mobile
            $(window).on('scroll', onScroll); 
                var page = 0;
                function onScroll()
                {   
                    //if($(window).scrollTop() + $(window).height() >= $(document).height()) 
                    if($(window).scrollTop() + window.innerHeight >= document.body.scrollHeight)
                    {
                        page++;
                        loadMoreData(page);
                    }

                    if ($(this).scrollTop() >= 250) {         
                        $('#return-to-top').fadeIn(200);     
                    } else {
                        $('#return-to-top').fadeOut(200);
                    }
                }    

            function loadMoreData(page)
            {   
                var chk_val  = $("#check_val").val();
                var category = $("#category").val();
                var price    = $("#tot_price").val();
                var sort_by  = $("#hid_sort_by").val();
                var sub_pro_category = $("#sub_pro_category").val();

                var search_value = $("#search_value").val();

                if(search_value !='')
                {
                    var search_res = "&search_value="+search_value;
                }
                else
                {
                    search_res = '';
                }

                $.ajax(
                {
                    url: '<?php echo base_url();?>products?page='+page+"&site="+chk_val+"&dept="+category+"&price="+price+"&price_filter="+sort_by+"&pro_category="+sub_pro_category+search_res,
                    type: "get",
                    beforeSend: function()
                    {
                        $('.ajax-load').show();
                    }
                })
                .done(function(data)
                {    
                    if(data==0)
                    {
                        $('.ajax-load').show();
                        $('.ajax-load').html("No more products found");
                        return;
                    }
                    else
                    {
                        $('.ajax-load').hide();
                        $("#post-data").append(data);    
                    }
                    
                })
                .fail(function(jqXHR, ajaxOptions, thrownError)
                {
                    alert('server not responding...');
                });
            }
        </script>
        <script type="text/javascript">
            var vglnk = {key: '7c7cd49fe471830c75c9967f05d5f292'};
            (function(d, t) {
                var s = d.createElement(t);
                    s.type = 'text/javascript';
                    s.async = true;
                    s.src = '//cdn.viglink.com/api/vglnk.js';
                var r = d.getElementsByTagName(t)[0];
                    r.parentNode.insertBefore(s, r);
            }(document, 'script'));
        </script>
    </body>
</html>
