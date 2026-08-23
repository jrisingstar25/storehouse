<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mobile API - authentication.
 *
 * POST   /api/auth/register   create a customer account, return a token
 * POST   /api/auth/login      exchange credentials for a token
 * POST   /api/auth/logout     revoke the current token
 * GET    /api/auth/me         the account behind the current token
 */
class Auth extends API_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_model');
	}

	/**
	 * Self-service sign-up for the mobile app.
	 *
	 * The role is hard-coded to 'customer'. A caller cannot promote itself by
	 * posting role=admin, and admin accounts remain something only an existing
	 * admin can create, in the web back office.
	 */
	public function register()
	{
		$this->require_method('POST');

		$data = $this->validate(array(
			array(
				'field' => 'name',
				'label' => 'Name',
				'rules' => 'required|trim|min_length[2]|max_length[100]',
			),
			array(
				'field' => 'email',
				'label' => 'Email',
				'rules' => 'required|trim|valid_email|max_length[150]|is_unique[users.email]',
				'errors' => array('is_unique' => 'An account with that email already exists.'),
			),
			array(
				'field' => 'password',
				'label' => 'Password',
				'rules' => 'required|min_length[8]|max_length[72]',
			),
			array(
				'field' => 'phone',
				'label' => 'Phone',
				'rules' => 'trim|max_length[30]',
			),
			array(
				'field' => 'address',
				'label' => 'Address',
				'rules' => 'trim',
			),
		));

		$user_id = $this->user_model->insert(array(
			'name'      => $data['name'],
			'email'     => $data['email'],
			'password'  => password_hash($data['password'], PASSWORD_DEFAULT),
			'role'      => 'customer',
			'phone'     => isset($data['phone']) ? $data['phone'] : NULL,
			'address'   => isset($data['address']) ? $data['address'] : NULL,
			'is_active' => 1,
		));

		$user  = $this->user_model->get($user_id);
		$token = $this->api_token_model->issue($user_id, $this->device_label());

		$this->ok(array(
			'token'      => $token['token'],
			'expires_at' => $token['expires_at'],
			'user'       => $this->present_user($user),
		), 201);
	}

	public function login()
	{
		$this->require_method('POST');

		$data = $this->validate(array(
			array('field' => 'email',    'label' => 'Email',    'rules' => 'required|trim|valid_email'),
			array('field' => 'password', 'label' => 'Password', 'rules' => 'required'),
		));

		$user = $this->auth->verify($data['email'], $data['password']);

		if ( ! $user)
		{
			// One message for wrong password, unknown address and disabled
			// account alike - the API must not confirm who has an account.
			$this->fail('Those credentials do not match our records.', 401);
		}

		$token = $this->api_token_model->issue($user['id'], $this->device_label());

		$this->ok(array(
			'token'      => $token['token'],
			'expires_at' => $token['expires_at'],
			'user'       => $this->present_user($user),
		));
	}

	/** Revoke the calling token, or every token the user holds. */
	public function logout()
	{
		$this->require_method('POST');
		$this->require_token();

		$data = $this->payload();

		if ( ! empty($data['all_devices']))
		{
			$this->api_token_model->revoke_all($this->api_user['id']);

			$this->ok(array('revoked' => 'all'));
		}

		$this->api_token_model->revoke($this->bearer_token());

		$this->ok(array('revoked' => 'current'));
	}

	/** Whoever the current token belongs to; also a cheap token check. */
	public function me()
	{
		$this->require_method('GET');
		$this->require_token();

		$this->ok(array('user' => $this->present_user($this->api_user)));
	}

	/**
	 * A label for the device a token was issued to, so a user can eventually
	 * be shown "signed in on ..." and revoke one session.
	 */
	protected function device_label()
	{
		$data = $this->payload();

		if ( ! empty($data['device']))
		{
			return (string) $data['device'];
		}

		$agent = $this->input->user_agent();

		return $agent !== NULL ? $agent : 'unknown';
	}
}
