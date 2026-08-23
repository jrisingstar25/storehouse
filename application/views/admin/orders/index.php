<div class="d-flex justify-content-between align-items-center mb-3">
	<div>
		<h1 class="h3 mb-0">Orders</h1>
		<p class="text-muted small mb-0"><?= (int) $total ?> order<?= $total === 1 ? '' : 's' ?></p>
	</div>
</div>

<div class="card shadow-sm mb-3">
	<div class="card-body">
		<form method="get" class="row g-2 align-items-end">
			<div class="col-md-5">
				<label class="form-label small" for="q">Search</label>
				<input type="search" class="form-control form-control-sm" id="q" name="q"
					value="<?= e($filters['search']) ?>" placeholder="Order number or customer name">
			</div>

			<div class="col-md-3">
				<label class="form-label small" for="status">Status</label>
				<select class="form-select form-select-sm" id="status" name="status">
					<option value="">Any status</option>
					<?php foreach (order_statuses() as $status): ?>
						<option value="<?= e($status) ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>>
							<?= ucfirst($status) ?>
						</option>
					<?php endforeach ?>
				</select>
			</div>

			<div class="col-md-2 d-grid">
				<button class="btn btn-sm btn-outline-dark" type="submit">Filter</button>
			</div>

			<div class="col-md-2 d-grid">
				<a class="btn btn-sm btn-link" href="<?= site_url('admin/orders') ?>">Reset</a>
			</div>
		</form>
	</div>
</div>

<?php if (empty($orders)): ?>
	<div class="alert alert-light border text-center py-5">
		<p class="fw-semibold mb-0">No orders match.</p>
	</div>
<?php else: ?>
	<div class="card shadow-sm">
		<div class="table-responsive">
			<table class="table align-middle mb-0">
				<thead class="table-light">
					<tr>
						<th scope="col">Order</th>
						<th scope="col">Customer</th>
						<th scope="col">Placed</th>
						<th scope="col">Payment</th>
						<th scope="col">Status</th>
						<th scope="col" class="text-end">Total</th>
						<th scope="col" class="text-end"></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($orders as $order): ?>
						<tr>
							<td>
								<a class="text-decoration-none fw-semibold"
									href="<?= site_url('admin/orders/view/' . (int) $order['id']) ?>"><?= e($order['order_number']) ?></a>
							</td>
							<td class="small">
								<?= e($order['customer_name']) ?>
								<?php if ($order['customer_phone']): ?>
									<div class="text-muted"><?= e($order['customer_phone']) ?></div>
								<?php endif ?>
							</td>
							<td class="small"><?= date('j M Y, H:i', strtotime($order['created_at'])) ?></td>
							<td class="small"><?= $order['payment_method'] === 'cod' ? 'Cash on delivery' : 'Bank transfer' ?></td>
							<td><span class="badge bg-<?= order_status_class($order['status']) ?>"><?= e($order['status']) ?></span></td>
							<td class="text-end fw-semibold"><?= money($order['total']) ?></td>
							<td class="text-end">
								<a class="btn btn-sm btn-outline-dark"
									href="<?= site_url('admin/orders/view/' . (int) $order['id']) ?>">Open</a>
							</td>
						</tr>
					<?php endforeach ?>
				</tbody>
			</table>
		</div>
	</div>

	<?php if ($pagination): ?>
		<nav class="mt-3" aria-label="Order pages"><?= $pagination ?></nav>
	<?php endif ?>
<?php endif ?>
