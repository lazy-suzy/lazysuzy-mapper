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
</style>
<?php //echo "<pre>";print_r($site_details);exit;?>
<div id="main-content">
  <div class="container-fluid">
    <?php $this->load->view('admin/bread_crumb');?>
    <div class="row-fluid">
      <div class="span12">
        <!-- BEGIN SAMPLE TABLE widget-->
        <div class="widget">
          <div class="widget-title">
            <h4><i class="icon-reorder"></i>Import bulk Products</h4>
            <span class="tools">
               <a href="javascript:;" class="icon-chevron-down"></a>
               <a href="javascript:;" class="icon-remove"></a>
            </span>
          </div>
          <div class="widget-body form">
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
          <a class="btn btn-primary pull-right" download href="<?php echo base_url();?>upload/sample.csv">Sample CSV</a>
            <!-- BEGIN FORM-->
            <form action="" class="form-horizontal" id="bulk_upload" method="post" enctype="multipart/form-data">
              <div class="control-group">
                <label class="control-label">Site Name</label>
                <div class="controls">
                  <select class="span6 " id="product_id" required="" data-placeholder="Choose site name" name="product_id" tabindex="1">
                    <option value="">Select one site...</option>
                    <?php foreach ($site_details as $all_site_details){
                      ?>
                      <option value="<?php echo $all_site_details->site_id; ?>"><?php echo $all_site_details->site_name; ?></option>
                      <?php
                    } 
                    ?>
                  </select>
                </div>
              </div> 
              <div class="control-group">
                <label class="control-label">Upload CSV</label>
                <div class="controls">
                    <input type="file" name="upload_csv" id="upload_csv" class="span6" required accept=".csv"/>
                </div><span class="newnew" style="margin-left: 180px; color:red;">*Allowed csv format only.</span>
              </div>
              <div class="form-actions">
                <input type="submit" name="import_csv_submit" value="Submit" class="btn btn-success">
                <a href="<?php echo $cancel_url; ?>"  class="btn">Cancel</a>
              </div>
            </form>
            <!-- END FORM-->           
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