// JavaScript Document

/// Add campaign validation check


// Reset Campaign Form
function reset_campaign()
{
	$('#campign_name').val('');
	$('#campaign_status').val('active');
	$('#runsch_start_date').val('');
	$('#runsch_end_date').val('');
	$('#campaign_location').val('236');	
}

function checkDatesVal(d1, d2) {
    if (d1 instanceof Date && d2 instanceof Date) {
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        if (date1.getTime() < (today.getTime() + 86400000)) {
            return "First date should be after today";
        }
        if (date2.getTime() < (date1.getTime() + 86400000)) {
            return "Second date should be after First date";
        }
        return "Dates are valid";
    }
    return "One or more invalid date";
}






/// Add campaign validation check

function save_vdo_ad_details()
{		
	var err=0;
	var vtxt_video_ad_name 	 = $('#txt_video_ad_name').val();
	
	
	if(vtxt_video_ad_name==''){
		$('#err_campign_name').html('Please enter campign name');
		err++;
	} 
	else if(vtxt_video_ad_name!='' && vtxt_video_ad_name.length < 4)
	{
		$('#err_campign_name').html('Campign name minimum 4 character is needed');	
		err++	
	}
	else
	$('#err_campign_name').html('');
			
	
	if(vrunsch_start_date=='' || vrunsch_end_date=='' ){
		$('#show_rs_date_err').html('Please select the running schedule details.');
		err++
	} 
	else
	{
		  	
		   var date1Arr = vrunsch_start_date.split("/");
		   var date2Arr = vrunsch_end_date.split("/");
		   var dateOne = new Date(date1Arr[2],date1Arr[0],date1Arr[1]);
 		   var dateTwo = new Date(date2Arr[2],date2Arr[0],date2Arr[1]);
										  
		   if (dateOne > dateTwo) {
				$('#show_rs_date_err').html('Start date is greather then end date.');
				 err++
		   }
		   else
		   {
				$('#show_rs_date_err').html('');   
		   }
		  
	}
	
	if( vrunsch_start_time > vrunsch_end_time  ){
		$('#show_rs_time_err').html('Please check the running schedule time details.');
		err++
	}
	else
	{
		$('#show_rs_time_err').html('');	
	}
	
	
	if( err==0 )
	{		
		
		jQuery.ajax({
			type: "POST",
			url: "../campaign/ajax-process.php",
			data: "action=campaign_add&campign_name="+vcampaign_name+"&campaign_status="+vcampaign_status+"&runsch_start_date="+vrunsch_start_date+"&runsch_end_date="+vrunsch_end_date+"&runsch_start_time="+vrunsch_start_time+"&runsch_end_time="+vrunsch_end_time+"&campaign_location="+vcampaign_location+"&rand="+Math.random(),
			dataType: "html",
			success: function(html){
				if(html>0)
				{
					$('ul.nav-tabs li').eq(0).removeClass('active');
					$('ul.nav-tabs li').eq(1).addClass('active');					
					$('#tab_1_1').removeClass('active');
					$('#tab_1_2').addClass('active');
					$('#campaign_id').val(html);
					GrtNotfication('added');
					jQuery('#glitter_alert_watch').css('display','block');
					setTimeout(function(){
						jQuery('#glitter_alert_watch').css('display','none');
					}, 60);
					
					
		
				}
				return false;				
			}
		 });			
	}	
	return false;
}

/// Delete Vdo Ad details
function delete_vdoad_info()
{
	var vdoargid = $('#delete_id_vdoad').val();
	
	jQuery.ajax({
			type: "POST",
			url: "../campaign/ajax-process",
			data: "action=delete_vdoad_and_mapp_info&vdoad_id="+vdoargid+"&rand="+Math.random(),
			dataType: "html",
			success: function(html)
			{
				if(html==1)
				{
					GrtNotfication_show('Video Ad details deleted successfully.');										
					videoad_table_yui_grid();					
					$('#delete_id_vdoad').val(0);
				}
				else
				{
					GrtNotfication_show('Oops - Try it Later !!!');			
				}
				
				jQuery('#glitter_alert_watch').css('display','block');
				setTimeout(function(){
					jQuery('#glitter_alert_watch').css('display','none');
				}, 60);
			}
		 });	
	return false;
}



/// Delete Camp details details
function delete_campaign_info()
{
	var vdoargid = $('#delete_id_camp').val();
	
	jQuery.ajax({
			type: "POST",
			url: "../campaign/ajax-process",
			data: "action=delete_camp_assoc_vodad&camp_id="+vdoargid+"&rand="+Math.random(),
			dataType: "html",
			success: function(html)
			{
				if(html==1)
				{
					GrtNotfication_show('Campaign details deleted successfully.');										
					campaign_table_yui_grid();					
					$('#delete_id_vdoad').val(0);
				}
				else
				{
					GrtNotfication_show('Oops - Try it Later !!!');	
					campaign_table_yui_grid();	
				}
				
				jQuery('#glitter_alert_watch').css('display','block');
				setTimeout(function(){
					jQuery('#glitter_alert_watch').css('display','none');
				}, 60);
			}
		 });	
	return false;
}

/// Delete Camp details details
function status_campaign_info()
{
	var vdoargid = $('#delete_id_camp').val();
	
	jQuery.ajax({
			type: "POST",
			url: "../campaign/ajax-process",
			data: "action=status_cng_camp&camp_id="+vdoargid+"&rand="+Math.random(),
			dataType: "html",
			success: function(html)
			{
				if(html==1)
				{
					GrtNotfication_show('Campaign status changed successfully.');										
					campaign_table_yui_grid();					
					$('#delete_id_vdoad').val(0);
				}
				else
				{
					GrtNotfication_show('Oops - Try it Later !!!');	
					campaign_table_yui_grid();	
				}
				
				jQuery('#glitter_alert_watch').css('display','block');
				setTimeout(function(){
					jQuery('#glitter_alert_watch').css('display','none');
				}, 60);
			}
		 });	
	return false;
}



// Load Video Ad page 
function add_new_video_ad_pop(arg_vdoad_id){	

	var vCampainId = $('#campaign_id').val();
	if(arg_vdoad_id==0)
	{
		if(vCampainId==0)
		{
			$('#show_new_vdo_chk_err').html('Please create the new campaign');
			return false;	
		}
		$('#show_new_vdo_chk_err').html('');
	}
	d = new dialog( {title:"",width:646, height:500, ismodel:true});
	
	d.show();
	jQuery.ajax({
		type: "POST",
		url: "../campaign/add-edit-video-ad",
		data: "campaign_id="+vCampainId+"&company_user_id=0&camp_vdoad_id="+arg_vdoad_id+"&rand="+Math.random(),
		dataType: "html",
		success: function(html){
			d.sethtml( html );
		}
	 });
}



function GrtNotfication(argType){

if(argType=='remove')
{
	 var dis= ' New campaign removed successfully';
}
else if(argType=='updated')
{
	var dis= ' New campaign updated successfully';
}
else if(argType=='error')
{
	var dis= ' Oops - Try it later !!';
}
else
{
	var dis= ' New campaign added successfully';
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

function GrtNotfication_show(argMsg){

	var dis= argMsg;


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
