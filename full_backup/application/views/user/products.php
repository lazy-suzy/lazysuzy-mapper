<?php require 'header.php'; ?>
<section id="departments">
<?php require 'mobile_department.php'; ?>
<?php require 'department.php'; ?>
</section>
            
<section id="products">
    <div class="container-fluid">
        <div class="row">
            <?php require 'filter.php'; ?>
            <div class="col-md-10 pad-0">
                <div class="ajax-load1 text-center" style="display: none; margin-top: 20%;">
                    <p>
                        <img src="<?php echo front_images_url();?>loader.gif">Loading More products
                    </p>
                </div>
                <div class="d-flex flex-wrap products-container postsList" id="post-data">
                
                    
                    <?php 
                    
                    if($products) { 
                    
                    foreach($products as $productdata) {
                    if($productdata->main_product_images!=''){
                        $new_images = "../".$productdata->site_name."/main_product_images/".$productdata->main_product_images;
                    }   
                    else
                    {
                        $new_images = front_images_url().'coming_soon.jpg';
                    }
                    ?>
                    
                    <div class="product-show ">
                        <div class="product-top-layer">
                            <div class="micro-intra">
                                <div class="d-flex justify-content-between flex-wrap">
                                    
                                    <?php if($productdata->was_price > $productdata->price) { ?>
                                    <div><span class="price-tag"><i class="fas fa-dollar-sign padd"></i></span></div>
                                    <?php } ?>
                                    
                                    <div><div class="heart-" id="1"><i class="far fa-heart product-heart animated" id="2"></i></div></div>
                                </div>
                            </div>
                            <div class="product-img">
                                <img data-target="<?php echo $productdata->id;?>" src="<?php echo $new_images;?>" alt="<?php echo $productdata->main_product_images; ?>" class="product-img-a" onclick="popup_box(<?php echo $productdata->id;  ?>);">
                            </div>
                            
                             <div class="product-bottom-layer">
                                <div class="d-flex justify-content-between">
                                    <div class="site"><?php echo $productdata->site_name;?></div>
                                    <div class="price  d-flex flex-nowrap">
                                        <?php if($productdata->was_price > $productdata->price) { ?>
                                        <p class="price-discount"><?php echo $this->currency_symbol.number_format($productdata->price); ?></p>
                                        <p class="simple-price-over"><?php echo $this->currency_symbol.number_format($productdata->was_price); ?></p>
                                        <?php } else { ?>
                                        <p><?php echo $this->currency_symbol.number_format($productdata->price); ?></p>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                       
                        <div class="product-name">
                            <p><?php echo $productdata->product_name; ?></p>
                        </div>
                    </div>
                    
                    
                    <!-- New code for product details popup script 5-12-18 -->
                    <?php  
                    /*if(strpos($productdata->product_images, ',') !== false ) 
                    {
                        $thumb_images   = explode(',', $productdata->product_images);
                        $thumb1         = $thumb_images[0];
                        $thumb2         = $thumb_images[1];
                        $thumb3         = $thumb_images[2];
                    }
                    else
                    {
                       $thumb1         =  $productdata->product_images;
                    }*/
                    ?>
                    <div id="product-modal<?php echo $productdata->id; ?>" class="product-modal" style="display:none;">
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
                                            if(strpos($productdata->product_images, ',') !== false ) 
                                            {   
                                                $thumb_images   = explode(',', $productdata->product_images);
                                                foreach ($thumb_images as $thumbs) 
                                                {
                                                    ?>
                                                    <div class="sub-img">
                                                        <img src="../<?php echo $productdata->site_name;?>/product_images/<?php echo $thumbs;?>" alt="">
                                                    </div>
                                                    <?php
                                                }
                                            } 
                                            else
                                            {
                                                ?>
                                                <div class="sub-img">
                                                    <img src="../<?php echo $productdata->site_name;?>/product_images/<?php echo $productdata->product_images;?>" alt="">
                                                </div>
                                                <?php 
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="product-name-long">
                                    <h4><?php echo $productdata->product_name; ?></h4>
                                </div>
                                <div class="product-site-long d-flex justify-content-between">
                                    <p>
                                        from <span class="site-name-long"><?php echo $productdata->site_name; ?></span>
                                    </p>
                                    <a href="<?php echo $productdata->product_url;?>" target="_blank" class="site-link">Buy From Seller</a>
                                </div>
                                <div class="product-prize-long">
                                    <p>
                                        <i class="fas fa-dollar-sign"></i>
                                        <?php echo $productdata->price; ?>
                                    </p>
                                </div>
                                <?php 
                                if(strpos($productdata->product_description, 'Show More') !== false ) 
                                { 
                                    $pro_description = explode('Show More', $productdata->product_description);
                                    $product_desc    = $pro_description[0];
                                }
                                else
                                {
                                    $product_desc    = $productdata->product_description;
                                }
                                ?>
                                <div class="product-long-desc">
                                    <p><?php echo nl2br($product_desc); ?></p>
                                </div>
                                <div class="product-specs">
                                    <ul>
                                        <?php echo html_entity_decode(nl2br($productdata->product_feature)); ?>
                                    </ul>
                                </div>
                                <?php 

                                if($productdata->product_diemension!='' || $productdata->product_diemension!=NULL)
                                {   
                                    
                                    ?>
                                    <div class="product-dimensions d-flex justify-content-center">
                                        <div class="dims">
                                            <div class="dims-head">
                                                <h5>Dimensions</h5>
                                            </div>
                                            <?php 
                                            if($productdata->site_name == 'CB2') 
                                            {
                                                if(strpos($productdata->product_diemension, ']}') !== false) 
                                                {
                                                    ?>
                                                    <ul class="product-list-details">
                                                    <?php
                                                     
                                                    $product_diemension = json_decode($productdata->product_diemension,true);
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
                                                    echo nl2br($productdata->product_diemension);
                                                }    
                                                ?>
                                                </ul>
                                                <?php
                                            } 
                                            else
                                            {
                                                echo nl2br($productdata->product_diemension); 
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
                    
                    <?php } }
                    else
                            {
                                echo '<div class="product-show" style="margin-left: 30%;margin-top: 20%;"><div class="product-top-layer"><p>Products not available</p></div></div>';
                            }
                     ?>
                    
                </div>
            </div>
        </div>
        <div class="ajax-load text-center" style="display: none;">
            <p>
                <img src="<?php echo front_images_url();?>loader.gif">Loading More products
            </p>
        </div>
        <a href="javascript:" id="return-to-top"><i class="fa fa-chevron-up" aria-hidden="true"></i></a>
    </div>
</section>


<?php require 'footer.php'; ?>

<?php 
$range = $this->session->userdata('price_range');
if(!empty($range))
{
    $price_range =  explode(',', $range);    
    $from = $price_range[0];
    $to   = $price_range[1];  
}
?>      

<script type="text/javascript">


    $('.filter-input').click(function(){

        var filter_option = $(this).attr('filter-option');

         var checkedvalue = $('[filter-option='+filter_option+']:checked').map(function() {return this.value;}).get().join(',');
        //alert(checkedvalue+'<>'+filter_option); return false;
        window.location.assign("<?php echo base_url();?>filter/"+filter_option+"/"+checkedvalue); 

    });

    function get_filter(session_name)
    {
        alert(session_name);

        if(session_name == 'site_name')
        {
            var checkedvalue = $('.filter-input:checked').map(function() {return this.value;}).get().join(',');
            window.location.assign("<?php echo base_url();?>filter/site_name/"+checkedvalue); 
        }
        else if(session_name == 'product_sub_category')
        {
            var checkedvalue1 = $('.filter1-input:checked').map(function() {return this.value;}).get().join(',');
            window.location.assign("<?php echo base_url();?>filter/product_sub_category/"+checkedvalue1); 
        }
    }


    function get_sub_category_filter()
    {
        var checkedvalue1 = $('.filter1-input:checked').map(function() {return this.value;}).get().join(',');
        window.location.assign("<?php echo base_url();?>filter/product_sub_category/"+checkedvalue1); 
    }


    var from_price = '<?php echo $from; ?>';
    var to_price   = '<?php echo $to; ?>';
    if(from_price == '')
    {
        from_price = 0;
    }
    if(to_price == '')
    {
        to_price = 5000;
    }
    $("#range").ionRangeSlider({
        hide_min_max: true,
        keyboard: true,
        min: 0,
        max: 5000,
        from: from_price,
        to: to_price,
        type: 'double',
        step: 1,
        prefix: "$",
    });

    $("#range").on("change", function () 
    {
        var $this = $(this),
            from  = $this.data("from"),
            to    = $this.data("to");
             
        $(".filter-input").prop("disabled",true);
        window.location.assign("<?php echo base_url();?>filter/price_range/"+from+","+to);
    });
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

        if ($(this).scrollTop() >= 250) 
        {         
            $('#return-to-top').fadeIn(200);     
        } 
        else 
        {
            $('#return-to-top').fadeOut(200);
        }
    }    

    function loadMoreData(page)
    {   
         
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
            url: '<?php echo base_url();?>products?page='+page+search_res,
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
            $('.ajax-load').show();
        });
    }

    $(document).ready(function()
    {    
        $("#new_table_yui_grid").click(function()
        {
            if($('#search_text').val()!='')
            {
                $('#php-pagination').hide();
                $('#ajax-pagination').show();

                var per_row         = 0;
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
            }
            /*else
            {
               $('.ht-balance').css('border-color','red'); 
            }*/    
        });

        //New script for line breaking 11-12-18
        String.prototype.nl2br = function()
        {
            return this.replace(/\n/g, "<br />");
        }

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
                    var id                  = result[index].id;
                    var product_name        = result[index].product_name;
                    var images              = result[index].images;
                    var model_code          = result[index].model_code;
                    var color               = result[index].color;
                    var product_url         = result[index].product_url;
                    var product_category    = result[index].product_category;
                    var price               = result[index].price;
                    var main_product_images = result[index].main_product_images;
                    var was_price           = result[index].was_price;
                    var site_name           = result[index].site_name;
                    var product_description = result[index].product_description.nl2br();
                    var product_feature     = result[index].product_feature;
                    var product_images      = result[index].product_images;
                    var product_diemension  = result[index].product_diemension;
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

                    var thumbs = '';
                    var thumb_images = product_images.split(",");
                    for (i=0;i<thumb_images.length;i++)
                    {
                        thumbs += '<img src="../'+pro_id+'/product_images/'+thumb_images[i]+'" alt=""></div><div class="sub-img">';
                    }
                    
                    images ='<div class="product-img"><img src="../'+pro_id+'/main_product_images/'+main_product_images+'" class="product-img-a" onclick="popup_box('+id+');"></div>';

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
                        var was_price = '';
                    }
                    var product_feature = $('<textarea />').html(product_feature).text();
                    var product_feature = product_feature.nl2br();

                    //var dimensions = jQuery.parseJSON(product_diemension);
                    //var diements = product_diemension;
                    var product_diemension = $('<textarea />').html(product_diemension).text();
                    var diements = product_diemension.nl2br();

                    if(diements!='')
                    {
                        
                        diements = '<div class="product-dimensions d-flex justify-content-center"><div class="dims"><div class="dims-head"><h5>Dimensions</h5></div><ul class="product-list-details">'+diements+'</ul></div></div>';
                    } 
                    else
                    {
                        diements = '';
                    }

                    var tr='<div class="product-show"><div class="product-top-layer"><div class="micro-intra"><div class="d-flex justify-content-between flex-wrap">'+price_tag+'<div><div class="heart- bounce" id="'+sno+'"><i class="far fa-heart product-heart animated" id="2"></i></div></div></div></div>'+images+'<div class="product-bottom-layer"><div class="d-flex justify-content-between flex-wrap"><div class="site">'+sitename+'</div><div class="price d-flex flex-nowrap">'+price+' '+was_price+'</div></div></div></div><div class="product-name"><p>'+product_name+'</p></div></div><div id="product-modal'+id+'" class="product-modal" style="display:none;"><button data-izimodal-close class="modal-close"><i class="fas fa-times"></i></button><div class="row outter-container"><div class="col-md-7 product-img-modal"><div class="row"><div class="active-img col-md-12"><img src="../'+pro_id+'/main_product_images/'+main_product_images+'" alt=""></div></div><div class="row"><div class="col-md-12"><div class="d-flex justify-content-around"><div class="sub-img">'+thumbs+'</div></div></div></div></div><div class="col-md-5"><div class="product-name-long"><h4>'+product_name+'</h4></div><div class="product-site-long d-flex justify-content-between"><p> from <span class="site-name-long">'+sitename+'</span></p><a href="'+product_url+'" class="site-link">Buy From Seller</a></div><div class="product-prize-long"><p><i class="fas fa-dollar-sign"></i>&nbsp;'+result[index].price+'</p></div><div class="product-long-desc"><p>'+product_description+'</p></div><div class="product-specs"><ul>'+product_feature+'</ul></div>'+diements+'</div></div></div>';
                    $('.postsList').append(tr);
                }
            }  
        }
    });
</script>

<!-- <script type="text/javascript">
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
 -->