//http://www.blackfishweb.com/blog/asynchronously-loading-twitter-google-facebook-and-linkedin-buttons-and-widgets-ajax-bonus
var CachedSuggestionsObject = {};
var cacheSeasons = {};
var GlobalCardInfoObject = [];
var StatResultObject = {};
var MaxVal = null;	
var GlobalStatsCache = {};
var GlobalHashObject = {};
var GlobalStatsSelect = {};
var FlterSelected = 'total';
var urlHash = 0;
var currentBitlyObj = {};
var ajaxLoading		=	goajaxLoading	=	false;
var Globalleagues = {};
var globalCardStats	=	[];
var StatsResult	=	[];
var ajaxtimer;
var suggestAjaxRequest;
var oldZindex;
var nonlog_stoploadingstats=0;
var generateFlag;

// LOADING SEASONS
function loadSeasons(obj,season)
{					
	var vIssuerId = $(obj).val();	
	var season_container = $(obj).parent().parent().find('.cm-season-dropdown');		
	season_container.empty();
	season_container.append('<option value="">Select</option>');			  
	if(vIssuerId > 0)
	{			
		if(cacheSeasons[vIssuerId] && cacheSeasons[vIssuerId].length > 0)
		{
			season_container.html(cacheSeasons[vIssuerId]);	
			$('option:eq(1)',season_container).prop('selected', true);	
			if(typeof season != 'undefined')
			{
				season_container.val(season);	
				$(obj).parent().parent().find('.get-stat-button').trigger('click');
			}			
		}
        else
        {		
        	$.ajax({
			  dataType: "json",
			  url: "/comparison-matrix",
			  data: { ajax:true,issuer_id:vIssuerId,mode:'getSeasons' },
			  ajaxIssuerId:vIssuerId,
			  ajaxSeason:season,	
			  beforeSend: function(){
			  	$(obj).parents().find('.card-loading').fadeIn();
			  },		  
			  success: function(result){
			  	if(result.length > 0)
			  	{
				  	$.each(result, function(key, value){		
						var season_name = $.trim(value.season_name.replace(/season/i,''));		
						season_container.append('<option value="'+value.vIssuerId+'">'+season_name+'</option>');				
					});
					$('option:eq(1)',season_container).prop('selected', true);										
					cacheSeasons[this.ajaxIssuerId] = season_container.html();	
					
					if(typeof this.ajaxSeason != 'undefined')
					{						
						season_container.val(this.ajaxSeason); 							
						$(obj).parent().parent().find('.get-stat-button').trigger('click'); 
						
					}	
			  	}			  	
			  },
			  complete	: function () {
			  	$(obj).parents().find('.card-loading').fadeOut();
			  }, 
			  error: function (xhr, textStatus, errorThrown) {
                //console.log("Ajax Error: " + (errorThrown ? errorThrown : xhr.status));
          	  },
			});			
		}	
						
	}	
}

/** AUTO SUGGESTIONS **/
function startAutoSuggest(inpt)
{		
	if(ajaxtimer)
	 clearTimeout(ajaxtimer);
	 
	ajaxtimer	=	setTimeout(autoSuggestions, 300, inpt);
	return null;
}

function autoSuggestions(elem){										
	var datearray1 = $('#card_comparision_date').val().split("/");													   	
	var vReportDate = datearray1[2] + '-' + datearray1[1] + '-' + datearray1[0];
	
	var vIssuerId	=	parseInt(elem.parent().parent().find('select.cm-competition-dropdown').val());
	var vGroupId	=	parseInt(elem.parent().parent().find('select.cm-season-dropdown').val());
	var whcontainer		=	elem.parent().parent().parent().parent().parent();		
	oldZindex	=	parseInt(whcontainer.css('z-index'));
	var search_input	=	elem.val();
	var inpt	=	elem;
	elem.parent().find('.cm-search-suggestions').empty().fadeOut();		
	if(isNaN(vIssuerId))	vIssuerId	=	0;
	if(isNaN(vGroupId))	vGroupId	=	0;
	
	var outputSugg = '';	
	
	var curDate = new Date();
	var curMilliseconds = curDate.getTime();						
	$.ajax({
		  dataType: "json",
		  url: "../competitor-cards/card-comparison-ajax",
		  data: { ajax:true,action:'searchCard',report_date:vReportDate,issuer_id:vIssuerId,group_id:vGroupId,search:search_input,mode:'getPlayersClubs',cache:curMilliseconds },
		  ajaxIssuerId:vIssuerId,
		  ajaxSearch:search_input,
		  beforeSend:function(){ this.elem.addClass('cm-player-team-input-loading'); },
		  elem:elem,
		  whcontainer:whcontainer,
		  oldZindex:oldZindex,
		  success: function(result)
		  {					
		  		if(typeof(result) != 'undefined' && !result){ 						  			
		  			this.elem.parent().find('.cm-search-suggestions').empty().fadeOut(); 
		  			this.elem.removeClass('cm-player-team-input-loading');
		  			return; 
		  		}
		  	  		  		
		  		if(result.length > 0){			  					
		  			$.each(result, function(key, value){
							outputSugg += '<li class="item" data-id='+value.group_id+'>'+value.group_name+'</li>';															
					});					
				} else {
		  			outputSugg = '';
				}
		  		
		  		if(outputSugg != '')		  		
		  			this.elem.parent().find('.cm-search-suggestions').html(outputSugg).fadeIn();
					
		  					  								 
		  		this.whcontainer.css('z-index', 120);		  									
		  		suggestionsOn	=	true;			  
				this.elem.removeClass('cm-player-team-input-loading');
		  },
		  error: function (xhr, textStatus, errorThrown) {
	        //console.log("Ajax Error: " + (errorThrown ? errorThrown : xhr.status));
	  	  }  	  
	});  							    	
				
}

/** STATS MATRIX PROCESS **/
var ComparisonMatrix = {
	CardInfoObject			: {},		
  	Cache			 		: new Date().getTime(),
  	CardNo					: 0,
  	parentCard				: null,
	GoButtonChange			: function(){
		ComparisonMatrix.UpdateBackPlayerCardInfo();	
	},
	UpdateBackPlayerCardInfo	: function(){
		var CardObj = ComparisonMatrix.CardInfoObject;	
		//alert(CardObj.toSource()+'=='+CardObj['issuer_id']);
		var vIssuerId	=	CardObj['issuer_id'];	
		var vGroupId	=	CardObj['group_id'];	
		var vReportDate	=	CardObj['card_date'];		
		var resultStatHtml = '';
		$.ajax({
		  dataType: "json",
		  url: '../competitor-cards/card-comparison-ajax',
		  data: {
		  	ajax:true,
			report_date:vReportDate,
			issuer_id:vIssuerId,
			group_id:vGroupId,
			type:CardObj.type,
			action:'add_competitor_card_comparison',
			mode:'getTeamPlayerInfo',
			cache:ComparisonMatrix.Cache
		  },
		  ajaxCardno:ComparisonMatrix.CardNo,
		  parentCard:ComparisonMatrix.parentCard,
		  success: function(result){
			if(!$.isEmptyObject(result)){
				//alert(result.toSource());
				updateStickyClear();
		  		var cparentCard = this.parentCard;
		  		//if this is the 5th card and a card adder, don't add another card after it, otherwise add
				if( this.ajaxCardno != 5 && cparentCard.hasClass('cm-card-adder') && urlHash == 0){
	            	
	                	cparentCard.clone(true).appendTo('#cm-player-contain').removeClass('cm-card-template').fadeOut(0).fadeIn(400,function(){
	                     //automatically fill in the next card adder with the competiton/season value just selected
	                    $(this).find('.cm-player-team-input').val('');
	                    //getLeagues($(this).find('.cm-competition-dropdown'));	                   
	                    //$(this).find('.cm-competition-dropdown').val(cparentCard.find('.cm-competition-dropdown').val()).prop('selected',true);
	                    /*$(this).find('.cm-season-dropdown')
							    .find('option')
							    .remove()
							    .end()
							    .append('<option value="">Select</option>')
							    .val('');*/    
						$(this).find('.cm-competition-dropdown').prepend('<option selected="selected" label="All Issuers" value="0">All Issuers</option>');
						$(this).find('.cm-season-dropdown').prepend('<option selected="selected" label="Card" value="0">Card</option>');
							//jQuery('.cm-competition-dropdown').html(vSelectIssuer);
							//jQuery('.cm-season-dropdown').html(vSelectGroup);
	                });
	            }       
				//alert(this.ajaxCardno);
				var pname	=	result.issuer_name;
	            if(pname.length > 20)
                	pname		=	pname.substring(0,20);  
                	
                var cname = '';
                if(result.group_name != null)
                	cname	=	result.group_name;	              	                	                               
                
                /*if(cname.length > 16)
                	cname		=	cname.substring(0,16);*/
					
				resultStatHtml = '<div class="cm-player-name" title="'+result.issuer_name+'">'+
								pname+
							 '</div>'+
							 '<div class="cm-team-name" style="margin-top:15px;height:40px;">'+
							 	cname+												
							 '</div>'+
							 '<div class="cm-player-image"><div class="sq-card-fwm"></div>'+
							 	'<img src="'+result.card_img_url+'" style="position: absolute; margin: auto; top: 10px; left: 0; right: 0; bottom: 0;">'+								
							 '</div>'+
							 '<div class="cm-player-league"></div>'+
							 '<div class="cm-player-season"></div>'+							
							 '<h3 class="card-title"></h3>'+
							 '<div class="cm-player-apps"></div>'+
							 '<div class="cm-mins-played"></div>'+
							 '<div class="clear-both"><a href="javascript:void(0)" class="remove-stat-button" data-card="'+this.ajaxCardno+'">Remove</a></div>';						 			
				//$('.cm-player-card').html(resultStatHtml);
				$('#cm-player-contain .cm-card-'+this.ajaxCardno+' .cm-player-card').html(resultStatHtml);
				
				GlobalCardInfoObject[this.ajaxCardno]['issuer_id'] = result.issuer_id;	
				GlobalCardInfoObject[this.ajaxCardno]['issuer_name'] = result.issuer_name;
				GlobalCardInfoObject[this.ajaxCardno]['group_id'] = result.group_id;
				GlobalCardInfoObject[this.ajaxCardno]['group_name'] = result.group_name;	
				GlobalCardInfoObject[this.ajaxCardno]['card_img_url'] = result.card_img_url;
				
				GlobalCardInfoObject[this.ajaxCardno]['card_modal_apr'] = result.card_modal_apr;	
				GlobalCardInfoObject[this.ajaxCardno]['card_modal_bt_perc'] = result.card_modal_bt_perc;
				GlobalCardInfoObject[this.ajaxCardno]['card_modal_bt_fee'] = result.card_modal_bt_fee;
				GlobalCardInfoObject[this.ajaxCardno]['card_modal_site_cnt'] = result.card_modal_site_cnt;	
				GlobalCardInfoObject[this.ajaxCardno]['card_modal_type_cnt'] = result.card_modal_type_cnt;
				GlobalCardInfoObject[this.ajaxCardno]['card_modal_avg_rank'] = result.card_modal_avg_rank;
				GlobalCardInfoObject[this.ajaxCardno]['card_modal_exp_rank'] = result.card_modal_exp_rank;
				
				cards.loadCardStats(this.parentCard); 
				cards.refreshColumns('all');			                
				
				//show the remove card button
				this.parentCard.find('.cm-remove-button').show();
				//updateDropdownOnGo();
				//ajaxLoading = false;
				goajaxLoading = false;
				
				var cardcount = this.ajaxCardno+1;
				//alert(cardcount);
				add_stat_bottom_row(cardcount);
			}
		  },
		  error: function (xhr, textStatus, errorThrown) {
                //console.log("Ajax Error: " + (errorThrown ? errorThrown : xhr.status));
          },
		});			
	},	
	StatDropDownChange		: function(element){
		var statRow = element.parents('.cm-stat-section'); 
		/*if(GlobalCardInfoObject.length > 0){
			alert(GlobalCardInfoObject.toSource());
			updateStickyClear();			
			cards.loadCardStats(statRow);
			var selectGroup = GlobalCardInfoObject['group_id'];
			var statObj = $('#cm-player-contain').find('.cm-card-0');
			var stat_val = '363.31';
			var percent = 100;
			var prefix = '%';
			cards.loadStatBlock(statObj,stat_val,percent,prefix);
		}*/
		if(GlobalCardInfoObject.length > 0)
		{		
			updateStickyClear();			
			cards.loadCardStats(statRow);					
			var selectGroup = element.val();	
			
			if(selectGroup != '')
			{
				var stext = element.find("option:selected").text();
				StatsHashArray();
				//console.log(stext);
				var decoded_json = encodeURIComponent(JSON.stringify(GlobalCardInfoObject));
				$.ajax({
				  dataType: "json",
				  url: "/comparison-matrix",	
				  type:'GET',			  
				  data: { 
					ajax:true,
					data_object:decoded_json,
					mode:'getStatsInfo',
					stat_group_id:selectGroup,
					cache:ComparisonMatrix.Cache,
					filter:FlterSelected,
				  },
				  stat_group_id:selectGroup,
				  statObj:statRow,
				  beforeSend:function(){ //hide all stars in row
		            $('#stats-loading').fadeIn(); 
		          },
				  success: function(result)
				  {				
				  		this.statObj.find('.cm-star').addClass('hidden');   										  	  						  			
				  		var statvals = [];
				  		var gid = this.stat_group_id;				
				  		var tstatObj	=	  this.statObj;		
						$.each(result,function(key,value){																	
							
							var cno = value['card'];							
							var statObj = tstatObj.find('.cm-card-'+cno);
							var stat_val = value['value'];						
							var percent = value['percent'];																										
									
							if(gid == '4,shot_accuracy' || gid == '9,takeonspercent' || gid == '9,headed_duals_won_percent' || gid == '9,total_duals_percent' || gid == '24,distribution_accuracy' || gid == '7,pass_completion')
							{
								var prefix = '%';
								//stat_val = percent;	
							}
							else if(gid == '8,avg_pass_length' || gid == '24,distribution_length')
							{
								var prefix = 'm';								
							}							
							statvals.push(percent);		
							cards.loadStatBlock(statObj,stat_val,percent,prefix);
							statObj.children('.cm-stat-value').attr('data-statval',percent);  
							
							if(typeof globalCardStats[cno] == 'undefined')
								globalCardStats[cno]	=	{};
							
							if(typeof globalCardStats[cno][gid] == 'undefined')
								globalCardStats[cno][gid]	=	[];
								
							globalCardStats[cno][gid]	=	value;							
						});							
						
						if(gid == '3,goals_conceded' || gid == '11,defensive_errors' || gid == '9,tackles_lost' || gid == '11,errors_leading_to_goal' || parseInt(gid) == 12 || gid == '9,fouls_committed')
			            	var markval = Math.min.apply(Math,statvals);
			            else
			            	var markval = Math.max.apply(Math,statvals);	
			            
			            if(typeof markval != 'undefined')					        
				        	this.statObj.find('.cm-stat-value[data-statval="'+markval+'"]').parent().children('.cm-star').removeClass('hidden');
				        	
				        			        
				  },
				  error: function (xhr, textStatus, errorThrown) {
	                //console.log("Ajax Error: " + (errorThrown ? errorThrown : xhr.status));
	          	  },
	          	  complete:function(){ 
	          	  		//ajaxLoading = false;
				        goajaxLoading = false;				         
				  },
				});	
			}
								
		}		
	}
};

