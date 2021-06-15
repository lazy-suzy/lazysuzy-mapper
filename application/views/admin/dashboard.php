<?php $this->load->view('admin/header');?>
<?php $this->load->view('admin/sidebar');?>
<style type="text/css">
#datatable table{ width:100% !important; text-align:center; }
.yui-dt-paginator{text-align: center;}
.modal-body{ text-align:center;}
.yui-dt th, .yui-dt td{height:40px;}
 
.modal.fade.in {
  top: 10%;
  left: 46%;
}
.dialog .modal {
  left: 38%!important;
  width:800px!important;
}
 th {
  background: #D8D8DA !important;
  text-align: center !important;
  }
</style>
<div id="main-content">
<div class="container-fluid">
	<?php $this->load->view('admin/bread_crumb');?>
      <div class="row-fluid">
		<div class="span12">
          <!-- BEGIN SAMPLE TABLE widget-->
          <div class="widget">
             <div class="widget-title">
                <h4><i class="icon-reorder"></i>Report</h4>
                <span class="tools">
                <a class="icon-chevron-down" href="javascript:;"></a>
                <!--<a class="icon-remove" href="javascript:;"></a>-->
                </span>
             </div>
             <div class="widget-body">
             	<?php 
              $error = $this->session->flashdata('error');
              if($error != '') 
              {
                echo '<div class="alert alert-danger">
                  <button aria-hidden="true" data-dismiss="alert" class="close" type="button">&#10005;</button>'.$error.'</div>';
              }
              $success = $this->session->flashdata('success');
              if($success != '') {
                  echo '<div class="alert alert-success">
                  <button aria-hidden="true" data-dismiss="alert" class="close" type="button">&#10005;</button>'.$success.'</div>';
              } 
              ?> <a class="btn btn-primary pull-right" href="<?php admin_url();?>view_all_products">View all products</a><br><br>
                <table class="table table-striped table-bordered" id="sample_1">
                    <thead>
                        <tr>
                            <th>Id</th> 
                            <th>Site Name</th>
                            <th>Frequently</th>
                            <th>Site URL</th>
                            <th>Product Count</th>
                            <th>Status</th>
                            <th>Cron start date</th>
                            <!-- <th>Cron end date</th> -->
                            <th>Last Updated</th>
                            <th>Options</th>
                        </tr>
                    </thead>
                    <tbody>
                    	<?php
                        if($site_details->num_rows() > 0) {
                            $i = 1;
                            foreach($site_details->result() as $result) 
                            {
                            	if($result->cron_frq > 1)
                            	{
                            		$sss = 's';
                            	}else
                            	{
                            		$sss = '';
                            	}

                            	if($result->status == 0)
	                            {
									$cron_status ='<a href="changeStatus/'.$result->site_id.'/1" style="cursor:pointer;">Pause</a>';
								}else{
									$cron_status ='<a href="changeStatus/'.$result->site_id.'/0" style="cursor:pointer;">Active</a>';
								}

								if($result->status == 0)
								$options = '<span><a href="'.base_url().''.$result->csv_url.'"  download="">Download CSV</a></span><span>&nbsp;|&nbsp;</span><span><a href="../'.$result->sql_url.'" download="">Download SQL</a></span><span>&nbsp;|&nbsp;</span><span><a href="product_url_list/'.$result->site_id.'" >View Product URL</a></span><span>&nbsp;|&nbsp;</span><span><a href="product_list/'.$result->site_id.'" >View All Products</a></span>';
							else
								$options = '<span style="color:red;">Please activate the site status</span>';

                            	?>
                        		<tr class="odd gradeX">
                        			<td><?php echo $i;?></td>
		                            <td class="hidden-phone"><?php echo $result->site_name;?></td>
		                            <td class="hidden-phone"><a href="#myModal" data-select-val="<?php echo $result->cron_frq;?>" data-select-day="<?php echo $result->cron_format;?>" data-siteid="<?php echo $result->site_id;?>" data-site-id='<?php echo $result->site_name;?>' class="sasbtn"><?php echo $result->cron_frq;?> <?php echo $result->cron_format;?><?php echo $sss;?></a></td> <!-- onClick="interval(<?php //echo $result->site_id;?>);" -->
		                            <td class="center hidden-phone"><?php echo $result->site_url;?></td>
		                            <td class="hidden-phone"><?php echo $result->product_count;?></td>
		                            <td><?php echo $cron_status;?></td>
		                            <td class="hidden-phone"><?php echo $result->cron_start_date;?></td>
		                            <!-- <td class="hidden-phone"><?php echo $result->modified_on;?></td> -->
		                            <td class="hidden-phone"><?php echo $result->modified_on;?></td>
		                            <td class="hidden-phone"><?php echo $options;?></td>
                        		</tr>
                        		<?php 
                        		$i++;
                        	}                   
                        }	
                        ?>
                        
                        </tbody>
                </table>

             </div>

             </div>
             
          </div>
          <!-- END SAMPLE TABLE widget-->
       </div>
    </div>
   <!--Bulk add-->
   
   
   
   <!--Bulk add--> 
