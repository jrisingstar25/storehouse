<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base class for the mobile API.
 *
 * Everything here speaks JSON and authenticates with a bearer token rather
 * than the web session, so an API call carries no ambient browser credentials.
 * That is also why api/* is listed in $config['csrf_exclude_uris']: CSRF
 * defends against a browser attaching cookies to a forged request, which
 * cannot happen when the only credential is a header the caller must know.
 */
class API_Controller extends CI_Controller {

	/** @var array|null The user behind the bearer token, once resolved. */
	protected $api_user = NULL;

	/** @var array|null Cached decoded request body. */
	private $payload = NULL;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('api_token_model');
	}

	/* ------------------------------------------------------------------
	 * Responses
	 * --------------------------------------------------------------- */

	/**
	 * Send a JSON response and end the request.
	 *
	 * Exits rather than returns so that guards can stop a request from
	 * anywhere, including a constructor.
	 */
	protected function respond(array $body, $status = 200)
	{
		$this->output
			->set_status_header($status)
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

		$this->output->_display();
		exit;
	}

	/** A successful response: {"success": true, "data": ...} */
	protected function ok($data, $status = 200)
	{
		$this->respond(array('success' => TRUE, 'data' => $data), $status);
	}

	/**
	 * A failure: {"success": false, "message": ..., "errors": {...}}
	 *
	 * @param array|null $errors Field name => message, for validation errors
	 */
	protected function fail($message, $status = 400, array $errors = NULL)
	{
		$body = array('success' => FALSE, 'message' => $message);

		if ($errors)
		{
			$body['errors'] = $errors;
		}

		$this->respond($body, $status);
	}

	/* ------------------------------------------------------------------
	 * Request
	 * --------------------------------------------------------------- */

	/** Reject anything that is not the expected HTTP verb. */
	protected function require_method($method)
	{
		if (strtoupper($this->input->method()) !== strtoupper($method))
		{
			$this->fail('This endpoint expects a ' . strtoupper($method) . ' request.', 405);
		}
	}

	/**
	 * The request body as an array.
	 *
	 * Accepts a JSON body (what a mobile client will normally send) and falls
	 * back to ordinary form encoding, so the endpoints are easy to try with
	 * curl or a form post.
	 *
	 * @return array
	 */
	protected function payload()
	{
		if ($this->payload !== NULL)
		{
			return $this->payload;
		}

		$type = (string) $this->input->get_request_header('Content-Type');

		if (stripos($type, 'application/json') !== FALSE)
		{
			$decoded = json_decode($this->input->raw_input_stream, TRUE);

			if (json_last_error() !== JSON_ERROR_NONE)
			{
				$this->fail('Request body is not valid JSON.', 400);
			}

			return $this->payload = is_array($decoded) ? $decoded : array();
		}

		$post = $this->input->post();

		return $this->payload = is_array($post) ? $post : array();
	}

	/**
	 * Run form_validation against the request body.
	 *
	 * @param  array $rules CodeIgniter validation rule definitions
	 * @return array The validated payload; never returns on failure
	 */
	protected function validate(array $rules)
	{
		$data = $this->payload();

		$this->form_validation->set_data($data);
		$this->form_validation->set_rules($rules);

		if ( ! $this->form_validation->run())
		{
			$errors = array();

			foreach ($rules as $rule)
			{
				$message = form_error($rule['field'], '', '');

				if ($message !== '')
				{
					$errors[$rule['field']] = trim(strip_tags($message));
				}
			}

			$this->fail('The submitted data is not valid.', 422, $errors);
		}

		return $data;
	}

	/* ------------------------------------------------------------------
	 * Authentication
	 * --------------------------------------------------------------- */

	/** The bearer token on this request, or NULL. */
	protected function bearer_token()
	{
		$header = (string) $this->input->get_request_header('Authorization');

		if (stripos($header, 'Bearer ') === 0)
		{
			return trim(substr($header, 7));
		}

		return NULL;
	}

	/**
	 * Require a valid token, and remember whose it is.
	 *
	 * @return array The authenticated user
	 */
	protected function require_token()
	{
		$user = $this->api_token_model->resolve($this->bearer_token());

		if ( ! $user)
		{
			$this->fail('Missing or invalid access token.', 401);
		}

		return $this->api_user = $user;
	}

	/**
	 * Shape a user record for the client.
	 *
	 * Whitelisted rather than filtered, so a column added later - a password
	 * reset token, say - cannot leak by default.
	 */
	protected function present_user(array $user)
	{
		return array(
			'id'      => (int) $user['id'],
			'name'    => $user['name'],
			'username' => $user['username'],
			'role'    => $user['role'],
			'phone'   => $user['phone'],
			'address' => $user['address'],
		);
	}
}
