<?php $editing = $product !== NULL ?>

<nav aria-label="breadcrumb">
	<ol class="breadcrumb small">
		<li class="breadcrumb-item"><a href="<?= site_url('admin/products') ?>">Products</a></li>
		<li class="breadcrumb-item active" aria-current="page"><?= $editing ? 'Edit' : 'New' ?></li>
	</ol>
</nav>

<h1 class="h3 mb-3"><?= $editing ? 'Edit product' : 'New product' ?></h1>

<?= form_open_multipart($editing ? 'admin/products/edit/' . (int) $product['id'] : 'admin/products/create') ?>
	<div class="row g-4">
		<div class="col-lg-8">
			<div class="card shadow-sm">
				<div class="card-body">
					<div class="mb-3">
						<label class="form-label" for="name">Name</label>
						<input type="text" class="form-control" id="name" name="name"
							value="<?= set_value('name', $editing ? $product['name'] : '') ?>" required autofocus>
					</div>

					<div class="mb-3">
						<label class="form-label" for="sku">SKU</label>
						<input type="text" class="form-control" id="sku" name="sku"
							value="<?= set_value('sku', $editing ? $product['sku'] : '') ?>">
					</div>

					<div class="mb-0">
						<label class="form-label" for="description">Description</label>
						<textarea class="form-control" id="description" name="description" rows="8"><?= set_value('description', $editing ? $product['description'] : '') ?></textarea>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-4">
			<div class="card shadow-sm mb-4">
				<div class="card-header bg-white fw-semibold">Pricing &amp; stock</div>
				<div class="card-body">
					<div class="mb-3">
						<label class="form-label" for="price">Price</label>
						<div class="input-group">
							<span class="input-group-text">&yen;</span>
							<input type="number" class="form-control" id="price" name="price" step="0.01" min="0"
								value="<?= set_value('price', $editing ? $product['price'] : '0') ?>" required>
						</div>
					</div>

					<div class="mb-3">
						<label class="form-label" for="stock">Stock</label>
						<input type="number" class="form-control" id="stock" name="stock" step="1" min="0"
							value="<?= set_value('stock', $editing ? $product['stock'] : '0') ?>" required>
					</div>

					<div class="mb-0">
						<label class="form-label" for="category_id">Category</label>
						<select class="form-select" id="category_id" name="category_id">
							<option value="">Uncategorised</option>
							<?php $selected = set_value('category_id', $editing ? $product['category_id'] : '') ?>
							<?php foreach ($categories as $id => $name): ?>
								<option value="<?= (int) $id ?>" <?= (string) $selected === (string) $id ? 'selected' : '' ?>>
									<?= e($name) ?>
								</option>
							<?php endforeach ?>
						</select>
					</div>
				</div>
			</div>

			<div class="card shadow-sm mb-4">
				<div class="card-header bg-white fw-semibold">Image</div>
				<div class="card-body">
					<?php if ($editing && $product['image']): ?>
						<img src="<?= product_image_url($product['image']) ?>" alt="" class="img-fluid rounded mb-2">
						<div class="form-check mb-3">
							<input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
							<label class="form-check-label small" for="remove_image">Remove this image</label>
						</div>
					<?php endif ?>

					<label class="form-label" for="image"><?= $editing && $product['image'] ? 'Replace image' : 'Upload image' ?></label>
					<input type="file" class="form-control" id="image" name="image" accept="image/*">
					<div class="form-text">JPG, PNG, GIF or WebP. Up to 4 MB.</div>
				</div>
			</div>

			<div class="card shadow-sm mb-4">
				<div class="card-header bg-white fw-semibold">Visibility</div>
				<div class="card-body">
					<?php
					// An unchecked box is simply absent from the POST, so set_value()
					// would wrongly fall back to the default when redisplaying a
					// rejected form. Read the request directly instead.
					$active = $this->input->method() === 'post'
						? (int) (bool) $this->input->post('is_active')
						: ($editing ? (int) $product['is_active'] : 1);
					?>
					<div class="form-check form-switch">
						<input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active"
							value="1" <?= $active === 1 ? 'checked' : '' ?>>
						<label class="form-check-label" for="is_active">Visible in the shop</label>
					</div>
				</div>
			</div>

			<div class="d-grid gap-2">
				<button class="btn btn-dark" type="submit"><?= $editing ? 'Save changes' : 'Create product' ?></button>
				<a class="btn btn-outline-secondary" href="<?= site_url('admin/products') ?>">Cancel</a>
			</div>
		</div>
	</div>
<?= form_close() ?>
