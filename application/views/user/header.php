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

        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/front/css/normalize.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izimodal/1.5.1/css/iziModal.min.css"/>
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/front/css/animate.min.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/front/css/ion.rangeSlider.css" />
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/front/css/ion.rangeSlider.skinModern.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/front/css/product-gal.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/front/css/modal-theme.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.theme.min.css" />


    </head>
    <body>
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
                            <a class="navbar-brand" href="<?php echo base_url(); ?>">
                                <img src="<?php echo base_url(); ?>assets/front/images/logo/png/color_logo_with_background.png" alt="LazySuzy" id="logo-small">
                            </a>
                        </div>
                    </div>
                    <div class="flex-div d-none d-sm-block">
                        <form class="card card-sm ht-balance">
                            <div class="card-body row no-gutters align-items-center">
                                <div class="col">
                                    <input class="form-control form-control-lg form-control-borderless search-big-d" type="search" required="" name="search_text" id="search_text" placeholder="Find your accent">
                                </div>
                                <div class="col-auto" id="new_table_yui_grid">
                                    <i class="fas fa-search h4 text-body"></i>
                                </div>
                                <!--end of col-->

                                <!--end of col-->
                            </div>
                        </form>
                    </div>
                    <div class="flex-div">
                        <ul class="small-nav">
                            <li class="nav-ite">
                                <a href="" class="nav-link user-ico dark small-text">
                                    About Us
                                </a>
                            </li>
                            <li class="nav-ite d-md-none d-sm-block d-xl-none d-lg-none">
                                <input type="search" required="" name="search_value" id="search_value" value="" class="expandable-search" placeholder="Find your Accent" >
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
        <?php //echo  $products_counts;?>
