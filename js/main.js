jQuery(document).ready(function($){
	var isLateralNavAnimating = false;
	
	//open/close lateral navigation
	$('.cd-nav-trigger').on('click', function(event){
		event.preventDefault();
		//stop if nav animation is running 
		if( !isLateralNavAnimating ) {
			if($(this).parents('.csstransitions').length > 0 ) isLateralNavAnimating = true; 
			
			$('body').toggleClass('navigation-is-open');
			$('.cd-navigation-wrapper').one('webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend', function(){
				//animation is over
				isLateralNavAnimating = false;
			});
		}
	});

	  $('.owl-carousel').owlCarousel({
	  	items: 4,
	  	margin: 10,
	  	loop: true,
	  	center: true,
	  	mousedrag: true,
	  	touchdrag: true,
	  	nav: true,
	  	navText : ['<i class="fa fa-angle-left" aria-hidden="true"></i>','<i class="fa fa-angle-right" aria-hidden="true"></i>']

	  });
	
});