// JavaScript Document

/// User Script


/// User Management Expired Now function
function _expire_now(argid)
{	
	jQuery.ajax({
		type: "POST",
		url: "../admin/ajax_common",
		data: "frmName=frmCompanyExpiry&action=company_expiry&company_id="+argid+"&rand="+Math.random(),
		dataType: "html",
		success: function(html){
			//alert(html);
			if(html > 0){
				GrtNotfication_script('This company expired successfully.');
				jQuery('#glitter_alert_watch').css('display','block');
				setTimeout(function(){
					jQuery('#glitter_alert_watch').css('display','none');
				}, 60);
				return false;
			} else {
				GrtNotfication_script("Oops - Try it Later.");
				jQuery('#glitter_alert_watch').css('display','block');
				setTimeout(function(){
					jQuery('#glitter_alert_watch').css('display','none');
				}, 60);
				return true;
			}
		}
	 });	
}	


/// Make Company account archeive & restore function
function _archieve_restore_now(argid,status)
{	
	jQuery.ajax({
		type: "POST",
		url: "../admin/ajax_common",
		data: "frmName=frmCompanyArchieve&action=company_archieve_restore&company_id="+argid+"&status="+status+"&rand="+Math.random(),
		dataType: "html",
		success: function(html){
			//alert(html);
			if(html > 0){
				if(status=='1')	
					GrtNotfication_script('This company archived successfully.');
				else
					GrtNotfication_script('This company restored successfully.');
				
				jQuery('#glitter_alert_watch').css('display','block');
				setTimeout(function(){
					jQuery('#glitter_alert_watch').css('display','none');
				}, 60);
				company_table_yui_grid();
				
				return false;
			} else {
				GrtNotfication_script("Oops - Try it Later.");
				jQuery('#glitter_alert_watch').css('display','block');
				setTimeout(function(){
					jQuery('#glitter_alert_watch').css('display','none');
				}, 60);
				return true;
			}
		}
	 });	
}	

// Expired company
function expire_confirmation_popup(id)
{ 	
	$('#update_messages_user').html("Are you sure you want to Expire now?");
	$('#userconfirm_model').modal('toggle');		
	$('#expire_comp_id').val(id);
}

// Expired company
function expired_now_ok()
{
	var exp_comp_id = $('#expire_comp_id').val();
	_expire_now(exp_comp_id);
	company_table_yui_grid();
	customer_table_yui_grid();
}	


// Expired company
function arch_res_confirmation_popup(id,type)
{ 	
	if(type==1)
	var msg_cap = ' Archive';
	else
	var msg_cap = ' Restore';
	
	$('#update_messages_arcres').html("Are you sure you want to "+msg_cap+" now?");
	$('#arcres_confirm_model').modal('toggle');		
	

	$('#arcres_comp_id').val(id);
	$('#arcres_type').val(type);
	
}

// Archeive - Restore company
function arch_res_confirm_ok()
{	
	var cid  = $('#arcres_comp_id').val();
	var type = $('#arcres_type').val();
	_archieve_restore_now(cid,type);

}

// USer Archeive restore 


function user_arch_res_confirmation_popup(id,type)
{ 	
	if(type==1)
	var msg_cap = ' Archive';
	else
	var msg_cap = ' Restore';
	
	$('#user_update_messages_arcres').html("Are you sure you want to "+msg_cap+" now?");
	$('#user_arcres_confirm_model').modal('toggle');		
	
	
	$('#user_arcres_comp_id').val(id);
	$('#user_arcres_type').val(type);
	
}

// Archeive - Restore company
function user_arch_res_confirm_ok()
{	
	var cid  = $('#user_arcres_comp_id').val();
	var type = $('#user_arcres_type').val();
	_user_archieve_restore_now(cid,type);

}


function _user_archieve_restore_now(argid,status)
{	
	jQuery.ajax({
		type: "POST",
		url: "../admin/ajax_common",
		data: "frmName=frmUserArchieve&action=user_archieve_restore&user_id="+argid+"&status="+status+"&rand="+Math.random(),
		dataType: "html",
		success: function(html){
			//alert(html);
			if(html > 0){
				if(status=='1')	
					GrtNotfication_script('This user archived successfully.');
				else
					GrtNotfication_script('This user restored successfully.');
				
				jQuery('#glitter_alert_watch').css('display','block');
				setTimeout(function(){
					jQuery('#glitter_alert_watch').css('display','none');
				}, 60);
				normal_table_yui_grid();
				admin_table_yui_grid();
				
				setTimeout(function(){
					customer_table_yui_grid();
				}, 60);
				
				
				return false;
			} else {
				GrtNotfication_script("Oops - Try it Later.");
				jQuery('#glitter_alert_watch').css('display','block');
				setTimeout(function(){
					jQuery('#glitter_alert_watch').css('display','none');
				}, 60);
				return true;
			}
		}
	 });	
}	