</div>
</div>

<?php $this->load->view('admin/footer');?>

<script type="text/javascript">
	//var News_id="{$smarty.session.cur_select_comp_id}";
</script>
<!-- {literal} -->
<!-- FOR YAHOO DATA GRID -->
<script type="text/javascript" src="<?php echo front_url();?>assets/script/grid/yahoo-dom-event.js"></script>
<script type="text/javascript" src="<?php echo front_url();?>assets/script/grid/connection-min.js"></script>
<script type="text/javascript" src="<?php echo front_url();?>assets/script/grid/json-min.js"></script>
<script type="text/javascript" src="<?php echo front_url();?>assets/script/grid/element-min.js"></script>
<script type="text/javascript" src="<?php echo front_url();?>assets/script/grid/paginator-min.js"></script>
<script type="text/javascript" src="<?php echo front_url();?>assets/script/grid/datasource-min.js"></script>
<script type="text/javascript" src="<?php echo front_url();?>assets/script/grid/datatable-min.js"></script>
<!-- FOR YAHOO DATA GRID -->




<script src="<?php echo js_url();?>table-editable.js"></script>

<script src="<?php echo front_url();?>assets/script/creative.js"></script>

<script type="text/javascript">
	function changeStatus(site_id,status)
	{
		
	}
</script>


<!-- Side bar script start -->
<script type="text/javascript">
var urlRequest = 'dashboard';
/*var urlRequest1=urlRequest.split("?");
if(urlRequest1.length > 0){
urlRequest=urlRequest1[0];
}else{
urlRequest=urlRequest;
}*/
//alert(urlRequest);
jQuery(document).ready(function(){ 
	jQuery('.has-sub ul.sub li').each(function(){
		if(urlRequest=='add-user-competitor'){
			urlRequest = 'user-management';
		}
		//alert(jQuery(this).find('a').attr('href')+'=='+urlRequest);
		var getUrlNew = jQuery(this).find('a').attr('href').split('/');
		//alert(getUrlNew[2]);
		/*var getUrl = jQuery(this).find('a').attr('href').match(urlRequest);
		console.log(this);*/
		var getUrl = getUrlNew[2];
	
		if(getUrl!== null && getUrl==urlRequest){
			jQuery(this).addClass('active');
			jQuery(this).parent().parent().addClass('has-sub active');
		} else {
			jQuery(this).removeClass('active');
		}
		
	});
		var pagename=urlRequest.split("?");		
		if(pagename[0]=='card-view'){
		jQuery('#firstTab').addClass('active');
		//jQuery('#firstTab').parent().parent().addClass('has-sub active');
		}
});
</script>
<!-- End sidebar script -->


<script type="text/javascript">
jQuery(document).ready(function() {       
	// initiate layout and plugins
	App.init();
	jQuery('body').addClass('yui-skin-sam');
	//videoad_table_yui_grid();
	table_yui_grid();
});

