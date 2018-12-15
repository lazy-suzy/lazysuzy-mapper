	</div>
	<!-- END CONTAINER -->
	<!-- BEGIN FOOTER -->
	<div id="footer">
        &copy; 2013 .
		<div class="span pull-right">
			<span class="go-top"><i class="icon-arrow-up"></i></span>
		</div>
	</div>
	<!-- END FOOTER -->
    <!-- BEGIN JAVASCRIPTS -->
    <!-- Load javascripts at bottom, this will reduce page load time -->
    <script src="<?php echo js_url();?>jquery-1.8.3.min.js"></script>
    <script src="<?php echo js_url();?>bootstrap.min.js"></script>
    <script src="<?php echo js_url();?>jquery.blockui.js"></script>
    <script src="<?php echo js_url();?>jquery.uniform.min.js"></script>
    <script src="<?php echo front_url();?>assets/data-tables/jquery.dataTables.js"></script>
    <script src="<?php echo js_url();?>DT_bootstrap.js"></script>
    <script src="<?php echo js_url();?>scripts.js"></script>


    <script src="<?php echo front_url();?>assets/jquery-slimscroll/jquery-ui-1.9.2.custom.min.js"></script>
    <script src="<?php echo front_url();?>assets/jquery-slimscroll/jquery.slimscroll.min.js"></script>
    <script src="<?php echo front_url();?>assets/fullcalendar/fullcalendar/fullcalendar.min.js"></script>
    
    
    <script src="<?php echo js_url();?>jquery.cookie.js"></script>
    
    <script src="<?php echo front_url();?>assets/jqvmap/jqvmap/jquery.vmap.js" type="text/javascript"></script>
    <script src="<?php echo front_url();?>assets/jqvmap/jqvmap/maps/jquery.vmap.russia.js" type="text/javascript"></script>
    <script src="<?php echo front_url();?>assets/jqvmap/jqvmap/maps/jquery.vmap.world.js" type="text/javascript"></script>
    <script src="<?php echo front_url();?>assets/jqvmap/jqvmap/maps/jquery.vmap.europe.js" type="text/javascript"></script>
    <script src="<?php echo front_url();?>assets/jqvmap/jqvmap/maps/jquery.vmap.germany.js" type="text/javascript"></script>
    <script src="<?php echo front_url();?>assets/jqvmap/jqvmap/maps/jquery.vmap.usa.js" type="text/javascript"></script>
    <script src="<?php echo front_url();?>assets/jqvmap/jqvmap/data/jquery.vmap.sampledata.js" type="text/javascript"></script>
    <script src="<?php echo front_url();?>assets/jquery-knob/js/jquery.knob.js"></script>
    <script src="<?php echo front_url();?>assets/flot/jquery.flot.js"></script>
    <script src="<?php echo front_url();?>assets/flot/jquery.flot.resize.js"></script>

    <script src="<?php echo front_url();?>assets/flot/jquery.flot.pie.js"></script>
    <script src="<?php echo front_url();?>assets/flot/jquery.flot.stack.js"></script>
    <script src="<?php echo front_url();?>assets/flot/jquery.flot.crosshair.js"></script>

    <script src="<?php echo js_url();?>jquery.peity.min.js"></script>
    
    <script src="<?php echo js_url();?>scripts.js"></script>
    

    <script type="text/javascript" src="<?php echo front_url();?>assets/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>   
    <script type="text/javascript" src="<?php echo front_url();?>assets/bootstrap-daterangepicker/date.js"></script>
    <script type="text/javascript" src="<?php echo front_url();?>assets/bootstrap-daterangepicker/daterangepicker.js"></script> 
    <script type="text/javascript" src="<?php echo front_url();?>assets/bootstrap-timepicker/js/bootstrap-timepicker.js"></script>
   
     
    <script type="text/javascript" language="javascript" src="<?php echo front_url();?>assets/script/less-1.3.3.min.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo front_url();?>assets/Highcharts/code/highcharts.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo front_url();?>assets/Highcharts/code/modules/exporting.js"></script>

    <script type="text/javascript" language="javascript" src="<?php echo front_url();?>assets/script/lightbox.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo front_url();?>assets/script/alerts.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo front_url();?>assets/script/functions.js"></script>
    <script type="text/javascript" language="javascript" src="<?php echo front_url();?>assets/script/jquery.validate.js"></script>
    <!-- {if $fileUpload eq true}
    <script src="<?php echo front_url();?>script/dropzone.js"></script>
    {/if} -->
    <script type="text/javascript">
    var grid_row_total = '';
    var btn_status = 'ON';
    var issuer_first_img = '';
    var issuer_water_mark_img = '';
    var issuer_water_mark_img_medium = '';
    var issuer_water_mark_img_big = '';
    </script>
    
    <script type="text/javascript">
jQuery(document).ready(function() {
    jQuery('.search-form').submit(function() {});
    
 });
function change_issuer_function(id){
     jQuery('#issuer_id_change').val(id);
     document.getElementById("change_issuer").submit();
}

function addwatermark(div_id,default_height,minus_height){}

function compnay_selection_post(id){                
        $('#user_company_list').val(id);
        $('#company_form_select_result').submit();  
}


</script>
    <!--End Define Custom Script-->

    <!-- END JAVASCRIPTS -->
    <script>
 /*jQuery.extend(jQuery.validator.messages, {
    required: "This field is required.",
    remote: "Please fix this field.",
    email: "Please enter a valid email address.",
    url: "Please enter a valid URL.",
    date: "Please enter a valid date.",
    dateISO: "Please enter a valid date (ISO).",
    number: "Please enter a valid number.",
    digits: "Please enter only digits.",
    creditcard: "Please enter a valid credit card number.",
    equalTo: "Please enter the same value again.",
    accept: "Please enter a value with a valid extension.",
    maxlength: jQuery.validator.format("Please enter no more than {0} characters."),
    minlength: jQuery.validator.format("Please enter at least {0} characters."),
    rangelength: jQuery.validator.format("Please enter a value between {0} and {1} characters long."),
    range: jQuery.validator.format("Please enter a value between {0} and {1}."),
    max: jQuery.validator.format("Please enter a value less than or equal to {0}."),
    min: jQuery.validator.format("Please enter a value greater than or equal to {0}.")
});*/
$.validator.addMethod('le', function(value, element, param) {
      return this.optional(element) || value < $(param).val();
}, 'Invalid value');
$.validator.addMethod('ge', function(value, element, param) {

      return this.optional(element) || value > $(param).val();

}, 'Invalid value');
jQuery.extend(jQuery.validator.messages, {
    min: jQuery.validator.format("Please Selet Value.")
});
 </script> 

<script type="text/javascript">
function readURL(input) 
{
    if(input.files && input.files[0]) 
    {
        var reader      = new FileReader();
        reader.onload   = function(e)
        {
          $('#img1').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}

$("#pro_image").change(function() {
  readURL(this);
});


 $('.alert-success').delay(3000).fadeOut('slow'); 
 $('.alert-danger').delay(3000).fadeOut('slow'); 
</script>


</body>
<!-- END BODY -->
</html>
