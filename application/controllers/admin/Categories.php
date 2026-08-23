<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Categories extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('category_model', 'product_model'));
	}

	public function index()
	{
		$this->data['title'] = 'Categories';

		$this->render('admin/categories/index', array(
			'categories' => $this->category_model->get_all_with_counts(),
		));
	}

	public function create()
	{
		$this->data['title'] = 'New category';

		if ($this->input->method() === 'post' && $this->validate())
		{
			$name = $this->input->post('name', TRUE);

			$this->category_model->insert(array(
				'name'        => $name,
				'slug'        => $this->category_model->unique_slug($this->slug_source($name)),
				'description' => $this->input->post('description', TRUE),
			));

			$this->flash_redirect('admin/categories', 'success', 'Category created.');
		}

		$this->render('admin/categories/form', array('category' => NULL));
	}

	public function edit($id)
	{
		$category = $this->category_model->get($id);

		if ( ! $category)
		{
			show_404();
		}

		$this->data['title'] = 'Edit category';

		if ($this->input->method() === 'post' && $this->validate())
		{
			$name = $this->input->post('name', TRUE);

			$this->category_model->update($category['id'], array(
				'name'        => $name,
				'slug'        => $this->category_model->unique_slug($this->slug_source($name), $category['id']),
				'description' => $this->input->post('description', TRUE),
			));

			$this->flash_redirect('admin/categories', 'success', 'Category updated.');
		}

		$this->render('admin/categories/form', array('category' => $category));
	}

	public function delete($id)
	{
		if ($this->input->method() !== 'post')
		{
			show_404();
		}

		$category = $this->category_model->get($id);

		if ( ! $category)
		{
			show_404();
		}

		// Products keep existing; the FK sets their category_id to NULL.
		$this->category_model->delete($category['id']);

		$this->flash_redirect('admin/categories', 'success',
			'Category deleted. Any products in it are now uncategorised.');
	}

	protected function validate()
	{
		$this->form_validation->set_rules('name', 'Name', 'required|trim|min_length[2]|max_length[100]');
		$this->form_validation->set_rules('slug', 'Slug', 'trim|max_length[120]');
		$this->form_validation->set_rules('description', 'Description', 'trim');

		return $this->form_validation->run();
	}

	/** Use the supplied slug if the admin typed one, else derive from the name. */
	protected function slug_source($name)
	{
		$slug = trim((string) $this->input->post('slug', TRUE));

		return $slug !== '' ? $slug : $name;
	}
}
