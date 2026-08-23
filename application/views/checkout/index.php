<h1 class="h3 mb-3">Checkout</h1>

<?= form_open('checkout') ?>
	<div class="row g-4">
		<div class="col-lg-7">
			<div class="card shadow-sm mb-4">
				<div class="card-header bg-white fw-semibold">Delivery details</div>
				<div class="card-body">
					<div class="row">
						<div class="col-sm-6 mb-3">
							<label class="form-label" for="customer_name">Name</label>
							<input type="text" class="form-control" id="customer_name" name="customer_name"
								value="<?= set_value('customer_name', $user['name']) ?>" required>
						</div>

						<div class="col-sm-6 mb-3">
							<label class="form-label" for="customer_phone">Phone <span class="text-muted">(optional)</span></label>
						<input type="text" class="form-control" id="customer_phone" name="customer_phone"
							value="<?= set_value('customer_phone', $user['phone']) ?>">
					</div>

					<div class="mb-3">
						<label class="form-label" for="shipping_address">Shipping address</label>
						<textarea class="form-control" id="shipping_address" name="shipping_address" rows="3"
							required><?= set_value('shipping_address', $user['address']) ?></textarea>
					</div>

					<div class="mb-0">
						<label class="form-label" for="notes">Order notes <span class="text-muted">(optional)</span></label>
						<textarea class="form-control" id="notes" name="notes" rows="2"><?= set_value('notes') ?></textarea>
					</div>
				</div>
			</div>

			<div class="card shadow-sm">
				<div class="card-header bg-white fw-semibold">Payment</div>
				<div class="card-body">
					<?php $method = set_value('payment_method', 'cod') ?>

					<div class="form-check mb-2">
						<input class="form-check-input" type="radio" name="payment_method" id="pay_cod"
							value="cod" <?= $method === 'cod' ? 'checked' : '' ?>>
						<label class="form-check-label" for="pay_cod">
							Cash on delivery
							<span class="d-block small text-muted">Pay the courier when your order arrives.</span>
						</label>
					</div>

					<div class="form-check">
						<input class="form-check-input" type="radio" name="payment_method" id="pay_bank"
							value="bank_transfer" <?= $method === 'bank_transfer' ? 'checked' : '' ?>>
						<label class="form-check-label" for="pay_bank">
							Bank transfer
							<span class="d-block small text-muted">Transfer details appear on your order once it is confirmed.</span>
						</label>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-5">
			<div class="card shadow-sm">
				<div class="card-header bg-white fw-semibold">Order summary</div>

				<ul class="list-group list-group-flush">
					<?php foreach ($lines as $line): ?>
						<li class="list-group-item d-flex justify-content-between align-items-start gap-2">
							<span>
								<?= e($line['name']) ?>
								<span class="d-block small text-muted"><?= (int) $line['qty'] ?> &times; <?= money($line['price']) ?></span>
							</span>
							<span class="fw-semibold text-nowrap"><?= money($line['subtotal']) ?></span>
						</li>
					<?php endforeach ?>
				</ul>

				<div class="card-body">
					<div class="d-flex justify-content-between">
						<span>Subtotal</span><span><?= money($subtotal) ?></span>
					</div>
					<div class="d-flex justify-content-between">
						<span>Shipping</span>
						<span><?= $shipping > 0 ? money($shipping) : '<span class="text-success">Free</span>' ?></span>
					</div>
					<hr>
					<div class="d-flex justify-content-between h5">
						<span>Total</span><span><?= money($total) ?></span>
					</div>

					<button class="btn btn-dark w-100 mt-3" type="submit">Place order</button>
					<a class="btn btn-link w-100 mt-1" href="<?= site_url('cart') ?>">Back to cart</a>
				</div>
			</div>
		</div>
	</div>
<?= form_close() ?>
