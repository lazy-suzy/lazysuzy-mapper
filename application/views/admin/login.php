<?php $this->load->view('admin/login_header');?>

  <div class="login-header">
      <!-- BEGIN LOGO -->
      <div id="logo" class="center">
          <a href="<?php echo admin_url();?>"><img src="<?php echo front_url();?>assets/images/logo.png" alt="logo" class="center" /></a>
      </div>
      <!-- END LOGO -->
  </div>

  <!-- BEGIN LOGIN -->
  <div id="login">
    <?php 
    $error = $this->session->flashdata('error');
    if($error != '') {
      echo '<div class="alert alert-danger">
      <button aria-hidden="true" data-dismiss="alert" class="close" type="button">&#10005;</button>'.$error.'</div>';
    }
    $success = $this->session->flashdata('success');
    if($success != '') {
      echo '<div class="alert alert-success">
      <button aria-hidden="true" data-dismiss="alert" class="close" type="button">&#10005;</button>'.$success.'</div>';
    } 
    ?>
    <!-- BEGIN LOGIN FORM -->
    <form id="loginform" class="form-vertical no-padding no-margin" method="post" action="<?php echo $action; ?>">
      <div class="lock">
          <i class="icon-lock"></i>
      </div>
      <div class="control-wrap">
          <h4>User Login</h4>
          <div class="control-group">
              <div class="controls">
                  <div class="input-prepend">
                      <span class="add-on"><i class="icon-user"></i></span><input required="" name="username" id="input-username" type="text" placeholder="Username" />
                  </div>
              </div>
          </div>
          <div class="control-group">
              <div class="controls">
                  <div class="input-prepend">
                      <span class="add-on"><i class="icon-key"></i></span><input id="input-password" required="" name="password" type="password" required="" placeholder="Password" />
                  </div>
                  <div class="mtop10">
                      <div class="block-hint pull-left small">
                          <input type="checkbox" id=""> Remember Me
                      </div>
                      <div class="block-hint pull-right">
                          <a href="<?php echo admin_url().'forget_password';?>" class="" id="forget-password">Forgot Password?</a>
                      </div>
                  </div>

                  <div class="clearfix space5"></div>
              </div>

          </div>
      </div>

      <input type="submit" id="login-btn" class="btn btn-block login-btn" value="Login" />
    </form>
    <!-- END LOGIN FORM -->        
    
  </div>
  <!-- END LOGIN -->
  <!-- BEGIN COPYRIGHT -->
   
  <!-- END COPYRIGHT -->
  <!-- BEGIN JAVASCRIPTS -->
  <script src="<?php echo js_url(); ?>jquery-1.8.3.min.js"></script>
  <script src="<?php echo js_url(); ?>bootstrap.min.js"></script>
  <script src="<?php echo js_url(); ?>jquery.blockui.js"></script>
  <script src="<?php echo js_url(); ?>scripts.js"></script>
  <script>
    jQuery(document).ready(function() {     
      App.initLogin();
    });
  </script>
  <!-- END JAVASCRIPTS -->
</body>
<!-- END BODY -->
</html>