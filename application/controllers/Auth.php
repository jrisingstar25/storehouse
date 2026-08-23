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

	public function logout()
	{
		$this->auth->logout();
		$this->flash_redirect('login', 'success', 'You have been signed out.');
	}
}
