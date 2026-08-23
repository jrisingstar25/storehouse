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

/**
 * Sign-up: show the supporting-document fields only when applying as a doctor.
 *
 * The block is rendered visible so the form still works with this script
 * absent; hiding it is the enhancement, not the default.
 */
(function ($) {
	'use strict';

	if (!$) {
		return;
	}

	$(function () {
		var $block = $('#doctor-documents');

		if (!$block.length) {
			return;
		}

		function sync() {
			var doctor = $('[data-account-type]:checked').val() === 'doctor';
			$block.prop('hidden', !doctor);
		}

		$(document).on('change', '[data-account-type]', sync);
		sync();
	});
}(window.jQuery));

/**
 * Preview an image the moment it is chosen, before anything is uploaded.
 *
 * Any file input carrying data-preview="<selector>" gets its selection shown
 * in that container. Enhancement only: with this script absent the inputs are
 * ordinary file fields and the server still decides what is acceptable.
 *
 * Object URLs are used rather than FileReader (no base64 copy of the file in
 * memory), and each input revokes its previous URL before making a new one,
 * so repeatedly re-picking a file does not leak.
 */
(function ($) {
	'use strict';

	if (!$ || typeof URL === 'undefined' || !URL.createObjectURL) {
		return;
	}

	/** "812 KB" / "2.4 MB" */
	function readableSize(bytes) {
		var kb = bytes / 1024;

		return kb >= 1024 ? (kb / 1024).toFixed(1) + ' MB' : Math.max(1, Math.round(kb)) + ' KB';
	}

	function clear($input, $target) {
		var previous = $input.data('previewUrl');

		if (previous) {
			URL.revokeObjectURL(previous);
			$input.removeData('previewUrl');
		}

		$target.prop('hidden', true)
			.find('[data-preview-image]').removeAttr('src').prop('hidden', true);
	}

	function preview($input) {
		var $target = $($input.attr('data-preview'));

		if (!$target.length) {
			return;
		}

		var files = $input[0].files;
		var file  = files && files.length ? files[0] : null;

		clear($input, $target);

		if (!file) {
			return;
		}

		var $image = $target.find('[data-preview-image]');
		var $meta  = $target.find('[data-preview-meta]');
		var maxKb  = parseInt($input.attr('data-max-kb'), 10) || 0;
		var notes  = [];

		if (/^image\//.test(file.type)) {
			var url = URL.createObjectURL(file);
			$input.data('previewUrl', url);
			$image.attr('src', url).prop('hidden', false);
		} else {
			// accept="" is only a hint; a determined picker can still get here.
			notes.push('That does not look like an image.');
		}

		if (maxKb > 0 && file.size > maxKb * 1024) {
			notes.push('Larger than the ' + Math.round(maxKb / 1024) + ' MB limit - it will be rejected.');
		}

		$meta
			.toggleClass('text-danger', notes.length > 0)
			.toggleClass('text-muted', notes.length === 0)
			.text(file.name + ' — ' + readableSize(file.size) + (notes.length ? ' — ' + notes.join(' ') : ''));

		$target.prop('hidden', false);
	}

	$(document).on('change', 'input[type="file"][data-preview]', function () {
		preview($(this));
	});

	/**
	 * The close icon on a preview: drop the chosen file.
	 *
	 * Emptying the input and firing change routes through the same handler
	 * above, so revoking the object URL and hiding the preview stay in one
	 * place. Focus goes back to the field, so a keyboard user is not left
	 * stranded on a button that has just disappeared.
	 */
	$(document).on('click', '[data-preview-clear]', function () {
		var $input = $($(this).attr('data-preview-clear'));

		if ( ! $input.length) {
			return;
		}

		$input.val('').trigger('change').trigger('focus');
	});
}(window.jQuery));