function _user_password_reset(argid)
{	
	jQuery.ajax({
		type: "POST",
		url: "../admin/ajax_common",
		data: "frmName=frmUserPWreset&action=user_pw_reset&user_id="+argid+"&rand="+Math.random(),
		dataType: "html",
		success: function(html){
			//alert(html);
			if(html > 0){

				GrtNotfication_script('This user resetted successfully.');
				
				jQuery('#glitter_alert_watch').css('display','block');
				setTimeout(function(){
					jQuery('#glitter_alert_watch').css('display','none');
				}, 60);
				//normal_table_yui_grid();
				return false;
			} else {
				GrtNotfication_script("Oops - Try it Later.");
				jQuery('#glitter_alert_watch').css('display','block');
				setTimeout(function(){
					jQuery('#glitter_alert_watch').css('display','none');
				}, 60);
				return true;
			}
		}
	 });	
}	


function _user_delete_company_mapping_confirm(userid,compid)
{ 	
	
	$('#user_mapping_delete_update_message').html("Are you sure you want to delete now?");
	$('#user_mapping_delete_confirm_model').modal('toggle');		
	
	
	$('#mapping_user_delete_id').val(userid);
	$('#mapping_company_delete_id').val(compid);
	
}


function _user_delete_company_mapping()
{	
	var argUserid = $('#mapping_user_delete_id').val();
	var argCompId = $('#mapping_company_delete_id').val();
	
	jQuery.ajax({
		type: "POST",
		url: "../admin/ajax_common",
		data: "frmName=frmAdminUserDeleteCompMapping&action=admin_user_delete_mapping&user_id="+argUserid+"&comp_id="+argCompId+"&rand="+Math.random(),
		dataType: "html",
		success: function(html){
			//alert(html);
			if(html > 0){

				GrtNotfication_script('User mapping removed successfully.');
				
				jQuery('#glitter_alert_watch').css('display','block');
				setTimeout(function(){
					jQuery('#glitter_alert_watch').css('display','none');
				}, 60);
				
				if(!isNaN($('#is_normaL_grid').val()))
				{
					normal_table_yui_grid();
				}
				
				customer_table_yui_grid();
				return false;
			} else {
				GrtNotfication_script("Oops - Try it Later.");
				jQuery('#glitter_alert_watch').css('display','block');
				setTimeout(function(){
					jQuery('#glitter_alert_watch').css('display','none');
				}, 60);
				return true;
			}
		}
	 });	
}	




function GrtNotfication_script(msg){

	$.gritter.add({
		// (string | mandatory) the heading of the notification
		title: '',
		// (string | mandatory) the text inside the notification
		text: '<i class="icon-envelope"></i>&nbsp; '+msg,
		// (string | optional) the image to display on the left
		//image: 'http://a0.twimg.com/profile_images/59268975/jquery_avatar_bigger.png',
		// (bool | optional) if you want it to fade out on its own or just sit there
		sticky: false,
		// (int | optional) the time you want it to be alive for before fading out
		time: ''
	});

	return false;
}

///////////////// Email report 

function save_user_email_report(email_report_id){

		var email_report_name    = $('#txtEmailReportName').val();	
		var rpt_receip_cnt       = $('#hidden_sel_usr_chk_val').val();
		var rpr_uni_id           = $('#hidden_report_unique_id').val();
		var select_trigger_type  = $('#sel_trigger_type').val();
		var sel_frequency_type   = $('#sel_frequency').val();
		
		var Err=0;
		if(email_report_name=='')
		{
			$('#err_emailid').html('Please enter the report name.');
			Err++;
		}
		else
		$('#err_emailid').html('');
		
		if(rpt_receip_cnt==0)
		{
			$('#err_rece_cnt').html('Please select the atleast one user.');
			Err++;
		}		
		else
		$('#err_rece_cnt').html('');
		
		if( Err > 0 )
		return false;
		else
		{			
			jQuery.ajax({
				type: "POST",
				url: "../users/email-report-process/ajax-email-reports.php",
				data: "para=add_edit_email_report&email_report_id="+email_report_id+"&report_unique_id="+rpr_uni_id+"&report_name="+encodeURIComponent(email_report_name)+"&recip_list="+rpt_receip_cnt+"&sel_freq_type="+sel_frequency_type+"&sel_trigger_type="+select_trigger_type+"&rand="+Math.random(),			
				success: function(res){
					var response = res.status;
					
					if( response == '1' ){
						$('#no_rpt_li').remove();
						var response_last_li = res.last_li; 
						var last_insert_id   = res.id;
						$('#EmailReportTab ul').append(response_last_li);	
						$('#nav_email_rtp_id_'+last_insert_id).fadeIn('slow');
						GrtNotfication_email_report('created');
						emailreport_table_yui_grid();
					}
					else if( response == '2' ){
						
						var response_rpt_name = res.report_name;
						var response_rpt_name_fulltxt = res.report_name_fulltext;
						
						$('#nav_email_rtp_id_'+email_report_id+ " a ").eq(0).html(response_rpt_name);
						$('#nav_email_rtp_id_'+email_report_id+ " a ").eq(0).attr('title',response_rpt_name_fulltxt);
						
						
						GrtNotfication_email_report('updated');
						emailreport_table_yui_grid();
						
					}					
					else
						GrtNotfication_email_report('err');
					
					jQuery('#glitter_alert_watch').css('display','block');
					setTimeout(function(){
						jQuery('#glitter_alert_watch').css('display','none');
					}, 60);
						
					closeDiv();
					return false;
				}
			 });	
		}
		
		return false;
		
}


