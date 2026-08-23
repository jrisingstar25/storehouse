<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Category_model extends CI_Model {

	protected $table = 'categories';

	public function get($id)
	{
		return $this->db->get_where($this->table, array('id' => (int) $id))->row_array();
	}

	public function get_by_slug($slug)
	{
		return $this->db->get_where($this->table, array('slug' => $slug))->row_array();
	}

	public function get_all()
	{
		return $this->db->order_by('name', 'ASC')->get($this->table)->result_array();
	}

	/** Categories with a live product count, for storefront navigation. */
	public function get_all_with_counts()
	{
		$sql = 'SELECT c.*, COUNT(p.id) AS product_count
			FROM categories c
			LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
			GROUP BY c.id
			ORDER BY c.name ASC';

		return $this->db->query($sql)->result_array();
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
		// products.category_id is ON DELETE SET NULL, so products survive.
		return $this->db->delete($this->table, array('id' => (int) $id));
	}

	public function slug_exists($slug, $ignore_id = NULL)
	{
		$this->db->where('slug', $slug);

		if ($ignore_id !== NULL)
		{
			$this->db->where('id !=', (int) $ignore_id);
		}

		return $this->db->count_all_results($this->table) > 0;
	}

	/** Slug guaranteed unique against the table, suffixing -2, -3, ... */
	public function unique_slug($name, $ignore_id = NULL)
	{
		$base = slugify($name);
		$slug = $base;
		$i    = 2;

		while ($this->slug_exists($slug, $ignore_id))
		{
			$slug = $base . '-' . $i++;
		}

		return $slug;
	}

	public function count_all()
	{
		return $this->db->count_all($this->table);
	}

	/** id => name map for <select> inputs. */
	public function dropdown()
	{
		$out = array();

		foreach ($this->get_all() as $row)
		{
			$out[$row['id']] = $row['name'];
		}

		return $out;
	}
}
