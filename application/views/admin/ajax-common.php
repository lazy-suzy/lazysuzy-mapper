<?php
session_start();
$app_root	= "../";
include_once ("{$app_root}includes/include.php");
require_once('../classes/PHPExcel.php');
switch($_REQUEST['action']){

	case 'delete_brand':
		delete_brand();
	break;
	case 'add_brand_info':
		add_brand_info();
	break;
	case 'save_brand_group_info':
		save_brand_group_info();
	break;
	case 'delete_category':
		delete_category();
	break;
	case 'add_category_info':
		add_category_info();
	break;
	case 'save_category_group_info':
		save_category_group_info();
	break;
 	case 'delete_manufacturer':
		delete_manufacturer();
	break;
	case 'add_manufacturer_info':
		add_manufacturer_info();
	break;
	case 'save_manufacturer_group_info':
		save_manufacturer_group_info();
	break;
	case 'delete_department':
		delete_department();
	break;
	case 'add_department_info':
		add_department_info();
	break;
	case 'save_department_group_info':
		save_department_group_info();
	break;
	case 'chagne_status':
		update_site_status();
	break;
	case 'delete_product':
		delete_product();
	break;
	case 'add_product_info':
		add_product_info();
	break;
}

function update_site_status()
{
	$objManagement	=	new Management();
	if(@$_REQUEST['site_id'] > 0){ 
		$arrKeyWordReport = $objManagement->update_site_status($_REQUEST['site_id'], $_REQUEST['status']);
	}
}


