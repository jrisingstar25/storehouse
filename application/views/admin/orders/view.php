<nav aria-label="breadcrumb">
	<ol class="breadcrumb small">
		<li class="breadcrumb-item"><a href="<?= site_url('admin/orders') ?>">Orders</a></li>
		<li class="breadcrumb-item active" aria-current="page"><?= e($order['order_number']) ?></li>
	</ol>
</nav>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
	<div>
		<h1 class="h4 mb-0">Order <?= e($order['order_number']) ?></h1>
		<p class="text-muted small mb-0">Placed <?= date('j M Y, H:i', strtotime($order['created_at'])) ?></p>
	</div>
	<span class="badge bg-<?= order_status_class($order['status']) ?> fs-6"><?= e($order['status']) ?></span>
</div>

<div class="row g-4">
	<div class="col-lg-8">
		<div class="card shadow-sm mb-4">
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
								<td>
									<?php if ($item['product_id']): ?>
										<a class="text-decoration-none link-dark"
											href="<?= site_url('admin/products/edit/' . (int) $item['product_id']) ?>"><?= e($item['product_name']) ?></a>
									<?php else: ?>
										<?= e($item['product_name']) ?>
										<span class="badge bg-light text-muted">deleted</span>
									<?php endif ?>
								</td>
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

		<div class="card shadow-sm">
			<div class="card-header bg-white fw-semibold">Update status</div>
			<div class="card-body">
				<?= form_open('admin/orders/status/' . (int) $order['id'], array('class' => 'row g-2 align-items-end')) ?>
					<div class="col-sm-6">
						<label class="form-label small" for="status">Status</label>
						<select class="form-select" id="status" name="status">
							<?php foreach (order_statuses() as $status): ?>
								<option value="<?= e($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>>
									<?= ucfirst($status) ?>
								</option>
							<?php endforeach ?>
						</select>
					</div>

					<div class="col-sm-6">
						<button class="btn btn-dark" type="submit">Update</button>
					</div>
				<?= form_close() ?>

				<p class="form-text mb-0 mt-2">
					Cancelling an order returns its items to stock. That happens once, on the
					first move to <em>cancelled</em>.
				</p>
			</div>
		</div>
	</div>

	<div class="col-lg-4">
		<div class="card shadow-sm mb-4">
			<div class="card-header bg-white fw-semibold">Customer</div>
			<div class="card-body small">
				<div class="fw-semibold"><?= e($order['customer_name']) ?></div>
				<?php if ($order['customer_phone']): ?><div><?= e($order['customer_phone']) ?></div><?php endif ?>

				<?php if ($customer): ?>
					<a class="btn btn-sm btn-outline-secondary mt-2"
						href="<?= site_url('admin/users/view/' . (int) $customer['id']) ?>">Open account</a>
				<?php else: ?>
					<div class="text-muted mt-2">Guest order (no linked account).</div>
				<?php endif ?>
			</div>
		</div>

		<div class="card shadow-sm mb-4">
			<div class="card-header bg-white fw-semibold">Shipping</div>
			<div class="card-body small">
				<div><?= nl2br(e($order['shipping_address'])) ?></div>
				<div class="text-muted mt-2">Payment</div>
				<div><?= $order['payment_method'] === 'cod' ? 'Cash on delivery' : 'Bank transfer' ?></div>

				<?php if ($order['notes']): ?>
					<div class="text-muted mt-2">Customer notes</div>
					<div><?= nl2br(e($order['notes'])) ?></div>
				<?php endif ?>
			</div>
		</div>

		<?= form_open('admin/orders/delete/' . (int) $order['id'], array(
			'onsubmit' => "return confirm('Delete this order permanently? Stock is NOT returned. Cancel it instead if you want stock back.')",
		)) ?>
			<button class="btn btn-outline-danger w-100" type="submit">Delete order</button>
		<?= form_close() ?>
	</div>
</div>
