<div class="row-fluid">
  
   		<div class="span12">
  
       <!-- BEGIN THEME CUSTOMIZER-->
       <!--<div class="hidden-phone" id="theme-change">
           <i class="icon-cogs"></i>
            <span class="settings">
                <span class="text">Theme:</span>
                <span class="colors">
                    <span data-style="default" class="color-default"></span>
                    <span data-style="gray" class="color-gray"></span>
                    <span data-style="purple" class="color-purple"></span>
                    <span data-style="navy-blue" class="color-navy-blue"></span>
                </span>
            </span>
       </div>-->
       <!-- END THEME CUSTOMIZER-->
      <h3 class="page-title">
        <?php echo ucfirst($varWelcomeHeaderTitle); ?>
        <?php 
        if($varSmallHeaderTitle!=''){
          ?>
          <small><?php echo ucfirst($varSmallHeaderTitle);?></small>
          <?php 
         }
        ?>
      </h3>
       <ul class="breadcrumb">
       		<li>
             <a href=""><i class="icon-home"></i></a><span class="divider">&nbsp;</span>
          </li>
          <li><a href="#">Home</a> <span class="divider">&nbsp;</span>
          </li>
          <li><a href=""><?php echo ucfirst($title);?></a><span class="divider-last">&nbsp;</span></li>
       </ul>

		    <div class="clear-both">       	
		    </div>
   </div>
   <!-- {if !empty($VarGroupfollowcheck)}
   		<div class="span3">
                                 <div class="widget-body" style="text-align:right">
                                	<img src="{$arrAllCardDet[0].img_url_org}" style="max-height:80px!important;" alt="{$arrAllCardDet[0].card_name}"/>
                                    <br /><br />
 								
											{if $VarGroupfollowcheck==2}
												<div id="follow" style="margin-right:10px">
                                                <a href="javascript:;" role="button" class="btn btn-danger" data-toggle="modal" onclick="remove_watch_card({$varGroupId});">Un-follow card</a></div>{else if $VarGroupfollowcheck==3}<div id="follow" style="margin-right:15px">
											<a href="javascript:;" role="button" class="btn btn-primary" data-toggle="modal" onclick="add_watch_card({$varGroupId});">Follow card</a></div>
                                   			 {/if}
                                    
                                </div>
                            </div>
   {/if} -->
   
</div>