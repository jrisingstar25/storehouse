<div class="d-flex justify-content-between align-items-center mb-3">
	<h1 class="h3 mb-0">My orders</h1>
	<a class="btn btn-sm btn-outline-secondary" href="<?= site_url('account') ?>">Account details</a>
</div>

<?php if (empty($orders)): ?>
	<div class="alert alert-light border text-center py-5">
		<p class="fw-semibold mb-1">No orders yet.</p>
		<a class="btn btn-dark mt-2" href="<?= site_url('shop') ?>">Start shopping</a>
	</div>
<?php else: ?>
	<div class="table-responsive">
		<table class="table align-middle">
			<thead class="table-light">
				<tr>
					<th scope="col">Order</th>
					<th scope="col">Placed</th>
					<th scope="col">Status</th>
					<th scope="col" class="text-end">Total</th>
					<th scope="col"></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($orders as $order): ?>
					<tr>
						<td class="fw-semibold"><?= e($order['order_number']) ?></td>
						<td><?= date('j M Y, H:i', strtotime($order['created_at'])) ?></td>
						<td><span class="badge bg-<?= order_status_class($order['status']) ?>"><?= e($order['status']) ?></span></td>
						<td class="text-end"><?= money($order['total']) ?></td>
						<td class="text-end">
							<a class="btn btn-sm btn-outline-dark"
								href="<?= site_url('account/orders/' . (int) $order['id']) ?>">View</a>
						</td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</div>
<?php endif ?>