function delete_brand(){
	$objManagement	=	new Management();
	if(@$_REQUEST['brand_id'] > 0){ 
		$arrKeyWordReport = $objManagement->delete_brand($_REQUEST['brand_id']);
	}
}
function save_brand_group_info(){
	$objManagement	=	new Management();
	$varBrandId	=	$objManagement->add_brand_group_info();
	echo $varBrandId;
}
function add_brand_info(){
	ob_start();
	
	$varGroupName = '';
	if($_REQUEST['brand_id'] > 0){
		$objManagement = new Management();		
		
		$arrInfo = $objManagement->get_brand_info($_REQUEST['brand_id']);
		$varBrandName = trim($arrInfo[0]['brand_name']);
	}
	?>
    <div style="clear:both;">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="d.close()">&#215;</button>
        <?php if($_REQUEST['brand_id'] > 0){ 
            ?>
        <h3 id="myModalLabel1">Edit Brand name</h3>
        <?php } else { ?>
        <h3 id="myModalLabel1">Add Brand name</h3>
        <?php } ?>
	</div>
    <form id="frmBrandGroup" name="frmBrandGroup" method="get" class="frm_margin">
    <div class="modal-body">
    	<div style="clear:both; text-align:left;">
        	<div style="float:left; width:100px; margin-top:4px;">Type Name</div>
            <div style="float:left; margin-left:10px;"><input id="txtBrandName" class="input-medium" type="text" name="txtBrandName" value="<?php echo @$varBrandName; ?>" _onkeyup="nospaces(this)"></div>
            <div style="float:left; margin-left:10px;">
            	<div style="clear:both; color:#FF0000; margin-top:4px;" id="err_groupname">&nbsp;</div>
			</div>                
        </div>
    </div>
		

    <div class="modal-footer">
    	<div style="float:left; color:#FF0000; margin-top:4px;" id="show_frmKeywordGroup_err">&nbsp;</div>
        <div style="float:right;">
        <button class="btn" onclick="return closeDiv();">Cancel</button>
        <button class="btn btn-primary" onclick="return save_add_edit_brand(<?php  echo @$_REQUEST['brand_id']; ?>)">Save</button>
        </div>
    </div>
    </form>
</div>
	<?php
	$varHtml = ob_get_contents();
	ob_end_clean();
	echo $varHtml;
}
function delete_category(){
	$objManagement	=	new Management();
	if(@$_REQUEST['category_id'] > 0){ 
		$arrKeyWordReport = $objManagement->delete_category($_REQUEST['category_id']);
	}
}
function save_category_group_info(){
	$objManagement	=	new Management();
	$varCatgoryId	=	$objManagement->add_category_group_info();
	echo $varCatgoryId;
}
function add_category_info(){
	ob_start();
	
	$varGroupName = '';
     $varParent=0;
	if($_REQUEST['category_id'] > 0){
		$objManagement = new Management();
		
		
		$arrInfo = $objManagement->get_category_info($_REQUEST['category_id']);
		$varCategoryName = trim($arrInfo[0]['category_name']);
        $varParent = trim($arrInfo[0]['parent_id']);
		$vardepartment_id = trim($arrInfo[0]['department_id']);
	}
	?>
    <div style="clear:both;">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="d.close()">&#215;</button>
        <?php if($_REQUEST['category_id'] > 0){ 
            ?>
        <h3 id="myModalLabel1">Edit category name</h3>
        <?php } else { ?>
        <h3 id="myModalLabel1">Add category name</h3>
        <?php } ?>
	</div>
    <form id="frmCategoryGroup" name="frmCategoryGroup" method="get" class="frm_margin" enctype="multipart/form-data">
    <div class="modal-body">
    <!--<div style="clear:both; text-align:left;">
        	<div style="float:left; width:100px; margin-top:4px;">State</div>
            <div style="float:left; margin-left:10px;">
                                    <select name="department_id" id="department_id">
									<option value="0">Select Department</option>
                                    
                                    <?php //echo show_dropdown_department(0,$vardepartment_id);?>
								</select></div>
            <div style="float:left; margin-left:10px;">
            	<div style="clear:both; color:#FF0000; margin-top:4px;" id="err_groupname">&nbsp;</div>
			</div>                
        </div>-->
       	<div style="clear:both; text-align:left;">
        	<div style="float:left; width:100px; margin-top:4px;">Category Root</div>
            <div style="float:left; margin-left:10px;">
                                    <select name="category" id="category">
									<option value="0">Select categories</option>
                                    
                                    <?php echo show_dropdown_category('','',$varParent);?>
								</select></div>
            <div style="float:left; margin-left:10px;">
            	
			</div>                
        </div>
        
    	<div style="clear:both; text-align:left;">
        	<div style="float:left; width:100px; margin-top:4px;">Category Name</div>
            <div style="float:left; margin-left:10px;"><input id="txtCategoryName" class="input-medium" type="text" name="txtCategoryName" value="<?php echo @$varCategoryName; ?>" _onkeyup="nospaces(this)"></div>
            <div style="float:left; margin-left:10px;">
            	<div style="clear:both; color:#FF0000; margin-top:4px;" id="err_groupname">&nbsp;</div>
			</div>                
        </div>
        
        <div style="clear:both; text-align:left;">
        	<div style="float:left; width:100px; margin-top:4px;">Image</div>
            <div style="float:left; margin-left:10px;">
             <input id="file" class="input-medium" type="file" name="file"  >
            <div id="image_preview">
            <?php if($_REQUEST['category_id'] > 0){ ?>
             <img src="../upload/cat/<?php echo @$arrInfo[0]['image_url'];?>" alt="img"  width="50" height="50" id="previewing"/> 
             <?php }else{ ?>
             <img src="../img/avatar.png" alt="img"  width="50" height="50" id="previewing"/> 
              <?php } ?>
             </div>
                                    </div>
            <div style="float:left; margin-left:10px;">
            	
			</div>                
        </div>
        
    </div>
    
    
		

    <div class="modal-footer">
    	<div style="float:left; color:#FF0000; margin-top:4px;" id="show_frmKeywordGroup_err">&nbsp;</div>
        <div style="float:right;">
        <button class="btn" onclick="return closeDiv();">Cancel</button>
        <button class="btn btn-primary" onclick="return save_add_edit_category(<?php  echo @$_REQUEST['category_id']; ?>)">Save</button>
        </div>
    </div>
    </form>
</div>
<script>
$(function() {
$("#file").change(function() {
$("#message").empty(); // To remove the previous error message
var file = this.files[0];
var imagefile = file.type;
var match= ["image/jpeg","image/png","image/jpg"];
if(!((imagefile==match[0]) || (imagefile==match[1]) || (imagefile==match[2])))
{
$('#previewing').attr('src','noimage.png');
$("#message").html("<p id='error'>Please Select A valid Image File</p>"+"<h4>Note</h4>"+"<span id='error_message'>Only jpeg, jpg and png Images type allowed</span>");
return false;
}
else
{
var reader = new FileReader();
reader.onload = imageIsLoaded;
reader.readAsDataURL(this.files[0]);
}
});
});
function imageIsLoaded(e) {
$("#file").css("color","green");
$('#image_preview').css("display", "block");
$('#previewing').attr('src', e.target.result);

$('#previewing').attr('width', '50px');
$('#previewing').attr('height', '50px');
};

</script>
	<?php
	$varHtml = ob_get_contents();
	ob_end_clean();
	echo $varHtml;
}

