<?php
$badge   = array('pending' => 'warning text-dark', 'approved' => 'success', 'rejected' => 'secondary');
$pending = $application['status'] === 'pending';
?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb small">
		<li class="breadcrumb-item"><a href="<?= site_url('admin/doctors') ?>">Doctor applications</a></li>
		<li class="breadcrumb-item active" aria-current="page"><?= e($application['user_name']) ?></li>
	</ol>
</nav>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
	<div>
		<h1 class="h4 mb-0"><?= e($application['user_name']) ?></h1>
		<p class="text-muted small mb-0">
			Applied <?= date('j M Y, H:i', strtotime($application['created_at'])) ?>
		</p>
	</div>
	<span class="badge bg-<?= $badge[$application['status']] ?> fs-6"><?= e($application['status']) ?></span>
</div>

<div class="row g-4">
	<div class="col-lg-8">
		<div class="card shadow-sm mb-4">
			<div class="card-header bg-white fw-semibold">Supporting documents</div>
			<div class="card-body">
				<p class="small text-muted">
					These are personal records. They are not reachable by URL &mdash; each one is
					streamed here only for signed-in admins.
				</p>

				<div class="row g-3">
					<?php foreach (array('diploma' => 'Diploma', 'graduation' => 'Graduation certificate') as $type => $label): ?>
						<div class="col-sm-6">
							<div class="border rounded p-2 h-100">
								<div class="small fw-semibold mb-2"><?= $label ?></div>
								<?php $src = site_url('admin/doctors/document/' . (int) $application['id'] . '/' . $type) ?>
								<?php
								// target=_blank is the fallback: if Fancybox does not load,
								// the link still opens the document in a new tab.
								?>
								<a href="<?= $src ?>" data-fancybox="documents"
									data-caption="<?= e($label) ?> &mdash; <?= e($application['user_name']) ?>"
									target="_blank" rel="noopener">
									<img src="<?= $src ?>" alt="<?= e($label) ?>" class="img-fluid rounded document-thumb">
								</a>
								<div class="small text-muted mt-2">Click to zoom.</div>
							</div>
						</div>
					<?php endforeach ?>
				</div>
			</div>
		</div>

		<?php if ($pending): ?>
			<div class="card shadow-sm">
				<div class="card-header bg-white fw-semibold">Decision</div>
				<div class="card-body">
					<?= form_open('admin/doctors/approve/' . (int) $application['id'], array('id' => 'decision-form')) ?>
						<div class="mb-3">
							<label class="form-label" for="review_note">Note <span class="text-muted">(optional)</span></label>
							<textarea class="form-control" id="review_note" name="review_note" rows="2"
								placeholder="Recorded against the decision."></textarea>
						</div>

						<button class="btn btn-success" type="submit">Approve &mdash; make them a doctor</button>
						<button class="btn btn-outline-danger" type="submit"
							formaction="<?= site_url('admin/doctors/reject/' . (int) $application['id']) ?>"
							onclick="return confirm('Reject this application? Their account keeps working as a customer.')">
							Reject
						</button>
					<?= form_close() ?>

					<p class="form-text mb-0 mt-2">
						Approving sets their role to <strong>doctor</strong>. Rejecting changes nothing about
						the account &mdash; they carry on as a customer either way.
					</p>
				</div>
			</div>
		<?php else: ?>
			<div class="card shadow-sm">
				<div class="card-header bg-white fw-semibold">Decision</div>
				<div class="card-body small">
					<div class="text-muted">Outcome</div>
					<div class="mb-2">
						<span class="badge bg-<?= $badge[$application['status']] ?>"><?= e($application['status']) ?></span>
						<?php if ($application['reviewer_name']): ?>
							by <?= e($application['reviewer_name']) ?>
						<?php endif ?>
						<?php if ($application['reviewed_at']): ?>
							on <?= date('j M Y, H:i', strtotime($application['reviewed_at'])) ?>
						<?php endif ?>
					</div>

					<?php if ($application['review_note']): ?>
						<div class="text-muted">Note</div>
						<div><?= nl2br(e($application['review_note'])) ?></div>
					<?php endif ?>
				</div>
			</div>
		<?php endif ?>
	</div>

	<div class="col-lg-4">
		<div class="card shadow-sm">
			<div class="card-header bg-white fw-semibold">Applicant</div>
			<div class="card-body small">
				<div class="text-muted">Name</div>
				<div class="mb-2"><?= e($application['user_name']) ?></div>

				<div class="text-muted">Username</div>
				<div class="mb-2"><code><?= e($application['username']) ?></code></div>

				<div class="text-muted">Current role</div>
				<div class="mb-2">
					<span class="badge bg-<?= $application['user_role'] === 'doctor' ? 'info' : 'light text-dark' ?>">
						<?= e($application['user_role']) ?>
					</span>
				</div>

				<div class="text-muted">Account status</div>
				<div class="mb-2">
					<?= (int) $application['user_is_active'] === 1
						? '<span class="badge bg-success">active</span>'
						: '<span class="badge bg-secondary">disabled</span>' ?>
				</div>

				<?php if ($application['user_phone']): ?>
					<div class="text-muted">Phone</div>
					<div class="mb-2"><?= e($application['user_phone']) ?></div>
				<?php endif ?>

				<a class="btn btn-sm btn-outline-secondary mt-2"
					href="<?= site_url('admin/users/view/' . (int) $application['user_id']) ?>">Open account</a>
			</div>
		</div>
	</div>
</div>