function seleted_user_list() { 
	var arr = [];
	i = 0;
    $('.user_manager_class:checked').each(function() { 
        arr[i++] = $(this).val();
    });	
	$('#hidden_sel_usr_chk_val').val(arr);
}


//// delete email report

 
function delete_email_rpt_popup(argid)
{
	$('#delete_email_rpt_model').modal('toggle');

	$('#delete_email_rpt_id').val(argid);
	
} 


function delete_email_rpt_now()
{ 		
	var email_report_id       = $('#delete_email_rpt_id').val();
	
	if( email_report_id > 0 )
	{			
		jQuery.ajax({
			type	: "POST",
			url: "../users/email-report-process/ajax-email-reports.php",
			data: "para=delete_email_report&email_report_id="+email_report_id+"&rand="+Math.random(),		
			success	: function(getReturnData){ 
				if(getReturnData.status=="3") {
					$("li#nav_email_rtp_id_"+email_report_id).remove();								
					$('#delete_email_rpt_model').hide();	
						GrtNotfication_email_report('remove');
						jQuery('#glitter_alert_watch').css('display','block');
						setTimeout(function(){
							jQuery('#glitter_alert_watch').css('display','none');
						}, 60);
						emailreport_table_yui_grid();
						
				} else {
					  GrtNotfication_email_report('err');
					  jQuery('#glitter_alert_watch').css('display','block');
						setTimeout(function(){
							jQuery('#glitter_alert_watch').css('display','none');
						}, 60);
				}				
			}
		});	
	} 		
}
 
 
 
function edit_user_email_reports(email_report_id){
	
	d = new dialog( {title:"",width:560, height:180, ismodel:true});	
	d.show();
	jQuery.ajax({
		type: "POST",
		url: "../summary/add-edit-email-reports.php",
		data: "email_report_id="+email_report_id+"&rand="+Math.random(),
		dataType: "html",
		success: function(html){
			d.sethtml( html );
		}
	 });
}




// Expired company
function extend_expiry_confirmation_popup(id)
{ 	
	$('#extend_messages_user').html("Are you want to extend the expiry date ?");
	$('#extend_confirm_model').modal('toggle');		
	$('#expire_comp_id').val(id);
}

// Expired company
function extend_expiry_now_ok()
{
	var exp_comp_id = $('#expire_comp_id').val();
	_extend_expiry_now(exp_comp_id);
	company_table_yui_grid();
	customer_table_yui_grid();
}
/// User Management Expired Now function
function _extend_expiry_now(argid)
{	
	jQuery.ajax({
		type: "POST",
		url: "../admin/ajax_common",
		data: "frmName=frmCompanyExpiryExtend&action=company_expiry_extend&company_id="+argid+"&rand="+Math.random(),
		dataType: "html",
		success: function(html){
			//alert(html);
			if(html > 0){
				GrtNotfication_script('This company expiry date extend successfully.');
				jQuery('#glitter_alert_watch').css('display','block');
				setTimeout(function(){
					jQuery('#glitter_alert_watch').css('display','none');
				}, 60);
				return false;
			} else {
				GrtNotfication_script("Oops - Try it Later.");
				jQuery('#glitter_alert_watch').css('display','block');
				setTimeout(function(){
					jQuery('#glitter_alert_watch').css('display','none');
				}, 60);
				return true;
			}
		}
	 });	
}	





function GrtNotfication_email_report(argType){

var rtype = 'Email ';

if(argType=='remove')
{
	 var dis= rtype +' report removed successfully';
}
else if(argType=='updated')
{
	var dis= rtype +' report updated successfully';
}
else if(argType=='created')
{
	var dis= rtype +' report added successfully';
}
else
{
	var dis= 'Oops - Try it later';
}
	$.gritter.add({
		// (string | mandatory) the heading of the notification
		title: '',
		// (string | mandatory) the text inside the notification
		text: '<i class="icon-envelope"></i>&nbsp; '+dis,
		// (string | optional) the image to display on the left
		//image: 'http://a0.twimg.com/profile_images/59268975/jquery_avatar_bigger.png',
		// (bool | optional) if you want it to fade out on its own or just sit there
		sticky: false,
		// (int | optional) the time you want it to be alive for before fading out
		time: ''
	});

	return false;
}


function closeDiv(){
	d.close();
	return false;
}
///////////////// Email report 



