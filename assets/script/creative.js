function delete_creative_info(creative_id){
	jConfirm('Are you sure? You want delete this?', 'Confirmation Dialog', function(r) {
  		if(r){ 
  			jQuery.ajax({
  				url: "../creative/ajax-common",
  				data: "action=delete_creative&creative_id="+creative_id,
  				success:function(response){
  					if(response){
  						orphan_table_yui_grid();
  					}
  				}
  			});
  		}
  
    });
	return false;
}

function copyCreative(creative_id){
	jQuery.ajax({
			url: "../creative/ajax-common",
			data: "action=copy_creative_html&creative_id="+creative_id,
			success:function(response){
				if(response){
					d = new dialog( {title:"Copy Creative",width:300,onclose:'d.close()', ismodel:true});	
					d.sethtml(response);
					d.show();
					submitCopyCreative();
				}
			}
		});
	return false;
	
}
function submitCopyCreative(){
	jQuery('#copycreative').submit(function(){
		var creative_name = $('#creative_name').val();
		$('#error_message').hide();
		if(creative_name==''){
			$('#error_message').html('Creative name should not be empty');
			$('#error_message').show();
		}
		var formData = jQuery(this).serialize();
		jQuery.ajax({
			url: "../creative/ajax-common",
			data: formData+"&action=checkandcreative",
			success:function(response){
				if(response){
					d.close();
					orphan_table_yui_grid();
				}else{
					$('#error_message').html('Creative name should not be duplicate');
					$('#error_message').show();
				}
			}
		});
		return false;
	});
}
