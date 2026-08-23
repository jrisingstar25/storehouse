<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
	<div>
		<h1 class="h3 mb-0">Products</h1>
		<p class="text-muted small mb-0"><?= (int) $total ?> product<?= $total === 1 ? '' : 's' ?></p>
	</div>
	<a class="btn btn-dark" href="<?= site_url('admin/products/create') ?>">New product</a>
</div>

<div class="card shadow-sm mb-3">
	<div class="card-body">
		<form method="get" class="row g-2 align-items-end">
			<div class="col-md-4">
				<label class="form-label small" for="q">Search</label>
				<input type="search" class="form-control form-control-sm" id="q" name="q"
					value="<?= e($filters['search']) ?>" placeholder="Name, SKU or description">
			</div>

			<div class="col-md-3">
				<label class="form-label small" for="category_id">Category</label>
				<select class="form-select form-select-sm" id="category_id" name="category_id">
					<option value="">All categories</option>
					<?php foreach ($categories as $id => $name): ?>
						<option value="<?= (int) $id ?>" <?= (string) $filters['category_id'] === (string) $id ? 'selected' : '' ?>>
							<?= e($name) ?>
						</option>
					<?php endforeach ?>
				</select>
			</div>

			<div class="col-md-2">
				<label class="form-label small" for="is_active">Status</label>
				<select class="form-select form-select-sm" id="is_active" name="is_active">
					<option value="">Any</option>
					<option value="1" <?= $filters['is_active'] === '1' ? 'selected' : '' ?>>Active</option>
					<option value="0" <?= $filters['is_active'] === '0' ? 'selected' : '' ?>>Inactive</option>
				</select>
			</div>

			<div class="col-md-2">
				<label class="form-label small" for="sort">Sort</label>
				<select class="form-select form-select-sm" id="sort" name="sort">
					<option value="">Newest</option>
					<option value="name_asc"   <?= $filters['sort'] === 'name_asc'   ? 'selected' : '' ?>>Name A-Z</option>
					<option value="price_asc"  <?= $filters['sort'] === 'price_asc'  ? 'selected' : '' ?>>Price low-high</option>
					<option value="price_desc" <?= $filters['sort'] === 'price_desc' ? 'selected' : '' ?>>Price high-low</option>
				</select>
			</div>

			<div class="col-md-1 d-grid">
				<button class="btn btn-sm btn-outline-dark" type="submit">Filter</button>
			</div>
		</form>
	</div>
</div>

<?php if (empty($products)): ?>
	<div class="alert alert-light border text-center py-5">
		<p class="fw-semibold mb-1">No products match.</p>
		<a class="btn btn-dark mt-2" href="<?= site_url('admin/products/create') ?>">Add your first product</a>
	</div>
<?php else: ?>
	<div class="card shadow-sm">
		<div class="table-responsive">
			<table class="table align-middle mb-0">
				<thead class="table-light">
					<tr>
						<th scope="col" colspan="2">Product</th>
						<th scope="col">Category</th>
						<th scope="col" class="text-end">Price</th>
						<th scope="col" class="text-end">Stock</th>
						<th scope="col">Status</th>
						<th scope="col" class="text-end">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($products as $product): ?>
						<tr>
							<td style="width: 60px;">
								<img src="<?= product_image_url($product['image']) ?>" alt="" class="product-thumb">
							</td>
							<td>
								<a class="text-decoration-none link-dark fw-semibold"
									href="<?= site_url('admin/products/edit/' . (int) $product['id']) ?>"><?= e($product['name']) ?></a>
								<?php if ($product['sku']): ?>
									<div class="small text-muted"><?= e($product['sku']) ?></div>
								<?php endif ?>
							</td>
							<td class="small"><?= $product['category_name'] ? e($product['category_name']) : '<span class="text-muted">Uncategorised</span>' ?></td>
							<td class="text-end"><?= money($product['price']) ?></td>
							<td class="text-end">
								<?php $stock = (int) $product['stock'] ?>
								<span class="badge bg-<?= $stock === 0 ? 'danger' : ($stock <= 5 ? 'warning text-dark' : 'light text-dark') ?>">
									<?= $stock ?>
								</span>
							</td>
							<td>
								<?= form_open('admin/products/toggle/' . (int) $product['id'], array('class' => 'd-inline')) ?>
									<button class="btn btn-sm btn-<?= (int) $product['is_active'] === 1 ? 'success' : 'outline-secondary' ?>"
										type="submit" title="Click to toggle">
										<?= (int) $product['is_active'] === 1 ? 'Active' : 'Inactive' ?>
									</button>
								<?= form_close() ?>
							</td>
							<td class="text-end text-nowrap">
								<a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener"
									href="<?= site_url('product/' . (int) $product['id']) ?>">View</a>
								<a class="btn btn-sm btn-outline-dark"
									href="<?= site_url('admin/products/edit/' . (int) $product['id']) ?>">Edit</a>
								<?= form_open('admin/products/delete/' . (int) $product['id'], array(
									'class'    => 'd-inline',
									'onsubmit' => "return confirm('Delete this product? This cannot be undone.')",
								)) ?>
									<button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
								<?= form_close() ?>
							</td>
						</tr>
					<?php endforeach ?>
				</tbody>
			</table>
		</div>
	</div>

	<?php if ($pagination): ?>
		<nav class="mt-3" aria-label="Product pages"><?= $pagination ?></nav>
	<?php endif ?>
<?php endif ?>
