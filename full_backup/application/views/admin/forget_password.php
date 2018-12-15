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
    <!-- BEGIN FORGOT PASSWORD FORM -->
    <form class="form-vertical no-padding no-margin" method="post" action="<?php echo $action; ?>">
      <p class="center">Enter your e-mail address below to reset your password.</p>
      <div class="control-group">
        <div class="controls">
          <div class="input-prepend">
            <span class="add-on"><i class="icon-envelope"></i></span><input id="input-email"  name="email" required="" type="email" placeholder="Email"  />
          </div>
        </div>
        <div class="space20"></div>
      </div>
      <input type="submit" class="btn btn-block login-btn" value="Submit" />
    </form>
    <!-- END FORGOT PASSWORD FORM -->
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
