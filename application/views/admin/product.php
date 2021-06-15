<?php $this->load->view('admin/header');?>
<?php $this->load->view('admin/sidebar');?>
<link rel="stylesheet" type="text/css" href="<?php echo front_url();?>assets/style/paginator.css" />
<link rel="stylesheet" type="text/css" href="<?php echo front_url();?>assets/style/datatable.css" />
<link rel="stylesheet" type="text/css" href="<?php echo front_url();?>assets/style/main-count.css" />
<link rel="stylesheet" type="text/css" href="<?php echo front_url();?>assets/gritter/css/jquery.gritter.css" />
<link rel="stylesheet" type="text/css" href="<?php echo css_url();?>DT_bootstrap.css" />
<script type="text/javascript" src="<?php echo front_url();?>assets/ckeditor/ckeditor.js"></script>
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

  .pagination {
      display: inline-block;
      float: right;
  }

  .pagination a {
      color: black;
      float: left;
      padding: 8px 16px;
      text-decoration: none;
      transition: background-color .3s;
      border: 1px solid #ddd;
  }

  .pagination a.active {
      background-color: #32C2CD;
      color: white;
      border: 1px solid #32C2CD;
  }

  .pagination a:hover:not(.active) {background-color: #ddd;}
</style><div id="main-content">
  <div class="container-fluid">
	  <?php $this->load->view('admin/bread_crumb');?>
    <div class="row-fluid">
		  <div class="span12">
        <!-- BEGIN SAMPLE TABLE widget-->
        <?php 
        if($view=='view_all')
        {   
          $id = $this->uri->segment(3); 
          ?>
          <div class="widget">
            <div class="widget-title">
              <h4><i class="icon-reorder"></i>Products</h4>
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

              <div class="report_option" >                        
                <div class="clear-both" >
                  <input type="text" name="product_name" style="width:95%;" id="product_name"  placeholder="Product name"/>
                </div>
              </div>
              
              <div class="report_option">                              
                <div class="clear-both">
                  <input type="text" name="product_sku" style="width:95%;"  id="product_sku" placeholder="Product Sku"/>
                </div>
              </div>
              
              <div class="report_option">                           
                <div class="clear-both">
                  <select name="product_category_src" style="width:100%;"  id="product_category_src" class="form-control">
                    <option value="">Select categories</option>
                    <?php 
                    foreach($category as $newcat)
                    {
                      ?>
                      <option value="<?php echo $newcat->category_name; ?>"><?php echo $newcat->category_name; ?></option>
                      <?php
                    } 
                    ?>
                  </select>
                </div>
              </div>
              
              <input type="hidden" name="site_id" id="site_id" value="<?php echo $id;?>">
              <div class="report_option">                        
                <div class="clear-both">
                  <button class="btn btn-success" id="new_table_yui_grid" title="Preview results" ><i class="icon-refresh"></i> Filter </button>
                  <a href="<?php echo admin_url()?>products/add/<?php echo $pro_id; ?>" class="btn btn-primary">Add </a>
                </div>
              </div> 

              <div class="clear-both" style="display:none;">     
                <a href="<?php echo admin_url()?>products/add/<?php echo $pro_id; ?>" class="btn btn-primary">Add Product</a>
              </div>
              <br>

              <div class="yui-dt-mask" style="display: none;"></div>

              <table class="table table-striped table-bordered postsList">
                <thead>
                  <tr>
                    <th class="hidden-phone">Product Name</th>
                    <th class="hidden-phone">Sku</th>
                    <th class="hidden-phone">Model Code</th>
                    <th class="hidden-phone">Color</th>
                    <th class="hidden-phone">URL</th>
                    <th class="hidden-phone"> Category</th>
                    <th class="hidden-phone">Price</th>
                    <th class="hidden-phone">Options</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $pro_id = $this->uri->segment(3);
                  if($result)
                  {
                      foreach($result as $key => $row) 
                      {
                          echo '<tr>
                                  <td>'.$row->product_name.'</td>
                                  <td>'.$row->product_sku.'</td>
                                  <td> - </td>
                                  <td>'.$row->color.'</td>
                                  <td>'.$row->product_url.'</td>
                                  <td>'.$row->product_category.'</td>
                                  <td>'.$row->price.'</td>



                                  <td><a href="' . admin_url() . 'products/edit/'.$pro_id .'/'. $row->id . '" title="Edit this Product">Edit</a>';?> | <?php echo '<a href="' . admin_url() . 'delete_product/' .$pro_id .'/'. $row->id . '" title="Delete this Product">Delete</a></td>
                                </tr>';
                      }
                  }
                  ?>
                </tbody>
              </table>

              <div class="pagination">
                <?php echo $first;?>
                <?php echo $next;?>
                <?php echo $previous;?>
                <?php echo $last;?>
              </div>
              
              <!-- <div id='pagination'></div> -->
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

                <?php if($site_id != 3) { ?>

                <div class="control-group">
                  <label class="control-label">Model Code</label>
                  <div class="controls">
                  
                      <input type="text" name="model_code" required="" id="model_code" class="span6" />
                  </div>
                </div>

                <?php } ?>


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
                  <label class="control-label">Product Image</label>
                  <div class="controls">
                      <input type="file" name="pro_image[]" id="pro_image[]" multiple="multiple" class="span6" required=""/> <!-- <img width="85" height="100" id="img1" src="#" alt="Image preview" /> -->
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
                <input type="hidden" name="site_id" id="site_id" value="<?php echo $id;?>">
                <div class="control-group">
                  <label class="control-label">Product Price</label>
                  <div class="controls">
                      <input type="text" name="pro_price" id="pro_price" class="span6" />
                  </div>
                </div>
                <div class="form-actions">
                  <input type="submit" name="category_submit" value="Submit" class="btn btn-success">
                  <a href="<?php echo $cancel_url; ?>"  class="btn">Cancel</a>
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
          $pro_id  = $this->uri->segment(5);
          
          if($site_id == 1)
          {
            $site_name = 'CB2';
          }
          else if($site_id == 2)
          { 
            $site_name = 'Pier-1';
          }
          else 
          { 
            $site_name = 'potterybarn'; 
          }
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

                <?php if($site_id != 3) { ?>

                <div class="control-group">
                  <label class="control-label">Model Code</label>
                  <div class="controls">
                  
                      <input type="text" name="edit_model_code" value="<?php echo $edit_product->model_code; ?>" required="" id="model_code" class="span6" />
                  </div>
                </div>

                <?php } ?>

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
                <?php 
                $i=1;
                if(!empty($edit_product->product_images))
                {
                  $all_images = explode(',', $edit_product->product_images);
                  foreach($all_images as $images)
                  {
                    ?>
                    <div class="control-group">
                      <label class="control-label">Product Image <?php echo $i;?></label>
                      <div class="controls">
                          <input type="file" name="edit_pro_image[]" multiple="multiple" class="span6"/><img src="/<?php echo $site_name; ?>/product_images/<?php echo $images;?>" width="50" height="110">
                      </div>
                    </div>
                    <?php
                    $i++;
                  }
                }
                else
                {
                  ?>
                  <div class="control-group">
                      <label class="control-label">Product Image</label>
                      <div class="controls">
                          <input type="file" name="edit_pro_image[]" class="span6" multiple="multiple"/><img src="/<?php echo $site_name; ?>/product_images/<?php echo $edit_product->product_images;?>">
                      </div>
                    </div>
                  <?php 
                } 
                ?>
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
                <input type="hidden" name="hidden_edit_pro_image" value="<?php echo $edit_product->images;?>">

                <input type="hidden" name="edit_site_id" value="<?php echo $site_id;?>">
                <input type="hidden" name="edit_pro_id" value="<?php echo $pro_id;?>">
                
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





<!-- Paginate -->
   

   <!-- Script -->
   <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> -->
   <script type='text/javascript'>
   $(document).ready(function(){
    
     // Detect pagination click
     $('#pagination').on('click','a',function(e)
     {
       e.preventDefault(); 
       var pageno = $(this).attr('data-ci-pagination-page');
       loadPagination(pageno);
     });

     //loadPagination(0);

     // Load pagination
     function loadPagination(pagno){
       $.ajax({
         url: '<?=admin_url()?>loadRecord/',
         data: 'pagno='+pagno,
         type: 'POST',
         dataType: 'json',
         success: function(response)
         {
            $('#pagination').html(response.pagination);
            createTable(response.result,response.row);
         }
       });
     }

      // Create table list
      function createTable(result,sno)
      {
        sno = Number(sno);
        
        $('.postsList tbody').empty();
        for(index in result)
        {
          var id = result[index].id;
          var product_name = result[index].product_name;
          var product_sku = result[index].product_sku;
          var model_code = result[index].model_code;
          var color = result[index].color;
          var product_url = result[index].product_url;
          var product_category = result[index].product_category;
          var price = result[index].price;
          sno+=1;

          var tr = "<tr>";
          tr += "<td>"+ product_name +"</td>";
          tr += "<td>"+ product_sku +"</td>";
          tr += "<td>"+ model_code +"</td>";
          tr += "<td>"+ color +"</td>";
          tr += "<td>"+ product_url +"</td>";
          tr += "<td>"+ product_category +"</td>";
          tr += "<td>"+ price +"</td>";
          tr += "<td><a href='<?php echo admin_url();?>products/edit/<?php echo $pro_id;?>/"+ id +"'>Edit</a> | <a href='<?php echo admin_url();?>delete_product/<?php echo $pro_id;?>/"+ id +"'>Delete</a></td>'";
          tr += "</tr>";
          $('.postsList tbody').append(tr);
 
        }
      }

      $("#new_table_yui_grid").click(function()
      {
        var product_name          = jQuery('#product_name').val();
        var product_sku           = jQuery('#product_sku').val();
        var product_category_src  = jQuery('#product_category_src').val();
        var pagno = 0;

        $.ajax({
            url: '<?=admin_url()?>loadRecord', 
            type: "POST",             
            data: 'pagno='+pagno+'&product_name='+encodeURI(product_name)+'&product_sku='+encodeURI(product_sku)+'&product_category_src='+encodeURI(product_category_src)+'&type=filter', 
            dataType: 'json',     
            success: function(response) 
            {
             $('#pagination').html(response.pagination);
            newcreateTable(response.result,response.row);  
            }
          });
      });

      // Create table list
      function newcreateTable(result,sno)
      {
        sno = Number(sno);
        $('.postsList tbody').empty();

        if(result == '')
        { 
          var tr = "<tr>";
          tr += "<td colspan='8'><center>No matching Records found</center></td>";
          tr += "</tr>";

          $('.postsList tbody').append(tr);
        }
        else
        {
          for(index in result)
          {
            var id = result[index].id;
            var product_name = result[index].product_name;
            var product_sku = result[index].product_sku;
            var model_code = result[index].model_code;
            var color = result[index].color;
            var product_url = result[index].product_url;
            var product_category = result[index].product_category;
            var price = result[index].price;
            sno+=1;

            var tr = "<tr>";
            tr += "<td>"+ product_name +"</td>";
            tr += "<td>"+ product_sku +"</td>";
            tr += "<td>"+ model_code +"</td>";
            tr += "<td>"+ color +"</td>";
            tr += "<td>"+ product_url +"</td>";
            tr += "<td>"+ product_category +"</td>";
            tr += "<td>"+ price +"</td>";
            tr += "<td><a href='<?php echo admin_url();?>products/edit/<?php echo $pro_id;?>/"+ id +"'>Edit</a> | <a href='<?php echo admin_url();?>delete_product/<?php echo $pro_id;?>/"+ id +"'>Delete</a></td>'";
            tr += "</tr>";
            $('.postsList tbody').append(tr);
   
          }
        }  
      }


    });
    </script>
