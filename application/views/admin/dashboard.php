<h1 class="h3 mb-4">Dashboard</h1>

<?php
$tiles = array(
	array('label' => 'Revenue',    'value' => money($stats['revenue']),   'note' => 'excluding cancelled', 'url' => 'admin/orders'),
	array('label' => 'Orders',     'value' => (int) $stats['orders'],     'note' => $stats['pending'] . ' pending', 'url' => 'admin/orders'),
	array('label' => 'Products',   'value' => (int) $stats['products'],   'note' => $stats['categories'] . ' categories', 'url' => 'admin/products'),
	array('label' => 'Customers',  'value' => (int) $stats['customers'],  'note' => 'registered accounts', 'url' => 'admin/users'),
);
?>

<div class="row g-3 mb-4">
	<?php foreach ($tiles as $tile): ?>
		<div class="col-sm-6 col-xl-3">
			<a class="text-decoration-none" href="<?= site_url($tile['url']) ?>">
				<div class="card shadow-sm stat-card h-100">
					<div class="card-body">
						<div class="stat-label text-muted"><?= e($tile['label']) ?></div>
						<div class="stat-value text-dark"><?= $tile['value'] ?></div>
						<div class="small text-muted"><?= e($tile['note']) ?></div>
					</div>
				</div>
			</a>
		</div>
	<?php endforeach ?>
</div>

<?php if ($stats['low_stock'] > 0): ?>
	<div class="alert alert-warning d-flex justify-content-between align-items-center">
		<span><strong><?= (int) $stats['low_stock'] ?></strong> product(s) at or below 5 units in stock.</span>
		<a class="btn btn-sm btn-outline-dark" href="<?= site_url('admin/products?sort=name_asc') ?>">Review products</a>
	</div>
<?php endif ?>

<div class="row g-4">
	<div class="col-xl-8">
		<div class="card shadow-sm mb-4">
			<div class="card-header bg-white fw-semibold d-flex justify-content-between">
				<span>Revenue, last 14 days</span>
				<?php $peak = max(array_map(function ($d) { return $d['revenue']; }, $revenue_series)) ?>
				<span class="small text-muted">peak <?= money($peak) ?></span>
			</div>
			<div class="card-body">
				<?php if ($peak <= 0): ?>
					<p class="text-muted mb-0 text-center py-4">No revenue recorded in this period yet.</p>
				<?php else: ?>
					<div class="revenue-chart mb-2">
						<?php foreach ($revenue_series as $day): ?>
							<?php $height = $peak > 0 ? max(2, round(($day['revenue'] / $peak) * 100)) : 2 ?>
							<div class="bar" style="height: <?= $height ?>%"
								title="<?= date('j M', strtotime($day['day'])) ?>: <?= money($day['revenue']) ?> (<?= (int) $day['orders'] ?> orders)"></div>
						<?php endforeach ?>
					</div>
					<div class="d-flex justify-content-between small text-muted">
						<span><?= date('j M', strtotime($revenue_series[0]['day'])) ?></span>
						<span><?= date('j M', strtotime(end($revenue_series)['day'])) ?></span>
					</div>
				<?php endif ?>
			</div>
		</div>

		<div class="card shadow-sm">
			<div class="card-header bg-white fw-semibold d-flex justify-content-between">
				<span>Recent orders</span>
				<a class="small" href="<?= site_url('admin/orders') ?>">All orders</a>
			</div>

			<?php if (empty($recent_orders)): ?>
				<div class="card-body text-muted">No orders yet.</div>
			<?php else: ?>
				<div class="table-responsive">
					<table class="table mb-0 align-middle">
						<thead class="table-light">
							<tr>
								<th scope="col">Order</th>
								<th scope="col">Customer</th>
								<th scope="col">Placed</th>
								<th scope="col">Status</th>
								<th scope="col" class="text-end">Total</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($recent_orders as $order): ?>
								<tr>
									<td>
										<a class="text-decoration-none fw-semibold"
											href="<?= site_url('admin/orders/view/' . (int) $order['id']) ?>"><?= e($order['order_number']) ?></a>
									</td>
									<td class="small"><?= e($order['customer_name']) ?></td>
									<td class="small"><?= date('j M, H:i', strtotime($order['created_at'])) ?></td>
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

	<div class="col-xl-4">
		<div class="card shadow-sm mb-4">
			<div class="card-header bg-white fw-semibold">Best sellers</div>
			<?php if (empty($top_products)): ?>
				<div class="card-body text-muted small">Nothing sold yet.</div>
			<?php else: ?>
				<ul class="list-group list-group-flush">
					<?php foreach ($top_products as $row): ?>
						<li class="list-group-item d-flex justify-content-between align-items-center">
							<span class="small"><?= e($row['product_name']) ?></span>
							<span class="badge bg-dark rounded-pill"><?= (int) $row['units'] ?></span>
						</li>
					<?php endforeach ?>
				</ul>
			<?php endif ?>
		</div>

		<div class="card shadow-sm">
			<div class="card-header bg-white fw-semibold">Low stock</div>
			<?php if (empty($low_stock)): ?>
				<div class="card-body text-muted small">Every product is well stocked.</div>
			<?php else: ?>
				<ul class="list-group list-group-flush">
					<?php foreach ($low_stock as $product): ?>
						<li class="list-group-item d-flex justify-content-between align-items-center">
							<a class="small text-decoration-none link-dark"
								href="<?= site_url('admin/products/edit/' . (int) $product['id']) ?>"><?= e($product['name']) ?></a>
							<span class="badge bg-<?= (int) $product['stock'] === 0 ? 'danger' : 'warning text-dark' ?> rounded-pill">
								<?= (int) $product['stock'] ?>
							</span>
						</li>
					<?php endforeach ?>
				</ul>
			<?php endif ?>
		</div>
	</div>
</div>
