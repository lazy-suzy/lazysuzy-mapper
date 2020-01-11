<?php require 'header.php';?>
<section id="departments">
<?php require 'mobile_department.php';?>
<?php require 'department.php';?>
</section>
<section id="products">
    <div class="container-fluid">
        <div class="row">
            <?php require 'filter.php';?>
            <div class="col-md-10 pad-0">
                <div class="ajax-load1 text-center" style="display: none; margin-top: 20%;">
                    <p>
                        <img src="<?php echo front_images_url(); ?>loader.gif">Loading More products
                    </p>
                </div>
                <div class="d-flex flex-wrap products-container postsList" id="filter-data"></div>
                <div class="d-flex flex-wrap products-container postsList" id="post-data">
                    <?php

                       if ($products) {
                          foreach ($products as $productdata) {
                             if ('' != $productdata->main_product_images) {
                                if ("pier1" == $productdata->site_name) {
                                   $site       = "Pier-1";
                                   $new_images = "/" . $site . "/" . $productdata->main_product_images;
                                } else {
                                   $new_images = $productdata->main_product_images;
                                }
                             } else {
                                $new_images = front_images_url() . 'coming_soon.jpg';
                             }
                          ?>

                    <div class="product-show">
                        <div class="product-top-layer">
                            <div class="micro-intra">
                                <div class="d-flex justify-content-between flex-wrap">

                                    <?php if ($productdata->was_price > $productdata->price) {?>
                                    <div><span class="price-tag"><i class="fas fa-dollar-sign padd"></i></span></div>
                                    <?php }?>

                                    <div><div class="heart-" id="1"><i class="far fa-heart product-heart animated" id="2"></i></div></div>
                                </div>
                            </div>
                            <div class="product-img">
                                <img data-target="<?php echo $productdata->id; ?>" src="<?php echo $new_images; ?>" alt="<?php echo $productdata->main_product_images; ?>" class="product-img-a lazyload" onclick="popup_box(<?php echo $productdata->id; ?>);">
                            </div>

                             <div class="product-bottom-layer">
                                <div class="d-flex justify-content-between">
                                    <div class="site"><?php echo $productdata->site_name; ?></div>
                                    <div class="price  d-flex flex-nowrap">
                                        <?php if ($productdata->was_price > $productdata->price) {?>
                                        <p class="price-discount"><?php echo $this->currency_symbol . number_format($productdata->price); ?></p>
                                        <p class="simple-price-over"><?php echo $this->currency_symbol . number_format($productdata->was_price); ?></p>
                                        <?php } else {?>
                                        <p><?php echo $this->currency_symbol . number_format($productdata->price); ?></p>
                                        <?php }?>
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
                                        <img src="<?php echo $new_images; ?>" alt="">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="owl-carousel sub-images">
                                            <?php
                                               if (strpos($productdata->product_images, ',') !== false) {
                                                        $thumb_images = explode(',', $productdata->product_images);
                                                        if ('pier1' == $productdata->site_name) {
                                                           $site_name = "Pier-1";
                                                        } else {
                                                           $site_name = $productdata->site_name;
                                                        }

                                                        foreach ($thumb_images as $thumbs) {
                                                        ?>
                                                    <div class="sub-img">
                                                        <img src="/<?php echo $site_name; ?>/<?php echo $thumbs; ?>"class="lazyload"  alt="">
                                                    </div>
                                                    <?php
                                                       }
                                                             } else {
                                                             ?>
                                                <div class="sub-img">
                                                    <img src="/<?php echo $site_name; ?>/<?php echo $productdata->product_images; ?>" class="lazyload" alt="">
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
                                    <a href="<?php echo $productdata->product_url; ?>" target="_blank" class="site-link">Buy From Seller</a>
                                </div>
                                <div class="product-prize-long">
                                    <p>
                                        <i class="fas fa-dollar-sign"></i>
                                        <?php echo $productdata->price; ?>
                                    </p>
                                </div>
                                <?php
                                   if (strpos($productdata->product_description, 'Show More') !== false) {
                                            $pro_description = explode('Show More', $productdata->product_description);
                                            $product_desc    = $pro_description[0];
                                         } else {
                                            $product_desc = $productdata->product_description;
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

                                         if ('' != $productdata->product_diemension || NULL != $productdata->product_diemension) {
                                         ?>
                                    <div class="product-dimensions d-flex justify-content-center">
                                        <div class="dims">
                                            <div class="dims-head">
                                                <h5>Dimensions</h5>
                                            </div>
                                            <?php
                                               if ('CB2' == $productdata->site_name) {
                                                           if (strpos($productdata->product_diemension, ']}') !== false) {
                                                           ?>
                                                    <ul class="product-list-details">
                                                    <?php

                                                                      $product_diemension = json_decode($productdata->product_diemension, true);
                                                                      $product_diemension = $product_diemension;
                                                                      $product_diemension = array_filter($product_diemension);
                                                                      foreach ($product_diemension as $product_diemension1) {
                                                                         $width  = nl2br($product_diemension1[0]);
                                                                         $depth  = nl2br($product_diemension1[1]);
                                                                         $height = nl2br($product_diemension1[2]);
                                                                      ?>
                                                        <li><?php echo substr(trim($width), 0, -6); ?></li>
                                                        <li><?php echo substr(trim($depth), 0, -6); ?></li>
                                                        <li><?php echo substr(trim($height), 0, -6); ?></li>
                                                        <?php
                                                           }
                                                                       } else {
                                                                          echo nl2br($productdata->product_diemension);
                                                                       }
                                                                    ?>
                                                </ul>
                                                <?php
                                                   } else {
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

                    <?php }} else {
                          echo '<div class="product-show" style="margin-left: 30%;margin-top: 20%;"><div class="product-top-layer"><p>Products not available</p></div></div>';
                       }
                    ?>

                </div>
            </div>
        </div>
        <div class="ajax-load text-center" style="display: none;">
            <p>
                <img src="<?php echo front_images_url(); ?>loader.gif">Loading More products
            </p>
        </div>
        <a href="javascript:" id="return-to-top"><i class="fa fa-chevron-up" aria-hidden="true"></i></a>
    </div>
</section>


<?php require 'footer.php';?>

<?php
   $range = $this->session->userdata('price_range');
   if (!empty($range)) {
      $price_range = explode(',', $range);
      $from        = $price_range[0];
      $to          = $price_range[1];
   }
?>

<script type="text/javascript">
  var fpage = 0;
  var minPrice = 0;
  var maxPrice = 4000;
  var filters = function (className) {
  var f = [];

  $('.' + className + ':checked').each(function() {
      f.push($(this).val());
  });

    return f;
  }

  function getFilterProducts(allBrandFilters, subCatFilters, page, minPrice, maxPrice) {
    console.log(':::: ' + minPrice + "  " + maxPrice)
    $.ajax({
            url:"http://localhost/LazySuzy/lazysuzy/filter",
            method:"POST",
            dataType : 'html',

            data: {
              brandFilters: allBrandFilters,
              subCategoryFilters: subCatFilters,
              page: fpage,
              minPrice: minPrice,
              maxPrice: maxPrice,
              ls_ids : '<?php echo $LS_IDs; ?>'
            },
            beforeSend: function() {

                $('.ajax-load').show();
            },
            success: function(data) {
              console.log('request success: ' );
            }
        }).done(function(data) {
            console.log('request done: ');
            $('#post-data').remove();
            if(data == 0) {
               // $('.ajax-load').show();
                //$('#filter-data').html("No Products Found");
                $('.ajax-load').hide();
                //show no products found div here
                return;
            }
            else {
                $('.ajax-load').hide();
                $("#filter-data").append(data);
            }
        })
        .fail(function(jqXHR, ajaxOptions, thrownError) {
          console.log('request failed' + thrownError)
            $('.ajax-load').show();
        });
  }

  $('.flt').on('click', function() {
        $("#filter-data").html("");

    var allBrandFilters = filters('brand-flt');
    var subCatFlt = filters('sub-cat-flt');
    fpage = 0;
    //check is any filter is active.
    //if there is an active filter then add class `filter-products` to #post-data
    console.log('filter length :' + allBrandFilters.length)

    $('#filter-data').addClass('filter-products');
    if (allBrandFilters.length == 0 && subCatFlt.length == 0){
      alert('removed');
      location.reload();
      $('#filter-data').removeClass('filter-products');
    }

    console.log('sending request'  + allBrandFilters);
      getFilterProducts(allBrandFilters, subCatFlt, fpage, minPrice, maxPrice);
  })


   $(".sub-images").owlCarousel({
      center: false,
      items:3,
      loop:false,
      margin:10,
      nav: true,
      responsive:{
          600:{
              items:4
          }
      }
    });
    $('.sub-img-view').on('click', function() {
      $('#active-img-view').attr('src', $(this).attr('src'))
    })
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
            if ($('#filter-data').hasClass('filter-products')) {
              var brandFilters = filters('brand-flt');
                console.log('In filter product section/ ' + fpage);
                getFilterProducts(filters('brand-flt'),filters('sub-cat-flt'), fpage++, minPrice, maxPrice);
                // add a terminating condition here.

            }
            else {
              page++;
              loadMoreData(page);
            }
            console.log(page)
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

    $("#range").ionRangeSlider({
        hide_min_max: true,
        keyboard: true,
        min: 0,
        max: 5000,
        from: 0,
        to: 5000,
        type: 'double',
        step: 1,
        prefix: "$",
        onFinish: function(data) {
          fpage = 0;
          minPrice = data.from;
          maxPrice = data.to;
          $('#filter-data').html("")
          console.log('page sent : ' + fpage);
              $('#filter-data').addClass('filter-products');

          getFilterProducts(filters('brand-flt'), filters('sub-cat-flt'), fpage, minPrice, maxPrice);
        }
      });

    function loadMoreData(page){

        var search_value = $("#search_value").val();

        if(search_value !='')
        {
            var search_res = "&search_value="+search_value;
        }
        else
        {
            search_res = '';
        }

        $.ajax({
            url: '?page='+page,
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

        //New script for line breaking 11-12-18
        String.prototype.nl2br = function()
        {
            return this.replace(/\n/g, "<br />");
        }

        // Create table list
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
