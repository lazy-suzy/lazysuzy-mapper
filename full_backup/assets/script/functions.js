jQuery(document).ready(function() { 
	if(jQuery.browser.msie){
		var cssObj = {'width' : '930px','margin-left' : '-8px'};
		jQuery("#navigation").css(cssObj);
		jQuery("#main").css("width","900px");
		jQuery("#clearboth").css("margin-top","-25px");
		jQuery(".addnewtop").css("margin-top","20px");
	}
	/*if(jQuery.browser.webkit){
		jQuery("#container").css("height","100%");
	}*/
	/*if(jQuery.browser.opera){
		jQuery("#navigation").css("margin-left","185px");
	}
	if(jQuery.browser.mozilla){	
		jQuery("#navigation").css("margin-left","205px");
	}*/
});
function closeDialog(){
	window.location.reload( true );
}
function cancelDialog(){
	jQuery('.popupBackground, .dialog').remove();
}
function changepassword(){
	d = new dialog( {title:"",width:660, height:205, onclose:'closeDialog()', ismodel:true, isdrag:false});	
	d.sethtml('<span class="warnning">loading...</span>');
	d.show();
	jQuery.ajax({
	   type: "POST",
	   url: g_site_path+"get_popup",
	    data: "frmName=frmUsers&action=chgpassword&rand="+Math.random(),
	   dataType: "html",
	   success: function(html){
		d.sethtml( html );
		//d.setHeight(15);
		}
	 });
}
function saveUserPassword(){
	var vOldPassword = jQuery('#oldpassword').val();
	var vNewPassword = jQuery('#newpassword').val();
	var vConfirmPassword = jQuery('#confirmpassword').val();
	var vUserName = jQuery('#hidUserName').val();
	if(!vOldPassword)
		jQuery('#errorMsg').html('You must enter your current password');
	else if(!vNewPassword)
		jQuery('#errorMsg').html('You must enter your new password');
	else if(!vConfirmPassword)
		jQuery('#errorMsg').html('You must enter your confirm password');
	else if(vNewPassword != vConfirmPassword)
		jQuery('#errorMsg').html('New & Confirm password does not match');
	else
	{
		var vData = "frmName=userform&action=change&oldpassword="+vOldPassword+"&newpassword="+vNewPassword+"&confirmpassword="+vConfirmPassword+"&username="+vUserName+"&rand="+Math.random();		
		jQuery.ajax({
		   type: "POST",
		   url: g_site_path+"ajax_common",
		   data: vData,
		   dataType: "html",
		   success: function(html){	
		   		if(html > 0)
				{
					if(html > 1)
					{
						jQuery('#errorMsg').html('Your current password does not match!');
					}
					else
					{
						jQuery('#errorMsg').html('Updated Successfully');
					 	setTimeout(function(){ window.location.reload( true ); }, 1000);
					}
				}
				else
					jQuery('#errorMsg').html('Mysql Failed');
		   }
	   });
	}
}
function convertToSlug(str){
	str = str.replace(/^\s+|\s+$/g, ''); // trim
	str = str.toLowerCase();
	str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
	.replace(/\s+/g, '-') // collapse whitespace and replace by -
	.replace(/-+/g, '-'); // collapse dashes
	
	return str;
}
