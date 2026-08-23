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
			$this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
			$this->form_validation->set_rules('password', 'Password', 'required');

			if ($this->form_validation->run())
			{
				$user = $this->auth->login(
					$this->input->post('email', TRUE),
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

				// One generic message: never reveal whether the email exists.
				$this->data['error'] = 'Those credentials do not match our records.';
			}
		}

		$this->render('auth/login');
	}

	public function register()
	{
		if ($this->auth->logged_in())
		{
			redirect('shop');
		}

		$this->data['title'] = 'Create an account';

		if ($this->input->method() === 'post')
		{
			$this->form_validation->set_rules('name', 'Name', 'required|trim|min_length[2]|max_length[100]');
			$this->form_validation->set_rules(
				'email', 'Email',
				'required|trim|valid_email|max_length[150]|is_unique[users.email]',
				array('is_unique' => 'An account with that email already exists.')
			);
			$this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|max_length[72]');
			$this->form_validation->set_rules(
				'password_confirm', 'Password confirmation',
				'required|matches[password]',
				array('matches' => 'The passwords do not match.')
			);
			$this->form_validation->set_rules('phone', 'Phone', 'trim|max_length[30]');
			$this->form_validation->set_rules('address', 'Address', 'trim');

			if ($this->form_validation->run())
			{
				$this->auth->register(array(
					'name'    => $this->input->post('name', TRUE),
					'email'   => $this->input->post('email', TRUE),
					'password' => $this->input->post('password'),
					'phone'   => $this->input->post('phone', TRUE),
					'address' => $this->input->post('address', TRUE),
				));

				// Log straight in so registration ends on the storefront.
				$this->auth->login($this->input->post('email', TRUE), $this->input->post('password'));

				$this->flash_redirect('shop', 'success', 'Your account is ready. Happy shopping!');
			}
		}

		$this->render('auth/register');
	}

	public function logout()
	{
		$this->auth->logout();
		$this->flash_redirect('login', 'success', 'You have been signed out.');
	}
}
