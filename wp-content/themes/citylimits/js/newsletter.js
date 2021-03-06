jQuery(document).ready(function($) {
	var homebannerCounter = 0,
	    signupH, signupOpenH, submitted = false;

	function setBannerSizes() {
		signupH = $('.newsletter-signup .not-expanded').outerHeight() + 'px';
		signupOpenH = $('.newsletter-signup .expanded').outerHeight() + 'px';
		//$('.newsletter-signup.maincolumn').css({'height': signupOpenH, 'max-height': signupH})

		//set height for thanks in footer
		signupFooterH = $('.newsletter-signup.footer form').outerHeight() + 'px';
		$('.newsletter-signup.footer .newsletter-response').css({'height': signupFooterH});
	}

	if ($('.newsletter-signup.maincolumn .not-expanded').length) {
		setBannerSizes();

		$('.newsletter-signup .not-expanded').on('mouseover click', function(e) {
			if (submitted) {
				return;
			}
			var $this = $(this);
			var $parent = $this.parent();
			var $expanded = $parent.find('.expanded');
			var $notexpanded = $parent.find('.not-expanded');
			if (e.originalEvent.type == 'click' || homebannerCounter < 3) {
				$parent.addClass('open');
				$notexpanded.hide();
				$expanded.show();
			}
			$expanded.mouseleave(function(e) {
				//don't hide if they're in the middle of filling it out.
				if (
					$expanded.find('input[name=newsletter_fname]').val()
					|| $expanded.find('input[name=newsletter_email]').val()
				) {
					return
				}
				if (homebannerCounter <= 3 && !submitted) {
					$parent.removeClass('open');
					$notexpanded.show();
					$(this).hide();
				}
			});
			homebannerCounter++;
		});
	}
	
	var footerShown = false;
	//show footer on scroll for wide layouts
	$(window).scroll(function() {
		if (
			!submitted 
			&& $(window).width() >= 769 
			&& !footerShown 
			&& $(window).scrollTop() + $(window).height() >= $('#site-footer').position().top
			&& !$('body').hasClass('newsletter-landing')
		) {
			footerShown = true;
			setTimeout(function() {
				$('.newsletter-signup.footer').css({'max-height': '500px'});
			}, 1000);
		}
	});
	
	//show footer on timeout for narrow
	if ($(window).width() < 769 
	&& !Cookies.get('newsletter_modal_snooze')
	&& !$('body').hasClass('newsletter-landing')) {
		var footerH = $('.newsletter-signup.footer.mobile .mobile_footer_content').outerHeight();
		setTimeout(function() {
			$('.newsletter-signup.footer.mobile').css({'max-height': (footerH + 183) + 'px'});
		}, 10000);
	}
			
	$('.newsletter-signup.footer .close_box').click(function(e) {
		$('.newsletter-signup.footer').css({'max-height': 0});
		Cookies.set('newsletter_modal_snooze', true, {expires: 7 * 24 * 60 * 60});//7 days
	});

	//HTML 5 validate checkbox group
	$('.newsletter-signup form input[type=submit]').click(function(e) {
		$this_form = $(this).parents('form');
		$cbx_group = $this_form.find("input:checkbox[name='newsletter[]']");
		$cbx_group.prop('required', true);
		if ($cbx_group.is(":checked")) {
			$cbx_group.prop('required', false);
		}
	})

	
	//Newsletter Landing Page
	if ($('body').hasClass('newsletter-landing')) {
		var cartTop
		$('#content .subscribe_button').click(function() {
			if ($(this).hasClass('selected')) {
				return
			}
			$(this).addClass('selected')
			var id = $(this).attr('data-newsletter-id')
			var title = $(this).parent().find('.newsletter_title').text()
			var entry = $('<li class="newsletter_to_subscribe" data-newsletter-id="' + id + '">' + title + '<div class="remove"></div></li>')
			$('#selected_newsletters').append(entry)
			$('#newsletter_cart').show()
			if (cartTop == undefined) {
				cartTop = $('#newsletter_cart').offset().top - 19
			}
			$(window).resize()
			$(window).scroll()
		
			entry.children('.remove').click(function() {
				var id = $(this).parent().attr('data-newsletter-id')
				$('.subscribe_button[data-newsletter-id=' + id + ']').removeClass('selected')
				$(this).parent().remove()
				if (!$('#selected_newsletters .newsletter_to_subscribe').length) {
					$('#newsletter_cart').hide()
				}
			})
		})
		
		$(window).scroll(function() {
			if (!$('#newsletter_cart').is(':visible') || $(window).width() < 769) {
				return
			}
			if ($(window).scrollTop() > cartTop) {
				$('#newsletter_cart').css({position: 'fixed', top: 0})
			} else {
				$('#newsletter_cart').css({position: 'absolute', top: 'auto'})
			}
		})
	}
	
	$(window).resize(function() {
		if ($(window).width() < 769) {
			$('#newsletter_cart').css({width: 'auto', position: 'static'});
		} else {
			$('#newsletter_cart').width($('#sidebar').width() - 53)
			if ($('.newsletter-signup.maincolumn .not-expanded').length) {
				setBannerSizes()
			}
		}
	})

	// actions to take place once the form is submitted, such as 
	// displaying the success message
	function form_submission_success(message) {

		if ($('body').hasClass('newsletter-landing')) {
			$('#main').html(message);
		} else {
			$('.newsletter-signup form, .newsletter-signup .not-expanded').hide();
			$('.newsletter-response-content').html(message);
			$('.newsletter-response').show();
			$('.newsletter-signup.maincolumn').removeClass('open');
		}

	}
	

	/*FORM SUBMIT*/
	//this form will do dual-duty for mini-forms on all pages, as well as 'cart' on newsletter landing
	$('.newsletter-signup form, #newsletter_cart form').submit(function(e) {
		e.preventDefault();

		var $this = $(e.target);

		// find our honeypot field
		var honeypot_name = $this.find('input[name=name]').val();

		// if our hidden honeypot field has a value, it's probably a bot filling out the form
		// let's return a false success
		if( honeypot_name ) {

			Cookies.set('signed_up_for_newsletter', true, { expires: Infinity });
			Cookies.set('newsletter_modal_snooze', true, { expires: Infinity });

			form_submission_success( 'Submitted.' );

			return;

		}

		$this.find('input[type=submit]').attr('disabled', true)
		var email = $this.find('input[name=newsletter_email]').val()
		var fname = $this.find('input[name=newsletter_fname]').val()

		var newsletters = []
		if ($('body').hasClass('newsletter-landing')) {
			$('.newsletter_to_subscribe').each(function(i, x) {
				newsletters.push($(x).attr('data-newsletter-id'))
			})
		} else {
			$this.find('input[type=checkbox]').filter(':checked').each(function(i, x) {
				newsletters.push($(x).val())
			})
		}

		submitted = true

		$.ajax({
			type : "post",
			dataType : "json",
			url : myAjax.ajaxurl,
			data : {action: "cl_mc_signup", fname: fname, email: email, newsletters: newsletters},
			success: function(response) {
				if (response.status == 'success') {
					Cookies.set('signed_up_for_newsletter', true, { expires: Infinity });
					Cookies.set('newsletter_modal_snooze', true, { expires: Infinity });
				}
				form_submission_success( response.message );
			}
		})
	})
});
