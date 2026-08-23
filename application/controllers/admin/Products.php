<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends Admin_Controller {

	const PER_PAGE = 15;

	/** Where product images land, relative to the web root. */
	protected $upload_path;

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('product_model', 'category_model'));
		$this->load->library('pagination');

		$this->upload_path = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;
	}

	public function index()
	{
		$this->data['title'] = 'Products';

		$filters = array(
			'search'      => $this->input->get('q', TRUE),
			'category_id' => $this->input->get('category_id', TRUE),
			'is_active'   => $this->input->get('is_active', TRUE),
			'sort'        => $this->input->get('sort', TRUE),
		);

		$total  = $this->product_model->count_all($filters);
		$offset = max(0, (int) $this->input->get('per_page'));

		$this->pagination->initialize(array(
			'base_url'   => site_url('admin/products'),
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

		$this->render('admin/products/index', array(
			'products'   => $this->product_model->get_all($filters, self::PER_PAGE, $offset),
			'categories' => $this->category_model->dropdown(),
			'filters'    => $filters,
			'total'      => $total,
			'pagination' => $this->pagination->create_links(),
		));
	}

	public function create()
	{
		$this->data['title'] = 'New product';

		if ($this->input->method() === 'post' && $this->validate())
		{
			$image = $this->handle_upload();

			if ($image === FALSE)
			{
				$this->render('admin/products/form', $this->form_data(NULL));
				return;
			}

			$name = $this->input->post('name', TRUE);

			$id = $this->product_model->insert(array(
				'category_id' => $this->input->post('category_id') ?: NULL,
				'name'        => $name,
				'slug'        => $this->product_model->unique_slug($this->slug_source($name)),
				'sku'         => $this->input->post('sku', TRUE),
				'description' => $this->input->post('description', TRUE),
				'price'       => (float) $this->input->post('price'),
				'stock'       => (int) $this->input->post('stock'),
				'image'       => $image,
				'is_active'   => (int) (bool) $this->input->post('is_active'),
			));

			$this->flash_redirect('admin/products/edit/' . $id, 'success', 'Product created.');
		}

		$this->render('admin/products/form', $this->form_data(NULL));
	}

	public function edit($id)
	{
		$product = $this->product_model->get($id);

		if ( ! $product)
		{
			show_404();
		}

		$this->data['title'] = 'Edit product';

		if ($this->input->method() === 'post' && $this->validate())
		{
			$image = $this->handle_upload();

			if ($image === FALSE)
			{
				$this->render('admin/products/form', $this->form_data($product));
				return;
			}

			$name   = $this->input->post('name', TRUE);
			$fields = array(
				'category_id' => $this->input->post('category_id') ?: NULL,
				'name'        => $name,
				'slug'        => $this->product_model->unique_slug($this->slug_source($name), $product['id']),
				'sku'         => $this->input->post('sku', TRUE),
				'description' => $this->input->post('description', TRUE),
				'price'       => (float) $this->input->post('price'),
				'stock'       => (int) $this->input->post('stock'),
				'is_active'   => (int) (bool) $this->input->post('is_active'),
			);

			if ($this->input->post('remove_image') && ! $image)
			{
				$this->delete_image($product['image']);
				$fields['image'] = NULL;
			}
			elseif ($image)
			{
				// A new file replaced the old one - do not leave it orphaned.
				$this->delete_image($product['image']);
				$fields['image'] = $image;
			}

			$this->product_model->update($product['id'], $fields);

			$this->flash_redirect('admin/products/edit/' . $product['id'], 'success', 'Product updated.');
		}

		$this->render('admin/products/form', $this->form_data($product));
	}

	public function delete($id)
	{
		if ($this->input->method() !== 'post')
		{
			show_404();
		}

		$product = $this->product_model->get($id);

		if ( ! $product)
		{
			show_404();
		}

		// order_items.product_id is ON DELETE SET NULL and each line keeps
		// its own product_name copy, so order history stays readable.
		$this->product_model->delete($product['id']);
		$this->delete_image($product['image']);

		$this->flash_redirect('admin/products', 'success', 'Product deleted.');
	}

	/** Quick active/inactive switch from the list view. */
	public function toggle($id)
	{
		if ($this->input->method() !== 'post')
		{
			show_404();
		}

		$product = $this->product_model->get($id);

		if ( ! $product)
		{
			show_404();
		}

		$active = (int) $product['is_active'] === 1 ? 0 : 1;
		$this->product_model->update($product['id'], array('is_active' => $active));

		$this->flash_redirect('admin/products', 'success',
			e($product['name']) . ($active ? ' is now visible in the shop.' : ' is now hidden from the shop.'));
	}

	/* ------------------------------------------------------------------
	 * Internals
	 * --------------------------------------------------------------- */

	protected function form_data($product)
	{
		return array(
			'product'    => $product,
			'categories' => $this->category_model->dropdown(),
		);
	}

	protected function validate()
	{
		$this->form_validation->set_rules('name', 'Name', 'required|trim|min_length[2]|max_length[180]');
		$this->form_validation->set_rules('slug', 'Slug', 'trim|max_length[200]');
		$this->form_validation->set_rules('sku', 'SKU', 'trim|max_length[60]');
		$this->form_validation->set_rules('price', 'Price', 'required|numeric|greater_than_equal_to[0]');
		$this->form_validation->set_rules('stock', 'Stock', 'required|integer|greater_than_equal_to[0]');
		$this->form_validation->set_rules('category_id', 'Category', 'trim|integer');
		$this->form_validation->set_rules('description', 'Description', 'trim');

		return $this->form_validation->run();
	}

	/** Use the slug the admin typed, if any, otherwise derive from the name. */
	protected function slug_source($name)
	{
		$slug = trim((string) $this->input->post('slug', TRUE));

		return $slug !== '' ? $slug : $name;
	}

	/**
	 * Move an uploaded image into place.
	 *
	 * @return string|null|false Filename on success, NULL if no file was
	 *                           submitted, FALSE if the upload was rejected
	 *                           (the reason is put into $this->data[error]).
	 */
	protected function handle_upload()
	{
		if (empty($_FILES['image']['name']))
		{
			return NULL;
		}

		if ( ! is_dir($this->upload_path))
		{
			mkdir($this->upload_path, 0755, TRUE);
		}

		$this->load->library('upload', array(
			'upload_path'   => $this->upload_path,
			'allowed_types' => 'jpg|jpeg|png|gif|webp',
			'max_size'      => 4096,
			'encrypt_name'  => TRUE,
		));

		if ( ! $this->upload->do_upload('image'))
		{
			$this->data['error'] = strip_tags($this->upload->display_errors('', ''));

			return FALSE;
		}

		return $this->upload->data('file_name');
	}

	protected function delete_image($filename)
	{
		if (empty($filename))
		{
			return;
		}

		// basename() keeps a crafted value from reaching outside the folder.
		$path = $this->upload_path . basename($filename);

		if (is_file($path))
		{
			@unlink($path);
		}
	}
}
