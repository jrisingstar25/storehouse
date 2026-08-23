<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('user_model', 'order_model'));
	}

	public function index()
	{
		$this->data['title'] = 'Users';

		$filters = array(
			'search' => $this->input->get('q', TRUE),
			'role'   => $this->input->get('role', TRUE),
		);

		$this->render('admin/users/index', array(
			'users'   => $this->user_model->get_all($filters),
			'filters' => $filters,
		));
	}

	public function create()
	{
		$this->data['title'] = 'New user';

		if ($this->input->method() === 'post')
		{
			$this->rules(NULL);
			$this->form_validation->set_rules('password', 'Password', 'required|min_length[3]|max_length[72]');

			if ($this->form_validation->run())
			{
				$this->user_model->insert(array(
					'name'      => $this->input->post('name', TRUE),
					'username'  => $this->input->post('username', TRUE),
					'password'  => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
					'role'      => $this->input->post('role', TRUE),
					'phone'     => $this->input->post('phone', TRUE),
					'address'   => $this->input->post('address', TRUE),
					'is_active' => (int) (bool) $this->input->post('is_active'),
				));

				$this->flash_redirect('admin/users', 'success', 'User created.');
			}
		}

		$this->render('admin/users/form', array('user' => NULL));
	}

	public function edit($id)
	{
		$user = $this->user_model->get($id);

		if ( ! $user)
		{
			show_404();
		}

		$this->data['title'] = 'Edit user';

		if ($this->input->method() === 'post')
		{
			$this->rules($user['id']);

			// Blank means "leave the current password alone".
			if ($this->input->post('password') !== '')
			{
				$this->form_validation->set_rules('password', 'Password', 'min_length[3]|max_length[72]');
			}

			if ($this->form_validation->run())
			{
				$fields = array(
					'name'    => $this->input->post('name', TRUE),
					'username' => $this->input->post('username', TRUE),
					'phone'   => $this->input->post('phone', TRUE),
					'address' => $this->input->post('address', TRUE),
				);

				if ($this->input->post('password') !== '')
				{
					$fields['password'] = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
				}

				$role   = $this->input->post('role', TRUE);
				$active = (int) (bool) $this->input->post('is_active');

				// Guard against an admin locking themselves out, and against
				// the last admin account disappearing entirely.
				if ((int) $user['id'] === $this->auth->user_id() && ($role !== 'admin' OR $active !== 1))
				{
					$this->flash_redirect('admin/users/edit/' . $user['id'], 'danger',
						'You cannot remove your own admin access.');
				}

				if ($user['role'] === 'admin' && ($role !== 'admin' OR $active !== 1)
					&& $this->user_model->count_by_role('admin') <= 1)
				{
					$this->flash_redirect('admin/users/edit/' . $user['id'], 'danger',
						'This is the only admin account - it must stay an active admin.');
				}

				$fields['role']      = $role;
				$fields['is_active'] = $active;

				$this->user_model->update($user['id'], $fields);

				$this->flash_redirect('admin/users', 'success', 'User updated.');
			}
		}

		$this->render('admin/users/form', array('user' => $user));
	}

	public function view($id)
	{
		$user = $this->user_model->get($id);

		if ( ! $user)
		{
			show_404();
		}

		$this->data['title'] = $user['name'];

		$this->render('admin/users/view', array(
			'user'   => $user,
			'orders' => $this->order_model->get_all(array('user_id' => $user['id'])),
		));
	}

	public function delete($id)
	{
		if ($this->input->method() !== 'post')
		{
			show_404();
		}

		$user = $this->user_model->get($id);

		if ( ! $user)
		{
			show_404();
		}

		if ((int) $user['id'] === $this->auth->user_id())
		{
			$this->flash_redirect('admin/users', 'danger', 'You cannot delete your own account.');
		}

		if ($user['role'] === 'admin' && $this->user_model->count_by_role('admin') <= 1)
		{
			$this->flash_redirect('admin/users', 'danger', 'The last admin account cannot be deleted.');
		}

		// orders.user_id is ON DELETE SET NULL, so past orders survive as
		// guest orders with the customer details already copied onto them.
		$this->user_model->delete($user['id']);

		$this->flash_redirect('admin/users', 'success', 'User deleted.');
	}

	protected function rules($ignore_id)
	{
		$this->form_validation->set_rules('name', 'Name', 'required|trim|min_length[2]|max_length[100]');
		$this->form_validation->set_rules(
			'username', 'Username',
			'required|trim|min_length[3]|max_length[60]|alpha_dash|callback_unique_username[' . (int) $ignore_id . ']'
		);
		$this->form_validation->set_rules('role', 'Role', 'required|in_list[customer,doctor,admin]');
		$this->form_validation->set_rules('phone', 'Phone', 'trim|max_length[30]');
		$this->form_validation->set_rules('address', 'Address', 'trim');
	}

	/** Validation callback; $ignore_id of 0 means "no row to skip". */
	public function unique_username($username, $ignore_id)
	{
		$ignore_id = (int) $ignore_id ?: NULL;

		if ($this->user_model->username_exists($username, $ignore_id))
		{
			$this->form_validation->set_message('unique_username', 'That username is already taken.');

			return FALSE;
		}

		return TRUE;
	}
}
