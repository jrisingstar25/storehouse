<div class="row justify-content-center">
	<div class="col-md-8 col-lg-6">
		<h1 class="h3 mb-3">Create an account</h1>

		<div class="card shadow-sm">
			<div class="card-body">
				<?= form_open('register') ?>
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

					<div class="mb-3">
						<label class="form-label" for="address">Shipping address <span class="text-muted">(optional)</span></label>
						<textarea class="form-control" id="address" name="address" rows="3"><?= set_value('address') ?></textarea>
					</div>

					<button class="btn btn-dark w-100" type="submit">Create account</button>
				<?= form_close() ?>
			</div>
		</div>

		<p class="text-center text-muted mt-3 mb-0">
			Already have an account? <a href="<?= site_url('login') ?>">Sign in</a>.
		</p>
	</div>
</div>
