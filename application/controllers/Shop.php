<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shop extends Customer_Controller {

	const PER_PAGE = 9;

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('product_model', 'category_model'));
		$this->load->library('pagination');
	}

	/** Catalogue with search, category filter and sorting. */
	public function index()
	{
		$this->data['title'] = 'Shop';

		$filters = array(
			'is_active' => 1,
			'search'    => $this->input->get('q', TRUE),
			'sort'      => $this->input->get('sort', TRUE),
		);

		$category = NULL;

		if ($slug = $this->input->get('category', TRUE))
		{
			$category = $this->category_model->get_by_slug($slug);

			if ($category)
			{
				$filters['category_id'] = $category['id'];
			}
		}

		$this->list_products($filters, $category, 'shop');
	}

	/** Catalogue scoped to one category, via /category/{slug}. */
	public function category($slug)
	{
		$category = $this->category_model->get_by_slug($slug);

		if ( ! $category)
		{
			show_404();
		}

		$this->data['title'] = $category['name'];

		$filters = array(
			'is_active'   => 1,
			'category_id' => $category['id'],
			'search'      => $this->input->get('q', TRUE),
			'sort'        => $this->input->get('sort', TRUE),
		);

		$this->list_products($filters, $category, 'category/' . $slug);
	}

	/**
	 * Shared listing routine for index() and category().
	 *
	 * @param array      $filters  Product_model filters
	 * @param array|null $category Active category, if any
	 * @param string     $base     Route the pagination links point at
	 */
	protected function list_products(array $filters, $category, $base)
	{
		$total  = $this->product_model->count_all($filters);
		$offset = (int) $this->input->get('per_page');
		$offset = max(0, $offset);

		$this->pagination->initialize($this->pagination_config($base, $total));

		$this->render('shop/index', array(
			'page_scripts'    => array(base_url('assets/js/shop.js')),
			'products'        => $this->product_model->get_all($filters, self::PER_PAGE, $offset),
			'categories'      => $this->category_model->get_all_with_counts(),
			'active_category' => $category,
			'total'           => $total,
			'filters'         => $filters,
			'pagination'      => $this->pagination->create_links(),
		));
	}

	/** Bootstrap-flavoured pagination that preserves the query string. */
	protected function pagination_config($base, $total)
	{
		return array(
			'base_url'    => site_url($base),
			'total_rows'  => $total,
			'per_page'    => self::PER_PAGE,
			'page_query_string' => TRUE,
			'reuse_query_string' => TRUE,
			'use_page_numbers'  => FALSE,
			'full_tag_open'   => '<ul class="pagination">',
			'full_tag_close'  => '</ul>',
			'first_link'      => 'First',
			'last_link'       => 'Last',
			'first_tag_open'  => '<li class="page-item">',
			'first_tag_close' => '</li>',
			'prev_link'       => '&laquo;',
			'prev_tag_open'   => '<li class="page-item">',
			'prev_tag_close'  => '</li>',
			'next_link'       => '&raquo;',
			'next_tag_open'   => '<li class="page-item">',
			'next_tag_close'  => '</li>',
			'last_tag_open'   => '<li class="page-item">',
			'last_tag_close'  => '</li>',
			'cur_tag_open'    => '<li class="page-item active"><span class="page-link">',
			'cur_tag_close'   => '</span></li>',
			'num_tag_open'    => '<li class="page-item">',
			'num_tag_close'   => '</li>',
			'attributes'      => array('class' => 'page-link'),
		);
	}

	/** Product detail page. */
	public function product($slug)
	{
		$product = $this->product_model->get_by_slug($slug);

		// Inactive products are hidden from the storefront but stay visible
		// to admins so they can preview before publishing.
		if ( ! $product OR ((int) $product['is_active'] !== 1 && ! $this->auth->is_admin()))
		{
			show_404();
		}

		$related = array();

		if ($product['category_id'])
		{
			foreach ($this->product_model->get_all(
				array('is_active' => 1, 'category_id' => $product['category_id']), 5) as $row)
			{
				if ((int) $row['id'] !== (int) $product['id'])
				{
					$related[] = $row;
				}
			}

			$related = array_slice($related, 0, 4);
		}

		$this->data['title'] = $product['name'];

		$this->render('shop/product', array(
			'page_scripts' => array(base_url('assets/js/shop.js')),
			'product' => $product,
			'related' => $related,
		));
	}
}
