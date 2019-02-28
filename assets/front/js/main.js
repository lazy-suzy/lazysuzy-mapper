jQuery(document).ready(function($) {
	var isLateralNavAnimating = false;
	var error_msg = "";

	$("#log-modal,#reg-modal").iziModal({
		width: "70%",
		top: "5%",
		closeButton: true
	});

	/*$("#login-link").on("click", function() {
		$("#log-modal").iziModal("open");
	});*/
	$("#register-link").on("click", function() {
		$("#reg-modal").iziModal("open");
	});

	$("#sign-up").on("click", function() {
		$("#log-modal").iziModal("close");
		$("#reg-modal").iziModal("open");
	});

	$("#sign-in").on("click", function() {
		$("#reg-modal").iziModal("close");
		$("#log-modal").iziModal("open");
	});

	var urlMap = location.pathname.split("/");
	var loginCodes = ['2', '77', '78'];
	var regCodes = ['3', '1', '79'];
	if (urlMap.length > 0) {
		var error_code = urlMap.pop();

		if (error_code == 3) {
			$("#reg-modal").iziModal("open");
			error_msg = "Oops! Seems like your email is already registered.";
		} else if (error_code == 1) {
			$("#reg-modal").iziModal("open");
			error_msg =
				"Oops! Seems like we have hit a bump. Will be back soon.";
		} else if (error_code == 2) {
			$("#log-modal").iziModal("open");
			error_msg = "Oops! Wrong E-Mail or Password. Please try again.";
		} else if (error_code == 77) {
			$('#log-modal').iziModal('open');
			error_msg = 'Please verify your mail to login.';
		} else if (error_code == 78) { 
			$("#log-modal").iziModal("open");
			error_msg = "Please login using your E-Mail and Password."
		}
		else if (error_code == 79){
			$("#reg-modal").iziModal("open");
			error_msg = "Sorry, could not verify your E-Mial.";
		}
		else {
			error_msg = "";
		}


		if (regCodes.includes(error_code)){
			alert('true')
			document.getElementById("error-msg-reg").innerText = error_msg;
		}
		else if (loginCodes.includes(error_code))
			document.getElementById('error-msg-log').innerText = error_msg;
		else {

		}

	}

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
