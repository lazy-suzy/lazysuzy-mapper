<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>LazySuzy</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
        
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,700" >
        <link rel="stylesheet" href="<?php echo front_css_url();?>normalize.css">
        <link rel="stylesheet" href="<?php echo front_css_url();?>product-gal.css">
        <style>
            a {
              padding-left: 5px;
              padding-right: 5px;
              margin-left: 5px;
              margin-right: 5px;
            }
            span.price-tag {
            top: 20px !important;
            left: 0px !important;
            width: 60px!important;
            }
            .pname {
    text-transform: capitalize;   
  }
        </style>
    </head>

    <body>
        <!--[if lte IE 9]>
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
                            <a class="navbar-brand" href="#">
                                <img src="<?php echo front_images_url();?>logo/png/color_logo_with_background.png" alt="LazySuzy" id="logo-small">
                            </a>
                        </div>
                    </div>
                    <div class="flex-div .d-none .d-sm-block">
                        <form class="card card-sm ht-balance">
                            <div class="card-body row no-gutters align-items-center">
                                
                                <!--end of col-->
                                <div class="col">
                                    <input class="form-control form-control-lg form-control-borderless search-big-d" type="search" name="search_text" id="search_text" placeholder="Search...">
                                </div>
                                <!--end of col-->
                                <div class="col-auto" id="click_search">
                                    <i class="fas fa-search h4 text-body"></i>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="flex-div">
                        <ul class="navbar-nav mr-auto float-right">
                            <li class="nav-ite">
                                <a href="" class="nav-link user-ico">
                                    <i class="fas fa-user"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </section>
        <br>
        <section id="products">
            <div class="container">
                <div class="row" id="postList"> 
                </div>
                <div style='margin-top: 10px;' id='pagination'></div>
            </div>
        </section>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
        
    </body>
</html>

<script type='text/javascript'>
$(document).ready(function(){
    $('#pagination').on('click','a',function(e)
    {
        e.preventDefault(); 
        var pageno = $(this).attr('data-ci-pagination-page');
        loadPagination(pageno);
    });

     loadPagination(0);

     // Load pagination
     function loadPagination(pagno)
     {  

        $.ajax({
         url: '<?php echo base_url();?>loadRecord/'+pagno,
         //data: 'pagno='+pagno,
         type: 'get',
         dataType: 'json',
         success: function(response)
         {
            $('#pagination').html(response.pagination);
            createTable(response.result,response.row);
         }
       });
     }

      // Create table list
    function createTable(result,sno)
    {
        sno = Number(sno);
        
        $('#postList').empty();
        for(index in result)
        {
          var id                = result[index].id;
          var product_name      = result[index].product_name;
          var product_sku       = result[index].product_sku;
          var model_code        = result[index].model_code;
          var color             = result[index].color;
          var product_url       = result[index].product_url;
          var product_category  = result[index].product_category;
          var price             = result[index].price;
          var images            = result[index].images;
          sno+=1;

          var tr = '<div class="col-md-3 col-4"><div class="product-show"><div class="product-top-layer"><div class="micro-intra"><div class="d-flex justify-content-between"><div><span class="price-tag">'+price+'</span></div><div><div class="heart"></div></div></div></div><div class="product-img"><img src="'+images+'" alt="images" class="product-img-a"><span class="pname">'+product_name+'</span></div></div></div></div>';
          
          $('#postList').append(tr);
 
        }
    }



     $("#click_search").click(function()
      {
        var product_name = jQuery('#search_text').val();
        var pagno = 0;

        $.ajax({
            url: '<?php echo base_url();?>loadRecord', 
            type: "POST",             
            data: 'pagno='+pagno+'&product_name='+encodeURI(product_name)+'&type=filter', 
            dataType: 'json',     
            success: function(response) 
            {
             $('#pagination').html(response.pagination);
            newcreateTable(response.result,response.row);  
            }
          });
      });

    // Create table list
    function newcreateTable(result,sno)
    {
        sno = Number(sno);
        $('#postList').empty();
        if(result == '')
        { 
          var tr = "<center>No matching Records found</center>";
          $('#postList').append(tr);
        }
        else
        {
            for(index in result)
            {
                var id                = result[index].id;
                var product_name      = result[index].product_name;
                var product_sku       = result[index].product_sku;
                var model_code        = result[index].model_code;
                var color             = result[index].color;
                var product_url       = result[index].product_url;
                var product_category  = result[index].product_category;
                var price             = result[index].price;
                var images            = result[index].images;
                sno+=1;

                var tr = '<div class="col-md-3 col-4"><div class="product-show"><div class="product-top-layer"><div class="micro-intra"><div class="d-flex justify-content-between"><div><span class="price-tag">'+price+'</span></div><div><div class="heart"></div></div></div></div><div class="product-img"><img src="'+images+'" alt="images" class="product-img-a">'+product_name+'</div></div></div></div>';
              
                $('#postList').append(tr);
            }
        }  
    }



    
});    
$(".heart").on("click", function() 
    {
        alert("hai");
        $(this).toggleClass("is-active");
    }); 
</script>
