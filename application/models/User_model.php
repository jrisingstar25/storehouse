<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

	protected $table = 'users';

	public function get($id)
	{
		return $this->db->get_where($this->table, array('id' => (int) $id))->row_array();
	}

	public function get_by_username($username)
	{
		return $this->db->get_where($this->table, array('username' => $username))->row_array();
	}

	/**
	 * @param array $filters Supported keys: search, role
	 */
	public function get_all(array $filters = array(), $limit = NULL, $offset = 0)
	{
		$this->apply_filters($filters);
		$this->db->order_by('created_at', 'DESC');

		if ($limit !== NULL)
		{
			$this->db->limit($limit, $offset);
		}

		return $this->db->get($this->table)->result_array();
	}

	public function count_all(array $filters = array())
	{
		$this->apply_filters($filters);

		return $this->db->count_all_results($this->table);
	}

	protected function apply_filters(array $filters)
	{
		if ( ! empty($filters['search']))
		{
			$this->db->group_start()
				->like('name', $filters['search'])
				->or_like('username', $filters['search'])
				->group_end();
		}

		if ( ! empty($filters['role']))
		{
			$this->db->where('role', $filters['role']);
		}
	}

	public function insert(array $data)
	{
		$data['created_at'] = date('Y-m-d H:i:s');
		$this->db->insert($this->table, $data);

		return (int) $this->db->insert_id();
	}

	public function update($id, array $data)
	{
		$data['updated_at'] = date('Y-m-d H:i:s');

		return $this->db->where('id', (int) $id)->update($this->table, $data);
	}

	public function delete($id)
	{
		return $this->db->delete($this->table, array('id' => (int) $id));
	}

	/**
	 * Uniqueness check for the username column, optionally ignoring one row
	 * so an edit form can keep its own name.
	 *
	 * The column collation is case-insensitive, so "Admin" collides with
	 * "admin" - deliberately, to keep look-alike accounts from existing.
	 */
	public function username_exists($username, $ignore_id = NULL)
	{
		$this->db->where('username', $username);

		if ($ignore_id !== NULL)
		{
			$this->db->where('id !=', (int) $ignore_id);
		}

		return $this->db->count_all_results($this->table) > 0;
	}

	public function count_by_role($role)
	{
		return $this->db->where('role', $role)->count_all_results($this->table);
	}
}
