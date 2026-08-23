<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_model extends CI_Model {

	protected $table = 'orders';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('product_model');
	}

	/**
	 * Persist an order and its lines, taking stock as it goes.
	 *
	 * Runs inside a transaction: if any line cannot reserve stock the whole
	 * order is rolled back, so an order never exists with unreserved items.
	 *
	 * @param array $order Order header fields
	 * @param array $lines Cart lines from Cart_model::contents()
	 * @return array{ok: bool, order_id: int|null, message: string}
	 */
	public function place(array $order, array $lines)
	{
		if (empty($lines))
		{
			return array('ok' => FALSE, 'order_id' => NULL, 'message' => 'Your cart is empty.');
		}

		$this->db->trans_begin();

		$order['order_number'] = $this->generate_order_number();
		$order['created_at']   = date('Y-m-d H:i:s');

		$this->db->insert($this->table, $order);
		$order_id = (int) $this->db->insert_id();

		foreach ($lines as $line)
		{
			if ( ! $this->product_model->decrement_stock($line['product_id'], $line['qty']))
			{
				$this->db->trans_rollback();

				return array(
					'ok'       => FALSE,
					'order_id' => NULL,
					'message'  => 'Sorry - "' . $line['name'] . '" sold out while you were checking out. Please review your cart.',
				);
			}

			$this->db->insert('order_items', array(
				'order_id'     => $order_id,
				'product_id'   => $line['product_id'],
				'product_name' => $line['name'],
				'price'        => $line['price'],
				'qty'          => $line['qty'],
				'subtotal'     => $line['subtotal'],
			));
		}

		if ($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();

			return array('ok' => FALSE, 'order_id' => NULL, 'message' => 'Could not place your order. Please try again.');
		}

		$this->db->trans_commit();

		return array('ok' => TRUE, 'order_id' => $order_id, 'message' => 'Order placed.');
	}

	/** Human-readable, collision-checked order number. */
	protected function generate_order_number()
	{
		do
		{
			$number = 'JJ-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
		}
		while ($this->db->where('order_number', $number)->count_all_results($this->table) > 0);

		return $number;
	}

	public function get($id)
	{
		return $this->db->get_where($this->table, array('id' => (int) $id))->row_array();
	}

	public function get_by_number($number)
	{
		return $this->db->get_where($this->table, array('order_number' => $number))->row_array();
	}

	public function get_items($order_id)
	{
		return $this->db->where('order_id', (int) $order_id)->get('order_items')->result_array();
	}

	/**
	 * @param array $filters status, user_id, search
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
		if ( ! empty($filters['status']))
		{
			$this->db->where('status', $filters['status']);
		}

		if ( ! empty($filters['user_id']))
		{
			$this->db->where('user_id', (int) $filters['user_id']);
		}

		if ( ! empty($filters['search']))
		{
			$this->db->group_start()
				->like('order_number', $filters['search'])
				->or_like('customer_name', $filters['search'])
				->group_end();
		}
	}

	/**
	 * Move an order to a new status. Cancelling returns stock to inventory
	 * exactly once - an already-cancelled order is a no-op.
	 *
	 * @return bool
	 */
	public function set_status($id, $status)
	{
		if ( ! in_array($status, order_statuses(), TRUE))
		{
			return FALSE;
		}

		$order = $this->get($id);

		if ( ! $order OR $order['status'] === $status)
		{
			return FALSE;
		}

		if ($status === 'cancelled' && $order['status'] !== 'cancelled')
		{
			foreach ($this->get_items($id) as $item)
			{
				if ($item['product_id'] !== NULL)
				{
					$this->product_model->increment_stock($item['product_id'], $item['qty']);
				}
			}
		}

		return $this->db->where('id', (int) $id)->update($this->table, array(
			'status'     => $status,
			'updated_at' => date('Y-m-d H:i:s'),
		));
	}

	public function delete($id)
	{
		// order_items is ON DELETE CASCADE.
		return $this->db->delete($this->table, array('id' => (int) $id));
	}

	/* ------------------------------------------------------------------
	 * Dashboard aggregates
	 * --------------------------------------------------------------- */

	public function count_by_status($status)
	{
		return $this->db->where('status', $status)->count_all_results($this->table);
	}

	/** Gross revenue, excluding cancelled orders. */
	public function total_revenue()
	{
		$row = $this->db->select_sum('total')
			->where('status !=', 'cancelled')
			->get($this->table)
			->row_array();

		return (float) $row['total'];
	}

	public function recent($limit = 8)
	{
		return $this->db->order_by('created_at', 'DESC')
			->limit($limit)
			->get($this->table)
			->result_array();
	}

	/**
	 * Revenue per day for the last N days, oldest first, with empty days
	 * filled in so the chart has no gaps.
	 */
	public function revenue_by_day($days = 14)
	{
		$days = (int) $days;

		$rows = $this->db->query(
			'SELECT DATE(created_at) AS day, SUM(total) AS revenue, COUNT(*) AS orders
			 FROM orders
			 WHERE status != "cancelled" AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
			 GROUP BY DATE(created_at)
			 ORDER BY day ASC',
			array($days - 1)
		)->result_array();

		$by_day = array();

		foreach ($rows as $row)
		{
			$by_day[$row['day']] = $row;
		}

		$series = array();

		for ($i = $days - 1; $i >= 0; $i--)
		{
			$day = date('Y-m-d', strtotime("-{$i} days"));

			$series[] = array(
				'day'     => $day,
				'revenue' => isset($by_day[$day]) ? (float) $by_day[$day]['revenue'] : 0.0,
				'orders'  => isset($by_day[$day]) ? (int) $by_day[$day]['orders'] : 0,
			);
		}

		return $series;
	}

	/** Best sellers by units shipped, excluding cancelled orders. */
	public function top_products($limit = 5)
	{
		return $this->db->query(
			'SELECT oi.product_id, oi.product_name,
			        SUM(oi.qty) AS units, SUM(oi.subtotal) AS revenue
			 FROM order_items oi
			 JOIN orders o ON o.id = oi.order_id
			 WHERE o.status != "cancelled"
			 GROUP BY oi.product_id, oi.product_name
			 ORDER BY units DESC
			 LIMIT ' . (int) $limit
		)->result_array();
	}
}
