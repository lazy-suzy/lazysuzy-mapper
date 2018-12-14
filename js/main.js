jQuery(document).ready(function($) {
	var isLateralNavAnimating = false;
	$("#log-modal,#reg-modal").iziModal({
		width: "70%",
		top: "5%",
		closeButton: true
	});

	$("#login-link").on("click", function() {
		$("#log-modal").iziModal("open");
	});
	$("#register-link").on("click", function() {
		$("#reg-modal").iziModal("open");
	});

	$('#sign-up').on('click',function(){
		$('#log-modal').iziModal("close");
		$('#reg-modal').iziModal("open");
	});

	$('#sign-in').on('click',function(){
		$('#reg-modal').iziModal("close");
		$('#log-modal').iziModal("open");
	})


	//open/close lateral navigation
	$(".cd-nav-trigger").on("click", function(event) {
		event.preventDefault();
		//stop if nav animation is running
		if (!isLateralNavAnimating) {
			if ($(this).parents(".csstransitions").length > 0)
				isLateralNavAnimating = true;

			$("body").toggleClass("navigation-is-open");
			$(".cd-navigation-wrapper").one(
				"webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend",
				function() {
					//animation is over
					isLateralNavAnimating = false;
				}
			);
		}
	});
	var googleUser = {};
	var startApp = function() {
		gapi.load("auth2", function() {
			// Retrieve the singleton for the GoogleAuth library and set up the client.
			auth2 = gapi.auth2.init({
				client_id: "YOUR_CLIENT_ID.apps.googleusercontent.com",
				cookiepolicy: "single_host_origin"
				// Request scopes in addition to 'profile' and 'email'
				//scope: 'additional_scope'
			});
			attachSignin(document.getElementById("customBtn-google"));
		});
	};

	function attachSignin(element) {
		console.log(element.id);
		auth2.attachClickHandler(
			element,
			{},
			function(googleUser) {
				document.getElementById("name").innerText =
					"Signed in: " + googleUser.getBasicProfile().getName();
			},
			function(error) {
				alert(JSON.stringify(error, undefined, 2));
			}
		);
	}
	startApp();

	/*$('.owl-carousel').owlCarousel({
	  	items: 4,
	  	margin: 10,
	  	loop: true,
	  	center: true,
	  	mousedrag: true,
	  	touchdrag: true,
	  	nav: true,
	  	navText : ['<i class="fa fa-angle-left" aria-hidden="true"></i>','<i class="fa fa-angle-right" aria-hidden="true"></i>']

	  });*/
});
