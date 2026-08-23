<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Authentication library, reachable as $this->auth.
 *
 * Named Authentication rather than Auth because CodeIgniter loads libraries
 * and controllers into the same global namespace - an Auth library would
 * collide with the Auth controller.
 *
 * Wraps password hashing, session identity and role checks. The session
 * only ever stores the user id; the record itself is re-read once per
 * request so a deactivated or demoted account loses access immediately.
 */
class Authentication {

	/** @var CI_Controller */
	protected $CI;

	/** @var array|null Cached user record for this request. */
	protected $user = NULL;

	/** @var bool Whether $user has been resolved yet. */
	protected $loaded = FALSE;

	const SESSION_KEY = 'auth_user_id';

	/** A valid bcrypt hash no password matches, used to even out timing. */
	const DUMMY_HASH = '$2y$10$C6UzMDM.H6dfI/f/IKcEe.3Ptk3s8vEbwUvKfLuBUKZQwCEFmvbSy';

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->model('user_model');
	}

	/**
	 * Check a set of credentials without touching the session.
	 *
	 * Shared by the web sign-in form and the mobile API, so both apply the
	 * same rules about inactive accounts and password re-hashing.
	 *
	 * @return array|false The user record on success, FALSE otherwise
	 */
	public function verify($username, $password)
	{
		$user = $this->CI->user_model->get_by_username($username);

		if ( ! $user)
		{
			// Hash something anyway so a missing account costs about as much
			// time as a wrong password, and the response time does not reveal
			// which usernames are taken.
			password_verify($password, self::DUMMY_HASH);

			return FALSE;
		}

		if ( ! password_verify($password, $user['password']))
		{
			return FALSE;
		}

		if ((int) $user['is_active'] !== 1)
		{
			return FALSE;
		}

		// Re-hash transparently if PHP's default cost/algorithm has moved on.
		if (password_needs_rehash($user['password'], PASSWORD_DEFAULT))
		{
			$this->CI->user_model->update($user['id'], array(
				'password' => password_hash($password, PASSWORD_DEFAULT),
			));
		}

		return $user;
	}

	/**
	 * Verify credentials and start a web session.
	 *
	 * @return array|false The user record on success, FALSE otherwise
	 */
	public function login($username, $password)
	{
		$user = $this->verify($username, $password);

		if ( ! $user)
		{
			return FALSE;
		}

		// New session id on privilege change guards against session fixation.
		$this->CI->session->sess_regenerate(TRUE);
		$this->CI->session->set_userdata(self::SESSION_KEY, (int) $user['id']);

		$this->user   = $user;
		$this->loaded = TRUE;

		return $user;
	}

	/** Destroy the identity but keep the session (so flash data survives). */
	public function logout()
	{
		$this->CI->session->unset_userdata(self::SESSION_KEY);
		$this->CI->session->sess_regenerate(TRUE);

		$this->user   = NULL;
		$this->loaded = TRUE;
	}

	/**
	 * The signed-in user record, or NULL for a guest.
	 *
	 * @return array|null
	 */
	public function user()
	{
		if ($this->loaded)
		{
			return $this->user;
		}

		$this->loaded = TRUE;
		$id = $this->CI->session->userdata(self::SESSION_KEY);

		if ( ! $id)
		{
			return $this->user = NULL;
		}

		$user = $this->CI->user_model->get($id);

		// Account deleted or deactivated since the session was created.
		if ( ! $user OR (int) $user['is_active'] !== 1)
		{
			$this->CI->session->unset_userdata(self::SESSION_KEY);
			return $this->user = NULL;
		}

		return $this->user = $user;
	}

	/** @return int|null */
	public function user_id()
	{
		$user = $this->user();

		return $user ? (int) $user['id'] : NULL;
	}

	/** @return bool */
	public function logged_in()
	{
		return $this->user() !== NULL;
	}

	/** @return bool */
	public function is_admin()
	{
		$user = $this->user();

		return $user !== NULL && $user['role'] === 'admin';
	}
}
