<!-- New design images 26-11-18 mobile view -->
            <div class="container">
                <div class="row d-flex justify-content-between d-sm-none">
                    <div class="d-flex">
                        <nav class="navbar navbar-light navbar-expand-lg mainmenu">
                            <ul class="navbar-nav mr-auto">
                                <li class="dropdown">
                                    <a class="dropdown-toggle custm" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-angle-down"></i></a>
                                    <ul class="dropdown-menu custm lvl-one" aria-labelledby="navbarDropdown">
                                     

                                        <?php
                                        
                                        foreach($get_categories as $getcategories)
                                        {
                                            $dept = $getcategories['name'];

                                            $get_sub_categories = $this->db->query('select * from dsb_department where name="'.addslashes($dept).'" AND status=1 group by product_category')->result_array();
                                            
                                            
                                            if($get_sub_categories)
                                            {
                                                echo '<li class="dropdown">
                                                            <a class="dropdown-toggle custm" href="'.base_url().'filter/department/'.$getcategories['name'].'" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.$getcategories['name'].'
                                                            <i class="fas fa-angle-right"></i>
                                                            </a>';
                                                            
                                                            echo '<ul class="dropdown-menu custm" aria-labelledby="navbarDropdown">';
                                                            
                                                            foreach($get_sub_categories as $sub_category)
                                                            {
                                                                if($sub_category['product_category'])
                                                                {
                                                                    ?>
                                                                    <li>
                                                                        <a href="<?php echo base_url();?>filter/product_category/<?php echo $sub_category['product_category'];?>"><?php echo $sub_category['product_category'];?>
                                                                        </a>
                                                                    </li>
                                                                    <?php
                                                                } 
                                                            }
                                                            echo '</ul>
                                                        </li>';
                                            }
                                            else
                                            {   
                                                ?>
                                                <li>
                                                    <a onClick="category_filter('<?php echo $getcategories["department_id"];?>');" href="javascript:void(0)"><?php echo $getcategories['name'];?>
                                                    </a>
                                                </li>
                                                <?php 
                                            }
                                        }

                                        ?>
                                        
                                    </ul>
                                </li>
                            </ul>
                        </nav>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb custm">
                                <li class="breadcrumb-item"><a href="<?php echo base_url();?>"><?php echo $current_department; ?></a></li>
                                <?php 
                                if($current_pro_category!='')
                                {
                                    ?>
                                    <li class="breadcrumb-item <?php if($current_pro_category!=''){ echo 'active'; }?>" aria-current="page"><?php echo $current_pro_category; ?></li>
                                    <?php 
                                }
                                ?>
                            </ol>
                        </nav>
                    </div>
                    <div class="filters-con">
                        <i class="fas fa-filter" id="call-filters"></i>
                    </div>
                </div>
            </div>
            <!-- End 26-11-18 -->