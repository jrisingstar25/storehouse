<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Account extends Customer_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('order_model', 'doctor_application_model'));
	}

	/** Profile form: contact details and an optional password change. */
	public function index()
	{
		$this->data['title'] = 'My account';
		$user = $this->auth->user();

		if ($this->input->method() === 'post')
		{
			$this->form_validation->set_rules('name', 'Name', 'required|trim|min_length[2]|max_length[100]');
			$this->form_validation->set_rules(
				'username', 'Username',
				'required|trim|min_length[3]|max_length[60]|alpha_dash|callback_unique_username[' . $user['id'] . ']'
			);
			$this->form_validation->set_rules('phone', 'Phone', 'trim|max_length[30]');
			$this->form_validation->set_rules('address', 'Address', 'trim');

			// Password fields are optional, but all three are required together.
			if ($this->input->post('new_password') !== '')
			{
				$this->form_validation->set_rules('current_password', 'Current password', 'required');
				$this->form_validation->set_rules('new_password', 'New password', 'required|min_length[3]|max_length[72]');
				$this->form_validation->set_rules(
					'new_password_confirm', 'Password confirmation',
					'required|matches[new_password]',
					array('matches' => 'The new passwords do not match.')
				);
			}

			if ($this->form_validation->run())
			{
				$fields = array(
					'name'    => $this->input->post('name', TRUE),
					'username' => $this->input->post('username', TRUE),
					'phone'   => $this->input->post('phone', TRUE),
					'address' => $this->input->post('address', TRUE),
				);

				if ($this->input->post('new_password') !== '')
				{
					if ( ! password_verify($this->input->post('current_password'), $user['password']))
					{
						$this->data['error'] = 'Your current password is not correct.';
						$this->render('account/index', array(
							'user'        => $user,
							'application' => $this->doctor_application_model->latest_for_user($user['id']),
						));
						return;
					}

					$fields['password'] = password_hash($this->input->post('new_password'), PASSWORD_DEFAULT);
				}

				$this->user_model->update($user['id'], $fields);
				$this->flash_redirect('account', 'success', 'Your details have been saved.');
			}
		}

		$this->render('account/index', array(
			'user'        => $user,
			'application' => $this->doctor_application_model->latest_for_user($user['id']),
		));
	}

	/** Validation callback: username must be free, ignoring this user's own row. */
	public function unique_username($username, $user_id)
	{
		if ($this->user_model->username_exists($username, (int) $user_id))
		{
			$this->form_validation->set_message('unique_username', 'That username is already taken.');

			return FALSE;
		}

		return TRUE;
	}

	public function orders()
	{
		$this->data['title'] = 'My orders';

		$this->render('account/orders', array(
			'orders' => $this->order_model->get_all(array('user_id' => $this->auth->user_id())),
		));
	}

	public function order($id)
	{
		$order = $this->order_model->get($id);

		// Never let one customer read another's order.
		if ( ! $order OR (int) $order['user_id'] !== $this->auth->user_id())
		{
			show_404();
		}

		$this->data['title'] = 'Order ' . $order['order_number'];

		$this->render('account/order', array(
			'order' => $order,
			'items' => $this->order_model->get_items($order['id']),
		));
	}
}