(function(e){function t(e,t){return e.toFixed(t.decimals)}e.fn.countTo=function(t){t=t||{};return e(this).each(function(){function l(){a+=i;u++;c(a);if(typeof n.onUpdate=="function"){n.onUpdate.call(s,a)}if(u>=r){o.removeData("countTo");clearInterval(f.interval);a=n.to;if(typeof n.onComplete=="function"){n.onComplete.call(s,a)}}}function c(e){var t=n.formatter.call(s,e,n);o.html(t)}var n=e.extend({},e.fn.countTo.defaults,{from:e(this).data("from"),to:e(this).data("to"),speed:e(this).data("speed"),refreshInterval:e(this).data("refresh-interval"),decimals:e(this).data("decimals")},t);var r=Math.ceil(n.speed/n.refreshInterval),i=(n.to-n.from)/r;var s=this,o=e(this),u=0,a=n.from,f=o.data("countTo")||{};o.data("countTo",f);if(f.interval){clearInterval(f.interval)}f.interval=setInterval(l,n.refreshInterval);c(a)})};e.fn.countTo.defaults={from:0,to:0,speed:1e3,refreshInterval:100,decimals:0,formatter:t,onUpdate:null,onComplete:null}})(jQuery)
var cards = {
    cardStates : [0,0,0,0,0],
    
    /**
     * 
     * @param {object} statblock
     * @param {int/decimal} stat
     * @param {int} pc
     * @param {string} (optional) append
     * 
     * @returns {undefined}
     */
    loadStatBlock : function(statblock,stat,pc,append){
        statblock.find('.cm-stat-bar div').animate({
            height : pc+'%'
        },800);
        
        var statvalcont = statblock.find('.cm-stat-value'), statval = parseInt(statvalcont.html());
        
        if( isNaN(statval))
            statval = 0;
        
        statvalcont.countTo({
            from: statval,
            to: stat,
            speed: 800,
            refreshInterval: 15,
            formatter: function (value, options) {
                //count decimals if decimal points                
                if( stat % 1 != 0 )
                    return value.toFixed(2);
                else
                    return parseFloat(value.toFixed(options.decimal));
            },
            onUpdate: function(){
            	//$('#stats-loading').fadeIn(); 
                //add any extra format
				if( typeof append != 'undefined' ){
                    if(append == '&pound;')
						statvalcont.html(append+statvalcont.html());
					else
						statvalcont.html(statvalcont.html()+append);
				}
            },
            onComplete: function(){
            	// if(login_user == 0 && GlobalCardInfoObject.length > 0)				
					// nonlog_stoploadingstats=1;
					
            	$('#stats-loading').fadeOut(); 
            }
        });
    },
            
    loadCardStats : function(element)
    {
        var loading = element.find('.card-loading');  
        //get stats, populate card front, show loading while getting stats
        loading.fadeIn(400,function(){
            //add flip animation classes
            //element.find('.flip-contain').addClass('flipper');
            element.find('.cm-player-card').removeClass('hidden');
            element.find('.cm-player-card-back').addClass('card-flip-back');
            element.removeClass('cm-card-adder');
            element.find('.cm-player-card-outer').removeClass('hover');//for touch
            loading.fadeOut(400); 
        }); 
    },
            
    refreshColumns : function(sect)
    {
        if( sect == 'cards' || sect == 'all' )
        {
            var ccnt = 0;
            $('.cm-card-glow').each(function(){
                if( !$(this).hasClass('cm-card-template') )
                {  
                    $(this).removeClass('cm-card-0 cm-card-1 cm-card-2 cm-card-3 cm-card-4');
                    $(this).addClass('cm-card-'+ccnt);
                    $(this).find('.get-stat-button').attr('data-card', ccnt);
                    $(this).find('.remove-stat-button').attr('data-card', ccnt);                    
                    ccnt++;
                }
            });
        }
        
        if( sect == 'stats' || sect == 'cards' )
        {
            $('.cm-stat-section .cstat').each(function(){ 
				var scnt = 1;
                $(this).find('.cm-player-stat').each(function(){
                    $(this).removeClass('cm-card-1 cm-card-2 cm-card-3 cm-card-4 cm-card-5');
                    $(this).addClass('cm-card-'+scnt);
                    scnt++;
                });
                /*var scnt = 1;
                $(this).children('.cm-player-stat').each(function(){
                    $(this).removeClass('cm-card-1 cm-card-2 cm-card-3 cm-card-4 cm-card-5');
                    $(this).addClass('cm-card-'+scnt);
                    scnt++;
                });*/
            });
        }
    },    
    updateStatsRow : function(row,low){
		var cardcount = 0, statvals = [];
        
        //hide all stars in row
        row.find('.cm-star').addClass('hidden');
        row.children('.cm-player-stat').each(function(index){
        	//alert(index);    
            //only load stat block if the column is active
            if( cards.cardStates[cardcount] === 1 ){
                //temp stats for testing functionality
                var tmpstat = Math.round(Math.random() * (100 - 10) + 10);
                
                cards.loadStatBlock($(this),tmpstat,tmpstat,'%');
                
                statvals.push(tmpstat);
                $(this).children('.cm-stat-value').attr('data-statval',tmpstat);
            }
            
            cardcount++;
        });
        
        //if the agument low is present, find the lowest stat and mark with star, else mark highest score
        var markval;
        if( typeof low != 'undefined')
            //mark lowest stat with a star
            markval = Math.min.apply(Math,statvals);
        else
            //mark the highest stat with a star
            markval = Math.max.apply(Math,statvals);
        
        row.find('.cm-stat-value[data-statval="'+markval+'"]').parent().children('.cm-star').removeClass('hidden');
        
        // animate share tool bar into view to catch attention
        $('#share-prompt').css({'margin-left':'-30px',opacity:0});
        $('#share-prompt').animate({
            'margin-left': '0px',
            opacity : 1
        },800);
    },
    
    updateCardStates : function(index){
        index = parseInt(index);
        cards.cardStates.splice(index,1);
        cards.cardStates.push(0);
    }
}

