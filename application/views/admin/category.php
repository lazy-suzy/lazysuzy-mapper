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
            <h4><i class="icon-reorder"></i>Searches</h4>
            <span class="tools">
              <a class="icon-chevron-down" href="javascript:;"></a>
              <!--<a class="icon-remove" href="javascript:;"></a>-->
            </span>
            </div>
            
            <div class="widget-body">
              <?php 
              /*$error = $this->session->flashdata('error');
              if($error != '') {
                  echo '<div class="alert alert-danger">
                  <button aria-hidden="true" data-dismiss="alert" class="close" type="button">&#10005;</button>'.$error.'</div>';
              }
*/              $success = $this->session->flashdata('success');
              if($success != '') {
                  echo '<div class="alert alert-success">
                  <button aria-hidden="true" data-dismiss="alert" class="close" type="button">&#10005;</button>'.$success.'</div>';
              } 
              ?>
              <div class="clear-both">     
                <a href="<?php echo admin_url()?>category/add" class="btn btn-primary">Add Category</a>
              </div>
              <br>
              <table class="table table-striped table-bordered" id="sample_1">
                  <thead>
                      <tr>
                          <th>Id</th> 
                          <th class="hidden-phone">Category Name</th>
                          <th class="hidden-phone">Parent category</th>
                          <th class="hidden-phone">Action</th>
                      </tr>
                  </thead>
                  <tbody>
                    <?php
                      if(!empty($cat_details)) {
                          $i = 1;
                          foreach($cat_details as $result) 
                          {
                            ?>
                          <tr class="odd gradeX">
                            <td><?php echo $i;?></td>
                              <td class="hidden-phone"><?php echo $result->category_name;?></td>
                              <td class="hidden-phone"><?php echo $result->parent_name;?></td>
                              <td class="hidden-phone"><center><?php echo '<a href="' . admin_url() . 'category/edit/' . $result->category_id . '" title="Edit this Category">Edit</a>';?> | <?php echo '<a href="' . admin_url() . 'delete_category/' . $result->category_id . '" title="Delete this Category">Delete</a>';?> </center>
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
              <h4><i class="icon-reorder"></i>Add new category</h4>
              <span class="tools">
                 <a href="javascript:;" class="icon-chevron-down"></a>
                 <a href="javascript:;" class="icon-remove"></a>
              </span>
            </div>
            <div class="widget-body form">
              <!-- BEGIN FORM-->
              <form action="add" class="form-horizontal" id="add_category" method="post" enctype="multipart/form-data">

                <div class="control-group">
                  <label class="control-label">Category Root</label>
                  <div class="controls">
                    <select class="span6 " data-placeholder="Choose a Category" name="cat_root_name" tabindex="1">
                      <option value="">Select categories</option>
                      <?php foreach ($category as $newcat){
                        ?>
                        <option value="<?php echo $newcat->category_id; ?>"><?php echo $newcat->category_name; ?>(1)</option>
                        <?php
                      } 
                      ?>
                    </select>
                  </div>
                </div>

                <div class="control-group">
                  <label class="control-label">Category name</label>
                  <div class="controls">
                    <input type="text" name="cat_name" id="cat_name" class="span6 " />
                  </div>
                </div>
                <div class="control-group">
                  <label class="control-label">Image</label>
                  <div class="controls">
                      <input type="file" name="cat_image" id="cat_image" class="default" />
                  </div>
                </div>
                <div class="form-actions">
                  <input type="submit" name="category_submit" value="Submit" class="btn btn-success">
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
          //echo "<pre>";print_r($edit_category);exit;
          ?>
          <div class="widget">
            <div class="widget-title">
              <h4><i class="icon-reorder"></i>Edit category</h4>
              <span class="tools">
                 <a href="javascript:;" class="icon-chevron-down"></a>
                 <a href="javascript:;" class="icon-remove"></a>
              </span>
            </div>
            <div class="widget-body form">
              <!-- BEGIN FORM-->
              <form action="" class="form-horizontal" id="edit_category" method="post" enctype="multipart/form-data">

                <div class="control-group">
                  <label class="control-label">Category Root</label>
                  <div class="controls">
                    <select class="span6 " data-placeholder="Choose a Category" name="edit_cat_root_name" tabindex="1">
                      <option value="">Select categories</option>
                      <?php foreach ($category as $newcat){
                        $select = ($edit_category->parent_id == $newcat->category_id) ? 'selected' : '';
                        ?>
                        <option value="<?php echo $newcat->category_id; ?>" <?php echo $select; ?>>
                        <?php echo $newcat->category_name; ?></option>
                        <?php
                      } 
                      ?>
                    </select>
                  </div>
                </div>

                <div class="control-group">
                  <label class="control-label">Category name</label>
                  <div class="controls">
                    <input type="text" name="edit_cat_name" id="edit_cat_name" value="<?php echo $edit_category->category_name; ?>" class="span6 " />
                  </div>
                </div>
                <div class="control-group" style="display:none;">
                  <label class="control-label">Image</label>
                  <div class="controls">
                      <input type="file" name="edit_cat_image" id="edit_cat_image" class="default" />
                      <img src="<?php base_url()?>upload/cat/<?php echo $edit_category->image_url;?>">
                  </div>
                </div>
                <div class="form-actions">
                  <input type="submit" name="update_category_submit" value="Update" class="btn btn-success">
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