function delete_manufacturer(){
	$objManagement	=	new Management();
	if(@$_REQUEST['manufacturer_id'] > 0){ 
		$arrKeyWordReport = $objManagement->delete_manufacturer($_REQUEST['manufacturer_id']);
	}
}
function save_manufacturer_group_info(){
	$objManagement	=	new Management();
	$varCatgoryId	=	$objManagement->add_manufacturer_group_info();
	echo $varCatgoryId;
}
function add_manufacturer_info(){
	ob_start();
	
	$varGroupName = '';
    $varstate_id=0;
	if($_REQUEST['manufacturer_id'] > 0){
		$objManagement = new Management();
		
		
		$arrInfo = $objManagement->get_manufacturer_info($_REQUEST['manufacturer_id']);
		$varManufacturerName = trim($arrInfo[0]['manufacturer_name']);
       
	}
	?>
    <div style="clear:both;">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="d.close()">&#215;</button>
        <?php if($_REQUEST['manufacturer_id'] > 0){ 
            ?>
        <h3 id="myModalLabel1">Edit manufacturer name</h3>
        <?php } else { ?>
        <h3 id="myModalLabel1">Add manufacturer name</h3>
        <?php } ?>
	</div>
    <form id="frmManufacturerGroup" name="frmManufacturerGroup" method="get" class="frm_margin">
    <div class="modal-body">
       	
    	<div style="clear:both; text-align:left;">
        	<div style="float:left; width:100px; margin-top:4px;">Manufacturer Name</div>
            <div style="float:left; margin-left:10px;"><input id="txtManufacturerName" class="input-medium" type="text" name="txtManufacturerName" value="<?php echo @$varManufacturerName; ?>" _onkeyup="nospaces(this)"></div>
            <div style="float:left; margin-left:10px;">
            	<div style="clear:both; color:#FF0000; margin-top:4px;" id="err_groupname">&nbsp;</div>
			</div>                
        </div>
        
    </div>
		

    <div class="modal-footer">
    	<div style="float:left; color:#FF0000; margin-top:4px;" id="show_frmKeywordGroup_err">&nbsp;</div>
        <div style="float:right;">
        <button class="btn" onclick="return closeDiv();">Cancel</button>
        <button class="btn btn-primary" onclick="return save_add_edit_manufacturer(<?php  echo @$_REQUEST['manufacturer_id']; ?>)">Save</button>
        </div>
    </div>
    </form>
</div>
	<?php
	$varHtml = ob_get_contents();
	ob_end_clean();
	echo $varHtml;
}

