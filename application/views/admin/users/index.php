<div class="d-flex justify-content-between align-items-center mb-3">
	<h1 class="h3 mb-0">Users</h1>
	<a class="btn btn-dark" href="<?= site_url('admin/users/create') ?>">New user</a>
</div>

<div class="card shadow-sm mb-3">
	<div class="card-body">
		<form method="get" class="row g-2 align-items-end">
			<div class="col-md-5">
				<label class="form-label small" for="q">Search</label>
				<input type="search" class="form-control form-control-sm" id="q" name="q"
					value="<?= e($filters['search']) ?>" placeholder="Name or username">
			</div>

			<div class="col-md-3">
				<label class="form-label small" for="role">Role</label>
				<select class="form-select form-select-sm" id="role" name="role">
					<option value="">Any role</option>
					<option value="customer" <?= $filters['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
					<option value="doctor"   <?= $filters['role'] === 'doctor'   ? 'selected' : '' ?>>Doctor</option>
					<option value="admin"    <?= $filters['role'] === 'admin'    ? 'selected' : '' ?>>Admin</option>
				</select>
			</div>

			<div class="col-md-2 d-grid">
				<button class="btn btn-sm btn-outline-dark" type="submit">Filter</button>
			</div>

			<div class="col-md-2 d-grid">
				<a class="btn btn-sm btn-link" href="<?= site_url('admin/users') ?>">Reset</a>
			</div>
		</form>
	</div>
</div>

<?php if (empty($users)): ?>
	<div class="alert alert-light border text-center py-5">
		<p class="fw-semibold mb-0">No users match.</p>
	</div>
<?php else: ?>
	<div class="card shadow-sm">
		<div class="table-responsive">
			<table class="table align-middle mb-0">
				<thead class="table-light">
					<tr>
						<th scope="col">Name</th>
						<th scope="col">Username</th>
						<th scope="col">Role</th>
						<th scope="col">Status</th>
						<th scope="col">Joined</th>
						<th scope="col" class="text-end">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($users as $user): ?>
						<tr>
							<td class="fw-semibold">
								<a class="text-decoration-none link-dark"
									href="<?= site_url('admin/users/view/' . (int) $user['id']) ?>"><?= e($user['name']) ?></a>
								<?php if ((int) $user['id'] === $current_user['id']): ?>
									<span class="badge bg-light text-muted">you</span>
								<?php endif ?>
							</td>
							<td class="small"><code><?= e($user['username']) ?></code></td>
							<td>
								<?php $role_class = array('admin' => 'dark', 'doctor' => 'info'); ?>
								<span class="badge bg-<?= isset($role_class[$user['role']]) ? $role_class[$user['role']] : 'light text-dark' ?>">
									<?= e($user['role']) ?>
								</span>
							</td>
							<td>
								<?php if ((int) $user['is_active'] === 1): ?>
									<span class="badge bg-success">active</span>
								<?php else: ?>
									<span class="badge bg-secondary">disabled</span>
								<?php endif ?>
							</td>
							<td class="small"><?= date('j M Y', strtotime($user['created_at'])) ?></td>
							<td class="text-end text-nowrap">
								<a class="btn btn-sm btn-outline-dark"
									href="<?= site_url('admin/users/edit/' . (int) $user['id']) ?>">Edit</a>
								<?php if ((int) $user['id'] !== $current_user['id']): ?>
									<?= form_open('admin/users/delete/' . (int) $user['id'], array(
										'class'    => 'd-inline',
										'onsubmit' => "return confirm('Delete this user? Their past orders are kept.')",
									)) ?>
										<button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
									<?= form_close() ?>
								<?php endif ?>
							</td>
						</tr>
					<?php endforeach ?>
				</tbody>
			</table>
		</div>
	</div>
<?php endif ?>
