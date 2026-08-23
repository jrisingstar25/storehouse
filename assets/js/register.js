/**
 * Sign-up form behaviour.
 *
 * Only the account-type toggle lives here. The document previews are in
 * document-upload.js, which the account page loads too.
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
