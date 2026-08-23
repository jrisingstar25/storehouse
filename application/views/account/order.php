<nav aria-label="breadcrumb">
	<ol class="breadcrumb small">
		<li class="breadcrumb-item"><a href="<?= site_url('account/orders') ?>">My orders</a></li>
		<li class="breadcrumb-item active" aria-current="page"><?= e($order['order_number']) ?></li>
	</ol>
</nav>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
	<h1 class="h4 mb-0">Order <?= e($order['order_number']) ?></h1>
	<span class="badge bg-<?= order_status_class($order['status']) ?> fs-6"><?= e($order['status']) ?></span>
</div>

<div class="row g-4">
	<div class="col-lg-8">
		<div class="card shadow-sm">
			<div class="card-header bg-white fw-semibold">Items</div>
			<div class="table-responsive">
				<table class="table mb-0 align-middle">
					<thead class="table-light">
						<tr>
							<th scope="col">Product</th>
							<th scope="col" class="text-end">Price</th>
							<th scope="col" class="text-end">Qty</th>
							<th scope="col" class="text-end">Subtotal</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($items as $item): ?>
							<tr>
								<td><?= e($item['product_name']) ?></td>
								<td class="text-end"><?= money($item['price']) ?></td>
								<td class="text-end"><?= (int) $item['qty'] ?></td>
								<td class="text-end"><?= money($item['subtotal']) ?></td>
							</tr>
						<?php endforeach ?>
					</tbody>
					<tfoot>
						<tr><th colspan="3" class="text-end">Subtotal</th><td class="text-end"><?= money($order['subtotal']) ?></td></tr>
						<tr><th colspan="3" class="text-end">Shipping</th><td class="text-end"><?= money($order['shipping_fee']) ?></td></tr>
						<tr><th colspan="3" class="text-end">Total</th><th class="text-end"><?= money($order['total']) ?></th></tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>

	<div class="col-lg-4">
		<div class="card shadow-sm">
			<div class="card-header bg-white fw-semibold">Details</div>
			<div class="card-body small">
				<div class="text-muted">Placed</div>
				<div class="mb-2"><?= date('j M Y, H:i', strtotime($order['created_at'])) ?></div>

				<div class="text-muted">Delivering to</div>
				<div class="mb-2">
					<?= e($order['customer_name']) ?><br>
					<?= nl2br(e($order['shipping_address'])) ?>
				</div>

				<div class="text-muted">Contact</div>
				<div class="mb-2">
					<?= e($order['customer_email']) ?>
					<?php if ($order['customer_phone']): ?><br><?= e($order['customer_phone']) ?><?php endif ?>
				</div>

				<div class="text-muted">Payment</div>
				<div class="mb-2"><?= $order['payment_method'] === 'cod' ? 'Cash on delivery' : 'Bank transfer' ?></div>

				<?php if ($order['notes']): ?>
					<div class="text-muted">Notes</div>
					<div><?= nl2br(e($order['notes'])) ?></div>
				<?php endif ?>
			</div>
		</div>
	</div>
</div>
