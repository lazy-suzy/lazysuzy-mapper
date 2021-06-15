<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $product_name;?> | <?php echo $site_name;?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="<?php echo front_css_url();?>bootstrap.min.css" >
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
        <link rel="stylesheet" href="<?php echo front_css_url();?>normalize.css">
        <link rel="stylesheet" href="<?php echo front_css_url();?>product-page.css">
    </head>
    <body>
        <section id="navbar">
            <nav class="navbar navbar-expand-sm navbar-light bg-light">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#nav-content" aria-controls="nav-content" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <!-- Brand -->
                <a class="navbar-brand" href="#">Logo</a>
                <a href=""><i class="fas fa-user my-golden user-small-view"></i></a>
                <!-- Links -->
                <div class="collapse navbar-collapse justify-content-end" id="nav-content">
                    <ul class="navbar-nav ">
                        <li class="nav-item">
                            <a class="nav-link" href="#">Link 1</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Link 2</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Link 3</a>
                        </li>
                    </ul>
            </nav>
        </section>
        <section id="product"> 
        	<div class="container-full"><br>
                <div class="row" style="margin-left:50%"><center><button class="btn btn-alert"><?php echo strtoupper($site_name);?></button></center></div>
        		<div class="row" style="margin: 0">
        			<div class="col-md-12">
        				<div class="flex">
        					<i class="fas fa-angle-left back-arrow"></i>
        					<nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?php echo base_url();?>">Home</a></li>
                                    <li class="breadcrumb-item"><a href="<?php echo base_url();?>products">Products</a></li>
                                    <li class="breadcrumb-item active" aria-current="page"><?php echo ucfirst($product_category);?></li>
                                    <li class="breadcrumb-item "><a href="#"><?php echo ucfirst($product_name);?></li></a>
                                </ol>
                            </nav>
        				</div>
        			</div>
        		</div>
                
        		<div class="row" style="margin: 0">
        			<div class="col-md-7">
                        <p><b><?php echo ucfirst($product_name);?></b></p>
                        <div id="demo" class="carousel slide" data-ride="carousel">
                            <!-- Indicators -->
                            <?php 
                            if(strpos($slider_images, ',') !== false ) 
                            {
                                $thumb_images = explode(',', $slider_images);
                            }
                            else
                            {
                                $thumb_images = $slider_images;   
                            }

                            if(is_array($thumb_images))
                            {    
                                ?>
                                 
                                <ul class="carousel-indicators">
                                    <?php 
                                    $ii=0;
                                    foreach ($thumb_images as $new_thumb_images) 
                                    {
                                        ?>
                                        <li data-target="#demo" data-slide-to="<?php echo $ii;?>" class=""></li>
                                        <?php
                                        $ii++;
                                    } 
                                    ?>    
                                </ul>
                                <div class="carousel-inner">
                                    <?php 
                                    $i=1;
                                    foreach ($thumb_images as $new_thumb_images) 
                                    {
                                        if($i == 1)
                                        {
                                            $active = 'active';
                                        }
                                        else
                                        {
                                            $active = '';   
                                        }
                                        ?>
                                        <!-- The slideshow -->
                                        <div class="carousel-item <?php echo $active;?>">
                                          <img src="<?php echo base_url();?><?php echo $site_name;?>/product_images/<?php echo $new_thumb_images;?>" alt="<?php echo $new_thumb_images;?>">
                                        </div>
                                        <?php
                                        $i++;
                                    } 
                                    ?>
                                </div>
                                <!-- Left and right controls -->
                                <a class="carousel-control-prev" href="#demo" data-slide="prev">
                                    <span class="carousel-control-prev-icon"></span>
                                </a>
                                <a class="carousel-control-next" href="#demo" data-slide="next">
                                    <span class="carousel-control-next-icon"></span>
                                </a>
                                </div>
                                <?php 
                            }
                            else
                            {
                                ?>
                                <img src="<?php echo base_url();?><?php echo $site_name;?>/product_images/<?php echo $thumb_images;?>" alt="<?php echo $thumb_images;?>">
                                </div>
                                <?php 
                            }
                            ?>
                             
                          
                        
                        <div>
                            <p><b>Product Description</b></p> 
                            <?php echo $product_description;?>
                        </div>
        			</div>
        			<div class="col-md-5">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-12 text-right">Price : <?php echo $price;?></div>
                                <div class="col-md-12 text-right">Sku   : <?php echo $product_sku;?></div>  
                                <div class="col-md-12 text-right" style="margin-top:30px;"><a href="<?php echo base_url();?>" class="btn btn-info" role="button">Request a quote</a></div>    
                            </div>

                            <?php if($product_color!='')
                           /* {
                                ?>
                                <div class="row" style="margin-top:20%;">
                                    <div class="col-md-12" style="margin-bottom: 20px;"><b>Select Fabric</b></div>
                                        
                                        ?>
                                        <div class="col-md-4"><?php echo $product_diemension1[0];?></div>
                                        <div class="col-md-4"><?php echo $product_diemension1[1];?></div>
                                        <div class="col-md-4"><?php echo $product_diemension1[2];?></div>
                                        <?php 
                                     
                                    ?>   
                                </div>
                                <?php
                            }*/     
                            ?>

                            <div class="row" style="margin-top:20%;">
                                <div class="col-md-12" style="margin-bottom: 20px;"><b>Over all dimensions</b></div>
                                <?php 
                                $product_diemension     = json_decode($product_diemension,true);
                                $product_diemension     = array_filter($product_diemension);
                                foreach ($product_diemension as $product_diemension1) 
                                {
                                    ?>
                                    <div class="col-md-4"><?php echo $product_diemension1[0];?></div>
                                    <div class="col-md-4"><?php echo $product_diemension1[1];?></div>
                                    <div class="col-md-4"><?php echo $product_diemension1[2];?></div>
                                    <?php 
                                }
                                ?>   
                            </div>
                        </div>        
                    </div>

        		</div>
        	</div>
        </section>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
        <!-- <script src="js/main.js"></script> -->
    </body>
</html>