function delete_department(){
	$objManagement	=	new Management();
	if(@$_REQUEST['department_id'] > 0){ 
		$arrKeyWordReport = $objManagement->delete_department($_REQUEST['department_id']);
	}
}
function save_department_group_info(){
	$objManagement	=	new Management();
	$varDepartmentId	=	$objManagement->add_department_group_info();
	echo $varDepartmentId;
}
function add_department_info(){
	ob_start();
	
	$varGroupName = '';
	if($_REQUEST['department_id'] > 0){
		$objManagement = new Management();		
		
		$arrInfo = $objManagement->get_department_info($_REQUEST['department_id']);
		$varDepartmentName = trim($arrInfo[0]['department_name']);
	}
	?>
    <div style="clear:both;">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="d.close()">&#215;</button>
        <?php if($_REQUEST['department_id'] > 0){ 
            ?>
        <h3 id="myModalLabel1">Edit Department name</h3>
        <?php } else { ?>
        <h3 id="myModalLabel1">Add Department name</h3>
        <?php } ?>
	</div>
    <form id="frmDepartmentGroup" name="frmDepartmentGroup" method="get" class="frm_margin">
    <div class="modal-body">
    	<div style="clear:both; text-align:left;">
        	<div style="float:left; width:100px; margin-top:4px;">Department Name</div>
            <div style="float:left; margin-left:10px;"><input id="txtDepartmentName" class="input-medium" type="text" name="txtDepartmentName" value="<?php echo @$varDepartmentName; ?>" _onkeyup="nospaces(this)"></div>
            <div style="float:left; margin-left:10px;">
            	<div style="clear:both; color:#FF0000; margin-top:4px;" id="err_groupname">&nbsp;</div>
			</div>                
        </div>
    </div>
		

    <div class="modal-footer">
    	<div style="float:left; color:#FF0000; margin-top:4px;" id="show_frmKeywordGroup_err">&nbsp;</div>
        <div style="float:right;">
        <button class="btn" onclick="return closeDiv();">Cancel</button>
        <button class="btn btn-primary" onclick="return save_add_edit_department(<?php  echo @$_REQUEST['department_id']; ?>)">Save</button>
        </div>
    </div>
    </form>
</div>
	<?php
	$varHtml = ob_get_contents();
	ob_end_clean();
	echo $varHtml;
}

function delete_product(){
	$objManagement	=	new Management();
	if(@$_REQUEST['product_id'] > 0){ 
		$arrKeyWordReport = $objManagement->delete_product($_REQUEST['product_id'], $_REQUEST['site_id']);
	}
}
function add_product_info(){
	$objManagement	=	new Management();
	if(@$_REQUEST['product_id'] > 0){ 
		$arrKeyWordReport = $objManagement->add_product($_REQUEST['product_id'], $_REQUEST['site_id']);
	}
?>
   <div style="clear:both;">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="d.close()">&#215;</button>
        <?php if($_REQUEST['product_id'] > 0){ 
            ?>
        <h3 id="myModalLabel1">Edit Product name</h3>
        <?php } else { ?>
        <h3 id="myModalLabel1">Add Product name</h3>
        <?php } ?>
	</div>
    <form id="frmDepartmentGroup" name="frmDepartmentGroup" method="get" class="frm_margin">
    <div class="modal-body">
    	<div style="clear:both; text-align:left;">
        	<div style="float:left; width:100px; margin-top:4px;">Product Name</div>
            <div style="float:left; margin-left:10px;"><input id="txtDepartmentName" class="input-medium" type="text" name="txtDepartmentName" value="<?php echo @$varDepartmentName; ?>" _onkeyup="nospaces(this)"></div>
            <div style="float:left; margin-left:10px;">
            	<div style="clear:both; color:#FF0000; margin-top:4px;" id="err_groupname">&nbsp;</div>
			</div>                
        </div>
    </div>
		

    <div class="modal-footer">
    	<div style="float:left; color:#FF0000; margin-top:4px;" id="show_frmKeywordGroup_err">&nbsp;</div>
        <div style="float:right;">
        <button class="btn" onclick="return closeDiv();">Cancel</button>
        <button class="btn btn-primary" onclick="return save_add_edit_department(<?php  echo @$_REQUEST['department_id']; ?>)">Save</button>
        </div>
    </div>
    </form>
</div>
	<?php
	$varHtml = ob_get_contents();
	ob_end_clean();
	echo $varHtml;
}
?>
