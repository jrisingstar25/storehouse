<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base controller shared by every controller in the application.
 *
 * Holds the data bag handed to views and a small layout renderer so that
 * controllers never have to load a header/footer pair by hand.
 */
class MY_Controller extends CI_Controller {

	/** @var array Data passed to the view layer. */
	protected $data = array();

	/** @var string Layout used by render(); overridden by Admin_Controller. */
	protected $layout = 'layouts/public';

	public function __construct()
	{
		parent::__construct();

		$this->data['title']       = 'Jinjong';
		$this->data['current_user'] = $this->auth->user();
		$this->data['cart_count']  = 0;
	}

	/**
	 * Render a view inside the active layout.
	 *
	 * @param string $view Path of the view relative to application/views/
	 * @param array  $data Extra data merged over $this->data
	 */
	protected function render($view, array $data = array())
	{
		$data = array_merge($this->data, $data);
		$data['content_view'] = $view;

		$this->load->view($this->layout, $data);
	}

	/**
	 * Flash a message and redirect. Types map to Bootstrap alert suffixes.
	 *
	 * @param string $url  Relative URL to redirect to
	 * @param string $type success|danger|warning|info
	 * @param string $msg  Message body
	 */
	protected function flash_redirect($url, $type, $msg)
	{
		$this->session->set_flashdata('flash', array('type' => $type, 'message' => $msg));
		redirect($url);
	}
}

/**
 * Open to everyone. Only the sign-in and registration pages sit here -
 * the storefront itself requires an account (see Customer_Controller).
 */
class Public_Controller extends MY_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->model('cart_model');
		$this->data['cart_count'] = $this->cart_model->count_items();
	}
}

/**
 * Requires any authenticated user. This covers the whole storefront -
 * catalogue, cart, checkout and account - not just the account pages.
 * Also adds the live cart badge to the layout.
 */
class Customer_Controller extends Public_Controller {

	public function __construct()
	{
		parent::__construct();

		if ( ! $this->auth->logged_in())
		{
			$this->session->set_userdata('redirect_after_login', uri_string());
			$this->flash_redirect('login', 'warning', 'Please sign in to continue.');
		}
	}
}

/**
 * Admin area: requires an authenticated user with the admin role.
 */
class Admin_Controller extends MY_Controller {

	protected $layout = 'layouts/admin';

	public function __construct()
	{
		parent::__construct();

		if ( ! $this->auth->logged_in())
		{
			$this->session->set_userdata('redirect_after_login', uri_string());
			$this->flash_redirect('login', 'warning', 'Please sign in to continue.');
		}

		if ( ! $this->auth->is_admin())
		{
			$this->flash_redirect('shop', 'danger', 'You do not have permission to access the admin area.');
		}

		$this->data['title'] = 'Admin';
	}
}
