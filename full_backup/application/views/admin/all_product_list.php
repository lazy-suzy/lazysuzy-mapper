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
                    <?php
                    if($site_details)
                    {
                        echo '<select name="site_id" id="site_id" style="width:100%;" class="form-control">
                                <option value="">Select Sitename</option>';
                        foreach ($site_details as $key => $site_data) {
                          echo '<option value="'.$site_data->product_tb_name.'">'.$site_data->site_name.'</option>';
                        }
                        echo '</select>';
                    }
                    ?>
                  </div>
              </div>

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
                      <?php foreach ($category as $newcat){
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
              </div>
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
            if($result)
            {
                foreach($result as $key => $row) 
                {   
                    if(strpos($row->product_url,'cb2') !== false) 
                    {
                      $pro_id = 1;
                    } 
                    if(strpos($row->product_url,'pier1') !== false)
                    {
                      $pro_id = 2;
                    }
                    if(strpos($row->product_url,'potterybarn') !== false)
                    {
                      $pro_id = 3;
                    }
                    echo '<tr>
                            <td>'.$row->product_name.'</td>
                            <td>'.$row->product_sku.'</td>
                            <td> - </td>
                            <td>'.$row->color.'</td>
                            <td>'.$row->product_url.'</td>
                            <td>'.$row->product_category.'</td>
                            <td>'.$row->price.'</td>
                            <td><a href="' . admin_url() . 'products/edit/'.$pro_id .'/'. $row->id . '/all" title="Edit this Product">Edit</a>';?> | <?php echo '<a href="' . admin_url() . 'delete_product/' .$pro_id .'/'. $row->id . '/all" title="Delete this Product">Delete</a></td>
                          </tr>';
                }
            }
            ?>
            </tbody>
          </table>

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

              <input type="hidden" class="hidden_pagination" value="0">
              <!-- <ul>
                          <li class="next disabled" onclick="next_pagination()">next</li>
                      </ul> -->
              
              <!-- <div id='pagination'></div> -->
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

<script type='text/javascript'>

  function previous_pagination()
  {
      var prev_count = parseInt($('.hidden_pagination').val()) - parseInt(15);
      if(prev_count > 0)
      {
          $('.hidden_pagination').val(prev_count);
      }
      else
      {
          $('.hidden_pagination').val('15');
      }
      $("#new_table_yui_grid").trigger('click');
  }
  function next_pagination()
  {
      var prev_count = parseInt($('.hidden_pagination').val()) + parseInt(15);
     
      $('.hidden_pagination').val(prev_count);

      $("#new_table_yui_grid").trigger('click');
  }


  $(document).ready(function()
  {
    $("#new_table_yui_grid").click(function()
    {
      $('.php-pagination').hide();
      $('.ajax-pagination').show();

      var per_row = $('.hidden_pagination').val();
      

      var product_name          = jQuery('#product_name').val();
      var product_sku           = jQuery('#product_sku').val();
      var product_category_src  = jQuery('#product_category_src').val();
      var site_id               = jQuery('#site_id').val();
      var pagno = per_row;

      $.ajax({
          url: '<?php echo admin_url();?>load_all_Record', 
          type: "POST",             
          data: 'pagno='+pagno+'&product_name='+encodeURI(product_name)+'&product_sku='+encodeURI(product_sku)+'&product_category_src='+encodeURI(product_category_src)+'&site_id='+encodeURI(site_id)+'&type=filter', 
          dataType: 'json',     
          success: function(response) 
          {
           $('#pagination').html(response.pagination);
          newcreateTable(response.result,response.row,response.total_rows);  
          }
        });
    });

    // Create table list
    function newcreateTable(result,sno, total_rows)
    {
      sno = Number(sno);
      total_rows = Number(total_rows);
      $('.postsList tbody').empty();
      $('.total_count').html(total_rows);

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