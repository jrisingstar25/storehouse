<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Orders extends Admin_Controller {

	const PER_PAGE = 20;

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('order_model', 'user_model'));
		$this->load->library('pagination');
	}

	public function index()
	{
		$this->data['title'] = 'Orders';

		$filters = array(
			'status' => $this->input->get('status', TRUE),
			'search' => $this->input->get('q', TRUE),
		);

		$total  = $this->order_model->count_all($filters);
		$offset = max(0, (int) $this->input->get('per_page'));

		$this->pagination->initialize(array(
			'base_url'   => site_url('admin/orders'),
			'total_rows' => $total,
			'per_page'   => self::PER_PAGE,
			'page_query_string'  => TRUE,
			'reuse_query_string' => TRUE,
			'full_tag_open'  => '<ul class="pagination">',
			'full_tag_close' => '</ul>',
			'first_tag_open' => '<li class="page-item">', 'first_tag_close' => '</li>',
			'last_tag_open'  => '<li class="page-item">', 'last_tag_close'  => '</li>',
			'prev_link' => '&laquo;', 'prev_tag_open' => '<li class="page-item">', 'prev_tag_close' => '</li>',
			'next_link' => '&raquo;', 'next_tag_open' => '<li class="page-item">', 'next_tag_close' => '</li>',
			'cur_tag_open'  => '<li class="page-item active"><span class="page-link">',
			'cur_tag_close' => '</span></li>',
			'num_tag_open'  => '<li class="page-item">', 'num_tag_close' => '</li>',
			'attributes'    => array('class' => 'page-link'),
		));

		$this->render('admin/orders/index', array(
			'orders'     => $this->order_model->get_all($filters, self::PER_PAGE, $offset),
			'filters'    => $filters,
			'total'      => $total,
			'pagination' => $this->pagination->create_links(),
		));
	}

	public function view($id)
	{
		$order = $this->order_model->get($id);

		if ( ! $order)
		{
			show_404();
		}

		$this->data['title'] = 'Order ' . $order['order_number'];

		$this->render('admin/orders/view', array(
			'order'    => $order,
			'items'    => $this->order_model->get_items($order['id']),
			'customer' => $order['user_id'] ? $this->user_model->get($order['user_id']) : NULL,
		));
	}

	/** Advance an order through its lifecycle. */
	public function status($id)
	{
		if ($this->input->method() !== 'post')
		{
			show_404();
		}

		$status = $this->input->post('status', TRUE);

		if ($this->order_model->set_status($id, $status))
		{
			$note = $status === 'cancelled'
				? ' Stock has been returned to inventory.'
				: '';

			$this->flash_redirect('admin/orders/view/' . (int) $id, 'success',
				'Order marked as ' . e($status) . '.' . $note);
		}

		$this->flash_redirect('admin/orders/view/' . (int) $id, 'warning',
			'Order status was not changed.');
	}

	public function delete($id)
	{
		if ($this->input->method() !== 'post')
		{
			show_404();
		}

		if ( ! $this->order_model->get($id))
		{
			show_404();
		}

		$this->order_model->delete($id);

		$this->flash_redirect('admin/orders', 'success', 'Order deleted.');
	}
}
