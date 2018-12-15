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
        <?php 
        if($view=='view_all')
        { 
          ?>
          <div class="widget">
            <div class="widget-title">
            <h4><i class="icon-reorder"></i>Product URL List</h4>
            <span class="tools">
              <a class="icon-chevron-down" href="javascript:;"></a>
              <!--<a class="icon-remove" href="javascript:;"></a>-->
            </span>
            </div>
            
            <div class="widget-body">
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

              <div class="report_option">                        
                         
              <div class="clear-both">
                <input type="text" name="common_search" style="width:95%;"  id="common_search" placeholder=""/>
              </div>
                    </div>
                    <div class="report_option">                        
                         
              <div class="clear-both">
                <select name="product_category" style="width:100%;"  id="product_category" class="form-control">
                                           <option value="">Select categories</option>
                      <?php foreach ($category as $newcat){
                        ?>
                        <option value="<?php echo $newcat->category_name; ?>"><?php echo $newcat->category_name; ?></option>
                        <?php
                      } 
                      ?>
                </select>
              </div>
                    </div>
                    <div class="report_option">                        
                        
              <div class="clear-both">
                 <button class="btn btn-success" id="new_table_yui_grid1" title="Preview results" ><i class="icon-refresh"></i> Filter </button>
              </div>
                    </div> 
             <!--  <div class="clear-both">     
                <a href="<?php echo admin_url()?>product_url/add/<?php echo $pro_id; ?>" class="btn btn-primary">Add Product URL</a>
              </div> -->
              <br>
              <table class="table table-striped table-bordered postsList1">
                  <thead>
                      <tr> 
                          <th class="hidden-phone">Product Name</th>
                          <th class="hidden-phone">Sku</th>
                          <th class="hidden-phone">URL</th>
                          <th class="hidden-phone"> Category</th>
                      </tr>
                  </thead>
                  <tbody>
                    <?php
                      if(!empty($result)) {
                          $i = 1;
                          foreach($result as $results) 
                          {
                            ?>
                          <tr class="odd gradeX">
                              <td class="hidden-phone"><?php echo $results->product_name;?></td>
                              <td class="hidden-phone"><?php echo $results->product_sku;?></td>
                              <td class="hidden-phone"><?php echo $results->product_url;?></td>
                              <td class="hidden-phone"><?php echo $results->product_category;?></td>
                          </tr>
                          <?php 
                          $i++;
                        }                   
                      } 
                      ?>
                      </tbody>
              </table>
              <!-- <div id='pagination1'></div> -->
              <div class="row-fluid">
                <div class="span6">
                  <div class="dataTables_info" id="sample_1_info">Total no of entries <span class="total_count"><?php echo $total_records; ?></span></div>
                </div>

                <div class="span6">
                    <div class="dataTables_paginate paging_bootstrap pagination php-pagination">
                        <ul>
                            <li class="active"><?php echo $first;?></li>
                            <li class="prev disabled"><?php echo $previous;?></li>
                            <li class="next disabled"><?php echo $next;?></li>
                            <li class="active"><?php echo $last;?></li>
                        </ul>
                    </div>

                    <div class="dataTables_paginate paging_bootstrap pagination ajax-pagination" style="display: none;">
                        <ul>
                            <li class="prev" ><a onclick="previous_pagination()"> ← Prev </a> </li>
                            <li class="next" ><a onclick="next_pagination()"> Next →  </a></li>
                        </ul>
                    </div>
                </div>
              </div>
            </div>
          </div>
          <?php 
        }
        else if($view=='add')
        { 
          $id = $this->uri->segment(4);
          ?>
          <div class="widget">
            <div class="widget-title">
              <h4><i class="icon-reorder"></i>Add new Products</h4>
              <span class="tools">
                 <a href="javascript:;" class="icon-chevron-down"></a>
                 <a href="javascript:;" class="icon-remove"></a>
              </span>
            </div>
            <div class="widget-body form">
              <!-- BEGIN FORM-->
              <form action="add/<?php echo $id;?>" class="form-horizontal" id="add_category" method="post" enctype="multipart/form-data">


                <div class="control-group">
                  <label class="control-label">Product name</label>
                  <div class="controls">
                    <input type="text" name="pro_name" required="" id="pro_name" class="span6 " />
                  </div>
                </div>
                <div class="control-group">
                  <label class="control-label">Product SKU</label>
                  <div class="controls">
                      <input type="text" name="pro_sku" required="" id="pro_sku" class="span6" />
                  </div>
                </div>
                <div class="control-group">
                  <label class="control-label">Model Code</label>
                  <div class="controls">
                  
                      <input type="text" name="model_code" required="" id="model_code" class="span6" />
                  </div>
                </div>
                <div class="control-group">
                  <label class="control-label">Color</label>
                  <div class="controls">
                      <input type="text" name="color" required="" id="color" class="span6" />
                  </div>
                </div>
                <div class="control-group">
                  <label class="control-label">Product URL</label>
                  <div class="controls">
                      <input type="text" name="pro_url" required="" id="pro_url" class="span6" />
                  </div>
                </div>

                <div class="control-group">
                  <label class="control-label">Category Name</label>
                  <div class="controls">
                    <select class="span6 " required="" data-placeholder="Choose a Category" name="cat_name" tabindex="1">
                      <option value="">Select categories</option>
                      <?php foreach ($category as $newcat){
                        ?>
                        <option value="<?php echo $newcat->category_id; ?>"><?php echo $newcat->category_name; ?></option>
                        <?php
                      } 
                      ?>
                    </select>
                  </div>
                </div>
                <input type="hidden" name="site_id" value="<?php echo $id;?>">
                <div class="control-group">
                  <label class="control-label">Product Price</label>
                  <div class="controls">
                      <input type="text" name="pro_price" id="pro_price" class="span6" />
                  </div>
                </div>

                <div class="form-actions">
                  <input type="submit" name="category_submit" value="Submit" class="btn btn-success">
                  <button type="reset" class="btn">Cancel</button>
                </div>
              </form>
              <!-- END FORM-->           
            </div>
          </div>
          <?php 
        }
        else
        { 
          $site_id = $this->uri->segment(4);
          //echo "<pre>";print_r($edit_category);exit;
          ?>
          <div class="widget">
            <div class="widget-title">
              <h4><i class="icon-reorder"></i>Edit Product</h4>
              <span class="tools">
                 <a href="javascript:;" class="icon-chevron-down"></a>
                 <a href="javascript:;" class="icon-remove"></a>
              </span>
            </div>
            <div class="widget-body form">
              <!-- BEGIN FORM-->
              <form action="" class="form-horizontal" id="edit_products" method="post" enctype="multipart/form-data">
                <div class="control-group">
                  <label class="control-label">Product name</label>
                  <div class="controls">
                    <input type="text" name="edit_pro_name" required="" value="<?php echo $edit_product->product_name; ?>" id="pro_name" class="span6 " />
                  </div>
                </div>
                <div class="control-group">
                  <label class="control-label">Product SKU</label>
                  <div class="controls">
                      <input type="text" name="edit_pro_sku" value="<?php echo $edit_product->product_sku; ?>" required="" id="pro_sku" class="span6" />
                  </div>
                </div>
                <div class="control-group">
                  <label class="control-label">Model Code</label>
                  <div class="controls">
                  
                      <input type="text" name="edit_model_code" value="<?php echo $edit_product->model_code; ?>" required="" id="model_code" class="span6" />
                  </div>
                </div>
                <div class="control-group">
                  <label class="control-label">Color</label>
                  <div class="controls">
                      <input type="text" name="edit_color" value="<?php echo $edit_product->color; ?>" required="" id="color" class="span6" />
                  </div>
                </div>
                <div class="control-group">
                  <label class="control-label">Product URL</label>
                  <div class="controls">
                      <input type="text" name="edit_pro_url" value="<?php echo $edit_product->product_url; ?>" required="" id="pro_url" class="span6" />
                  </div>
                </div>

                <div class="control-group">
                  <label class="control-label">Category Name</label>
                  <div class="controls">
                    <select class="span6 " required="" data-placeholder="Choose a Category" name="edit_cat_name" tabindex="1">
                      <option value="">Select categories</option>
                      <?php foreach ($category as $newcat){
                        ?>
                        <option value="<?php echo $newcat->category_id; ?>"><?php echo $newcat->category_name; ?></option>
                        <?php
                      } 
                      ?>
                    </select>
                  </div>
                </div>
                <input type="hidden" name="edit_site_id" value="<?php echo $site_id;?>">
                <div class="control-group">
                  <label class="control-label">Product Price</label>
                  <div class="controls">
                      <input type="text" name="edit_pro_price" value="<?php echo $edit_product->price; ?>" id="pro_price" class="span6" />
                  </div>
                </div>

                <div class="form-actions">
                  <input type="submit" name="update_product_submit" value="Submit" class="btn btn-success">
                  <button type="reset" class="btn">Cancel</button>
                </div>
              </form>
              <!-- END FORM-->           
            </div>
          </div>
          <?php 
        }
        ?>

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
jQuery(document).ready(function() {       
  // initiate layout and plugins
  App.init();
});


