<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Catch-all for unknown /api/... paths.
 *
 * Without this, a typo in a client would get CodeIgniter's HTML error page,
 * which a JSON parser turns into a confusing crash rather than a clear 404.
 */
class Fallback extends API_Controller {

	public function index()
	{
		$this->fail('No such API endpoint.', 404);
	}
}
