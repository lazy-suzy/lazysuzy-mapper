<div class="col-md-2 filter-fun animated d-none d-sm-none d-md-block">
    <div class="filters">
        <div class="row">
        <div class="brand-filter-container col-12 col-md-12">
            <h6 class="filter-heading">Brands</h6>
            <ul class="filter-list">
                <?php

                   $site_name = $this->session->userdata('site_name');
                   $explode   = explode(',', $site_name);

                   foreach ($allsitedetails as $site_details) {
                      if (in_array($site_details->site_name, $explode)) {
                         $checked = 'checked';
                      } else {
                         $checked = '';
                      }

                   ?>
                        <li>
                            <label for="filter">
                                <input type="checkbox" id="sites[]" name="sites[]" value="<?php echo $site_details->site_name; ?>" filter-option="site_name" class="flt filter-input brand-flt"<?php echo $checked; ?>>
                                <?php echo $site_details->site_name; ?>
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
                <input type="text" id="range" value="" name="range" class="price-flt" />
            </div>
        </div>
        <?php
           if (sizeof($sub_categories) != 0) {
           ?>
      <div class="brand-filter-container col-12 col-md-12">
        <h6 class="filter-heading">Type</h6>
        <ul class="filter-list">
          <?php

                foreach ($sub_categories as $key => $val) {
                ?>
                <li>
                    <label for="filter">
                        <input type="checkbox" value="<?php echo $val->id ?>" class="filter-input sub-cat-flt flt" ><?php echo $val->name ?>
                    </label>
                </li>

                <?php
                   }

                   ?>

        </ul>
    </div>
  <?php }?>
    </div>

    <div class="brand-filter-container">
        <a class="btn" style="color:white;background-color:#0c1c31;" href="<?php echo base_url(); ?>filter/clear_filter/all">Clear filter</a>
    </div>
</div>
</div>