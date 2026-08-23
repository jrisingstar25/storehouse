<?php
/**
 * Renders the one-shot flash message set by MY_Controller::flash_redirect(),
 * plus any $error a controller left on the data bag for a failed submission.
 */
$flash = $this->session->flashdata('flash');
?>
<?php if ($flash): ?>
	<div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
		<?= $flash['message'] ?>
		<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
	</div>
<?php endif ?>

<?php if ( ! empty($error)): ?>
	<div class="alert alert-danger" role="alert"><?= e($error) ?></div>
<?php endif ?>

<?php if (validation_errors()): ?>
	<div class="alert alert-danger">
		<strong>Please fix the following:</strong>
		<?= validation_errors('<div>&bull; ', '</div>') ?>
	</div>
<?php endif ?>
