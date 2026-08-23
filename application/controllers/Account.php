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
						$this->render('account/index', $this->account_view_data($user));
						return;
					}

					$fields['password'] = password_hash($this->input->post('new_password'), PASSWORD_DEFAULT);
				}

				$this->user_model->update($user['id'], $fields);
				$this->flash_redirect('account', 'success', 'Your details have been saved.');
			}
		}

		$this->render('account/index', $this->account_view_data($user));
	}

	/**
	 * Everything the account page needs, including the upload rules read from
	 * the library that enforces them so the hints cannot drift.
	 *
	 * @return array
	 */
	protected function account_view_data(array $user)
	{
		$this->load->library('doctor_documents');

		$data = array(
			'user'        => $user,
			'application' => $this->doctor_application_model->latest_for_user($user['id']),
			'doc_max_kb'  => Doctor_documents::MAX_SIZE_KB,
			'doc_types'   => explode('|', Doctor_documents::ALLOWED_TYPES),
		);

		// The account-type card is hidden from admins, so the preview script
		// would be dead weight on their page.
		if ($user['role'] !== 'admin')
		{
			$data['page_scripts'] = array(base_url('assets/js/document-upload.js'));
		}

		return $data;
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

	/* ------------------------------------------------------------------
	 * Account type
	 *
	 * A customer can apply to be recognised as a doctor, and a doctor can
	 * step back down. Applying never grants the role directly - it files an
	 * application for an admin to review, exactly as sign-up does. Admins are
	 * excluded throughout: their role is managed under Admin -> Users, and
	 * letting one demote themselves here would sidestep the guard that keeps
	 * at least one admin in place.
	 * --------------------------------------------------------------- */

	/** Submit documents and ask to be recognised as a doctor. */
	public function apply_doctor()
	{
		$user = $this->guard_role_change();

		if ($user['role'] === 'doctor')
		{
			$this->flash_redirect('account', 'warning', 'You are already a doctor.');
		}

		if ($this->doctor_application_model->pending_for_user($user['id']))
		{
			$this->flash_redirect('account', 'warning', 'You already have an application under review.');
		}

		$this->load->library('doctor_documents');

		$documents = $this->doctor_documents->store(array('diploma', 'graduation'));

		if ($documents === FALSE)
		{
			$this->flash_redirect('account', 'danger', e($this->doctor_documents->last_error()));
		}

		$this->doctor_application_model->insert(array(
			'user_id'         => $user['id'],
			'diploma_file'    => $documents['diploma'],
			'graduation_file' => $documents['graduation'],
		));

		$this->flash_redirect('account', 'success',
			'Your documents have been submitted. An admin will review them; '
			. 'your account keeps working as a customer in the meantime.');
	}

	/** Take back an application that has not been decided yet. */
	public function withdraw_application()
	{
		$user        = $this->guard_role_change();
		$application = $this->doctor_application_model->pending_for_user($user['id']);

		if ( ! $application)
		{
			$this->flash_redirect('account', 'warning', 'You have no application awaiting review.');
		}

		$this->load->library('doctor_documents');

		// Drop the row first, then the files it pointed at.
		$this->doctor_application_model->delete($application['id']);
		$this->doctor_documents->delete($application['diploma_file']);
		$this->doctor_documents->delete($application['graduation_file']);

		$this->flash_redirect('account', 'success',
			'Your application has been withdrawn and your documents deleted.');
	}

	/** Give up the doctor role and go back to being an ordinary customer. */
	public function leave_doctor()
	{
		$user = $this->guard_role_change();

		if ($user['role'] !== 'doctor')
		{
			$this->flash_redirect('account', 'warning', 'You are not currently a doctor.');
		}

		$this->user_model->update($user['id'], array('role' => 'customer'));

		$this->flash_redirect('account', 'success',
			'You are now a regular customer. You can apply again at any time.');
	}

	/**
	 * Shared entry check for the three actions above.
	 *
	 * @return array The signed-in user
	 */
	protected function guard_role_change()
	{
		if ($this->input->method() !== 'post')
		{
			show_404();
		}

		$user = $this->auth->user();

		if ($user['role'] === 'admin')
		{
			$this->flash_redirect('account', 'danger',
				'Admin accounts are managed under Admin -> Users.');
		}

		return $user;
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
