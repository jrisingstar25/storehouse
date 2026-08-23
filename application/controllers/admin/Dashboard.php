<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Admin_Controller {

	const LOW_STOCK_THRESHOLD = 5;

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('product_model', 'category_model', 'order_model', 'user_model'));
	}

	public function index()
	{
		$this->data['title'] = 'Dashboard';

		$revenue_series = $this->order_model->revenue_by_day(14);

		$this->render('admin/dashboard', array(
			'stats' => array(
				'revenue'    => $this->order_model->total_revenue(),
				'orders'     => $this->order_model->count_all(),
				'pending'    => $this->order_model->count_by_status('pending'),
				'products'   => $this->product_model->count_all_products(),
				'categories' => $this->category_model->count_all(),
				'customers'  => $this->user_model->count_by_role('customer'),
				'low_stock'  => $this->product_model->count_low_stock(self::LOW_STOCK_THRESHOLD),
			),
			'recent_orders'  => $this->order_model->recent(8),
			'low_stock'      => $this->product_model->get_low_stock(self::LOW_STOCK_THRESHOLD, 8),
			'top_products'   => $this->order_model->top_products(5),
			'revenue_series' => $revenue_series,
		));
	}
}
