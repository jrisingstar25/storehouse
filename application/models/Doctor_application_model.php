<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Applications from customers asking to be recognised as doctors.
 *
 * The account exists and works as a normal customer from the moment it is
 * created; approving an application here is what promotes users.role to
 * 'doctor'. Rejection leaves the account exactly as it was, so nobody loses
 * access by being turned down.
 */
class Doctor_application_model extends CI_Model {

	protected $table = 'doctor_applications';

	/** Select list used wherever an application is shown with its applicant. */
	protected function base_select()
	{
		$this->db->select('a.*, u.name AS user_name, u.username, u.role AS user_role,
			u.phone AS user_phone, u.is_active AS user_is_active,
			r.name AS reviewer_name')
			->from('doctor_applications a')
			->join('users u', 'u.id = a.user_id', 'inner')
			->join('users r', 'r.id = a.reviewed_by', 'left');
	}

	public function get($id)
	{
		$this->base_select();

		return $this->db->where('a.id', (int) $id)->get()->row_array();
	}

	/**
	 * @param array $filters status, search
	 */
	public function get_all(array $filters = array())
	{
		$this->base_select();
		$this->apply_filters($filters);

		// Pending first, then newest - the queue an admin actually works from.
		$this->db->order_by("FIELD(a.status, 'pending', 'rejected', 'approved')", '', FALSE)
			->order_by('a.created_at', 'DESC');

		return $this->db->get()->result_array();
	}

	public function count_all(array $filters = array())
	{
		$this->base_select();
		$this->apply_filters($filters);

		return $this->db->count_all_results();
	}

	protected function apply_filters(array $filters)
	{
		if ( ! empty($filters['status']))
		{
			$this->db->where('a.status', $filters['status']);
		}

		if ( ! empty($filters['search']))
		{
			$this->db->group_start()
				->like('u.name', $filters['search'])
				->or_like('u.username', $filters['search'])
				->group_end();
		}
	}

	/** Every application a user has made, newest first. */
	public function get_for_user($user_id)
	{
		$this->base_select();

		return $this->db->where('a.user_id', (int) $user_id)
			->order_by('a.created_at', 'DESC')
			->get()
			->result_array();
	}

	/** The application currently awaiting review for a user, if any. */
	public function pending_for_user($user_id)
	{
		$this->base_select();

		return $this->db->where('a.user_id', (int) $user_id)
			->where('a.status', 'pending')
			->get()
			->row_array();
	}

	/** The most recent application for a user, whatever its state. */
	public function latest_for_user($user_id)
	{
		$this->base_select();

		return $this->db->where('a.user_id', (int) $user_id)
			->order_by('a.created_at', 'DESC')
			->limit(1)
			->get()
			->row_array();
	}

	public function insert(array $data)
	{
		$data['created_at'] = date('Y-m-d H:i:s');
		$this->db->insert($this->table, $data);

		return (int) $this->db->insert_id();
	}

	/**
	 * Approve an application and promote the applicant.
	 *
	 * Runs in a transaction: an approved application must never exist
	 * alongside an account that was not promoted.
	 *
	 * @return bool
	 */
	public function approve($id, $reviewer_id, $note = NULL)
	{
		$application = $this->get($id);

		if ( ! $application OR $application['status'] !== 'pending')
		{
			return FALSE;
		}

		$this->db->trans_begin();

		$this->db->where('id', (int) $id)->update($this->table, array(
			'status'      => 'approved',
			'review_note' => $note,
			'reviewed_by' => (int) $reviewer_id,
			'reviewed_at' => date('Y-m-d H:i:s'),
			'updated_at'  => date('Y-m-d H:i:s'),
		));

		// Never demote an admin who happened to apply.
		if ($application['user_role'] !== 'admin')
		{
			$this->db->where('id', (int) $application['user_id'])->update('users', array(
				'role'       => 'doctor',
				'updated_at' => date('Y-m-d H:i:s'),
			));
		}

		if ($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();

			return FALSE;
		}

		$this->db->trans_commit();

		return TRUE;
	}

	/**
	 * Turn an application down. The account keeps working as a customer.
	 *
	 * @return bool
	 */
	public function reject($id, $reviewer_id, $note = NULL)
	{
		$application = $this->get($id);

		if ( ! $application OR $application['status'] !== 'pending')
		{
			return FALSE;
		}

		return $this->db->where('id', (int) $id)->update($this->table, array(
			'status'      => 'rejected',
			'review_note' => $note,
			'reviewed_by' => (int) $reviewer_id,
			'reviewed_at' => date('Y-m-d H:i:s'),
			'updated_at'  => date('Y-m-d H:i:s'),
		));
	}

	public function count_pending()
	{
		return $this->db->where('status', 'pending')->count_all_results($this->table);
	}

	public function delete($id)
	{
		return $this->db->delete($this->table, array('id' => (int) $id));
	}
}