$(document).ready(function(){
    card_hover_rotation_css();
	
    //autocomplete
    $(document).on('keyup','.cm-player-team-input',function(e){
    	var inpt = $(this);    
        var cnno	=	parseInt(inpt.parent().parent().find('.get-stat-button').attr('data-card'));
    	
    	$('#signin-container').fadeOut();    	        
         
        var compVal = inpt.parent().parent().find('.cm-season-dropdown').val();
         	                 
        //activate search when 3 or more characters have been entered
        if( inpt.val().length > 2){       
        	startAutoSuggest(inpt); 	             						                                          
            //get data and display suggest box            
        } else {
            //hide sugest box
            inpt.parent().find('.cm-search-suggestions').hide();            
			//getLeagues(inpt.parent().parent().find('.cm-competition-dropdown'));
			//inpt.parent().parent().find('.cm-season-dropdown').empty();
			//inpt.parent().parent().find('.cm-season-dropdown').append('<option value="">Select</option>');			
        }
    });
    $(document).on('change','.cm-competition-dropdown', function(){
		var eve1 = $(this);
		//eve1.parent().hide();
		var whcontainer		=	eve1.parents().parents().parents().parents().parents('.cm-card-glow');		       
		whcontainer.css('z-index', 100);
		
		var vIssuerId 	= $(this).val(); //jQuery('#sel_provider_type').val();
		eve1.parent().parent().find('.cm-season-select .cm-season-dropdown').html('<option>Loading...</option>');
		//jQuery('#sel_group_card').html('<option>Loading...</option>');
		jQuery.ajax({
			type	: 'GET',
			url		: '../common-ajax.php',
			data: "action=issuer_base_load_groups&issuer_id="+vIssuerId+"&rand="+Math.random(),
			success	: function(getData){
				eve1.parent().parent().find('.cm-season-select .cm-season-dropdown').html(getData);
				var vGroupId = eve1.parent().parent().find('.cm-season-select .cm-season-dropdown option:selected').val();
				var vGroupName = eve1.parent().parent().find('.cm-season-select .cm-season-dropdown option:selected').text();
				//eve1.parent().parent().find('.cm-player-team-input').val(vGroupName);
				
				var CurrentgoButton = eve1.parent().parents().children('.get-stat-button');
				var CurrentgoButtonId = CurrentgoButton.attr('data-card'); 
				if(typeof GlobalCardInfoObject[CurrentgoButtonId] == 'undefined');
					GlobalCardInfoObject[CurrentgoButtonId] = [];
					GlobalCardInfoObject[CurrentgoButtonId] = { type:eve1.attr('data-type'),data_id:vGroupId,curren_comp_id:eve1.attr('data-compid') };
					
				AutoFillLeaguesSeasons(CurrentgoButtonId,vGroupId,0);
			}
		 });
	});
	$(document).on('change','.cm-season-dropdown', function(){
		var eve2 = $(this);
		//eve1.parent().hide();
		var whcontainer		=	eve2.parents().parents().parents().parents().parents('.cm-card-glow');		       
		whcontainer.css('z-index', 100);
		
		var vGroupId   = eve2.val(); //jQuery('#sel_provider_type').val();
		var vGroupName = eve2.find('option:selected').text();
		
		eve2.parent().parent().find('.cm-competition-select .cm-competition-dropdown').html('<option>Loading...</option>');
		//jQuery('#sel_group_card').html('<option>Loading...</option>');
		jQuery.ajax({
			type	: 'GET',
			url		: '../common-ajax.php',
			data: "action=group_base_load_issuer&group_id="+vGroupId+"&rand="+Math.random(),
			success	: function(getData){
				eve2.parent().parent().find('.cm-player-team-input').val(vGroupName);
				eve2.parent().parent().find('.cm-competition-select .cm-competition-dropdown').html(getData);
				
				/*eve1.parent().parent().find('.cm-season-select .cm-season-dropdown').html(getData);
				var vGroupId = eve1.parent().parent().find('.cm-season-select .cm-season-dropdown option:selected').val();
				var vGroupName = eve1.parent().parent().find('.cm-season-select .cm-season-dropdown option:selected').text();
				eve1.parent().parent().find('.cm-player-team-input').val(vGroupName);*/
				
				var CurrentgoButton = eve2.parent().parents().children('.get-stat-button');
				var CurrentgoButtonId = CurrentgoButton.attr('data-card'); 
				if(typeof GlobalCardInfoObject[CurrentgoButtonId] == 'undefined');
					GlobalCardInfoObject[CurrentgoButtonId] = [];
					GlobalCardInfoObject[CurrentgoButtonId] = { type:eve2.attr('data-type'),data_id:vGroupId,curren_comp_id:eve2.attr('data-compid') };
					
				AutoFillLeaguesSeasons(CurrentgoButtonId,vGroupId,0);
			}
		 });
	});
	$(document).on('click','.cm-search-suggestions li.item',function(){
        var thisli = $(this);
		thisli.parent().hide();
        var whcontainer		=	thisli.parents('.cm-card-glow');		       
		whcontainer.css('z-index', 100);
		var vGroupId = $.trim(thisli.attr('data-id').replace(/[(].*[)]/, ''));	
		var vl	=	$.trim(thisli.text().replace(/[(].*[)]/, ''));				
		thisli.parents('.cm-playerteam-container').children('.cm-player-team-input').val(vl);
		var CurrentgoButton = thisli.parents().children('.get-stat-button');
		//alert(CurrentgoButton.toSource());
        var CurrentgoButtonId = CurrentgoButton.attr('data-card');  
		if(typeof GlobalCardInfoObject[CurrentgoButtonId] == 'undefined');
        	GlobalCardInfoObject[CurrentgoButtonId] = [];
        	GlobalCardInfoObject[CurrentgoButtonId] = { type:thisli.attr('data-type'),data_id:thisli.attr('data-id'),curren_comp_id:thisli.attr('data-compid') };
		//AutoFillLeaguesSeasons(CurrentgoButtonId,vGroupId,thisli.attr('data-clubid')); 
		//alert(CurrentgoButtonId+'=='+vGroupId);
		AutoFillLeaguesSeasons(CurrentgoButtonId,vGroupId,0);        
    });
	//card 'GO!' button
	$( "#sel_load_report" ).change(function() {
		var datearray1 = $('#card_comparision_date').val().split("/");
		var cardDate = datearray1[2] + '-' + datearray1[1] + '-' + datearray1[0];
		var vReportVal = $('#sel_load_report').val();
		//alert('on change'+'=='+cardDate);		
		clearSelections();	
		if(vReportVal > 0){
			$.ajax({
				url		: "../competitor-cards/card-comparison-ajax",
				data	: { ajax:true,action:'get_card_comparison_report',user_report_id:vReportVal,card_date:cardDate,rand:Math.random() },
				dataType: "json",
				success	: function(result){
					//alert('adfadf'+'=='+result);
					buildCards(result);
					//updateHashStatsRow_new(result);
					var cardno = ''; var cparentCard = '';	
					$.each(result,function(index,cardInfo){
						cardno = index;	
						if(typeof GlobalCardInfoObject[cardno] == 'undefined')
							GlobalCardInfoObject[cardno] = {};							
						if(typeof GlobalCardInfoObject[cardno]['card_img_url'] == 'undefined')
							GlobalCardInfoObject[cardno]['card_img_url'] = [];
						if(typeof GlobalCardInfoObject[cardno]['data_id'] == 'undefined')
							GlobalCardInfoObject[cardno]['data_id'] = [];							
						if(typeof GlobalCardInfoObject[cardno]['group_id'] == 'undefined')
							GlobalCardInfoObject[cardno]['group_id'] = [];
						if(typeof GlobalCardInfoObject[cardno]['group_name'] == 'undefined')
							GlobalCardInfoObject[cardno]['group_name'] = [];
						if(typeof GlobalCardInfoObject[cardno]['issuer_id'] == 'undefined')
							GlobalCardInfoObject[cardno]['issuer_id'] = [];							
						if(typeof GlobalCardInfoObject[cardno]['issuer_name'] == 'undefined')
							GlobalCardInfoObject[cardno]['issuer_name'] = [];
							
						if(typeof GlobalCardInfoObject[cardno]['card_modal_apr'] == 'undefined')
							GlobalCardInfoObject[cardno]['card_modal_apr'] = [];
						if(typeof GlobalCardInfoObject[cardno]['card_modal_bt_perc'] == 'undefined')
							GlobalCardInfoObject[cardno]['card_modal_bt_perc'] = [];							
						if(typeof GlobalCardInfoObject[cardno]['card_modal_bt_fee'] == 'undefined')
							GlobalCardInfoObject[cardno]['card_modal_bt_fee'] = [];
						if(typeof GlobalCardInfoObject[cardno]['card_modal_site_cnt'] == 'undefined')
							GlobalCardInfoObject[cardno]['card_modal_site_cnt'] = [];
						if(typeof GlobalCardInfoObject[cardno]['card_modal_type_cnt'] == 'undefined')
							GlobalCardInfoObject[cardno]['card_modal_type_cnt'] = [];	
						if(typeof GlobalCardInfoObject[cardno]['card_modal_avg_rank'] == 'undefined')
							GlobalCardInfoObject[cardno]['card_modal_avg_rank'] = [];						
						if(typeof GlobalCardInfoObject[cardno]['card_modal_exp_rank'] == 'undefined')
							GlobalCardInfoObject[cardno]['card_modal_exp_rank'] = [];
							
						
						GlobalCardInfoObject[cardno]['card_img_url'] = cardInfo['card_img_url'];
						GlobalCardInfoObject[cardno]['data_id'] = cardInfo['group_id'];								     
						GlobalCardInfoObject[cardno]['group_id'] = cardInfo['group_id'];
						GlobalCardInfoObject[cardno]['group_name'] = cardInfo['group_name'];
						GlobalCardInfoObject[cardno]['issuer_id'] = cardInfo['issuer_id'];								     
						GlobalCardInfoObject[cardno]['issuer_name'] = cardInfo['issuer_name'];
						
						GlobalCardInfoObject[cardno]['card_modal_apr'] = cardInfo['card_modal_apr'];
						GlobalCardInfoObject[cardno]['card_modal_bt_perc'] = cardInfo['card_modal_bt_perc'];					     
						GlobalCardInfoObject[cardno]['card_modal_bt_fee'] = cardInfo['card_modal_bt_fee'];
						GlobalCardInfoObject[cardno]['card_modal_site_cnt'] = cardInfo['card_modal_site_cnt'];
						GlobalCardInfoObject[cardno]['card_modal_type_cnt'] = cardInfo['card_modal_type_cnt'];	
						GlobalCardInfoObject[cardno]['card_modal_avg_rank'] = cardInfo['card_modal_avg_rank'];				     
						GlobalCardInfoObject[cardno]['card_modal_exp_rank'] = cardInfo['card_modal_exp_rank'];
						
						var vIssuerName = cardInfo['issuer_name'];
						var vGroupName = cardInfo['group_name'];
						var vGroupImg = cardInfo['card_img_url'];
						var CardContain = $('#cm-player-contain .cm-card-'+cardno);
						CardContain.find('.cm-player-team-input').val(vGroupName);			
						AutoFillLeaguesSeasons(cardno,cardInfo['group_id'],cardInfo['issuer_id']);
						
						
						cparentCard = $('.cm-card-'+cardno);
						/*if( cardno != 5){
	            	
	                		cparentCard.clone(true).appendTo('#cm-player-contain').removeClass('cm-card-template').fadeOut(0).fadeIn(400,function(){
	                     		//automatically fill in the next card adder with the competiton/season value just selected
								cparentCard.find('.cm-player-team-input').val('');
							
								cparentCard.find('.cm-competition-dropdown').prepend('<option selected="selected" label="All Issuers" value="0">All Issuers</option>');
								cparentCard.find('.cm-season-dropdown').prepend('<option selected="selected" label="Card" value="0">Card</option>');
							});
	            		}*/       
						//alert(this.ajaxCardno);
						var pname	=	vIssuerName;
						if(pname.length > 20)
							pname		=	pname.substring(0,20);  
							
						var cname = '';
						if(vGroupName != null)
							cname	=	vGroupName;	              	                	                               
						
						/*if(cname.length > 16)
							cname		=	cname.substring(0,16);*/
							
						resultStatHtml = '<div class="cm-player-name" title="'+vIssuerName+'">'+
										pname+
									 '</div>'+
									 '<div class="cm-team-name" style="margin-top:15px; height:40px;">'+
										cname+												
									 '</div>'+
									 '<div class="cm-player-image"><div class="sq-card-fwm"></div>'+
										'<img src="'+vGroupImg+'" style="position: absolute; margin: auto; top: 10px; left: 0; right: 0; bottom: 0;">'+								
									 '</div>'+
									 '<div class="cm-player-league"></div>'+
									 '<div class="cm-player-season"></div>'+							
									 '<h3 class="card-title"></h3>'+
									 '<div class="cm-player-apps"></div>'+
									 '<div class="cm-mins-played"></div>'+
									 '<div class="clear-both"><a href="javascript:void(0)" class="remove-stat-button" data-card="'+cardno+'">Remove</a></div>';							 			
						//$('.cm-player-card').html(resultStatHtml);
						$('#cm-player-contain .cm-card-'+cardno+' .cm-player-card').html(resultStatHtml);
						
						cards.loadCardStats(cparentCard); 
						cards.refreshColumns('all');
						
						cparentCard.find('.cm-remove-button').show();
						goajaxLoading = false;
						
						
						//#Card mouser rotation css#//
						//card_hover_rotation_css();
						
					});
					
					add_stat_bottom_row(result.length);
					if( cardno != 5){
						var cardId = parseInt(cardno)+1;
	            		$('.cm-card-template').clone(true).appendTo('#cm-player-contain').removeClass('cm-card-template').addClass('cm-card-'+cardId).fadeOut(0).fadeIn(400, function(){
							$(this).find('.get-stat-button').attr('data-card',cardId);
							$(this).find('.cm-remove-button').attr('data-card',cardId);
																																													  						});
					}
					
				}
			});	
		}
	});
	//card 'GO!' button
    $(document).on('click','.get-stat-button',function(e){
		var datearray1 = $('#card_comparision_date').val().split("/");
		var cardDate = datearray1[2] + '-' + datearray1[1] + '-' + datearray1[0];													   	
		
		updateStickyClear();   		    	    		   		    		 	    		   		
		
		$('#signin-container').fadeOut(); 
					  
		var valid = 1;
		var GoObj = $(this);
		
		/*$(this).parents('.cm-player-card-back').find('.cm-input').each(function(){
			var ele = $(this);
			if( ele.val() === '' || ele.val() === 'select' ){
				if( ele.hasClass('cm-season-dropdown') || ele.hasClass('cm-competition-dropdown') )
					ele.parent().addClass('card-error');
				else
					ele.addClass('card-error');
				
				valid = 0;
			} else {
				ele.removeClass('card-error');
				ele.parent().removeClass('card-error');
			}
		});*/
		$(this).parents('.cm-player-card-back').find('.cm-season-dropdown').each(function(){
			var ele = $(this);
			if( ele.val() > 0 ){
				ele.parents('.cm-player-card-back').find('.cm-season-select').removeClass('card-error');
			} else {
				ele.parents('.cm-player-card-back').find('.cm-season-select').addClass('card-error');					
				
				valid = 0;	
			}
		});
		
		var cardno = parseInt($(this).attr('data-card'));
		//alert(typeof GlobalCardInfoObject[cardno]);
		if( valid === 1 && typeof GlobalCardInfoObject[cardno] != 'undefined'){                            	
			//alert(cardno+'=='+typeof GlobalCardInfoObject[cardno]);
			parentCard = $(this).parents('.cm-card-glow');                 
			cards.cardStates[cardno-1] = 1;
			
			var vIssuerId = GoObj.parent().find('.cm-competition-dropdown').val();
			var vGroupId = GoObj.parent().find('.cm-season-dropdown').val();
			//alert(cardno+'=='+vIssuerId+'=='+vGroupId);
			GlobalCardInfoObject[cardno]['issuer_id'] = vIssuerId;
			GlobalCardInfoObject[cardno]['group_id'] = vGroupId; 
			GlobalCardInfoObject[cardno]['card_date'] = cardDate;
			
			ComparisonMatrix.CardNo = cardno; //alert(ComparisonMatrix.CardNo); return false;
			//alert(GlobalCardInfoObject.toSource());
			ComparisonMatrix.CardInfoObject = GlobalCardInfoObject[cardno]; 
			ComparisonMatrix.parentCard = parentCard; 	
			//alert(ComparisonMatrix.CardInfoObject.toSource());
			ComparisonMatrix.GoButtonChange(); 	
			
			//var statRow = $('#cm-stat-container').children('.cm-stat-section');
			
			//alert(GlobalCardInfoObject.toSource());
	        //setHashTags();
		}
		
		//$('.cm-card-'+cardno).children().children().find('.cm-player-card-back').html('');
	});
	//card column deletion
    $(document).on('click','.remove-stat-button', function(e){ 
			  	    	    	
    	//if(ajaxLoading) { e.preventDefault(); return; } 
    	//ajaxLoading = true;    		
            //ajaxLoading = true; 
		var card = $(this).parents('.cm-card-glow'),
		cardno = $(this).attr('data-card'),
        compVal = card.find('.cm-competition-dropdown').val(),
        seasonVal = card.find('.cm-season-dropdown').val(); 
		deleteGlobalCardObject(cardno); 
        updateStickyClear();
		//WaitAllCards();        
        card.animate({
            opacity: 0,
            width: '0px'
        },400,function(){
            card.remove();            
            //add a adder card back in if non present
            if( $('#cm-player-contain').children('.cm-card-adder').length == 0  ){ 
                $('.cm-card-template').clone(true).appendTo('#cm-player-contain').removeClass('cm-card-template').fadeOut(0).fadeIn(400,function(){                   
                   //getLeagues($(this).find('.cm-competition-dropdown'));
                   $(this).find('.cm-competition-dropdown').val('').prop('selected',true);
                   //loadSeasons($(this).find('.cm-competition-dropdown'));
                   $(this).find('.cm-season-dropdown')
							    .find('option')
							    .remove()
							    .end()
							    .append('<option value="">Select</option>')
							    .val('')
							;
                });
            }   
            cards.updateCardStates(cardno-1);                       
            //go through cards and recolour    
            cards.refreshColumns('cards');                                  
            //WaitAllCards('yes');  
            //setTimeout(updateMatrixGeneratedTime, 30000); 
			var vCardLen = GlobalCardInfoObject.length;
			//alert(vCardLen);
			add_stat_bottom_row(vCardLen);
        });
		
        var cardlen = $('#cm-player-contain .cm-card-glow').length;
		//alert(cardlen);
        $('.cm-stat-section .cstat').each(function(index,ele){
			var cm_card = parseInt(cardno)+1;
			//alert(cm_card);
			//alert($(this).find('.cm-card-'+cm_card).html());
			var statsec = $(this), toremove = $(this).find('.cm-card-'+cm_card);
			//remove corresponding stats for the column
            toremove.animate({
                opacity: 0,
                width: '0px'
            },400,function(){
                toremove.remove();
                //add a stat block back in 
                //$('.cm-stat-template').clone().insertBefore($('.cm-stat-section .clearer')).removeClass('cm-stat-template').fadeOut(0).fadeIn(400);
				//alert(cardlen);
				if(3 > cardlen){
					//$('.cm-stat-template').clone().insertAfter(statsec.children('.cm-stat-title')).removeClass('cm-stat-template').fadeOut(0).fadeIn(400);
					//alert('test');
					clearStatSelection();
				}
                cards.refreshColumns('stats');
                
                /*if( index == $('.cm-stat-section .cstat').length - 1 )
                   removeStats(cm_card);  */                                                     
            });
        });
        
        //setHashTags();		                   
               
    });
	
	//loadHashUrl();

});

