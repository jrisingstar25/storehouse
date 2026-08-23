<?php $editing = $user !== NULL ?>

<nav aria-label="breadcrumb">
	<ol class="breadcrumb small">
		<li class="breadcrumb-item"><a href="<?= site_url('admin/users') ?>">Users</a></li>
		<li class="breadcrumb-item active" aria-current="page"><?= $editing ? 'Edit' : 'New' ?></li>
	</ol>
</nav>

<h1 class="h3 mb-3"><?= $editing ? 'Edit user' : 'New user' ?></h1>

<div class="row">
	<div class="col-lg-7">
		<div class="card shadow-sm">
			<div class="card-body">
				<?= form_open($editing ? 'admin/users/edit/' . (int) $user['id'] : 'admin/users/create') ?>
					<div class="mb-3">
						<label class="form-label" for="name">Name</label>
						<input type="text" class="form-control" id="name" name="name"
							value="<?= set_value('name', $editing ? $user['name'] : '') ?>" required autofocus>
					</div>

					<div class="mb-3">
						<label class="form-label" for="email">Email</label>
						<input type="email" class="form-control" id="email" name="email"
							value="<?= set_value('email', $editing ? $user['email'] : '') ?>" required>
					</div>

					<div class="mb-3">
						<label class="form-label" for="password">Password</label>
						<input type="password" class="form-control" id="password" name="password"
							autocomplete="new-password" <?= $editing ? '' : 'required' ?>>
						<div class="form-text">
							<?= $editing ? 'Leave blank to keep the current password.' : 'At least 8 characters.' ?>
						</div>
					</div>

					<div class="row">
						<div class="col-sm-6 mb-3">
							<label class="form-label" for="role">Role</label>
							<?php $role = set_value('role', $editing ? $user['role'] : 'customer') ?>
							<select class="form-select" id="role" name="role">
								<option value="customer" <?= $role === 'customer' ? 'selected' : '' ?>>Customer</option>
								<option value="admin"    <?= $role === 'admin'    ? 'selected' : '' ?>>Admin</option>
							</select>
						</div>

						<div class="col-sm-6 mb-3">
							<label class="form-label d-block">Account status</label>
							<?php
							// See the note on the product form: an unchecked box is
							// absent from the POST, so read the request directly.
							$active = $this->input->method() === 'post'
								? (int) (bool) $this->input->post('is_active')
								: ($editing ? (int) $user['is_active'] : 1);
							?>
							<div class="form-check form-switch mt-2">
								<input class="form-check-input" type="checkbox" role="switch" id="is_active"
									name="is_active" value="1" <?= $active === 1 ? 'checked' : '' ?>>
								<label class="form-check-label" for="is_active">Active (can sign in)</label>
							</div>
						</div>
					</div>

					<div class="mb-3">
						<label class="form-label" for="phone">Phone</label>
						<input type="text" class="form-control" id="phone" name="phone"
							value="<?= set_value('phone', $editing ? $user['phone'] : '') ?>">
					</div>

					<div class="mb-3">
						<label class="form-label" for="address">Address</label>
						<textarea class="form-control" id="address" name="address" rows="3"><?= set_value('address', $editing ? $user['address'] : '') ?></textarea>
					</div>

					<button class="btn btn-dark" type="submit"><?= $editing ? 'Save changes' : 'Create user' ?></button>
					<a class="btn btn-outline-secondary" href="<?= site_url('admin/users') ?>">Cancel</a>
				<?= form_close() ?>
			</div>
		</div>
	</div>
</div>
