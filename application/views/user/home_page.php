<!DOCTYPE html>
<html lang="en">
    <head>
      <title>LazySuzy</title>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">

      <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet">
      <link rel="stylesheet" href="<?php echo front_css_url(); ?>reset.css">

      <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
      <link href="https://fonts.googleapis.com/css?family=Marcellus+SC" rel="stylesheet">
      <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">

      <link rel="stylesheet" href="<?php echo front_css_url(); ?>owl.carousel.min.css">
      <link rel="stylesheet" href="<?php echo front_css_url(); ?>owl.theme.default.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izimodal/1.5.1/css/iziModal.min.css"/>
      <link rel="stylesheet" href="<?php echo front_css_url(); ?>style.css">
      <link rel="stylesheet" href="<?php echo front_css_url(); ?>log-reg-theme.css">
      <script src="<?php echo front_js_url(); ?>modernizr.js"></script>
    </head>

    <body>
        <main>
            <div class="background-img"></div>
            <div class="msg">
                <!-- <button class="msg-btn d-flex flex-wrap">
                    <span class="line"></span>
                    <span class="line"></span>
                    <span class="line"></span>
                </button> -->
            </div>
            <div class="search">
                <form  action="<?php echo base_url(); ?>products" method="post">
                <div class="search-container d-flex flex-nowrap">
                    <div class="ico">
                        <i class="fas fa-search"></i>
                    </div>

                        <div class="search-input">
                            <input type="text" required="" name="search-prod" class="search-bar-home" id="ss-search" placeholder="Find your Accent">
                        </div>
                        <div class="s-btn justify-content-end">
                            <button type="submit" name="submit" value="Search" class="search-btn-home">Search</button>
                        </div>

                </div>
                </form>
                <div class="departments-container-v2 d-flex">

                    <?php
                       foreach ($departments as $department) {
                       ?>
                      <div class="department-v2">
                          <div class="department-text-v2">
                              <a href="<?php echo base_url(); ?>department/<?php echo $department->name; ?>/<?php echo $department->id; ?>" class="dept-link">
                                  <h2 class="dept-text-v2"><?php echo $department->name; ?></h2>
                              </a>
                          </div>
                      </div>
                      <?php
                         }
                      ?>
                </div>
            </div>
            <div class="brands-container">
                <div class="text-brand">
                    <h4 id="txt-bb">Shop from your favorite brands</h4>
                </div>
                <div class="partner-logo-overall d-flex flex-wrap">

                    <!-- <div class="partner-logo">
                        <img src="<?php echo front_images_url(); ?>Ashley_G.png" class="img-responsive" style="width:100%" alt="Image">
                    </div>
                    <div class="partner-logo">
                        <img src="<?php echo front_images_url(); ?>RTG_G.png" class="img-responsive" style="width:100%" alt="Image">
                    </div> -->

                    <a href="<?php echo base_url(); ?>filter/site_name/CB2" target="_blank">
                    <div class=" h-100 align-items-center justify-content-center partner-logo">
                        <img src="<?php echo front_images_url(); ?>CB2_G.png" class="img-responsive" style="width:100%" alt="Image">
                    </div>
                    </a>

                   <!--  <div class=" h-100 align-items-center justify-content-center partner-logo">
                        <img src="<?php echo front_images_url(); ?>C&amp;B_G.png" class="img-responsive" style="width:100%" alt="Image">
                    </div> -->

                    <a href="<?php echo base_url(); ?>filter/site_name/Pier1" target="_blank">
                    <div class=" h-100 align-items-center justify-content-center partner-logo">
                        <img src="<?php echo front_images_url(); ?>P1_G.png" class="img-responsive" style="width:100%" alt="Image">
                    </div>
                    </a>

                    <a href="<?php echo base_url(); ?>filter/site_name/Potterybarn" target="_blank">
                    <div class="partner-logo">
                        <img src="<?php echo front_images_url(); ?>PB_G.png" class="img-responsive" style="width:100%" alt="Image">
                    </div>
                    </a>

                    <div class="max-width-mob">
                        <p id="many-more">& More Coming Soon</p>
                    </div>
                    <!-- <div class="partner-logo">
                        <img src="<?php echo front_images_url(); ?>RH_G.png" class="img-responsive" style="width:100%" alt="Image">
                    </div>
                    <div class=" partner-logo">
                        <img src="<?php echo front_images_url(); ?>WE_G.png" class="img-responsive" style="width:100%" alt="Image">
                    </div>
                    <div class="partner-logo more-cntnt">
                        <p>&amp;More</p>
                    </div> -->

                    <!--
                    <div class="col-sm-4">
                      <div class="well">
                       <p>Some text..</p>
                      </div>
                      <div class="well">
                       <p>Some text..</p>
                      </div>
                    </div>
                    -->
                </div>
            </div>
        </main>

        <div class="top-bar">
           <!--  <a href="#cd-nav" class="cd-nav-trigger">
               Menu
               <span class="cd-nav-icon"></span>
               <svg x="0px" y="0px" width="54px" height="54px" viewBox="0 0 54 54">
                   <circle fill="transparent" stroke="rgba(255,204,0)" stroke-width="2" cx="27" cy="27" r="20" stroke-dasharray="157 157" stroke-dashoffset="157"></circle>
               </svg>
           </a> -->
            <a href="<?php echo base_url(); ?>" class="cda cd-logo">
                <img src="<?php echo front_images_url(); ?>logo/png/dark_logo_transparent.png" alt="LazySuzy" id="company-logo">
            </a>
            <a href="<?php echo base_url(); ?>" class="cda cd-logo">
                <img src="<?php echo front_images_url(); ?>logo/png/color_logo_with_background.png" alt="LazySuzy" class="toggel-small" id="company-logo">
            </a>
            <a href="#" class="cda cd-liked">
                <i class="far fa-heart"></i>
            </a>
            <!-- <a href="#" class="cda cd-user">
                <i class="far fa-user-circle"></i>
            </a> -->
            <!--
            **********************************************************************************
                This dropdown section will only be visible when the user is not logged in.
                When the user is logged in the .fa-user-circle icon is a link to profile page
            **********************************************************************************
            -->

            <?php if (1 != $_SESSION['login']) {
               ?>

                            <div class="dropdown">
                <a href="#" id="register-link" class="cda cd-user" data-toggle="dropdown">
                    <i class="far fa-user-circle"></i>
                </a>
            </div>

            <?php
               } else {
               ?>
<a href="#" class="cda cd-user" data-toggle="dropdown">
                    <i class="far fa-user-circle"></i>
                </a>
    <?php
       }
    ?>

        </div>

        <div id="cd-nav" class="cd-nav">
            <div class="cd-navigation-wrapper">
                <div class="cd-half-block">
                    <h2>Navigation</h2>
                    <nav>
                        <ul class="cd-primary-nav">
                            <li>
                                <a href="#0" class="selected">The team</a>
                            </li>
                            <li>
                                <a href="#0">Our services</a>
                            </li>
                            <li>
                                <a href="#0">Our projects</a>
                            </li>
                            <li>
                                <a href="#0">Start a project</a>
                            </li>
                            <li>
                                <a href="#0">Contact us</a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <!-- .cd-half-block -->
                <div class="cd-half-block">
                    <address>
                        <ul class="cd-contact-info">
                            <li>
                                <a href="mailto:info@myemail.co">info@myemail.co</a>
                            </li>
                            <li>0244-12345678</li>
                            <li>
                                <span>MyStreetName 24</span>
                                <span>W1234X</span>
                                <span>London, UK</span>
                            </li>
                        </ul>
                    </address>
                </div>
                <!-- .cd-half-block -->
            </div>
            <!-- .cd-navigation-wrapper -->
        </div>

        <!-- .cd-nav -->
        <div id="reg-modal">
            <div class="log-reg-form-container">
                <button data-izimodal-close class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
                <div class="header-greet">
                    <h2>Hey, Star Stuff!</h2>
                </div>
                <div class="sign-in-btns">
                    <div class="fb-wrapper">
                    <a href="<?php echo $facebook_login_url; ?>">

                        <div id="fb-btn">
                            <span class="icon-">
                                <i class="fab fa-facebook-square"></i>
                            </span>
                            <span class="buttonText">Continue With Facebook</span>
                        </div>
                    </a>
                    </div>
                    <div id="gSignInWrapper">
                    <a href="<?php echo $google_login_url; ?>">

                        <div id="customBtn-google" class="customGPlusSignIn">
                            <span class="icon"></span>
                            <span class="buttonText">Continue With Google</span>
                        </div>
                    </a>
                    </div>
                    <div id="name"></div>
                </div>
                <hr class="long-vertical-break">
                <div class="custom-fileds-header">
                    <h5>Not feeling Social?</h5>
                </div>
                <div id="error-msg-reg"></div>
                <div class="sign-up-form">
                <?php echo form_open('user/sign-up') ?>

                        <div class="input-div">
                            <input type="text" class="custom-input-fields" name="txt_name" required="true" placeholder="What should we call you?">
                        </div>
                        <div class="input-div">
                            <input type="text" class="custom-input-fields" name="txt_email" required="true" placeholder="Your Email Address">
                        </div>
                        <div class="input-div">
                            <input type="password" class="custom-input-fields" name="txt_password" required="true" placeholder="Choose a Password">
                        </div>
                        <div class="join-btn-container">
                            <button type="submit">Join The LazySuzy Club
                            </button>
                        </div>
                <?php echo form_close(); ?>

                </div>
                <div class="alter-way">
                    <p>
                        Already In? <a href="#" id="sign-in">Sign In</a>
                    </p>
                </div>
            </div>
        </div>
        <div id="log-modal">
            <div class="log-reg-form-container">
                <button data-izimodal-close class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
                <div class="header-greet">
                    <h2>Hey, Star Stuff!</h2>
                </div>
                <div class="sign-in-btns">
                    <div class="fb-wrapper">
                    <a href="<?php echo $facebook_login_url; ?>">

                        <div id="fb-btn">
                            <span class="icon-">
                                <i class="fab fa-facebook-square"></i>
                            </span>
                            <span class="buttonText">
                                <span class="hd-small">Continue With </span>
                                <span class="hd-show">Log In </span>
                                Facebook

                            </span>
                        </div>
                    </a>
                    </div>
                    <div id="gSignInWrapper">
                        <a href="<?php echo $google_login_url; ?>">
                        <div id="customBtn-google" class="customGPlusSignIn">
                            <span class="icon"></span>
                            <span class="buttonText">
                                <span class="hd-small">Continue With </span>
                                <span class="hd-show">Log In </span>
                                Google

                            </span>
                        </div>
                        </a>
                    </div>
                    <div id="name"></div>
                </div>
                <hr class="long-vertical-break">
                <div class="custom-fileds-header">
                    <h5>Not feeling Social?</h5>
                </div>
                <div id="error-msg-log"></div>
                <div class="sign-in-form">
                    <?php echo form_open('user/sign-in'); ?>
                        <div class="input-div">
                            <input type="text" class="custom-input-fields" name="txt_username" required="true" placeholder="Your E-Mail address">
                        </div>
                        <div class="input-div">
                            <input type="password" class="custom-input-fields" name="txt_password" required="true" placeholder="Your Password">
                        </div>
                        <div class="join-btn-container">
                            <button type="submit">Log In
                            </button>
                        </div>
                    <?php echo form_close(); ?>
                </div>
                <div class="alter-way">
                    <p>
                        <a href="#">Reset Password</a>
                    </p>
                </div>
                <hr class="long-vertical-break">
                <div class="alter-way">
                    <p>
                        Not Yet In? <a href="#" id="sign-up">Join Now</a>
                    </p>
                </div>
            </div>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
        <script src="<?php echo front_js_url(); ?>owl.carousel.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/izimodal/1.5.1/js/iziModal.min.js"></script>
        <script src="https://apis.google.com/js/api:client.js"></script>
        <script src="<?php echo front_js_url(); ?>main.js"></script>
        <!-- Resource jQuery -->
    </body>
</html>