function table_yui_grid(){
	var max_result	= 15;
	
	
	YAHOO.example.DynamicData = function() {
		// Column definitions
		var myColumnDefs = [ // sortable:true enables sorting
			{key:"site_name",	label:"Site Name"},
			{key:"cron_frq",	label:"Frequently"},
			{key:"site_url",	label:"Site URL"},
			{key:"product_count",	label:"Product Count"},			
			{key:"cron_status",	label:"Status"},
			{key:"cron_last_update",	label:"Last Updated"},		
			{key:"options",label:"Options" }	
		];
		// DataSource instance
		var myDataSource = new YAHOO.util.DataSource("../json_proxy.php?");
		myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
		myDataSource.responseSchema = {
			resultsList: "records",
			fields: [
				{key:"site_name"},
				{key:"cron_frq"},
				{key:"site_url"},
				{key:"product_count"},				
				{key:"cron_status"},
				{key:"cron_last_update"},				
				{key:"options"}
			],
			// Access to values in the server response
			metaFields: {
				totalRecords: "totalRecords", 
				startIndex: "startIndex"
			}
			
		};
		
		// Customize request sent to server to be able to set total # of records
		var generateRequest = function(oState, oSelf) {
			// Get states or use defaults
			
			 oState = oState || { pagination: null, sortedBy: null };
			var sort = (oState.sortedBy) ? oState.sortedBy.key : "site_name";
			var dir = (oState.sortedBy && oState.sortedBy.dir === YAHOO.widget.DataTable.CLASS_DESC) ? "desc" : "asc";
			//var startIndex = (oState.pagination) ? oState.pagination.recordOffset : 0;
			var startIndex = (oState.pagination) ? oState.pagination.recordOffset : 0;
			var results = (oState.pagination) ? oState.pagination.rowsPerPage : max_result;
	
			// Build custom request

			return  "sort=" + sort +
					"&dir=" + dir +
					"&startIndex=" + startIndex +
					"&results=" +results+
					"&a=all_site"
		};
	
		// DataTable configuration
		var myConfigs = {
			generateRequest: generateRequest,
			initialRequest: generateRequest(), // Initial request for first page of data
			dynamicData: true, // Enables dynamic server-driven data
			sortedBy : {key:"site_name", dir:YAHOO.widget.DataTable.CLASS_ASC}, // Sets UI initial sort arrow
			paginator: new YAHOO.widget.Paginator({ rowsPerPage:max_result}) // Enables pagination 
		};
		
		// DataTable instance
		var myDataTable = new YAHOO.widget.DataTable("datatable", myColumnDefs, myDataSource, myConfigs);
		// Update totalRecords on the fly with values from server
		myDataTable.doBeforeLoadData = function(oRequest, oResponse, oPayload) {
			oPayload.totalRecords = oResponse.meta.totalRecords;
			oPayload.pagination.recordOffset = oResponse.meta.startIndex;
			return oPayload;
		};
		// Set width and height as string values
		/*var myDataTableXY = new YAHOO.widget.ScrollingDataTable("datatable", myColumnDefs,
				myDataSource, {width:"800px", height:"400px"});*/
		// Set width as a string value 
		
		return {
			ds: myDataSource,
			dt: myDataTable
		};
			
	}(); 
}
</script>
<!-- {/literal} -->
<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Update Cron timing</h4>
      </div>
      <div class="modal-body">
      	<form class="form-control" action="update_cron_frequency" method="post">
	        <div class="form-group">
	            <label for="recipient-name" class="control-label">Site name:</label>
	            <input type="text" value="" name="site_name" readonly="" class="form-control" id="site_name">
	        </div>
	        <div class="form-group neqw1">
	            <label for="message-text" required="" class="control-label">Number of (Days / Weeks):</label>
	            <select name="cron_frequency_number" id="cron_frequency_number">
	            	<option value="1">1</option>
	            	<option value="2">2</option>
	            	<option value="3">3</option>
	            	<option value="4">4</option>
	            	<option value="5">5</option>
	            	<option value="6">6</option>
	            </select>
	            <span class="newnew1">
	            <select name="cron_frequency_days" id="cron_frequency_days" class="newnew1">
	            	<option value="day">Day</option>
	            	<option value="week">Week</option>
	            </select>
	            </span>
	        </div>
	        <input type="hidden" name="site_id" id="site_id">
        
      </div>
      <div class="modal-footer">
        <button type="submit" name="submit" value="Submit" class="btn btn-primary">Update</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
      </form>

    </div>
  </div>
</div>
<script type="text/javascript">
	/*function interval(site_id)
	{
	    
	}*/
	jQuery(document).ready(function() {
		// executes when HTML-Document is loaded and DOM is ready
		console.log("document is ready");
  		jQuery('.sasbtn[href^=#]').click(function(e){
	    e.preventDefault();
	    var href = jQuery(this).attr('href');
	    jQuery(href).modal('toggle');

	    var site_id = $(this).data("site-id");
	    var siteid  = $(this).data("siteid");
	    $('#site_name').val(site_id);
	    $('#site_id').val(siteid);
  		
  		var select_day = $(this).data("select-day");
  		var select_val = $(this).data("select-val");
  		$(".neqw1 select").val(select_val);
  		$(".newnew1 select").val(select_day);
  		});
	});  
</script>
