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
            <h4><i class="icon-reorder"></i>Brand Management</h4>
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
              ?>
              <div class="clear-both">     
                <a href="<?php echo admin_url()?>brand/add" class="btn btn-primary">Add Brand</a>
              </div>
              <br>
              <table class="table table-striped table-bordered" id="sample_1">
                <thead>
                    <tr>
                        <th>Id</th> 
                        <th class="hidden-phone">Brand Name</th>
                        <th class="hidden-phone">Action</th>
                    </tr>
                </thead>
                <tbody>
                  <?php
                  if(!empty($brand_details)) 
                  {
                    $i = 1;
                    foreach($brand_details as $result) 
                    {
                      ?>
                      <tr class="odd gradeX">
                        <td><?php echo $i;?></td>
                        <td class="hidden-phone"><?php echo $result->brand_name;?></td>
                        <td class="hidden-phone"><center><?php echo '<a href="' . admin_url() . 'brand/edit/' . $result->brand_id . '" title="Edit this Brand">Edit</a>';?> | <?php echo '<a href="' . admin_url() . 'delete_brand/' . $result->brand_id . '" title="Delete this Brand details">Delete</a>';?> </center>
                        </td>
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
          <?php 
        }
        else if($view=='add')
        { 
          ?>
          <div class="widget">
            <div class="widget-title">
              <h4><i class="icon-reorder"></i>Add new Brand</h4>
              <span class="tools">
                 <a href="javascript:;" class="icon-chevron-down"></a>
                 <a href="javascript:;" class="icon-remove"></a>
              </span>
            </div>
            <div class="widget-body form">
              <!-- BEGIN FORM-->
              <form action="add" class="form-horizontal" id="add_brand" method="post" enctype="multipart/form-data">
                <div class="control-group">
                  <label class="control-label">Type Name</label>
                  <div class="controls">
                    <input type="text" required="" name="brand_name" id="brand_name" class="span6 " />
                  </div>
                </div>
                <div class="form-actions">
                  <input type="submit" name="brand_submit" value="Submit" class="btn btn-success">
                  <a href="<?php echo $cancel_url; ?>" class="btn">Cancel</a>
                </div>
              </form>
              <!-- END FORM-->           
            </div>
          </div>
          <?php 
        }
        else
        { 
          $id = $this->uri->segment(4);
          ?>
          <div class="widget">
            <div class="widget-title">
              <h4><i class="icon-reorder"></i>Edit Brand</h4>
              <span class="tools">
                 <a href="javascript:;" class="icon-chevron-down"></a>
                 <a href="javascript:;" class="icon-remove"></a>
              </span>
            </div>
            <div class="widget-body form">
              <!-- BEGIN FORM-->
              <form action="" class="form-horizontal" id="edit_brand" method="post" enctype="multipart/form-data">
                <div class="control-group">
                  <label class="control-label">Type Name</label>
                  <div class="controls">
                    <input type="text" name="edit_brand_name" required="" id="edit_brand_name" value="<?php echo $edit_brand->brand_name; ?>" class="span6 " />
                  </div>
                </div>
                <div class="form-actions">
                  <input type="submit" name="update_brand_submit" value="Update" class="btn btn-success">
                  <a href="<?php echo $cancel_url; ?>" class="btn">Cancel</a>
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

<script src="<?php echo js_url();?>table-editable.js"></script>
<script src="<?php echo front_url();?>assets/script/creative.js"></script>

<script type="text/javascript">
jQuery(document).ready(function() {       
  // initiate layout and plugins
  App.init();
});
</script>

