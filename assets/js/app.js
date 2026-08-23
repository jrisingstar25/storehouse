/**
 * Background "add to cart".
 *
 * Forms marked data-ajax-cart post with jQuery instead of reloading the page.
 * They remain ordinary forms, so the button still works if this script (or
 * jQuery itself) never loads.
 *
 * CSRF is the fiddly part. CodeIgniter rotates the token every time it
 * verifies a POST — including a POST it rejects — so a token embedded in the
 * page goes stale as soon as anything posts, from this tab or another one.
 * The token cookie is readable from script, so we take the current value from
 * there immediately before each request rather than trusting the DOM.
 */
(function ($) {
	'use strict';

	// jQuery missing (blocked CDN): leave the forms as plain forms.
	if (!$) {
		return;
	}

	var CONFIG = window.JINJONG || {};

	/** Current CSRF token as the browser holds it, or null. */
	function csrfFromCookie() {
		if (!CONFIG.csrfCookie) {
			return null;
		}

		var parts = document.cookie.split(';');

		for (var i = 0; i < parts.length; i++) {
			var pair  = $.trim(parts[i]);
			var split = pair.indexOf('=');

			if (split > -1 && pair.slice(0, split) === CONFIG.csrfCookie) {
				return decodeURIComponent(pair.slice(split + 1));
			}
		}

		return null;
	}

	/** Write a token into every CSRF field on the page. */
	function applyCsrf(hash) {
		if (!hash || !CONFIG.csrfField) {
			return;
		}

		$('input[name="' + CONFIG.csrfField + '"]').val(hash);
	}

	/**
	 * Show a transient message.
	 *
	 * Server messages arrive with product names already HTML-escaped, the same
	 * as the flash partial renders them, so they are inserted as markup.
	 */
	function toast(message, ok) {
		var $container = $('#toasts');

		if (!$container.length || !window.bootstrap) {
			return;
		}

		var $el = $(
			'<div class="toast align-items-center text-bg-' + (ok ? 'success' : 'danger') + ' border-0"' +
				' role="alert" aria-live="assertive" aria-atomic="true">' +
				'<div class="d-flex">' +
					'<div class="toast-body"></div>' +
					'<button type="button" class="btn-close btn-close-white me-2 m-auto"' +
						' data-bs-dismiss="toast" aria-label="Close"></button>' +
				'</div>' +
			'</div>'
		);

		$el.find('.toast-body').html(message);
		$container.append($el);

		$el.on('hidden.bs.toast', function () { $el.remove(); });
		new bootstrap.Toast($el[0], { delay: 4000 }).show();
	}

	/** Update the navbar counter, hiding it at zero. */
	function setCartCount(count) {
		$('[data-cart-count]').text(count).prop('hidden', count < 1);
	}

	$(document).on('submit', 'form[data-ajax-cart]', function (event) {
		event.preventDefault();

		var $form = $(this);

		// Guard against a double click firing two requests.
		if ($form.data('busy')) {
			return;
		}

		$form.data('busy', true);

		var $button = $form.find('button[type="submit"], button:not([type])').first();
		var label   = $button.html();

		$button.prop('disabled', true).html('Adding…');

		// Prefer the browser's copy of the token over the one rendered into
		// the page, which another request may already have invalidated.
		// Must happen before serialize() so the fresh value is sent.
		applyCsrf(csrfFromCookie());

		$.ajax({
			url: $form.attr('action'),
			method: 'POST',
			data: $form.serialize(),
			dataType: 'json'
		})
			.done(function (data) {
				applyCsrf(data.csrf_hash);

				if (typeof data.cart_count !== 'undefined') {
					setCartCount(data.cart_count);
				}

				toast(data.message, data.ok);
			})
			.fail(function (jqXHR) {
				var data = jqXHR.responseJSON;

				// Session ended underneath us - send them to sign in.
				if (jqXHR.status === 401 && data && data.redirect) {
					applyCsrf(data.csrf_hash);
					toast(data.message, false);
					window.location = data.redirect;
					return;
				}

				// No JSON body: the framework rejected the request before it
				// reached us, almost always a stale token. It issued a fresh
				// one with that same response, so take it and let them retry.
				if (!data) {
					applyCsrf(csrfFromCookie());
					toast('That did not go through. Please try again.', false);
					return;
				}

				applyCsrf(data.csrf_hash);
				toast(data.message || 'Something went wrong. Please try again.', false);
			})
			.always(function () {
				$form.data('busy', false);
				$button.prop('disabled', false).html(label);
			});
	});
}(window.jQuery));
