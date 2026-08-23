<?php
// Keep the chosen account type across a rejected submission.
$account_type = set_value('account_type', 'customer');
?>
<div class="row justify-content-center">
	<div class="col-md-8 col-lg-7">
		<h1 class="h3 mb-3">Create an account</h1>

		<?= form_open_multipart('register') ?>
			<div class="card shadow-sm mb-4">
				<div class="card-header bg-white fw-semibold">Account type</div>
				<div class="card-body">
					<div class="form-check mb-2">
						<input class="form-check-input" type="radio" name="account_type" id="type_customer"
							value="customer" <?= $account_type === 'customer' ? 'checked' : '' ?>
							data-account-type>
						<label class="form-check-label" for="type_customer">
							Customer
							<span class="d-block small text-muted">Shop straight away. Nothing else needed.</span>
						</label>
					</div>

					<div class="form-check">
						<input class="form-check-input" type="radio" name="account_type" id="type_doctor"
							value="doctor" <?= $account_type === 'doctor' ? 'checked' : '' ?>
							data-account-type>
						<label class="form-check-label" for="type_doctor">
							Doctor
							<span class="d-block small text-muted">
								Attach your diploma and graduation certificate. An admin reviews them;
								until then your account works as a normal customer.
							</span>
						</label>
					</div>
				</div>
			</div>

			<?php
			// Rendered visible: with JavaScript off the inputs must still be
			// reachable, and the server only requires them when account_type
			// is "doctor". app.js collapses this when Customer is selected.
			?>
			<div class="card shadow-sm mb-4" id="doctor-documents">
				<div class="card-header bg-white fw-semibold">Supporting documents</div>
				<div class="card-body">
					<div class="mb-3">
						<label class="form-label" for="diploma">Diploma</label>
						<input type="file" class="form-control" id="diploma" name="diploma" accept="image/*">
					</div>

					<div class="mb-0">
						<label class="form-label" for="graduation">Graduation certificate</label>
						<input type="file" class="form-control" id="graduation" name="graduation" accept="image/*">
						<div class="form-text">
							JPG, PNG or WebP, up to 4&nbsp;MB each. Only admins can view these.
						</div>
					</div>
				</div>
			</div>

			<div class="card shadow-sm mb-4">
				<div class="card-header bg-white fw-semibold">Your details</div>
				<div class="card-body">
					<div class="mb-3">
						<label class="form-label" for="name">Name</label>
						<input type="text" class="form-control" id="name" name="name"
							value="<?= set_value('name') ?>" autocomplete="name" required autofocus>
					</div>

					<div class="mb-3">
						<label class="form-label" for="username">Username</label>
						<input type="text" class="form-control" id="username" name="username"
							value="<?= set_value('username') ?>" autocomplete="username" required>
						<div class="form-text">
							3&ndash;60 characters. Letters, numbers, underscores and dashes. This is what you sign in with.
						</div>
					</div>

					<div class="row">
						<div class="col-sm-6 mb-3">
							<label class="form-label" for="password">Password</label>
							<input type="password" class="form-control" id="password" name="password"
								autocomplete="new-password" required>
							<div class="form-text">At least 8 characters.</div>
						</div>

						<div class="col-sm-6 mb-3">
							<label class="form-label" for="password_confirm">Confirm password</label>
							<input type="password" class="form-control" id="password_confirm" name="password_confirm"
								autocomplete="new-password" required>
						</div>
					</div>

					<div class="mb-3">
						<label class="form-label" for="phone">Phone <span class="text-muted">(optional)</span></label>
						<input type="text" class="form-control" id="phone" name="phone" value="<?= set_value('phone') ?>">
					</div>

					<div class="mb-0">
						<label class="form-label" for="address">Shipping address <span class="text-muted">(optional)</span></label>
						<textarea class="form-control" id="address" name="address" rows="3"><?= set_value('address') ?></textarea>
					</div>
				</div>
			</div>

			<button class="btn btn-dark w-100" type="submit">Create account</button>
		<?= form_close() ?>

		<p class="text-center text-muted mt-3 mb-0">
			Already have an account? <a href="<?= site_url('login') ?>">Sign in</a>.
		</p>
	</div>
</div>