//var rscript = "/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi";
$.ajaxSetup({	
    dataFilter: function(data, type) {
        var prefixes = ['//', 'while(true);', 'for (;;);'],i,l,pos;
 		//data.replace(rscript, "");
 		
        if (type != 'json' && type != 'jsonp') {
            return data;
        }	        	        
 		
        for (i = 0, l = prefixes.length; i < l; i++) {
            pos = data.indexOf(prefixes[i]);
            if (pos === 0) {
                return data.substring(prefixes[i].length);
            }
        }
 
        return data;
    },    
});	

function updateDropdownOnGo()
{
	var drop_obj = $('select.CM-stat-dropdown');
	$.each(drop_obj,function(){		
		if($(this).val()!='')
			ComparisonMatrix.StatDropDownChange($(this));
	});
	//setHashTags();
}

function getMaxValue(array)
{
	var BuildArray = [];
	$.each(array,function(key,value){
		$.each(value,function(key,value){
			BuildArray.push(value);
		});		
	});		
	return Math.max.apply(Math,BuildArray);	
} 

function getCleanString(string)
{
	var nstr = $.trim(string);
	return nstr.toLowerCase().replace(/\s/g , "_").replace(/\'/g , "$");
}

function getUnCleanString(string)
{
	var nstr = $.trim(string);
	return capitaliseFirstLetter(nstr.replace(/(_)/g,' ').replace(/\$/g , "'"));
}

function getUnCleanStatsString(string)
{
	var nstr = $.trim(string);
	return nstr.toUpperCase().replace(/(_)/g,' ');
}
function setHashTags()
{
	var CardContain = $('#cm-player-contain');	
	var hashbuild = '';
	var total_cards = Object.keys(GlobalCardInfoObject).length;	
	//alert(GlobalCardInfoObject.toSource());
	$.each(GlobalCardInfoObject,function(key,value){
		//alert(key+'=='+value.toSource());
	});
}
function setHashTags_old()
{
	var CardContain = $('#cm-player-contain');	
	var hashbuild = '';
	var total_cards = Object.keys(GlobalCardInfoObject).length;	
	//if(total_cards > 0){
		$.each(GlobalCardInfoObject,function(key,value){
			var cardobj = CardContain.find('.cm-card-'+key);
			var inptval = getCleanString(cardobj.find('.cm-player-team-input').val());
			var compval = cardobj.find('.cm-competition-dropdown').val();
			var compname = getCleanString(value['short_name']);
			var seasonval = cardobj.find('.cm-season-dropdown').val();			
			var seasonname = getCleanString(cardobj.find('.cm-season-dropdown option:selected').text());
			
			var data_id = value['data_id'];
			var type = value['type'];
			if(type == 'team')
				var nt = 't';
			else
				var nt = 'p';		
					
			var cclub_id = 0;		
			if(typeof GlobalCardInfoObject[key]['club_id'] != 'undefined')		
				cclub_id = GlobalCardInfoObject[key]['club_id'] > 0 ? GlobalCardInfoObject[key]['club_id'] : 0;	
					
			GlobalHashObject[key] = { 
				'input':inptval,
				'comp_id':compval,
				'group_id':seasonval,
				'type':GlobalCardInfoObject[key]['type'],
				'comp_name':compname,
				'season_name':seasonname,
				'club_id':cclub_id,			
			};
			if(compname != '' && compname != 'select' && seasonname != '' && seasonname != 'select' && inptval != '' && compval > 0 && seasonval > 0 && data_id > 0){
				if(key == 0)
					hashbuild += '#'+compname+'/'+seasonname+'/'+inptval+'/'+compval+'/'+seasonval+'/'+data_id+'/'+cclub_id+'/'+nt;
				else
					hashbuild += '|'+compname+'/'+seasonname+'/'+inptval+'/'+compval+'/'+seasonval+'/'+data_id+'/'+cclub_id+'/'+nt;	
			}		
		}); 
				
		
			var inc = 0;
			$.each(GlobalStatsSelect,function(key,value){
				var stat_val = getCleanString(value);		
				if(inc == 0)
					hashbuild += '#'+stat_val;
				else
					hashbuild += '/'+stat_val;				
				inc++;	
			});
		hashbuild += '#'+FlterSelected;	
	//}		
			
	if(hashbuild != '')
		Hash.add(hashbuild);
	
	//updateBitlyUrl();			
}

/** URL HASH **/
var Hash = {	
	addObject		: function(params){	  
	   var HashAppend = ''; 
	   $.each(params,function(key,value){	   	    
	   	    HashAppend += key+'|'+value;	   		   		
	   });	   
	   window.location.hash = HashAppend;	
	},
	add				: function(params){	  	   	  
	   window.location.hash = params;	
	}
}
function add_stat_bottom_row(cardcount){
	$('#card_matrix_body').html('<tr><td style="vertical-align:middle; text-align:center;" colspan="7"><img src="../images/loader.gif"/></td></tr>');
	var vHtmlContent = '';
	if(cardcount > 0){
		for (i = 1; i <= cardcount; i++) { 
			var objLoop = parseInt(i)-1;
			//alert(GlobalCardInfoObject[objLoop].toSource());
			var card_grp_name = GlobalCardInfoObject[objLoop]['group_name'];
			var card_grp_name_full = card_grp_name;
			if(card_grp_name.length > 40){
				card_grp_name =	card_grp_name.substring(0,40); 
			}
			
			var card_apr_stat = GlobalCardInfoObject[objLoop]['card_modal_apr'];
			if(card_apr_stat.length > 0){
				card_apr_stat = card_apr_stat+'%';
			}
			var card_bt_perc_stat = GlobalCardInfoObject[objLoop]['card_modal_bt_perc'];
			if(card_bt_perc_stat.length > 0){
				card_bt_perc_stat = card_bt_perc_stat+'%';
			}
			var card_btpound_stat = GlobalCardInfoObject[objLoop]['card_modal_bt_fee'];
			if(card_btpound_stat.length > 0){
				card_btpound_stat = '&pound;'+card_btpound_stat;
			}
			var card_site_stat = GlobalCardInfoObject[objLoop]['card_modal_site_cnt'];
			var card_type_stat = GlobalCardInfoObject[objLoop]['card_modal_type_cnt'];
			var card_avg_rank_stat = GlobalCardInfoObject[objLoop]['card_modal_avg_rank'];
			var card_exp_rank_stat = GlobalCardInfoObject[objLoop]['card_modal_exp_rank'];
			
			vHtmlContent += '<tr><td style="text-align:center;" title="'+card_grp_name_full+'">'+card_grp_name+'</td><td style="text-align:center;">'+card_apr_stat+'</td><td style="text-align:center;">'+card_bt_perc_stat+'</td><td style="text-align:center;">'+card_btpound_stat+'</td><td style="text-align:center;">'+card_site_stat+'</td><td style="text-align:center;">'+card_type_stat+'</td><td style="text-align:center;">'+card_exp_rank_stat+'</td></tr>';
		}
	} else {
		vHtmlContent = '<tr><td style="vertical-align:middle; text-align:center;" colspan="7">No records found</td></tr>';
	}
	$('#card_matrix_body').html(vHtmlContent);
	
}
function add_stat_bottom_row_bk(cardcount){
	//alert(GlobalCardInfoObject.toSource());
	//alert(cardcount);
	for (i = 1; i <= cardcount; i++) { 
		var objLoop = parseInt(i)-1;
		//alert(GlobalCardInfoObject[objLoop]['card_modal_apr']);
		var vCardAprHtml = $('#card-apr-rate .cm-card-'+i).html();
		//alert(typeof vCardAprHtml);
		if(typeof vCardAprHtml=='undefined'){
			$('.cm-stat-template').clone().insertAfter($('#card-apr-rate .cm-card-'+objLoop)).removeClass('cm-stat-template').addClass('cm-card-'+i).fadeOut(0).fadeIn(400);
		}
		//CARD APR RATE DIV
		$('#card-apr-rate .cm-card-'+i).css('display','block');		
		//alert($('#card-apr-rate .cm-card-'+i).css('display'));
		if($('#card-apr-rate .cm-card-'+i).css('display')=='block'){
			statvals = [];
			//temp stats for testing functionality
			//var tmpstat = Math.round(Math.random() * (100 - 10) + 10);
			var card_apr_stat = GlobalCardInfoObject[objLoop]['card_modal_apr'];
			cards.loadStatBlock($('#card-apr-rate .cm-card-'+i),card_apr_stat,card_apr_stat,'%');
			
			$('#card-apr-rate .cm-card-'+i).children('.cm-stat-value').attr('data-statval',card_apr_stat);
			
			//$(this).find('.cm-stat-value[data-statval="'+markval+'"]').parent().find('.cm-star').removeClass('hidden');
			$('#card-apr-rate .cm-card-'+i).find('.cm-star').removeClass('hidden');
			
			// animate share tool bar into view to catch attention
			$('#share-prompt').css({'margin-left':'-30px',opacity:0});
			$('#share-prompt').animate({
				'margin-left': '0px',
				opacity : 1
			},800);
		}
		
		
		//CARD BALANCE TRANSFER PERC
		var vCardBtPerc = $('#card-bt-perc .cm-card-'+i).html();
		//alert(typeof vCardAprHtml);
		if(typeof vCardBtPerc=='undefined'){
			$('.cm-stat-template').clone().insertAfter($('#card-bt-perc .cm-card-'+objLoop)).removeClass('cm-stat-template').addClass('cm-card-'+i).fadeOut(0).fadeIn(400);
		}
		$('#card-bt-perc .cm-card-'+i).css('display','block');
			
		if($('#card-bt-perc .cm-card-'+i).css('display')=='block'){
			statvals = [];
			//temp stats for testing functionality
			//var tmpstat = Math.round(Math.random() * (100 - 10) + 10);
			var card_bt_perc_stat = GlobalCardInfoObject[objLoop]['card_modal_bt_perc'];
			cards.loadStatBlock($('#card-bt-perc .cm-card-'+i),card_bt_perc_stat,card_bt_perc_stat,'%');
			
			$('#card-bt-perc .cm-card-'+i).children('.cm-stat-value').attr('data-statval',card_bt_perc_stat);
			
			//$(this).find('.cm-stat-value[data-statval="'+markval+'"]').parent().find('.cm-star').removeClass('hidden');
			$('#card-bt-perc .cm-card-'+i).find('.cm-star').removeClass('hidden');
			
			// animate share tool bar into view to catch attention
			$('#share-prompt').css({'margin-left':'-30px',opacity:0});
			$('#share-prompt').animate({
				'margin-left': '0px',
				opacity : 1
			},800);
		}
		
		//CARD BT FEE
		var vCardBtFee = $('#card-bt-pound .cm-card-'+i).html();
		//alert(typeof vCardBtFee);
		if(typeof vCardBtFee=='undefined'){
			$('.cm-stat-template').clone().insertAfter($('#card-bt-pound .cm-card-'+objLoop)).removeClass('cm-stat-template').addClass('cm-card-'+i).fadeOut(0).fadeIn(400);
		}
		$('#card-bt-pound .cm-card-'+i).css('display','block');		
			
		if($('#card-bt-pound .cm-card-'+i).css('display')=='block'){
			statvals = [];
			//temp stats for testing functionality
			//var tmpstat = Math.round(Math.random() * (100 - 10) + 10);
			var card_btpound_stat = GlobalCardInfoObject[objLoop]['card_modal_bt_fee'];
			
			cards.loadStatBlock($('#card-bt-pound .cm-card-'+i),card_btpound_stat,card_btpound_stat,'&pound;');
			
			$('#card-bt-pound .cm-card-'+i).children('.cm-stat-value').attr('data-statval',card_btpound_stat);
			
			//$(this).find('.cm-stat-value[data-statval="'+markval+'"]').find('.cm-star').removeClass('hidden');
			$('#card-bt-pound .cm-card-'+i).find('.cm-star').removeClass('hidden');
			
			// animate share tool bar into view to catch attention
			$('#share-prompt').css({'margin-left':'-30px',opacity:0});
			$('#share-prompt').animate({
				'margin-left': '0px',
				opacity : 1
			},800);
		}
		
		//CARD SITE PRESENT ON
		var vCardSite = $('#card-site-present .cm-card-'+i).html();
		//alert(typeof vCardBtFee);
		if(typeof vCardSite=='undefined'){
			$('.cm-stat-template').clone().insertAfter($('#card-site-present .cm-card-'+objLoop)).removeClass('cm-stat-template').addClass('cm-card-'+i).fadeOut(0).fadeIn(400);
		}
		$('#card-site-present .cm-card-'+i).css('display','block');		
			
		if($('#card-site-present .cm-card-'+i).css('display')=='block'){
			statvals = [];
			//temp stats for testing functionality
			//var tmpstat = Math.round(Math.random() * (100 - 10) + 10);
			var card_site_stat = GlobalCardInfoObject[objLoop]['card_modal_site_cnt'];
			
			cards.loadStatBlock($('#card-site-present .cm-card-'+i),card_site_stat,card_site_stat,'');
			
			$('#card-site-present .cm-card-'+i).children('.cm-stat-value').attr('data-statval',card_site_stat);
			
			//$(this).find('.cm-stat-value[data-statval="'+markval+'"]').find('.cm-star').removeClass('hidden');
			$('#card-site-present .cm-card-'+i).find('.cm-star').removeClass('hidden');
			
			// animate share tool bar into view to catch attention
			$('#share-prompt').css({'margin-left':'-30px',opacity:0});
			$('#share-prompt').animate({
				'margin-left': '0px',
				opacity : 1
			},800);
		}
		
		
		//CARD TYPE PRESENT ON
		var vCardType = $('#card-type-show .cm-card-'+i).html();
		//alert(typeof vCardBtFee);
		if(typeof vCardType=='undefined'){
			$('.cm-stat-template').clone().insertAfter($('#card-type-show .cm-card-'+objLoop)).removeClass('cm-stat-template').addClass('cm-card-'+i).fadeOut(0).fadeIn(400);
		}
		$('#card-type-show .cm-card-'+i).css('display','block');		
			
		if($('#card-type-show .cm-card-'+i).css('display')=='block'){
			statvals = [];
			//temp stats for testing functionality
			//var tmpstat = Math.round(Math.random() * (100 - 10) + 10);
			var card_type_stat = GlobalCardInfoObject[objLoop]['card_modal_type_cnt'];
			
			cards.loadStatBlock($('#card-type-show .cm-card-'+i),card_type_stat,card_type_stat,'');
			
			$('#card-type-show .cm-card-'+i).children('.cm-stat-value').attr('data-statval',card_type_stat);
			
			//$(this).find('.cm-stat-value[data-statval="'+markval+'"]').find('.cm-star').removeClass('hidden');
			$('#card-type-show .cm-card-'+i).find('.cm-star').removeClass('hidden');
			
			// animate share tool bar into view to catch attention
			$('#share-prompt').css({'margin-left':'-30px',opacity:0});
			$('#share-prompt').animate({
				'margin-left': '0px',
				opacity : 1
			},800);
		}
		
		//CARD AVERAGE RANK
		var vCardAvgRank = $('#card-avg-rank .cm-card-'+i).html();
		//alert(typeof vCardBtFee);
		if(typeof vCardAvgRank=='undefined'){
			$('.cm-stat-template').clone().insertAfter($('#card-avg-rank .cm-card-'+objLoop)).removeClass('cm-stat-template').addClass('cm-card-'+i).fadeOut(0).fadeIn(400);
		}
		$('#card-avg-rank .cm-card-'+i).css('display','block');
			
		if($('#card-avg-rank .cm-card-'+i).css('display')=='block'){
			statvals = [];
			//temp stats for testing functionality
			//var tmpstat = Math.round(Math.random() * (100 - 10) + 10);
			var card_exp_rank_stat = GlobalCardInfoObject[objLoop]['card_modal_exp_rank'];
			var card_avg_rank_stat = GlobalCardInfoObject[objLoop]['card_modal_avg_rank'];
			cards.loadStatBlock($('#card-avg-rank .cm-card-'+i),card_avg_rank_stat,card_avg_rank_stat,'');
			
			$('#card-avg-rank .cm-card-'+i).children('.cm-stat-value').attr('data-statval',card_exp_rank_stat);
			
			//$(this).find('.cm-stat-value[data-statval="'+markval+'"]').parent().find('.cm-star').removeClass('hidden');
			$('#card-avg-rank .cm-card-'+i).find('.cm-star').removeClass('hidden');
			
			// animate share tool bar into view to catch attention
			$('#share-prompt').css({'margin-left':'-30px',opacity:0});
			$('#share-prompt').animate({
				'margin-left': '0px',
				opacity : 1
			},800);
		}
	}
	card_hover_rotation_css();
}
function card_hover_rotation_css(){
	/*$( ".cm-card-0" ).hover(function() {
		$( this ).children('.cm-player-card-outer').find('.cm-player-card').addClass( "hidden" );
	  }, function() {
		$( this ).children('.cm-player-card-outer').find('.cm-player-card').removeClass( "hidden" );
	  }
	);
	
	$( ".cm-card-1" ).hover(function() {
		$( this ).children('.cm-player-card-outer').find('.cm-player-card').addClass( "hidden" );
	  }, function() {
		$( this ).children('.cm-player-card-outer').find('.cm-player-card').removeClass( "hidden" );
	  }
	);
	
	$( ".cm-card-2" ).hover(function() {
		$( this ).children('.cm-player-card-outer').find('.cm-player-card').addClass( "hidden" );
	  }, function() {
		$( this ).children('.cm-player-card-outer').find('.cm-player-card').removeClass( "hidden" );
	  }
	);
	
	$( ".cm-card-3" ).hover(function() {
		$( this ).children('.cm-player-card-outer').find('.cm-player-card').addClass( "hidden" );
	  }, function() {
		$( this ).children('.cm-player-card-outer').find('.cm-player-card').removeClass( "hidden" );
	  }
	);
	
	$( ".cm-card-4" ).hover(function() {
		$( this ).children('.cm-player-card-outer').find('.cm-player-card').addClass( "hidden" );
	  }, function() {
		$( this ).children('.cm-player-card-outer').find('.cm-player-card').removeClass( "hidden" );
	  }
	);*/	
}
function save_card_comparison_report(){
	var datearray1 = $('#card_comparision_date').val().split("/");													   						
	var vReportDate = datearray1[2] + '-' + datearray1[1] + '-' + datearray1[0];
	
	var vHidReportId = jQuery('#hidReportId').val();
	var vReportName = jQuery('#txtReportName').val();
	if(vReportName==''){
		jQuery('#err_report_name').html('Please enter report name');
		return false;
	} else {
		jQuery.ajax({
			type: "GET",
			url: "../competitor-cards/card-comparison-ajax",
			data: "action=check_report_name&hid_report_id="+vHidReportId+"&report_name="+vReportName+"&report_date="+vReportDate+"&rand="+Math.random(),
			dataType: "html",
			success: function(html){
				//alert(html);
				if(html > 0){
					jQuery('#err_report_name').html('This report name already exist');
					setTimeout(function(){
						jQuery('#err_report_name').html('');
					}, 1000);
					return false;
				} else {
					var total_cards = Object.keys(GlobalCardInfoObject).length;	
					if(total_cards > 0){
						
						var vReportVal = $('#sel_load_report').val();
						var vCardReportCnt = $('#hidCardReportCnt').val();
						var arrCardInfo = [];
						var CardContain = $('#cm-player-contain');	
						var hashbuild = '';
						
						if(total_cards > 0){
							//alert(GlobalCardInfoObject.toSource());
							$.each(GlobalCardInfoObject,function(key,value){
								jQuery.ajax({
									type	: 'GET',
									url		: "../competitor-cards/card-comparison-ajax",
									data: "action=save_card_comparison_report&user_report_id="+vCardReportCnt+"&issuer_id="+value['issuer_id']+"&group_id="+value['group_id']+"&curr_report_id="+vReportVal+"&report_date="+vReportDate+"&report_name="+vReportName+"&rand="+Math.random(),
									success	: function(getData){
										//alert('added successfully');
										window.location.reload();
									}
								});
							});
						}
						return true;
					} else {
						jQuery('#err_report_name').html('Please add atleast one card');
						setTimeout(function(){
							jQuery('#err_report_name').html('');
						}, 1500);
						return false;	
					}
				}
			}
		 });
	}
	return false;
}
function updateCardsHashes(cards,statText)
{		
	//alert(cards+'=='+statText);
	buildCards(cards);
	//updateHashStatsRow(statText); 
	$.each(cards,function(key,value){ 				 
					
		var ncards = value.split('/');
		var cardno = key;
		//alert(ncards+'=='+cardno);
		//console.log('CCCC'+cardno);
		var cardType = ncards[ncards.length-1] == 't' ? 'team' : 'player';
		var data_id = ncards[ncards.length-3];
		var group_id = ncards[ncards.length-4];		
		var comp_id = ncards[ncards.length-5];	
		var club_id = ncards[ncards.length-2];
		var data_text = getUnCleanString(ncards[ncards.length-6]);
		var CardContain = $('#cm-player-contain .cm-card-'+cardno); 
		
		getTeamPlayerName(key, value);	
		
		if((cardno+1) == cards.length)
		{
			var parentCard = $('#cm-player-contain .cm-card-glow').last();			
		    if(cards.length < 5)
		    {
		    	
		    	var ncl = parentCard.clone(true);
			    ncl.removeClass('cm-card-0 cm-card-1 cm-card-2 cm-card-3 cm-card-4');
			    ncl.addClass('cm-card-'+(cards.length+1)).addClass('cm-card-adder');
			    ncl.find('.get-stat-button').attr('data-card', (cards.length+1));
			    ncl.find('.cm-remove-button').attr('data-card', (cards.length+1));
			    ncl.find('.cm-player-team-input').val('');
		        //ncl.find('.cm-competition-dropdown').val(parentCard.find('.cm-competition-dropdown').val());
		       // ncl.find('.cm-season-dropdown').val(parentCard.find('.cm-season-dropdown').val());		       
		       ncl.find('.cm-season-dropdown')
							    .find('option')
							    .remove()
							    .end()
							    .append('<option value="">Select</option>')
							    .val('')
							;	
		        ncl.appendTo('#cm-player-contain');
		        //loadSeasons(ncl.find('.cm-competition-dropdown')); 
		    }
		}		
										
	});		
	/*if(login_user == 0)
	{			
		$('#signin-container').fadeIn(800);					
	}*/
	urlHash = 0;			
}

function buildCards(cards)
{	
	var cloneVar = $('#cm-player-contain .cm-card-glow').last(); 
	var ncloneVar = cloneVar; 
	//var lastCardNo = parseInt(cloneVar.find('.get-stat-button').attr('data-card'));
	cloneVar.remove();	
	$.each(cards,function(key,value){								
		var cardno = key;		
		//console.log('BBBBBBB'+cardno);
		var newHt = cloneVar.clone(true).appendTo('#cm-player-contain');		
		newHt.removeClass('cm-card-adder');		  
		newHt.removeClass('cm-card-0 cm-card-1 cm-card-2 cm-card-3 cm-card-4');
        newHt.addClass('cm-card-'+cardno);
        newHt.find('.get-stat-button').attr('data-card', cardno);
        newHt.find('.cm-remove-button').attr('data-card', cardno);
    });   
    
    /*if(login_user == 0)
	{			
		$('#signin-container').fadeIn(800);					
	}*/
    
    //$('#cm-player-contain .cm-card-glow:last').addClass('cm-card-adder');      	
}

function updateHashStatsRow(value)
{
	var SplitHashStats = value.split('/');
	if(SplitHashStats.length > 0)
	{						
		$.each(SplitHashStats,function(key,value){			
			if(value == 'total' || value == 'avg' || value == '90') return;
			var lastStatRow = $('.cm-stat-section').last();  
			var clastStatRow = lastStatRow;			
			//lastStatRow.remove();				
				var text = getUnCleanStatsString(value);							          
			    //duplicate
			    var newSt = lastStatRow.clone(true).appendTo('#cm-stat-container');
			    newSt.find('.cm-stat-action').show();			    
			    $('option',newSt).filter(function() { return $.trim( $(this).text() ) == text; }).attr('selected',true); 
			    //newSt.trigger('change'); 				    
				if((key+1) == SplitHashStats.length)
				{					
					$('.cm-stat-section').first().remove();
					//return false;
					$('.cm-stat-section').last().clone(true).appendTo('#cm-stat-container');
					$('.cm-stat-section select').last().val('');
				}							
		});
		$('.cm-stat-section:last .cm-stat-action').hide();  		
	}		
}
function updateHashStatsRow_new(SplitHashStats)
{
							
	$.each(SplitHashStats,function(key,value){			
		if(value == 'total' || value == 'avg' || value == '90') return;
		var lastStatRow = $('.cm-stat-section').last();  
		var clastStatRow = lastStatRow;			
		//lastStatRow.remove();				
			var text = getUnCleanStatsString(value);							          
			//duplicate
			var newSt = lastStatRow.clone(true).appendTo('#cm-stat-container');
			newSt.find('.cm-stat-action').show();			    
			$('option',newSt).filter(function() { return $.trim( $(this).text() ) == text; }).attr('selected',true); 
			//newSt.trigger('change'); 				    
			if((key+1) == SplitHashStats.length)
			{					
				$('.cm-stat-section').first().remove();
				//return false;
				$('.cm-stat-section').last().clone(true).appendTo('#cm-stat-container');
				$('.cm-stat-section select').last().val('');
			}							
	});
	$('.cm-stat-section:last .cm-stat-action').hide();  		
	
}
function buildStats(text)
{		
    var lastStatRow = $('.cm-stat-section').last();  
    var clastStatRow = lastStatRow;
    lastStatRow.remove();      
    //duplicate
    var newSt = lastStatRow.clone(true).appendTo('#cm-stat-container');
    $('.cm-stat-action').show();
    $('.cm-stat-section:last .cm-stat-action').hide();  
    $('option',newSt).filter(function() { return $.trim( $(this).text() ) == text; }).attr('selected',true);
    newSt.trigger('change');          	
}

function loadHashUrl(){
	urlHash = $(location).attr('hash').length;
	//url = '#Nationwide/74/Nationwide Balance Transfer/172/http://192.168.1.3/sherlock/cc_images/1/25/nationwide_bt_logo.gif#total';
	//alert(window.location.hash);
	//urlHash = url.length;
	var urlHashobj = unescape($(location).attr('hash'));	
	if(urlHash > 0)
	{		
		var SplitHash = urlHashobj.split('#');
		var CardHash = SplitHash[1]; 
		var StatHash = SplitHash[2];
		var FilterHash = SplitHash[SplitHash.length-1];
		var Cards = CardHash.split('|');			
		updateCardsHashes(Cards,StatHash);	
		FlterSelected = FilterHash;	
		if(FlterSelected == 'total')
		{			
			 $('#list-totals').trigger('click');
		}
		else if(FlterSelected == '90')
		{
			$('#list-permetric').trigger('click');
		}
		else
		{			
			$('#list-average').trigger('click');	
		}	
	}
	
}

function deleteGlobalCardObject(cardno)
{			
	GlobalCardInfoObject.splice(cardno,1);	
}

function capitaliseFirstLetter(string)
{
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function updateAvgStatsTot()
{
	if(FlterSelected == 'avg')
	{
		var i = 1;
		$.each($('.cm-stat-section'),function(){
			var c = $(this).find('.CM-stat-dropdown');			
			if(c.val() != '')
			{
				switch(c.val())
				{
				case '2,total_score':
				  
				  break;				
				default:
				  
				}	
			}							
		});	
	}	
}

function updateSocialButtons(utype)
{	
	// Pinterest does not support Short url; Twitter & Pinterest doesn't need any HTML meta tags replacement, can give our own through APIs;
	var http = window.location.protocol;
	var slashes = http.concat("//");
	var host = slashes.concat(window.location.hostname);			
	var currentUrl = escape(host+window.location.pathname+window.location.hash);		
	 
	var sqTitle = '';
	
	$.each(GlobalCardInfoObject,function(k,v){		
		if(typeof v.dataname != 'undefined')
		{			
			if(k > 0)
				sqTitle += ' v '+v.dataname;
			else
				sqTitle += v.dataname;
		}
		
	});
	if(utype == 'twitter')
	{
		var tweetDataText = 'Just been comparing the stats of '+sqTitle+' over on @Squawka.';
		var pinDataText		=	'Compare players from all the top European Leagues using the Squawka Comparison Matrix and get instantly customised and visualised stats.';
		var tweetBoxHtml = '<a href="https://twitter.com/share" id="matrix-twitter-tweet" class="twitter-share-button twitter-share-pos" data-text="'+tweetDataText+'" data-url="'+currentBitlyObj['TW'].shortUrl+'" data-related="Squawka" >Tweet</a>';
		$('.twitter-width').html(tweetBoxHtml);		
		twttr.widgets.load();
	}	
	else if(utype == 'facebook')
	{
		var fbText = '<div class="fb-share-button" data-href="'+currentBitlyObj['FB'].shortUrl+'" data-type="button_count"></div>';
		// var fbText = '<div class="fb-like" data-href="'+currentBitlyObj.shortUrl+'" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>';		
		$('.fb-width').html(fbText);
		try{ 
			FB.XFBML.parse(); //console.log('FB OK');
		}catch(ex){ //console.log('FB Error'); 
		}
	}
	else
	{
		var gplusHtml = '<div class="g-plusone" data-size="medium" data-annotation="bubble" data-href="'+currentBitlyObj['GP'].shortUrl+'"></div>';
		$('.gplus-width').html(gplusHtml);	
		//gapi.plusone.go();
	}
	
	var pinturl = '//www.pinterest.com/pin/create/button/?url='+currentUrl+'&media='+escape(host+'/wp-content/themes/squawka_web/images/v3/squawka-social-logo.png')+'&description='+escape(pinDataText);
	var pinthtml = '<a href="'+pinturl+'" data-pin-do="buttonPin" data-pin-config="beside" id="matrix-pinit"><img src="//assets.pinterest.com/images/pidgets/pin_it_button.png" /></a>';
	$('.pint-width').html(pinthtml);
	$.ajax({ url: 'http://assets.pinterest.com/js/pinit.js', dataType: 'script', cache:false});	
			
} 

function updateBitlyUrl()
{
	var http = window.location.protocol;
	var slashes = http.concat("//");
	var host = slashes.concat(window.location.hostname);	
	var longUrl = host+'/comparison-matrix';	
	
	var curDate = new Date();
	var curMilliseconds = curDate.getTime();
	$.ajax({
	  dataType: "json",
	  url: "/comparison-matrix",
	  data: { ajax:true,longurl:longUrl,hash:window.location.hash, mode:'getBitly',cache:curMilliseconds },
	  cache: false,
	  longUrl:longUrl,	
	  hasht:window.location.hash, 			 
	  success: function(result){
	  	
	  	if(typeof currentBitlyObj['FB'] == 'undefined')currentBitlyObj['FB'] = {};
	  	if(typeof currentBitlyObj['TW'] == 'undefined')currentBitlyObj['TW'] = {};
	  	if(typeof currentBitlyObj['GP'] == 'undefined')currentBitlyObj['GP'] = {};	  	
	  	
	  	if(result['FB']['errorCode'] == 0)
	  		currentBitlyObj['FB']	=	result['FB']['results'][this.longUrl+'?__matrixsharer=true&stype=fb'+this.hasht];
	  		
	  	if(result['TW']['errorCode'] == 0)
	  		currentBitlyObj['TW']	=	result['TW']['results'][this.longUrl+this.hasht];
	  		
	  	if(result['GP']['errorCode'] == 0)
	  		currentBitlyObj['GP']	=	result['GP']['results'][this.longUrl+'?__matrixsharer=true&stype=gp'+this.hasht];	 
	  		
	  	if(result['FB']['errorCode'] == 0)	
	  	{
	  		updateSocialButtons('facebook');
		  	refreshShareButtons();		  	
	  	}
	  	
	  	if(result['TW']['errorCode'] == 0)	
	  	{
	  		updateSocialButtons('twitter');
		  	refreshShareButtons();		  	
	  	}	  		  	
	  	
	  	if(result['GP']['errorCode'] == 0)	
	  	{
	  		updateSocialButtons('google');
		  	refreshShareButtons();		  	
	  	} 			  					  		  	
	  		
	  },		   
	  error: function (xhr, textStatus, errorThrown) {
        //console.log("Error: " + (errorThrown ? errorThrown : xhr.status));
  	  },
	});	
}


function WaitAllCards(stat)
{		
	if(typeof stat == 'undefined')
		$('#cm-player-contain .cm-card-glow').find('.card-loading').fadeIn(); 
	else
		$('#cm-player-contain .cm-card-glow').find('.card-loading').fadeOut();	
}

function StatsHashArray()
{	
	GlobalStatsSelect = {};
	$('select.CM-stat-dropdown').each(function(){
		if($(this).val() != '')
			GlobalStatsSelect[$(this).val()] = $(':selected',$(this)).text();
	});
}

function getLeagues(o, comp_id, group_id)
{	
		$.ajax({		
			  dataType	: "json",
			  url		: "/comparison-matrix",
			  data		: { ajax:true, mode:'getLeagues' },			  			  			      	
			  success	: function (result) {
			  			Globalleagues = result;			
			  			o.empty();
			  			o.append('<option value="">Select</option>');
			  			$.each(result,function(k,v){
			  				o.append('<option value="'+v.issuer_id+'">'+v.competition_name+'</option>');
			  			});
			  			if(typeof comp_id != 'undefined' && typeof group_id != 'undefined')
			  			{			  						  				  			
			  				o.val(comp_id).attr("selected", true);				  							  			
							loadSeasons(o,group_id);	
			  			}		
			  						  				  			   			  				  				  	
			  },	
			  complete	: function () {
			  		
			  }, 
			  error		: function (xhr, textStatus, errorThrown) {
		        //console.log("Error: " + (errorThrown ? errorThrown : xhr.status));
		  	  },  	  
		});	
}

function AutoFillLeaguesSeasons (card_id, isGroupId, isIssuerId)
{
	var CardObj = GlobalCardInfoObject[card_id];	
	if(CardObj['data_id'] > 0)
	{
		$.ajax({		
			  dataType	: "json",
			  url		: "../competitor-cards/card-comparison-ajax",
			  data		: { ajax:true, action: 'autoFillSelectBoxData', group_id:CardObj['data_id'], type:CardObj['type'], issuer_id:0 },
			  cache		: false,
			  CardId	: card_id,
			  isGroupId : isGroupId,
			  ctype		: CardObj['type'],
			  isIssuerId  : isIssuerId,
			  beforeSend: function(){
			  		$('.cm-card-'+this.CardId).find('.card-loading').fadeIn();
			  },    			
			  success	: function (result) {
				  	var CompContainer = $('.cm-card-'+this.CardId).find('.cm-competition-dropdown');
			  		//var CompContainer = $('.cm-competition-dropdown');
					CompContainer.empty();
					CompContainer.append(result['provider_info']);
					
					var groupContainer = $('.cm-card-'+this.CardId).find('.cm-season-dropdown');
					//var groupContainer = $('.cm-season-dropdown');
					groupContainer.empty();
					groupContainer.append(result['group_info']);
			  },	
			  complete	: function () {
			  		$('.cm-card-'+this.CardId).find('.card-loading').fadeOut();
			  }, 
			  error		: function (xhr, textStatus, errorThrown) {
		        //console.log("Error: " + (errorThrown ? errorThrown : xhr.status));
		  	  },  	  
		});
	}
	else
	{
		var CompContainer = $('.cm-card-'+card_id).find('.cm-competition-dropdown');
		CompContainer.append('<option value="">Select</option>');
		if(typeof comp_id != 'undefined')
			CompContainer.val(comp_id);
		else	
			$('option:eq(1)',CompContainer).prop('selected', true);
			
		CompContainer.trigger('change');
	}		
}

function removeStats(cardno)
{
	globalCardStats.splice(cardno, 1);		
	$('select.CM-stat-dropdown').each(function(){
		StatsResult	=	[];
		var gid	=	$(this).val();
		var statRow	=	$(this).parents('.cm-stat-section');
		var statvals	=	[];
		cards.loadCardStats(statRow);
		statRow.find('.cm-star').addClass('hidden');
		if(gid != '' && globalCardStats.length > 0)
		{
			$('#stats-loading').fadeIn();
			for(k in globalCardStats)
			{
				var value	=	globalCardStats[k][gid];																									
				StatsResult.push(value['value']);					
			}					
			
			for(i in globalCardStats)
			{
				var value	=	globalCardStats[i][gid];				
				var cno = i;				
				var statObj	=	statRow.find('.cm-card-'+cno);													
				var stat_val = value['value'];					
				var percent = calculatePercent(stat_val);																																	
						
				if(gid == '4,shot_accuracy' || gid == '9,takeonspercent' || gid == '9,headed_duals_won_percent' || gid == '9,total_duals_percent' || gid == '24,distribution_accuracy' || gid == '7,pass_completion')
				{
					var prefix = '%';
					stat_val = percent;	
				}
				else if(gid == '8,avg_pass_length' || gid == '24,distribution_length')
				{
					var prefix = 'm';								
				}							
				statvals.push(percent);		
				cards.loadStatBlock(statObj,stat_val,percent,prefix);
				statObj.children('.cm-stat-value').attr('data-statval',percent);								
			}
			
			if(gid == '3,goals_conceded' || gid == '11,defensive_errors' || gid == '9,tackles_lost' || gid == '11,errors_leading_to_goal' || parseInt(gid) == 12 || gid == '9,fouls_committed')
    			var markval = Math.min.apply(Math,statvals);
		    else
		    	var markval = Math.max.apply(Math,statvals);	
		    //console.log(markval);
		    if(typeof markval != 'undefined')					        
		    	statRow.find('.cm-stat-value[data-statval="'+markval+'"]').parent().children('.cm-star').removeClass('hidden');
		}
		
	});
				  									
}

function calculatePercent(val)
{
	var maxVal	=	Math.max.apply(Math,StatsResult);
	
	if(maxVal > 0)
	{
		if(maxVal != val)
			return Math.round(((val * 100)/maxVal));
		else
			return 100;	
	}
	
	return 0;
}

function refreshShareButtons()
{
	// animate share tool bar into view to catch attention
    $('#share-prompt').css({'margin-left':'-30px',opacity:0});
    $('#share-prompt').animate({
        'margin-left': '0px',
        opacity : 1
    },800);
}

function setComparisonMatrixCookie(cname, cvalue, seconds)
{				
	var date = new Date();
    date.setTime(date.getTime() + (seconds * 1000));
    var expires = "expires=" + date.toGMTString();	
	document.cookie = cname+"="+cvalue+"; "+expires;	
}

function clearStatSelection(){
	var lastStat 	=	'<div class="cm-stat-section">'+
        	'<div id="card-apr-rate" class="cstat cs-card-1">'+
			'<div style="float:left; vertical-align:middle; height:80px; margin-top:40px; width:150px;"><b>Apr Rate</b></div>'+
			'<div class="cm-player-stat cm-card-1">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
			'<div class="cm-player-stat cm-card-2" style="display:none;">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
			'<div class="cm-player-stat cm-card-3" style="display:none;">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
			'<div class="cm-player-stat cm-card-4" style="display:none;">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
			'<div class="cm-player-stat cm-card-5" style="display:none;">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
            '</div>'+
            '<div id="card-bt-perc" class="cstat cs-card-2 clear-both">'+
			'<div style="float:left; vertical-align:middle; height:80px; margin-top:40px; width:150px;"><b>Balance Transfer %</b></div>'+
			'<div class="cm-player-stat cm-card-1">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
			'<div class="cm-player-stat cm-card-2" style="display:none;">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
			'<div class="cm-player-stat cm-card-3" style="display:none;">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
			'<div class="cm-player-stat cm-card-4" style="display:none;">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
			'<div class="cm-player-stat cm-card-5" style="display:none;">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
            '</div>'+
			'<div id="card-bt-pound" class="cstat clear-both">'+
			'<div style="float:left; vertical-align:middle; height:80px; margin-top:40px; width:150px;" class="cm-stat-title"><b>Balance Transfer &pound;</b></div>'+
            '<div class="cm-player-stat cm-card-1">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
			'<div class="cm-player-stat cm-card-2" style="display:none;">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
			'<div class="cm-player-stat cm-card-3" style="display:none;">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
			'<div class="cm-player-stat cm-card-4" style="display:none;">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
			'<div class="cm-player-stat cm-card-5" style="display:none;">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
            '<div class="clearer"></div>'+
            '</div>'+
            '<div id="card-site-present" class="cstat cs-card-3 clear-both">'+
			'<div style="float:left; vertical-align:middle; height:80px; margin-top:40px; width:150px;"><b>Sites Present On</b></div>'+
			'<div class="cm-player-stat cm-card-1">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
			'<div class="cm-player-stat cm-card-2" style="display:none;">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
			'<div class="cm-player-stat cm-card-3" style="display:none;">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
			'<div class="cm-player-stat cm-card-4" style="display:none;">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
			'<div class="cm-player-stat cm-card-5" style="display:none;">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
            '</div>'+
			'<div id="card-type-show" class="cstat clear-both">'+
			'<div style="float:left; vertical-align:middle; height:80px; margin-top:40px; width:150px;" class="cm-stat-title"><b>Categories showing in</b></div>'+
            '<div class="cm-player-stat cm-card-1">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
			'<div class="cm-player-stat cm-card-2" style="display:none;">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
			'<div class="cm-player-stat cm-card-3" style="display:none;">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
			'<div class="cm-player-stat cm-card-4" style="display:none;">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
			'<div class="cm-player-stat cm-card-5" style="display:none;">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
            '<div class="clearer"></div>'+
            '</div>'+
            '<div id="card-avg-rank" class="cstat clear-both">'+
			'<div style="float:left; vertical-align:middle; height:80px; margin-top:40px; width:150px;" class="cm-stat-title"><b>Average Rank</b></div>'+
            '<div class="cm-player-stat cm-card-1">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
			'<div class="cm-player-stat cm-card-2" style="display:none;">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
			'<div class="cm-player-stat cm-card-3" style="display:none;">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
			'<div class="cm-player-stat cm-card-4" style="display:none;">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
			'<div class="cm-player-stat cm-card-5" style="display:none;">'+
				'<span class="cm-star hidden"></span>'+
				'<div class="cm-stat-value"></div>'+
				'<div class="cm-stat-bar">'+
					'<div></div>'+
				'</div>'+
			'</div>'+
            '<div class="clearer"></div>'+
            '</div>'+
			'<div class="clearer"></div>'+
		'</div>';
	$('#cm-stat-container').html(lastStat).hide().fadeIn();	
}
function clearSelections()
{
	CachedSuggestionsObject = {};
	cacheSeasons = {};
	GlobalCardInfoObject = [];
	StatResultObject = {};
	MaxVal = null;	
	GlobalStatsCache = {};
	GlobalHashObject = {};
	GlobalStatsSelect = {};
	FlterSelected = 'total';
	urlHash = 0;
	currentBitlyObj = {};
	ajaxLoading		=	goajaxLoading	=	false;
	Globalleagues = {};
	globalCardStats	=	[];
	StatsResult	=	[];
	
	var resetCardHtml	=	'<div class="cm-card-glow cm-card-0 cm-card-adder">'+
							'<span class="addglow"></span>'+				
							'<div class="cm-player-card-outer has-hover">'+
							'<div _class="flip-contain">'+
							'<div class="cm-player-card hidden">'+
							'</div>'+						
							'<div class="cm-player-card-back">'+
							'<div class="card-loading"></div>'+
							'<p style="margin-bottom:0px;">'+
							'Select Issuer'+
							'</p>'+
							'<div class="cm-competition-select card-style-select">'+
							'<select id="sel_provider_type" name="CMStatDropdown" class="cm-competition-dropdown cm-input" _onchange="get_issuer_base_load_groups(this);">'+
							'<option value="0" label="All Issuers" selected="selected">All Issuers</option>'+vSelectIssuer+
							'</select>'+
							'</div>'+
							'<p style="margin-top:0px; margin-bottom:0px;">'+
							'Select Card'+
							'</p>'+
							'<div class="cm-season-select card-style-select">'+
							'<select id="sel_group_card" name="CMStatDropdown" class="cm-season-dropdown cm-input" _onchange="get_group_id_base_load_issuer(this);">'+
							'<option value="0" label="Card" selected="selected">Card</option>'+vSelectGroup+
							'</select>'+
							'</div>'+
							'<a class="get-stat-button" data-card="0" href="javascript:void(0)">Add card</a>'+
							'</div>'+
							'</div>'+
							'</div>'+
							'</div>';												
		
	$('#cm-player-contain').html(resetCardHtml).hide().fadeIn();
	//getLeagues($('.cm-competition-dropdown').eq(0));		
		
	//var lastStat 	=	$('#cm-stat-container .cm-stat-section:last').clone();
	
	$('#card_matrix_body').html('<tr><td style="vertical-align:middle; text-align:center;" colspan="7">No records found</td></tr>');
	
	//window.location.hash	=	'';
	//$( "body" ).scrollTop( 0 );
	//$('#create-newmatrix').fadeOut();
	//updateMatrixGeneratedTime();
	return true;
}

function updateStickyClear()
{
	if(GlobalCardInfoObject.length == 0 && Object.keys(GlobalStatsSelect).length == 0)
		$('#create-newmatrix').fadeOut();
	else
		$('#create-newmatrix').fadeIn();
}

function getTeamPlayerName(key, value)
{
		var ncards = value.split('/');
		var cardno = key;
		//console.log('CCCC'+cardno);
		var cardType = ncards[ncards.length-1] == 't' ? 'team' : 'player';
		var data_id = ncards[ncards.length-3];
		var group_id = ncards[ncards.length-4];		
		var comp_id = ncards[ncards.length-5];	
		var club_id = ncards[ncards.length-2];
		var data_text = getUnCleanString(ncards[ncards.length-6]);
		var CardContain = $('#cm-player-contain .cm-card-'+cardno); 
		
		if(CardContain)		
		{
			$.ajax({		
				  dataType	: "json",
				  url		: "/comparison-matrix",
				  data		: { ajax:true, data_id:data_id, type:cardType, mode:'getTeamPlayerName' },		  
				  cardno	: cardno,		
				  comp_id	: comp_id,    
				  group_id	: group_id,	
				  cardType	: cardType,
				  data_id	: data_id,	
				  club_id	: club_id,
				  beforeSend: function(){
			  		$('.cm-card-'+this.cardno).find('.card-loading').fadeIn();
			  	  },	
				  success	: function (result) {
				  		
				  		if(typeof GlobalCardInfoObject[this.cardno] == 'undefined')
							GlobalCardInfoObject[this.cardno] = {};							
						if(typeof GlobalCardInfoObject[this.cardno]['type'] == 'undefined')
							GlobalCardInfoObject[this.cardno]['type'] = [];
						if(typeof GlobalCardInfoObject[this.cardno]['data_id'] == 'undefined')
							GlobalCardInfoObject[this.cardno]['data_id'] = [];							
						if(typeof GlobalCardInfoObject[this.cardno]['curren_comp_id'] == 'undefined')
							GlobalCardInfoObject[this.cardno]['curren_comp_id'] = [];										
													
						GlobalCardInfoObject[this.cardno]['type'] = this.cardType;
						GlobalCardInfoObject[this.cardno]['data_id'] = this.data_id;								     
						GlobalCardInfoObject[this.cardno]['curren_comp_id'] = group_id;   										
				        			
						CardContain.find('.cm-player-team-input').val(result);			
						AutoFillLeaguesSeasons(this.cardno,this.group_id,this.club_id, this.comp_id);
				  						  			  				  				  		
				  },			   
				  error		: function (xhr, textStatus, errorThrown) {
			        //console.log("Error: " + (errorThrown ? errorThrown : xhr.status));
			  	  },  	  
			});			
		}
}

function updateMatrixGeneratedTime()
{		
	$('#matrix-gen').hide();	
	var dd 		= squawkaClock.getDate();
	var mm 		= squawkaClock.getMonth()+1; //January is 0!
	var yyyy 	= squawkaClock.getFullYear();
	
	if(dd<10) {
	    dd		=	'0'+dd
	} 
	
	if(mm<10) {
	    mm		=	'0'+mm
	}
	
	var curDate	=	dd+'/'+mm+'/'+yyyy;
	var curTime	=	$('#sq-live-time').text();	
	
	$('#matrix-gentime').text(curDate+' '+curTime+' GMT');		
	
	if(GlobalCardInfoObject.length > 0)
		$('#matrix-gen').fadeIn();	
}