/**
 * Lightbox and zoom for the documents on a doctor application.
 *
 * The two anchors already point at the streaming route, so if Fancybox does
 * not load (blocked CDN, for one) the links still open the document in a new
 * tab. Binding the lightbox is the enhancement, not the way in.
 */
(function () {
	'use strict';

	// Fancybox v5 exposes a global from its UMD build.
	if (typeof Fancybox === 'undefined') {
		return;
	}

	if (!document.querySelector('[data-fancybox="documents"]')) {
		return;
	}

	Fancybox.bind('[data-fancybox="documents"]', {
		// Both documents form one group, so the reviewer can arrow between
		// the diploma and the certificate without closing the viewer.
		groupAll: false,

		Images: {
			// Click the image to zoom to full size, then drag to pan. Scans
			// are often high resolution, so allow well past 1:1.
			zoom: true,
			Panzoom: { maxScale: 5 }
		},

		Toolbar: {
			display: {
				left: ['infobar'],
				// Rotate earns its place here: photographed certificates
				// frequently arrive sideways.
				middle: ['zoomIn', 'zoomOut', 'toggle1to1', 'rotateCCW', 'rotateCW'],
				right: ['close']
			}
		},

		Thumbs: false
	});
}());
