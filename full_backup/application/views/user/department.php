<div class="container-fluid d-none d-sm-block">
	<div class="row">
		<div class="col-11 offset-md-x">
			<div class="departments-container-v2 d-flex flex-wrap justify-content-center">
				<?php
				if($department)
				{
					foreach($department as $department_row)
					{
						if($department_name == $department_row->name){ $active='active'; } else {$active='';} 
                        echo '<div class="department-v2">
								<div class="department-text-v2">
									<a class="dept-link '.$active.'" href="'.base_url().'filter/department/'.$department_row->name.'">
										<h2 class="dept-text-v2">'.$department_row->name.'</h2>
									</a>
								</div>
							</div>';
					}
				}
				?>
			</div>
		</div>
	</div>
				
				
    <div class="row">
        <div class="col-md-10 offset-md-x d-flex flex-wrap justify-content-between">
            <div class="department-container-short d-flex flex-wrap justify-content-center">
                <?php 
                if($sub_categories)
                {    
                    foreach($sub_categories as $subcategories) {
                         
                        $product_category = $subcategories->product_category;

                        if($product_category == 'Misc')
                        {
                            $product_category = 'Accessories';
                        }

                        if($search_product_category == $product_category){ $active='active'; } else {$active='';}

                        echo '<div class="short-dept">
                            <a href="'.base_url().'filter/product_category/'.$subcategories->product_category.'" class="short-dept-link '.$active.'">
                            <p>'.$subcategories->product_category.'</p></a>
                        </div>';
                    }
                }
                ?>
            </div>
			<?php 
            if($price_filter)
            ?>
            <div class="selector d-flex">
                <label for="">Sort By </label>
                <select class="cstm-select" onchange="location = this.value;" name="sort-by">
                    
                    <option <?php if($price_filter == 0) { echo "selected=selected";}?> value="<?php echo base_url();?>filter/price_filter/0">Recommended</option>
                    
                    <option <?php if($price_filter == 1) { echo "selected=selected";}?> value="<?php echo base_url();?>filter/price_filter/1">$ Low to High</option>

                    <option <?php if($price_filter == 2) { echo "selected=selected";}?> value="<?php echo base_url();?>filter/price_filter/2">$ High to Low</option>

                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-10 offset-md-x text-right">
            <p id="result-num"><?php echo $products_counts;//$total_records; ?> Results</p>
        </div>
    </div>
</div>
        