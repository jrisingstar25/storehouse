<div class="row justify-content-center">
	<div class="col-md-6 col-lg-5">
		<h1 class="h3 mb-3">Sign in</h1>

		<div class="card shadow-sm">
			<div class="card-body">
				<?= form_open('login') ?>
					<div class="mb-3">
						<label class="form-label" for="email">Email</label>
						<input type="email" class="form-control" id="email" name="email"
							value="<?= set_value('email') ?>" required autofocus>
					</div>

					<div class="mb-3">
						<label class="form-label" for="password">Password</label>
						<input type="password" class="form-control" id="password" name="password" required>
					</div>

					<button class="btn btn-dark w-100" type="submit">Sign in</button>
				<?= form_close() ?>
			</div>
		</div>

		<p class="text-center text-muted mt-3 mb-0">
			No account yet? <a href="<?= site_url('register') ?>">Create one</a>.
		</p>
	</div>
</div>
