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
		<div class="card shadow-sm">
			<div class="card-body small">
				<div class="text-muted">Account type</div>
				<div class="mb-2 text-capitalize"><?= e($user['role']) ?></div>

				<div class="text-muted">Member since</div>
				<div><?= date('j M Y', strtotime($user['created_at'])) ?></div>
			</div>
		</div>
	</div>
</div>
