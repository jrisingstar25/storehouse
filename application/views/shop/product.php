<nav aria-label="breadcrumb">
	<ol class="breadcrumb small">
		<li class="breadcrumb-item"><a href="<?= site_url('shop') ?>">Shop</a></li>
		<?php if ($product['category_name']): ?>
			<li class="breadcrumb-item">
				<a href="<?= site_url('category/' . $product['category_slug']) ?>"><?= e($product['category_name']) ?></a>
			</li>
		<?php endif ?>
		<li class="breadcrumb-item active" aria-current="page"><?= e($product['name']) ?></li>
	</ol>
</nav>

<?php if ((int) $product['is_active'] !== 1): ?>
	<div class="alert alert-warning">
		This product is <strong>inactive</strong> and hidden from customers. You are seeing it because you are an admin.
	</div>
<?php endif ?>

<div class="row g-4">
	<div class="col-lg-6">
		<img src="<?= product_image_url($product['image']) ?>" alt="<?= e($product['name']) ?>" class="product-hero">
	</div>

	<div class="col-lg-6">
		<?php if ($product['category_name']): ?>
			<div class="text-muted small"><?= e($product['category_name']) ?></div>
		<?php endif ?>

		<h1 class="h2 mb-2"><?= e($product['name']) ?></h1>

		<?php if ($product['sku']): ?>
			<div class="text-muted small mb-2">SKU <?= e($product['sku']) ?></div>
		<?php endif ?>

		<div class="h3 mb-3"><?= money($product['price']) ?></div>

		<?php $stock = (int) $product['stock'] ?>
		<p class="mb-3">
			<?php if ($stock < 1): ?>
				<span class="badge bg-secondary">Out of stock</span>
			<?php elseif ($stock <= 5): ?>
				<span class="badge bg-warning text-dark">Only <?= $stock ?> left</span>
			<?php else: ?>
				<span class="badge bg-success">In stock</span>
			<?php endif ?>
		</p>

		<?php if ($product['description']): ?>
			<div class="mb-4"><?= nl2br(e($product['description'])) ?></div>
		<?php endif ?>

		<?php if ($stock > 0): ?>
			<?= form_open('cart/add', array('class' => 'row g-2 align-items-end', 'data-ajax-cart' => '')) ?>
				<input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
				<input type="hidden" name="return_to" value="<?= e(uri_string()) ?>">

				<div class="col-auto" style="width: 7rem;">
					<label class="form-label small" for="qty">Quantity</label>
					<input type="number" class="form-control" id="qty" name="qty"
						value="1" min="1" max="<?= $stock ?>" step="1">
				</div>

				<div class="col-auto">
					<button class="btn btn-dark" type="submit">Add to cart</button>
				</div>

				<div class="col-auto">
					<a class="btn btn-outline-secondary" href="<?= site_url('cart') ?>">View cart</a>
				</div>
			<?= form_close() ?>
		<?php else: ?>
			<button class="btn btn-secondary" disabled>Out of stock</button>
		<?php endif ?>
	</div>
</div>

<?php if ( ! empty($related)): ?>
	<hr class="my-5">
	<h2 class="h5 mb-3">More in <?= e($product['category_name']) ?></h2>

	<div class="row g-3">
		<?php foreach ($related as $item): ?>
			<div class="col-6 col-md-3">
				<div class="card h-100 shadow-sm product-card">
					<a href="<?= site_url('product/' . $item['slug']) ?>">
						<img src="<?= product_image_url($item['image']) ?>" alt="<?= e($item['name']) ?>"
							class="card-img-top" loading="lazy">
					</a>
					<div class="card-body">
						<h3 class="h6 card-title mb-1">
							<a class="text-decoration-none link-dark" href="<?= site_url('product/' . $item['slug']) ?>">
								<?= e($item['name']) ?>
							</a>
						</h3>
						<div class="small fw-semibold"><?= money($item['price']) ?></div>
					</div>
				</div>
			</div>
		<?php endforeach ?>
	</div>
<?php endif ?>
