<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/izimodal/1.5.1/js/iziModal.min.js"></script>
<script src="<?php echo base_url(); ?>assets/front/js/ion.rangeSlider.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-lazyload/11.0.2/lazyload.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.js"></script>
<style>.product-modal{display:none;}</style>

	<script>
		function popup_box(id)
		{
			$('#product-modal'+id).iziModal({
				width: '70%',
				top: 65,
				closeButton: true,
				onOpening: function(modal) {

					//var pro_id = event.target.dataset.target;
					var sitename = $("#sitename").val();

					modal.startLoading();

					setTimeout(function() {
						modal.stopLoading();
					}, 3000)
				}
			});

			$('#product-modal'+id).iziModal('open');
		}

		$(function() {
			//lazyload();
			var hearts = document.getElementsByClassName('product-heart');
			for( var i = 0; i < hearts.length; i++) {
				hearts[i].addEventListener('click', function(e) {
					e.preventDefault();
					console.log(this);
					this.classList.toggle('far');
					this.classList.toggle('fas');
					this.classList.toggle('heart-red');
					this.classList.toggle('bounce');
				})
			}

			/**
			 * Format a number as a string with commas separating the thousands.
			 * @param num - The number to be formatted (e.g. 10000)
			 * @return A string representing the formatted number (e.g. "10,000")
			 */
			var formatNumber = function(num) {
				var array = num.toString().split('');
				var index = -3;
				while (array.length + index > 0) {
					array.splice(index, 0, ',');
					// Decrement by 4 since we just added another unit to the array.
					index -= 4;
				}
				return array.join('');
			};

			$(".heart").on("click", function() {
				$(this).toggleClass("is-active");
			});

			$(".expandable-search").on('focus', function(e) {
				$('#logo-small').toggle('d-none')
			})

			$(".expandable-search").on('blur', function(e) {
				$('#logo-small').toggle('d-none')
				$(this).val("")
			})
			$('.dropdown-menu a.dropdown-toggle').on('click', function(e) {
			  if (!$(this).next().hasClass('show')) {
				$(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
			  }
			  var $subMenu = $(this).next(".dropdown-menu");
			  $subMenu.toggleClass('show');

			  $(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function(e) {
				$('.dropdown-submenu .show').removeClass("show");
			  });

			  return false;
			});
			$("#call-filters").on('click', function(e) {
				//alert("ASd");
				$(".filter-fun").toggleClass('d-none');
				$(".filter-fun").toggleClass('flipInX');
				//$(".filter-fun").toggleClass('flipOutX');

			})


		});

	</script>
	<!-- <script type="text/javascript">
		var vglnk = {key: '7c7cd49fe471830c75c9967f05d5f292'};
		(function(d, t) {
			var s = d.createElement(t);
				s.type = 'text/javascript';
				s.async = true;
				s.src = '//cdn.viglink.com/api/vglnk.js';
			var r = d.getElementsByTagName(t)[0];
				r.parentNode.insertBefore(s, r);
		}(document, 'script'));
	</script> -->

    </body>
</html>