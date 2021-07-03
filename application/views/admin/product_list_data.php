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
<div id="main-content">
  <div class="container-fluid">
	  <?php $this->load->view('admin/bread_crumb');?>
    <div class="row-fluid">
		  <div class="span12">

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


      </div>
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

     loadPagination(0);

     // Load pagination
     function loadPagination(pagno){
       $.ajax({
         url: '<?=admin_url()?>newloadRecord/',
         data: 'pagno='+pagno,
         type: 'POST',
         dataType: 'json',
         success: function(response)
         {
            $('#pagination1').html(response.pagination);
            createTable(response.result,response.row);
         }
       });
     }

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
            url: '<?=admin_url()?>newloadRecord', 
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

     