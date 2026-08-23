<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends Public_Controller {

	public function index()
	{
		redirect('login');
	}

	public function login()
	{
		if ($this->auth->logged_in())
		{
			redirect($this->auth->is_admin() ? 'admin' : 'shop');
		}

		$this->data['title'] = 'Sign in';

		if ($this->input->method() === 'post')
		{
			$this->form_validation->set_rules('username', 'Username', 'required|trim');
			$this->form_validation->set_rules('password', 'Password', 'required');

			if ($this->form_validation->run())
			{
				$user = $this->auth->login(
					$this->input->post('username', TRUE),
					$this->input->post('password')
				);

				if ($user)
				{
					// Send the user back where they were headed, if anywhere.
					$intended = $this->session->userdata('redirect_after_login');
					$this->session->unset_userdata('redirect_after_login');

					$destination = $intended ?: ($user['role'] === 'admin' ? 'admin' : 'shop');

					$this->flash_redirect($destination, 'success', 'Welcome back, ' . e($user['name']) . '.');
				}

				// One generic message: never reveal whether the username exists.
				$this->data['error'] = 'Those credentials do not match our records.';
			}
		}

		$this->render('auth/login');
	}

	/**
	 * Self-service sign-up, as a customer or as a doctor.
	 *
	 * Both paths create an ordinary customer account that works immediately -
	 * Authentication::register() decides the role, so nothing posted here can
	 * grant admin rights. Choosing "doctor" additionally files an application
	 * with two uploaded documents; the account only becomes a doctor once an
	 * admin approves it, and in the meantime the person shops as a customer.
	 */
	public function register()
	{
		if ($this->auth->logged_in())
		{
			redirect('shop');
		}

		$this->data['title'] = 'Create an account';
		$applying = $this->input->post('account_type') === 'doctor';

		if ($this->input->method() === 'post')
		{
			$this->form_validation->set_rules('name', 'Name', 'required|trim|min_length[2]|max_length[100]');
			$this->form_validation->set_rules(
				'username', 'Username',
				'required|trim|min_length[3]|max_length[60]|alpha_dash|is_unique[users.username]',
				array(
					'is_unique'  => 'That username is already taken.',
					'alpha_dash' => 'Username may contain only letters, numbers, underscores and dashes.',
				)
			);
			$this->form_validation->set_rules('password', 'Password', 'required|min_length[3]|max_length[72]');
			$this->form_validation->set_rules(
				'password_confirm', 'Password confirmation',
				'required|matches[password]',
				array('matches' => 'The passwords do not match.')
			);
			$this->form_validation->set_rules('account_type', 'Account type', 'required|in_list[customer,doctor]');
			$this->form_validation->set_rules('phone', 'Phone', 'trim|max_length[30]');
			$this->form_validation->set_rules('address', 'Address', 'trim');

			if ($this->form_validation->run())
			{
				$documents = NULL;

				if ($applying)
				{
					// Store the documents before creating anything, so a
					// rejected upload leaves no half-made account behind.
					$documents = $this->store_documents();

					if ($documents === FALSE)
					{
						$this->render('auth/register');
						return;
					}
				}

				$username = $this->input->post('username', TRUE);
				$password = $this->input->post('password');

				$user_id = $this->auth->register(array(
					'name'     => $this->input->post('name', TRUE),
					'username' => $username,
					'password' => $password,
					'phone'    => $this->input->post('phone', TRUE),
					'address'  => $this->input->post('address', TRUE),
				));

				if ($applying)
				{
					$this->load->model('doctor_application_model');
					$this->doctor_application_model->insert(array(
						'user_id'         => $user_id,
						'diploma_file'    => $documents['diploma'],
						'graduation_file' => $documents['graduation'],
					));
				}

				$this->auth->login($username, $password);

				$message = $applying
					? 'Your account is ready and your doctor application has been submitted. '
						. 'You can shop as a customer while an admin reviews it.'
					: 'Your account is ready. Happy shopping!';

				$this->flash_redirect('shop', 'success', $message);
			}
		}

		$this->render('auth/register');
	}

	/**
	 * Move the two supporting documents into protected storage.
	 *
	 * @return array|false {diploma, graduation} filenames, or FALSE with the
	 *                     reason left in $this->data['error']
	 */
	protected function store_documents()
	{
		$this->load->library('doctor_documents');

		$result = $this->doctor_documents->store(array('diploma', 'graduation'));

		if ($result === FALSE)
		{
			$this->data['error'] = $this->doctor_documents->last_error();

			return FALSE;
		}

		return $result;
	}

	public function logout()
	{
		$this->auth->logout();
		$this->flash_redirect('login', 'success', 'You have been signed out.');
	}
}
