<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cart extends Customer_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('product_model');
	}

	public function index()
	{
		$this->data['title'] = 'Your cart';

		$lines = $this->cart_model->contents();

		// contents() drops products that vanished or were delisted; tell the
		// customer rather than letting the total change silently.
		if ($removed = $this->cart_model->last_removed())
		{
			$this->session->set_flashdata('flash', array(
				'type'    => 'warning',
				'message' => 'No longer available and removed from your cart: ' . e(implode(', ', $removed)),
			));
			redirect('cart');
		}

		$subtotal = 0.0;

		foreach ($lines as $line)
		{
			$subtotal += $line['subtotal'];
		}

		$this->render('cart/index', array(
			'lines'    => $lines,
			'subtotal' => $subtotal,
		));
	}

	/**
	 * POST target for "Add to cart" buttons.
	 *
	 * Answers JSON for a background request and redirects for a plain form
	 * submission, so the button still works with JavaScript disabled.
	 */
	public function add()
	{
		if ($this->input->method() !== 'post')
		{
			redirect('shop');
		}

		$product_id = (int) $this->input->post('product_id');
		$qty        = (int) $this->input->post('qty');
		$result     = $this->cart_model->add($product_id, $qty > 0 ? $qty : 1);

		if ($this->input->is_ajax_request())
		{
			$this->json(array(
				'ok'         => $result['ok'],
				'message'    => $result['message'],
				'cart_count' => $this->cart_model->count_items(),
			));

			return;
		}

		// Stay on the page the customer came from, if it was one of ours.
		$return = $this->safe_return($this->input->post('return_to'));

		$this->flash_redirect($return, $result['ok'] ? 'success' : 'danger', $result['message']);
	}

	/**
	 * Reduce a submitted return path to a safe internal URI.
	 *
	 * redirect() follows absolute URLs, so an unchecked form field here
	 * would be an open redirect. Anything with a scheme or a leading slash
	 * falls back to the cart.
	 *
	 * @return string
	 */
	protected function safe_return($path)
	{
		$path = trim((string) $path);

		if ($path === '' OR $path[0] === '/' OR $path[0] === '\\' OR strpos($path, ':') !== FALSE)
		{
			return 'cart';
		}

		return $path;
	}

	/**
	 * Single POST target for the cart form: quantity updates, plus the
	 * Remove and Empty cart buttons (which submit the same form so they
	 * are CSRF-protected and need no nested markup).
	 */
	public function update()
	{
		if ($this->input->method() !== 'post')
		{
			redirect('cart');
		}

		if ($this->input->post('clear'))
		{
			$this->cart_model->clear();
			$this->flash_redirect('cart', 'success', 'Your cart is now empty.');
		}

		if (($remove = $this->input->post('remove')) !== NULL)
		{
			$this->cart_model->remove($remove);
			$this->flash_redirect('cart', 'success', 'Item removed from your cart.');
		}

		$quantities = $this->input->post('qty');

		if ( ! is_array($quantities))
		{
			redirect('cart');
		}

		$messages = array();
		$type     = 'success';

		foreach ($quantities as $product_id => $qty)
		{
			$result = $this->cart_model->update_qty($product_id, $qty);

			// Surface only the interesting outcomes, not "Cart updated." x5.
			if ($result['message'] !== 'Cart updated.')
			{
				$messages[] = $result['message'];

				if ( ! $result['ok'])
				{
					$type = 'warning';
				}
			}
		}

		$this->flash_redirect('cart', $type, $messages ? implode(' ', $messages) : 'Cart updated.');
	}

}
