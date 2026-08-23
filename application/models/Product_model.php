<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model {

	protected $table = 'products';

	/** Select list used everywhere a product is read with its category. */
	protected function base_select()
	{
		$this->db->select('p.*, c.name AS category_name, c.slug AS category_slug')
			->from('products p')
			->join('categories c', 'c.id = p.category_id', 'left');
	}

	public function get($id)
	{
		$this->base_select();

		return $this->db->where('p.id', (int) $id)->get()->row_array();
	}

	/**
	 * @param array $filters search, category_id, is_active, in_stock, sort
	 */
	public function get_all(array $filters = array(), $limit = NULL, $offset = 0)
	{
		$this->base_select();
		$this->apply_filters($filters);
		$this->apply_sort(isset($filters['sort']) ? $filters['sort'] : NULL);

		if ($limit !== NULL)
		{
			$this->db->limit($limit, $offset);
		}

		return $this->db->get()->result_array();
	}

	public function count_all(array $filters = array())
	{
		$this->db->from('products p')->join('categories c', 'c.id = p.category_id', 'left');
		$this->apply_filters($filters);

		return $this->db->count_all_results();
	}

	protected function apply_filters(array $filters)
	{
		if ( ! empty($filters['search']))
		{
			$this->db->group_start()
				->like('p.name', $filters['search'])
				->or_like('p.sku', $filters['search'])
				->or_like('p.description', $filters['search'])
				->group_end();
		}

		if ( ! empty($filters['category_id']))
		{
			$this->db->where('p.category_id', (int) $filters['category_id']);
		}

		if (isset($filters['is_active']) && $filters['is_active'] !== '')
		{
			$this->db->where('p.is_active', (int) $filters['is_active']);
		}

		if ( ! empty($filters['in_stock']))
		{
			$this->db->where('p.stock >', 0);
		}
	}

	protected function apply_sort($sort)
	{
		switch ($sort)
		{
			case 'price_asc':  $this->db->order_by('p.price', 'ASC');   break;
			case 'price_desc': $this->db->order_by('p.price', 'DESC');  break;
			case 'name_asc':   $this->db->order_by('p.name', 'ASC');    break;
			default:           $this->db->order_by('p.created_at', 'DESC');
		}
	}

	public function get_featured($limit = 6)
	{
		return $this->get_all(array('is_active' => 1, 'in_stock' => 1), $limit);
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
	 * Decrement stock without dropping below zero. The `stock >=` guard makes
	 * this safe against two checkouts racing for the last unit.
	 *
	 * @return bool TRUE if stock was actually taken
	 */
	public function decrement_stock($id, $qty)
	{
		$this->db->set('stock', 'stock - ' . (int) $qty, FALSE)
			->where('id', (int) $id)
			->where('stock >=', (int) $qty)
			->update($this->table);

		return $this->db->affected_rows() > 0;
	}

	public function increment_stock($id, $qty)
	{
		return $this->db->set('stock', 'stock + ' . (int) $qty, FALSE)
			->where('id', (int) $id)
			->update($this->table);
	}

	public function count_all_products()
	{
		return $this->db->count_all($this->table);
	}

	public function count_low_stock($threshold = 5)
	{
		return $this->db->where('stock <=', (int) $threshold)->count_all_results($this->table);
	}

	public function get_low_stock($threshold = 5, $limit = 10)
	{
		return $this->db->where('stock <=', (int) $threshold)
			->order_by('stock', 'ASC')
			->limit($limit)
			->get($this->table)
			->result_array();
	}
}
