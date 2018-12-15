<div class="col-md-2 filter-fun animated d-none d-sm-none d-md-block">
                        <div class="filters">
                            <div class="row">
                            <div class="brand-filter-container col-12 col-md-12">
                                <h6 class="filter-heading">Brands</h6>
                                <ul class="filter-list">
                                    <?php 

                                    $site_name = $this->session->userdata('site_name');
                                    $explode = explode(',',$site_name);

                                    foreach($allsitedetails as $site_details)
                                        {
                                            if(in_array($site_details->site_name,$explode))
                                                $checked = 'checked';
                                            else
                                                $checked = '';
                                            ?>
                                            <li>
                                                <label for="filter">
                                                    <input type="checkbox" id="sites[]" name="sites[]" value="<?php echo $site_details->site_name;?>" filter-option="site_name" class="filter-input" <?php echo $checked; ?>>
                                                    <?php echo $site_details->site_name;?>
                                                </label>
                                            </li>
                                            <?php 
                                        }
                                        ?>
                                </ul>
                            </div>
                            <div class="brand-filter-container col-12 col-md-12">
                                <h6 class="filter-heading">Price</h6>
                                <div class="theme-ccff22">
                                    <input type="text" id="range" value="" name="range" />
                                </div>
                            </div>
                            <?php 
                            //echo "<pre>";print_r($all_product_sub_category_new->product_sub_category);echo "<br>";
                                 
                                $not_important     = array('Laundry','Mattresses','Desks & Tables','Seating,Cushion');
                                $product_category       = urldecode($this->session->userdata('product_category'));
                                $product_sub_category   = $this->session->userdata('product_sub_category');
                                $department             = $this->session->userdata('department');
                                if($department!='')
                                {
                                    if(!empty($all_product_sub_category_new)) //$all_product_sub_category
                                    {
                                        if(!in_array($product_category,$not_important))
                                        {
                                            ?>
                                            <div class="brand-filter-container col-12 col-md-12">
                                                <h6 class="filter-heading">Type</h6>
                                                <ul class="filter-list">
                                                    <?php 
                                                   
                                                    if($product_sub_category == 'all')
                                                        $check = 'checked';
                                                    else
                                                        $check = '';
                                                    ?>
                                                    <li>
                                                        <label for="filter">
                                                            <input type="checkbox" id="sub_category[]" name="sub_category[]" value="all" filter-option="product_sub_category_all" class="filter-input" <?php echo $check; ?>>
                                                            All items
                                                        </label>    
                                                    </li>

                                                    <?php 
                                                    
                                                    $explode = explode(',',$product_sub_category);
                                                    //$all_product_sub_category
                                                    foreach($all_product_sub_category_new as $pro_sub_category)
                                                    {
                                                        if($product_sub_category!='')
                                                        {
                                                            if(in_array($pro_sub_category->product_sub_category,$explode))
                                                                $checked = 'checked';
                                                            else
                                                                $checked = '';
                                                        }
                                                        else
                                                        {
                                                            $checked = '';
                                                        }
                                                        ?>
                                                        
                                                        <li>
                                                            <label for="filter">

                                                                <input type="checkbox" id="sub_category[]" name="sub_category[]" value="<?php echo $pro_sub_category->product_sub_category;?>"  filter-option="product_sub_category" class="filter-input" <?php echo $checked; ?>><?php echo $pro_sub_category->product_sub_category;?>
                                                            </label>
                                                        </li>
                                                        <?php 
                                                    }
                                                    ?>

                                                </ul>
                                            </div>
                                            <?php 
                                        }
                                    }    
                                }
                                ?>
                            
                        </div>
                        <div class="brand-filter-container">
                            <a class="btn" style="color:white;background-color:#0c1c31;" href="<?php echo base_url();?>filter/clear_filter/all">Clear filter</a>
                        </div>
                    </div>
                    </div>