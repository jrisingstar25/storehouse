<h1 class="h3 mb-3">Your cart</h1>

<?php if (empty($lines)): ?>
	<div class="alert alert-light border text-center py-5">
		<p class="fw-semibold mb-1">Your cart is empty.</p>
		<p class="text-muted">Browse the shop and add something you like.</p>
		<a class="btn btn-dark" href="<?= site_url('shop') ?>">Continue shopping</a>
	</div>
<?php else: ?>
	<?= form_open('cart/update') ?>
		<div class="table-responsive">
			<table class="table align-middle">
				<thead class="table-light">
					<tr>
						<th scope="col" colspan="2">Product</th>
						<th scope="col" class="text-end">Price</th>
						<th scope="col" style="width: 8rem;">Quantity</th>
						<th scope="col" class="text-end">Subtotal</th>
						<th scope="col"><span class="visually-hidden">Remove</span></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($lines as $line): ?>
						<tr>
							<td style="width: 80px;">
								<a href="<?= site_url('product/' . $line['slug']) ?>">
									<img src="<?= product_image_url($line['image']) ?>" alt="<?= e($line['name']) ?>" class="cart-thumb">
								</a>
							</td>
							<td>
								<a class="text-decoration-none link-dark fw-semibold" href="<?= site_url('product/' . $line['slug']) ?>">
									<?= e($line['name']) ?>
								</a>
								<?php if ($line['over_stock']): ?>
									<div class="small text-danger">
										Only <?= (int) $line['stock'] ?> in stock &mdash; please reduce the quantity.
									</div>
								<?php elseif ($line['stock'] <= 5): ?>
									<div class="small text-muted">Only <?= (int) $line['stock'] ?> left</div>
								<?php endif ?>
							</td>
							<td class="text-end"><?= money($line['price']) ?></td>
							<td>
								<input type="number" class="form-control form-control-sm"
									name="qty[<?= (int) $line['product_id'] ?>]"
									value="<?= (int) $line['qty'] ?>" min="0" max="<?= max(1, (int) $line['stock']) ?>" step="1"
									aria-label="Quantity for <?= e($line['name']) ?>">
							</td>
							<td class="text-end fw-semibold"><?= money($line['subtotal']) ?></td>
							<td class="text-end">
								<button class="btn btn-sm btn-outline-danger" type="submit" name="remove"
									value="<?= (int) $line['product_id'] ?>" formnovalidate>Remove</button>
							</td>
						</tr>
					<?php endforeach ?>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="4" class="text-end">Subtotal</th>
						<th class="text-end"><?= money($subtotal) ?></th>
						<th></th>
					</tr>
				</tfoot>
			</table>
		</div>

		<div class="d-flex flex-wrap justify-content-between gap-2">
			<div class="d-flex gap-2">
				<a class="btn btn-outline-secondary" href="<?= site_url('shop') ?>">Continue shopping</a>
				<button class="btn btn-outline-danger" type="submit" name="clear" value="1" formnovalidate>Empty cart</button>
			</div>

			<div class="d-flex gap-2">
				<button class="btn btn-outline-dark" type="submit">Update quantities</button>
				<a class="btn btn-dark" href="<?= site_url('checkout') ?>">Checkout</a>
			</div>
		</div>
	<?= form_close() ?>
<?php endif ?>
