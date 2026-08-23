<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Storage for the documents backing a doctor application.
 *
 * Files land in uploads/doctor_documents/, which denies direct HTTP access.
 * Nothing here builds a public URL: reading a document goes through
 * Admin\Doctors::document(), which authorises the request and streams the
 * bytes. Keeping both halves in one class is what stops a later change from
 * accidentally exposing them the way product images are exposed.
 */
class Doctor_documents {

	/** @var CI_Controller */
	protected $CI;

	/** @var string Absolute path to the storage directory. */
	protected $path;

	/** @var string|null Why the last store() failed. */
	protected $error = NULL;

	const ALLOWED_TYPES = 'jpg|jpeg|png|webp';
	const MAX_SIZE_KB   = 4096;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->library('upload');
		$this->path = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'doctor_documents' . DIRECTORY_SEPARATOR;
	}

	/**
	 * Store one uploaded file per named field.
	 *
	 * All or nothing: if any file is missing or rejected, anything already
	 * written is removed and FALSE is returned, so an application can never
	 * end up with one document.
	 *
	 * @param  array $fields Form field names, e.g. array('diploma', 'graduation')
	 * @return array|false   field => stored filename, or FALSE
	 */
	public function store(array $fields)
	{
		$this->error = NULL;

		if ( ! is_dir($this->path) && ! mkdir($this->path, 0755, TRUE))
		{
			$this->error = 'Could not open document storage. Please try again.';

			return FALSE;
		}

		$stored = array();

		foreach ($fields as $field)
		{
			$name = $this->store_one($field);

			if ($name === FALSE)
			{
				// Roll back whatever this call already wrote.
				foreach ($stored as $written)
				{
					$this->delete($written);
				}

				return FALSE;
			}

			$stored[$field] = $name;
		}

		return $stored;
	}

	/**
	 * @return string|false Stored filename, or FALSE with $this->error set
	 */
	protected function store_one($field)
	{
		if (empty($_FILES[$field]['name']))
		{
			$this->error = 'Please attach both your diploma and your graduation certificate.';

			return FALSE;
		}

		// A fresh instance per file: CI's upload library keeps per-run state.
		$this->CI->upload->initialize(array(
			'upload_path'   => $this->path,
			'allowed_types' => self::ALLOWED_TYPES,
			'max_size'      => self::MAX_SIZE_KB,
			'encrypt_name'  => TRUE,
		));

		if ( ! $this->CI->upload->do_upload($field))
		{
			$this->error = strip_tags($this->CI->upload->display_errors('', ''));

			return FALSE;
		}

		return $this->CI->upload->data('file_name');
	}

	/**
	 * Absolute path of a stored document.
	 *
	 * basename() keeps a crafted value from reaching outside the folder.
	 *
	 * @return string|null NULL when the file is not there
	 */
	public function path($filename)
	{
		if (empty($filename))
		{
			return NULL;
		}

		$full = $this->path . basename($filename);

		return is_file($full) ? $full : NULL;
	}

	public function delete($filename)
	{
		$full = $this->path($filename);

		if ($full !== NULL)
		{
			@unlink($full);
		}
	}

	/** @return string|null */
	public function last_error()
	{
		return $this->error;
	}
}
