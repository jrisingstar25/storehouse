<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Checkout extends Customer_Controller {

	/** Flat shipping, waived above the threshold. */
	const SHIPPING_FEE       = 600;
	const FREE_SHIPPING_OVER = 15000;

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('product_model', 'order_model'));
	}

	public function index()
	{
		$lines = $this->cart_model->contents();

		if (empty($lines))
		{
			$this->flash_redirect('shop', 'warning', 'Your cart is empty.');
		}

		// Refuse to take payment for stock we no longer have.
		if ($this->cart_model->stock_problems())
		{
			$this->flash_redirect('cart', 'warning', 'Some items exceed available stock. Please adjust your quantities.');
		}

		$subtotal = $this->cart_model->subtotal();
		$shipping = $subtotal >= self::FREE_SHIPPING_OVER ? 0 : self::SHIPPING_FEE;

		$this->data['title'] = 'Checkout';

		if ($this->input->method() === 'post')
		{
			$this->form_validation->set_rules('customer_name', 'Name', 'required|trim|max_length[100]');
			$this->form_validation->set_rules('customer_email', 'Email', 'required|trim|valid_email|max_length[150]');
			$this->form_validation->set_rules('customer_phone', 'Phone', 'trim|max_length[30]');
			$this->form_validation->set_rules('shipping_address', 'Shipping address', 'required|trim');
			$this->form_validation->set_rules('payment_method', 'Payment method', 'required|in_list[cod,bank_transfer]');
			$this->form_validation->set_rules('notes', 'Notes', 'trim|max_length[1000]');

			if ($this->form_validation->run())
			{
				$result = $this->order_model->place(array(
					'user_id'          => $this->auth->user_id(),
					'customer_name'    => $this->input->post('customer_name', TRUE),
					'customer_email'   => $this->input->post('customer_email', TRUE),
					'customer_phone'   => $this->input->post('customer_phone', TRUE),
					'shipping_address' => $this->input->post('shipping_address', TRUE),
					'notes'            => $this->input->post('notes', TRUE),
					'payment_method'   => $this->input->post('payment_method', TRUE),
					'subtotal'         => $subtotal,
					'shipping_fee'     => $shipping,
					'total'            => $subtotal + $shipping,
				), $lines);

				if ($result['ok'])
				{
					$this->cart_model->clear();

					// Keep the id in the session so success() cannot be
					// opened for somebody else's order by guessing an id,
					// and so a page refresh still resolves.
					$this->session->set_userdata('last_order_id', $result['order_id']);

					redirect('checkout/success');
				}

				$this->flash_redirect('cart', 'danger', $result['message']);
			}
		}

		$user = $this->auth->user();

		$this->render('checkout/index', array(
			'lines'    => $lines,
			'subtotal' => $subtotal,
			'shipping' => $shipping,
			'total'    => $subtotal + $shipping,
			'user'     => $user,
		));
	}

	public function success()
	{
		$order_id = $this->session->userdata('last_order_id');

		if ( ! $order_id)
		{
			redirect('shop');
		}

		$order = $this->order_model->get($order_id);

		if ( ! $order)
		{
			redirect('shop');
		}

		$this->data['title'] = 'Order confirmed';

		$this->render('checkout/success', array(
			'order' => $order,
			'items' => $this->order_model->get_items($order_id),
		));
	}
}
