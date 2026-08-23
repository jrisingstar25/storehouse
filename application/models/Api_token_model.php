<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Bearer tokens for the mobile API.
 *
 * The table stores only a SHA-256 of each token. The plaintext is returned
 * once, when the token is issued, and is never recoverable afterwards - so a
 * dump of this table cannot be replayed against the API. SHA-256 rather than
 * bcrypt is deliberate: the token is 256 bits of entropy from the CSPRNG, not
 * a guessable human secret, so there is nothing for a slow hash to defend and
 * lookups stay a single indexed query.
 */
class Api_token_model extends CI_Model {

	protected $table = 'api_tokens';

	/** How long a freshly issued token stays valid. */
	const LIFETIME_DAYS = 30;

	/**
	 * Issue a token for a user.
	 *
	 * @param  int    $user_id
	 * @param  string $device Free-text client label, for the user to identify
	 *                        sessions later
	 * @return array{token: string, expires_at: string} The plaintext token,
	 *         which the caller must return to the client immediately
	 */
	public function issue($user_id, $device = NULL)
	{
		$token   = bin2hex(random_bytes(32));
		$expires = date('Y-m-d H:i:s', strtotime('+' . self::LIFETIME_DAYS . ' days'));

		$this->db->insert($this->table, array(
			'user_id'    => (int) $user_id,
			'token_hash' => $this->hash($token),
			'device'     => $device !== NULL ? substr($device, 0, 120) : NULL,
			'expires_at' => $expires,
			'created_at' => date('Y-m-d H:i:s'),
		));

		return array('token' => $token, 'expires_at' => $expires);
	}

	/**
	 * Resolve a plaintext token to its user.
	 *
	 * Expired rows are deleted as they are encountered, so the table does not
	 * accumulate dead tokens without a separate cleanup job.
	 *
	 * @return array|null The user record, or NULL if the token is unusable
	 */
	public function resolve($token)
	{
		if ( ! is_string($token) OR $token === '')
		{
			return NULL;
		}

		$row = $this->db->get_where($this->table, array('token_hash' => $this->hash($token)))->row_array();

		if ( ! $row)
		{
			return NULL;
		}

		if ($row['expires_at'] !== NULL && strtotime($row['expires_at']) < time())
		{
			$this->db->delete($this->table, array('id' => $row['id']));

			return NULL;
		}

		$this->load->model('user_model');
		$user = $this->user_model->get($row['user_id']);

		// Deactivated or deleted since the token was issued.
		if ( ! $user OR (int) $user['is_active'] !== 1)
		{
			return NULL;
		}

		$this->db->where('id', $row['id'])->update($this->table, array(
			'last_used_at' => date('Y-m-d H:i:s'),
		));

		return $user;
	}

	/** Revoke a single token (sign out on one device). */
	public function revoke($token)
	{
		$this->db->delete($this->table, array('token_hash' => $this->hash($token)));

		return $this->db->affected_rows() > 0;
	}

	/** Revoke every token a user holds (sign out everywhere). */
	public function revoke_all($user_id)
	{
		return $this->db->delete($this->table, array('user_id' => (int) $user_id));
	}

	protected function hash($token)
	{
		return hash('sha256', $token);
	}
}
