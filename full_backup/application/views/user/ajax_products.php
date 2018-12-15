<?php 
if(!empty($products))
{	
	$i=16;
	foreach($products as $product_details)
	{ 
		 
		if($product_details->main_product_images!=''){
            $new_images = "../".$product_details->site_name."/main_product_images/".$product_details->main_product_images;
        }   
        else
        {
            $new_images = front_images_url().'coming_soon.jpg';
        } 
        $bounce = 'bounce';

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
        ?>
                                 
        <div class="product-show">
            <div class="product-top-layer">
                <div class="micro-intra">
                    <div class="d-flex justify-content-between flex-wrap">
                        <?php
                        if($was_price > $price)
                        {
                            ?>
                            <div>
                                <span class="price-tag">
                                    <i class="fas fa-dollar-sign padd"></i>
                                </span>
                            </div>
                            <?php 
                        }
                        ?>

                        <div>
                            <div id="<?php echo $i; ?>" class="heart- <?php echo $bounce;?>" >
                                <i id="2" class="far fa-heart product-heart animated" ></i>
                            </div>
                        </div>
                    </div>
                </div>
                 
                    <div class="product-img">
                        <img src="<?php echo $new_images;?>" class="product-img-a" onclick="popup_box(<?php echo $product_details->id;  ?>);">
                    </div>
                
                <div class="product-bottom-layer">
                    <div class="d-flex justify-content-between flex-wrap">
                        <div class="site">
                            <?php echo $product_details->site_name;?>
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

        <div id="product-modal<?php echo $product_details->id; ?>" class="product-modal" style="display:none;">
            <button data-izimodal-close class="modal-close">
                <i class="fas fa-times"></i>
            </button>
            <div class="row outter-container">
                <div class="col-md-7 product-img-modal">
                    <div class="row">
                        <div class="active-img col-md-12">
                            <img src="<?php echo $new_images;?>" alt="">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-around">
                                <?php  
                                if(strpos($product_details->product_images, ',') !== false ) 
                                {   
                                    $thumb_images   = explode(',', $product_details->product_images);
                                    foreach ($thumb_images as $thumbs) 
                                    {
                                        ?>
                                        <div class="sub-img">
                                            <img src="../<?php echo $product_details->site_name;?>/product_images/<?php echo $thumbs;?>" alt="">
                                        </div>
                                        <?php
                                    }

                                } 
                                else
                                {
                                    ?>
                                    <div class="sub-img">
                                        <img src="../<?php echo $product_details->site_name;?>/product_images/<?php echo $product_details->product_images;?>" alt="">
                                    </div>
                                    <?php 
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                if(strpos($product_details->product_description, 'Show More') !== false) 
                { 
                    $pro_description = explode('Show More', $product_details->product_description);
                    $product_desc    = $pro_description[0];
                }
                else
                {
                    $product_desc    = $product_details->product_description;
                }
                ?>
                <div class="col-md-5">
                    <div class="product-name-long">
                        <h4><?php echo $product_details->product_name; ?></h4>
                    </div>
                    <div class="product-site-long d-flex justify-content-between">
                        <p>
                            from <span class="site-name-long"><?php echo $product_details->site_name; ?></span>
                        </p>
                        <a href="<?php echo $product_details->product_url;?>" target="_blank" class="site-link">Buy From Seller</a>
                    </div>
                    <div class="product-prize-long">
                        <p>
                            <i class="fas fa-dollar-sign"></i>
                           <?php echo $product_details->price; ?>
                        </p>
                    </div>
                    <div class="product-long-desc">
                        <p><?php echo nl2br($product_desc); ?></p>
                    </div>
                    <div class="product-specs">
                        <ul>
                            <?php echo html_entity_decode(nl2br($product_details->product_feature)); ?>
                        </ul>
                    </div>
                    <?php 
                    if($product_details->product_diemension!='' || $product_details->product_diemension!=NULL)
                    {   
                        ?>
                        <div class="product-dimensions d-flex justify-content-center">
                            <div class="dims">
                                <div class="dims-head">
                                    <h5>Dimensions</h5>
                                </div>
                                <?php 
                                if($product_details->site_name == 'CB2') 
                                {
                                    if(strpos($product_details->product_diemension, ']}') !== false) 
                                    {
                                        ?>
                                        <ul class="product-list-details">
                                        <?php
                                         
                                        $product_diemension = json_decode($product_details->product_diemension,true);
                                        $product_diemension = $product_diemension;
                                        $product_diemension  = array_filter($product_diemension);
                                        foreach ($product_diemension as $product_diemension1) 
                                        {
                                            $width = nl2br($product_diemension1[0]);
                                            $depth = nl2br($product_diemension1[1]);
                                            $height= nl2br($product_diemension1[2]);
                                            ?>
                                            <li><?php echo substr(trim($width), 0, -6);?></li>
                                            <li><?php echo substr(trim($depth), 0, -6);?></li>
                                            <li><?php echo substr(trim($height), 0, -6);?></li>
                                            <?php 
                                        }
                                    }
                                    else{
                                        echo nl2br($product_details->product_diemension);
                                    }    
                                    ?>
                                    </ul>
                                    <?php
                                } 
                                else
                                {
                                    echo nl2br($product_details->product_diemension); 
                                }
                                ?>
                            </div>
                        </div>
                        <?php 
                    }
                    ?>
                </div>
            </div>
        </div>
        <!-- end 5-12-18 -->
        <?php 
        $i++;
	}	
}
else
{
	echo 0;
}
?>


