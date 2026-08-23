<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin review queue for doctor applications.
 *
 * Approving promotes users.role to 'doctor'. Rejecting changes nothing about
 * the account - the applicant carries on as a customer either way, so a
 * decision here never costs anybody access.
 */
class Doctors extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('doctor_application_model', 'user_model'));
		$this->load->library('doctor_documents');
	}

	public function index()
	{
		$this->data['title'] = 'Doctor applications';

		$filters = array(
			'status' => $this->input->get('status', TRUE),
			'search' => $this->input->get('q', TRUE),
		);

		$this->render('admin/doctors/index', array(
			'applications' => $this->doctor_application_model->get_all($filters),
			'filters'      => $filters,
			'pending'      => $this->doctor_application_model->count_pending(),
		));
	}

	public function view($id)
	{
		$application = $this->doctor_application_model->get($id);

		if ( ! $application)
		{
			show_404();
		}

		$this->data['title'] = 'Application from ' . $application['user_name'];

		$this->render('admin/doctors/view', array(
			'application' => $application,
		));
	}

	public function approve($id)
	{
		$this->decide($id, 'approve');
	}

	public function reject($id)
	{
		$this->decide($id, 'reject');
	}

	/** Shared body of approve() and reject(). */
	protected function decide($id, $action)
	{
		if ($this->input->method() !== 'post')
		{
			show_404();
		}

		$application = $this->doctor_application_model->get($id);

		if ( ! $application)
		{
			show_404();
		}

		$note = trim((string) $this->input->post('review_note', TRUE));
		$note = $note !== '' ? $note : NULL;

		$done = $action === 'approve'
			? $this->doctor_application_model->approve($id, $this->auth->user_id(), $note)
			: $this->doctor_application_model->reject($id, $this->auth->user_id(), $note);

		if ( ! $done)
		{
			$this->flash_redirect('admin/doctors/view/' . (int) $id, 'warning',
				'That application has already been decided.');
		}

		$message = $action === 'approve'
			? e($application['user_name']) . ' is now a doctor.'
			: 'Application from ' . e($application['user_name'])
				. ' was rejected. Their account continues to work as a customer.';

		$this->flash_redirect('admin/doctors', 'success', $message);
	}

	/**
	 * Stream one of the uploaded documents.
	 *
	 * The storage directory refuses direct HTTP access, so this is the only
	 * way to see a document - and it runs behind Admin_Controller, meaning
	 * the request is already known to be an authenticated admin.
	 *
	 * @param int    $id   Application id
	 * @param string $type diploma|graduation
	 */
	public function document($id, $type)
	{
		$application = $this->doctor_application_model->get($id);

		if ( ! $application OR ! in_array($type, array('diploma', 'graduation'), TRUE))
		{
			show_404();
		}

		$path = $this->doctor_documents->path($application[$type . '_file']);

		if ($path === NULL)
		{
			show_404();
		}

		$mime = function_exists('mime_content_type')
			? mime_content_type($path)
			: 'application/octet-stream';

		// inline: an admin wants to look at it, not download it. nosniff stops
		// a browser second-guessing the type on a file a stranger uploaded.
		// set_content_type() always appends the app charset, which is wrong on
		// a binary image, and it offers no way to suppress that - so the header
		// goes out verbatim instead.
		$this->output
			->set_header('Content-Type: ' . $mime)
			->set_header('Content-Length: ' . filesize($path))
			->set_header('Content-Disposition: inline; filename="' . $type . '"')
			->set_header('X-Content-Type-Options: nosniff')
			->set_header('Cache-Control: private, no-store')
			->set_output(file_get_contents($path));
	}
}
