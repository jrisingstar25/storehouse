/**
 * Sign-up form behaviour.
 *
 * Loaded only by auth/register: the account-type toggle and the previews
 * for the two documents a doctor applicant attaches.
 */

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
