<div class="row justify-content-center">
	<div class="col-lg-8">
		<div class="text-center mb-4">
			<div class="display-6 mb-2">Thank you!</div>
			<p class="text-muted mb-1">Your order has been placed.</p>
			<p class="h5">Order <span class="text-monospace"><?= e($order['order_number']) ?></span></p>
		</div>

		<div class="card shadow-sm mb-3">
			<div class="card-header bg-white fw-semibold">What you ordered</div>
			<ul class="list-group list-group-flush">
				<?php foreach ($items as $item): ?>
					<li class="list-group-item d-flex justify-content-between">
						<span><?= e($item['product_name']) ?> <span class="text-muted small">&times; <?= (int) $item['qty'] ?></span></span>
						<span><?= money($item['subtotal']) ?></span>
					</li>
				<?php endforeach ?>
			</ul>
			<div class="card-body">
				<div class="d-flex justify-content-between"><span>Subtotal</span><span><?= money($order['subtotal']) ?></span></div>
				<div class="d-flex justify-content-between"><span>Shipping</span><span><?= money($order['shipping_fee']) ?></span></div>
				<hr>
				<div class="d-flex justify-content-between h5 mb-0"><span>Total</span><span><?= money($order['total']) ?></span></div>
			</div>
		</div>

		<div class="card shadow-sm mb-4">
			<div class="card-body small">
				<div class="row">
					<div class="col-sm-6">
						<div class="text-muted">Delivering to</div>
						<div class="fw-semibold"><?= e($order['customer_name']) ?></div>
						<div><?= nl2br(e($order['shipping_address'])) ?></div>
					</div>
					<div class="col-sm-6">
						<div class="text-muted">Payment</div>
						<div><?= $order['payment_method'] === 'cod' ? 'Cash on delivery' : 'Bank transfer' ?></div>
						<div class="text-muted mt-2">Status</div>
						<span class="badge bg-<?= order_status_class($order['status']) ?>"><?= e($order['status']) ?></span>
					</div>
				</div>
			</div>
		</div>

		<div class="text-center d-flex justify-content-center gap-2">
			<a class="btn btn-dark" href="<?= site_url('shop') ?>">Continue shopping</a>
			<a class="btn btn-outline-secondary" href="<?= site_url('account/orders') ?>">My orders</a>
		</div>
	</div>
</div>
