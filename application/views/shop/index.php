<div class="row">
	<aside class="col-lg-3 mb-4">
		<div class="card shadow-sm">
			<div class="card-header bg-white fw-semibold">Categories</div>
			<div class="list-group list-group-flush">
				<a class="list-group-item list-group-item-action d-flex justify-content-between <?= $active_category ? '' : 'active' ?>"
					href="<?= site_url('shop') ?>">
					All products
				</a>
				<?php foreach ($categories as $category): ?>
					<a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= ($active_category && $active_category['id'] === $category['id']) ? 'active' : '' ?>"
						href="<?= site_url('category/' . $category['slug']) ?>">
						<span><?= e($category['name']) ?></span>
						<span class="badge bg-secondary rounded-pill"><?= (int) $category['product_count'] ?></span>
					</a>
				<?php endforeach ?>
			</div>
		</div>
	</aside>

	<div class="col-lg-9">
		<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
			<div>
				<h1 class="h3 mb-0"><?= $active_category ? e($active_category['name']) : 'All products' ?></h1>
				<p class="text-muted small mb-0">
					<?= (int) $total ?> product<?= $total === 1 ? '' : 's' ?>
					<?php if ( ! empty($filters['search'])): ?>
						matching &ldquo;<?= e($filters['search']) ?>&rdquo;
					<?php endif ?>
				</p>
			</div>

			<form method="get" class="d-flex align-items-center gap-2">
				<?php if ( ! empty($filters['search'])): ?>
					<input type="hidden" name="q" value="<?= e($filters['search']) ?>">
				<?php endif ?>
				<label class="form-label mb-0 small text-muted" for="sort">Sort</label>
				<?php
				$sort = isset($filters['sort']) ? $filters['sort'] : '';
				$options = array(
					''           => 'Newest',
					'price_asc'  => 'Price: low to high',
					'price_desc' => 'Price: high to low',
					'name_asc'   => 'Name A-Z',
				);
				?>
				<select class="form-select form-select-sm w-auto" id="sort" name="sort" onchange="this.form.submit()">
					<?php foreach ($options as $value => $label): ?>
						<option value="<?= e($value) ?>" <?= $sort === $value ? 'selected' : '' ?>><?= e($label) ?></option>
					<?php endforeach ?>
				</select>
				<noscript><button class="btn btn-sm btn-outline-secondary" type="submit">Go</button></noscript>
			</form>
		</div>

		<?php if (empty($products)): ?>
			<div class="alert alert-light border text-center py-5">
				<p class="mb-1 fw-semibold">Nothing here yet.</p>
				<p class="text-muted mb-0">Try a different search or category.</p>
			</div>
		<?php else: ?>
			<div class="row g-3">
				<?php foreach ($products as $product): ?>
					<div class="col-sm-6 col-xl-4">
						<div class="card h-100 shadow-sm product-card">
							<a href="<?= site_url('product/' . $product['slug']) ?>">
								<img src="<?= product_image_url($product['image']) ?>" alt="<?= e($product['name']) ?>"
									class="card-img-top" loading="lazy">
							</a>
							<div class="card-body d-flex flex-column">
								<?php if ($product['category_name']): ?>
									<div class="small text-muted"><?= e($product['category_name']) ?></div>
								<?php endif ?>

								<h2 class="h6 card-title mt-1">
									<a class="text-decoration-none link-dark" href="<?= site_url('product/' . $product['slug']) ?>">
										<?= e($product['name']) ?>
									</a>
								</h2>

								<div class="fw-semibold mb-2"><?= money($product['price']) ?></div>

								<div class="mt-auto">
									<?php if ((int) $product['stock'] < 1): ?>
										<button class="btn btn-sm btn-outline-secondary w-100" disabled>Out of stock</button>
									<?php else: ?>
										<?= form_open('cart/add') ?>
											<input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
											<input type="hidden" name="qty" value="1">
											<input type="hidden" name="return_to" value="<?= e(uri_string()) ?>">
											<button class="btn btn-sm btn-dark w-100" type="submit">Add to cart</button>
										<?= form_close() ?>
									<?php endif ?>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach ?>
			</div>

			<?php if ($pagination): ?>
				<nav class="mt-4" aria-label="Product pages"><?= $pagination ?></nav>
			<?php endif ?>
		<?php endif ?>
	</div>
</div>
