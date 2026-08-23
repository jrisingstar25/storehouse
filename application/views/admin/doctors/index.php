<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
	<div>
		<h1 class="h3 mb-0">Doctor applications</h1>
		<p class="text-muted small mb-0">
			<?= (int) $pending ?> awaiting review
		</p>
	</div>
</div>

<div class="card shadow-sm mb-3">
	<div class="card-body">
		<form method="get" class="row g-2 align-items-end">
			<div class="col-md-5">
				<label class="form-label small" for="q">Search</label>
				<input type="search" class="form-control form-control-sm" id="q" name="q"
					value="<?= e($filters['search']) ?>" placeholder="Applicant name or username">
			</div>

			<div class="col-md-3">
				<label class="form-label small" for="status">Status</label>
				<select class="form-select form-select-sm" id="status" name="status">
					<option value="">Any status</option>
					<?php foreach (array('pending', 'approved', 'rejected') as $status): ?>
						<option value="<?= $status ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>>
							<?= ucfirst($status) ?>
						</option>
					<?php endforeach ?>
				</select>
			</div>

			<div class="col-md-2 d-grid">
				<button class="btn btn-sm btn-outline-dark" type="submit">Filter</button>
			</div>

			<div class="col-md-2 d-grid">
				<a class="btn btn-sm btn-link" href="<?= site_url('admin/doctors') ?>">Reset</a>
			</div>
		</form>
	</div>
</div>

<?php if (empty($applications)): ?>
	<div class="alert alert-light border text-center py-5">
		<p class="fw-semibold mb-0">No applications match.</p>
	</div>
<?php else: ?>
	<div class="card shadow-sm">
		<div class="table-responsive">
			<table class="table align-middle mb-0">
				<thead class="table-light">
					<tr>
						<th scope="col">Applicant</th>
						<th scope="col">Applied</th>
						<th scope="col">Status</th>
						<th scope="col">Account role</th>
						<th scope="col">Reviewed by</th>
						<th scope="col" class="text-end"></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$badge = array('pending' => 'warning text-dark', 'approved' => 'success', 'rejected' => 'secondary');
					?>
					<?php foreach ($applications as $app): ?>
						<tr>
							<td>
								<a class="text-decoration-none link-dark fw-semibold"
									href="<?= site_url('admin/doctors/view/' . (int) $app['id']) ?>"><?= e($app['user_name']) ?></a>
								<div class="small text-muted"><code><?= e($app['username']) ?></code></div>
							</td>
							<td class="small"><?= date('j M Y, H:i', strtotime($app['created_at'])) ?></td>
							<td><span class="badge bg-<?= $badge[$app['status']] ?>"><?= e($app['status']) ?></span></td>
							<td>
								<span class="badge bg-<?= $app['user_role'] === 'doctor' ? 'info' : 'light text-dark' ?>">
									<?= e($app['user_role']) ?>
								</span>
							</td>
							<td class="small text-muted">
								<?= $app['reviewer_name'] ? e($app['reviewer_name']) : '&mdash;' ?>
								<?php if ($app['reviewed_at']): ?>
									<div><?= date('j M Y', strtotime($app['reviewed_at'])) ?></div>
								<?php endif ?>
							</td>
							<td class="text-end">
								<a class="btn btn-sm btn-<?= $app['status'] === 'pending' ? 'dark' : 'outline-dark' ?>"
									href="<?= site_url('admin/doctors/view/' . (int) $app['id']) ?>">
									<?= $app['status'] === 'pending' ? 'Review' : 'Open' ?>
								</a>
							</td>
						</tr>
					<?php endforeach ?>
				</tbody>
			</table>
		</div>
	</div>
<?php endif ?>
