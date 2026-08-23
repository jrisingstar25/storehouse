<div class="d-flex justify-content-between align-items-center mb-3">
	<h1 class="h3 mb-0">My account</h1>
	<a class="btn btn-sm btn-outline-secondary" href="<?= site_url('account/orders') ?>">My orders</a>
</div>

<div class="row">
	<div class="col-lg-8">
		<?= form_open('account') ?>
			<div class="card shadow-sm mb-4">
				<div class="card-header bg-white fw-semibold">Your details</div>
				<div class="card-body">
					<div class="row">
						<div class="col-sm-6 mb-3">
							<label class="form-label" for="name">Name</label>
							<input type="text" class="form-control" id="name" name="name"
								value="<?= set_value('name', $user['name']) ?>" required>
						</div>

						<div class="col-sm-6 mb-3">
							<label class="form-label" for="username">Username</label>
							<input type="text" class="form-control" id="username" name="username"
								value="<?= set_value('username', $user['username']) ?>" required>
						</div>
					</div>

					<div class="mb-3">
						<label class="form-label" for="phone">Phone</label>
						<input type="text" class="form-control" id="phone" name="phone"
							value="<?= set_value('phone', $user['phone']) ?>">
					</div>

					<div class="mb-0">
						<label class="form-label" for="address">Default shipping address</label>
						<textarea class="form-control" id="address" name="address" rows="3"><?= set_value('address', $user['address']) ?></textarea>
					</div>
				</div>
			</div>

			<div class="card shadow-sm mb-4">
				<div class="card-header bg-white fw-semibold">Change password</div>
				<div class="card-body">
					<p class="text-muted small">Leave these blank to keep your current password.</p>

					<div class="mb-3">
						<label class="form-label" for="current_password">Current password</label>
						<input type="password" class="form-control" id="current_password" name="current_password" autocomplete="current-password">
					</div>

					<div class="row">
						<div class="col-sm-6 mb-3">
							<label class="form-label" for="new_password">New password</label>
							<input type="password" class="form-control" id="new_password" name="new_password" autocomplete="new-password">
						</div>

						<div class="col-sm-6 mb-0">
							<label class="form-label" for="new_password_confirm">Confirm new password</label>
							<input type="password" class="form-control" id="new_password_confirm" name="new_password_confirm" autocomplete="new-password">
						</div>
					</div>
				</div>
			</div>

			<button class="btn btn-dark" type="submit">Save changes</button>
		<?= form_close() ?>
	</div>

	<div class="col-lg-4">
		<div class="card shadow-sm mb-4">
			<div class="card-body small">
				<div class="text-muted">Account type</div>
				<div class="mb-2 text-capitalize"><?= e($user['role']) ?></div>

				<div class="text-muted">Member since</div>
				<div><?= date('j M Y', strtotime($user['created_at'])) ?></div>
			</div>
		</div>

		<?php
		// Admins manage their own role under Admin -> Users; offering it here
		// would let one demote themselves past the last-admin guard.
		$pending  = $application && $application['status'] === 'pending';
		$rejected = $application && $application['status'] === 'rejected';
		?>
		<?php if ($user['role'] !== 'admin'): ?>
			<div class="card shadow-sm">
				<div class="card-header bg-white fw-semibold">Account type</div>
				<div class="card-body small">

					<?php if ($user['role'] === 'doctor'): ?>
						<span class="badge bg-info">doctor</span>
						<p class="text-muted mt-2">
							Your documents were approved
							<?= $application && $application['reviewed_at']
								? 'on ' . date('j M Y', strtotime($application['reviewed_at'])) : '' ?>.
						</p>

						<?= form_open('account/leave_doctor', array(
							'onsubmit' => "return confirm('Go back to a customer account? You would need to apply again, with your documents, to be a doctor.')",
						)) ?>
							<button class="btn btn-sm btn-outline-secondary" type="submit">
								Switch back to a customer account
							</button>
						<?= form_close() ?>

					<?php elseif ($pending): ?>
						<span class="badge bg-warning text-dark">doctor application under review</span>
						<p class="text-muted mt-2">
							An admin is checking your documents. Your account works as a
							customer in the meantime, so you can shop as normal.
						</p>

						<?= form_open('account/withdraw_application', array(
							'onsubmit' => "return confirm('Withdraw the application? Your uploaded documents are deleted.')",
						)) ?>
							<button class="btn btn-sm btn-outline-danger" type="submit">Withdraw application</button>
						<?= form_close() ?>

					<?php else: ?>
						<span class="badge bg-light text-dark">customer</span>

						<?php if ($rejected): ?>
							<p class="text-muted mt-2 mb-1">
								Your last application was not approved. You can submit new documents below.
							</p>
							<?php if ($application['review_note']): ?>
								<div class="text-muted">Note from the reviewer</div>
								<div class="mb-2"><?= nl2br(e($application['review_note'])) ?></div>
							<?php endif ?>
						<?php else: ?>
							<p class="text-muted mt-2">
								Are you a doctor? Attach your diploma and graduation certificate and
								an admin will review them. You carry on shopping as a customer while
								they do.
							</p>
						<?php endif ?>

						<?= form_open_multipart('account/apply_doctor') ?>
							<?php
							$documents = array(
								'diploma'    => 'Diploma',
								'graduation' => 'Graduation certificate',
							);
							$accept = array();

							foreach ($doc_types as $ext)
							{
								$accept[] = '.' . $ext;
							}
							?>
							<?php foreach ($documents as $field => $label): ?>
								<div class="mb-3">
									<label class="form-label" for="acc_<?= $field ?>"><?= $label ?></label>
									<input type="file" class="form-control form-control-sm" id="acc_<?= $field ?>"
										name="<?= $field ?>"
										accept="<?= e(implode(',', $accept)) ?>,image/*"
										data-preview="#acc-<?= $field ?>-preview"
										data-max-kb="<?= (int) $doc_max_kb ?>" required>

									<?php // Filled in by document-upload.js once a file is chosen. ?>
									<div class="document-preview mt-2" id="acc-<?= $field ?>-preview" hidden>
										<div class="document-preview-frame position-relative">
											<img alt="Preview of the <?= strtolower($label) ?> you selected"
												class="document-preview-img rounded border" data-preview-image hidden>
											<button type="button" class="btn-close document-preview-clear"
												data-preview-clear="#acc_<?= $field ?>"
												aria-label="Remove the selected <?= strtolower($label) ?>"
												title="Remove"></button>
										</div>
										<div class="small mt-1" data-preview-meta></div>
									</div>
								</div>
							<?php endforeach ?>

							<div class="form-text mb-2">
								<?= e(strtoupper(implode(', ', $doc_types))) ?>, up to
								<?= (int) round($doc_max_kb / 1024) ?>&nbsp;MB each. Only admins can view these.
							</div>

							<button class="btn btn-sm btn-dark" type="submit">
								<?= $rejected ? 'Apply again' : 'Apply to be a doctor' ?>
							</button>
						<?= form_close() ?>
					<?php endif ?>
				</div>
			</div>
		<?php endif ?>
	</div>

</div>
