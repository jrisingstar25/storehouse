<nav aria-label="breadcrumb">
	<ol class="breadcrumb small">
		<li class="breadcrumb-item"><a href="<?= site_url('admin/users') ?>">Users</a></li>
		<li class="breadcrumb-item active" aria-current="page"><?= e($user['name']) ?></li>
	</ol>
</nav>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
	<h1 class="h4 mb-0"><?= e($user['name']) ?></h1>
	<a class="btn btn-sm btn-outline-dark" href="<?= site_url('admin/users/edit/' . (int) $user['id']) ?>">Edit user</a>
</div>

<div class="row g-4">
	<div class="col-lg-4">
		<div class="card shadow-sm">
			<div class="card-body small">
				<div class="text-muted">Username</div>
				<div class="mb-2"><code><?= e($user['username']) ?></code></div>

				<div class="text-muted">Role</div>
				<div class="mb-2 text-capitalize"><?= e($user['role']) ?></div>

				<div class="text-muted">Status</div>
				<div class="mb-2">
					<?= (int) $user['is_active'] === 1
						? '<span class="badge bg-success">active</span>'
						: '<span class="badge bg-secondary">disabled</span>' ?>
				</div>

				<?php if ($user['phone']): ?>
					<div class="text-muted">Phone</div>
					<div class="mb-2"><?= e($user['phone']) ?></div>
				<?php endif ?>

				<?php if ($user['address']): ?>
					<div class="text-muted">Address</div>
					<div class="mb-2"><?= nl2br(e($user['address'])) ?></div>
				<?php endif ?>

				<div class="text-muted">Joined</div>
				<div><?= date('j M Y, H:i', strtotime($user['created_at'])) ?></div>
			</div>
		</div>
	</div>

	<div class="col-lg-8">
		<div class="card shadow-sm">
			<div class="card-header bg-white fw-semibold">Order history</div>

			<?php if (empty($orders)): ?>
				<div class="card-body text-muted small">This user has not placed any orders.</div>
			<?php else: ?>
				<div class="table-responsive">
					<table class="table mb-0 align-middle">
						<thead class="table-light">
							<tr>
								<th scope="col">Order</th>
								<th scope="col">Placed</th>
								<th scope="col">Status</th>
								<th scope="col" class="text-end">Total</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($orders as $order): ?>
								<tr>
									<td>
										<a class="text-decoration-none fw-semibold"
											href="<?= site_url('admin/orders/view/' . (int) $order['id']) ?>"><?= e($order['order_number']) ?></a>
									</td>
									<td class="small"><?= date('j M Y, H:i', strtotime($order['created_at'])) ?></td>
									<td><span class="badge bg-<?= order_status_class($order['status']) ?>"><?= e($order['status']) ?></span></td>
									<td class="text-end"><?= money($order['total']) ?></td>
								</tr>
							<?php endforeach ?>
						</tbody>
					</table>
				</div>
			<?php endif ?>
		</div>
	</div>
</div>