function delete_category(cid){
  jConfirm('Are you sure?','Delete Category', function(r) {
    
      if(r){ 
        jQuery.ajax({
        url: "../management/ajax-common",
        data: "action=delete_category&category_id="+cid+"&rand="+Math.random(),
          success:function(response){
            
              table_yui_grid();
            
          }
        });
      }
  
    });
  return false;
}
</script>

<script type='text/javascript'>
   $(document).ready(function(){
      
       
     // Detect pagination click
     $('#pagination1').on('click','a',function(e)
     {
       e.preventDefault(); 
       var pageno = $(this).attr('data-ci-pagination-page');
       loadPagination(pageno);
     });

     //loadPagination(0);

     // Load pagination
    /* function loadPagination(pagno){
       $.ajax({
         url: '<?php echo admin_url();?>newloadRecord/',
         data: 'pagno='+pagno,
         type: 'POST',
         dataType: 'json',
         success: function(response)
         {
            $('#pagination1').html(response.pagination);
            createTable(response.result,response.row);
         }
       });
     }*/

      // Create table list
      function createTable(result,sno)
      {
        sno = Number(sno);
         
        $('.postsList1 tbody').empty();
        for(index in result)
        {
          var id = result[index].id;
          var product_name = result[index].product_name;
          var product_sku = result[index].product_sku;
          var product_url = result[index].product_url;
          var product_category = result[index].product_category;
           
          sno+=1;

          var tr = "<tr>";
          tr += "<td>"+ product_name +"</td>";
          tr += "<td>"+ product_sku +"</td>";
          tr += "<td>"+ product_url +"</td>";
          tr += "<td>"+ product_category +"</td>";
          tr += "</tr>";
          $('.postsList1 tbody').append(tr);
 
        }
      }


      $("#new_table_yui_grid1").click(function(){
       
        var common_search    = jQuery('#common_search').val();
        var product_category = jQuery('#product_category').val();
        var pagno = 0;

        $.ajax({
            url: '<?php echo admin_url();?>newloadRecord', 
            type: "POST",             
            data: 'pagno='+pagno+'&common_search='+encodeURI(common_search)+'&product_category='+encodeURI(product_category)+'&type=filter', 
            dataType: 'json',     
            success: function(response) 
            {
             $('#pagination1').html(response.pagination);
            newcreateTable(response.result,response.row);  
            }
          });
      });

      // Create table list
      function newcreateTable(result,sno)
      {
        sno = Number(sno);
        $('.postsList1 tbody').empty();

        if(result == '')
        { 
          var tr = "<tr>";
          tr += "<td colspan='4'><center>No matching Records found</center></td>";
          tr += "</tr>";

          $('.postsList1 tbody').append(tr);
        }
        else
        {
          for(index in result)
          {
            var id                = result[index].id;
            var product_name      = result[index].product_name;
            var product_sku       = result[index].product_sku;
            var product_url       = result[index].product_url;
            var product_category  = result[index].product_category;
             
            sno+=1;

            var tr = "<tr>";
            tr += "<td>"+ product_name +"</td>";
            tr += "<td>"+ product_sku +"</td>";
            tr += "<td>"+ product_url +"</td>";
            tr += "<td>"+ product_category +"</td>";
            tr += "</tr>";
            $('.postsList1 tbody').append(tr);
   
          }
        }  
      } 
        

    });
    </script>

     