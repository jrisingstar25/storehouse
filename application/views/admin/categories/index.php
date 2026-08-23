<div class="d-flex justify-content-between align-items-center mb-3">
	<h1 class="h3 mb-0">Categories</h1>
	<a class="btn btn-dark" href="<?= site_url('admin/categories/create') ?>">New category</a>
</div>

<?php if (empty($categories)): ?>
	<div class="alert alert-light border text-center py-5">
		<p class="fw-semibold mb-1">No categories yet.</p>
		<a class="btn btn-dark mt-2" href="<?= site_url('admin/categories/create') ?>">Create one</a>
	</div>
<?php else: ?>
	<div class="card shadow-sm">
		<div class="table-responsive">
			<table class="table align-middle mb-0">
				<thead class="table-light">
					<tr>
						<th scope="col">Name</th>
						<th scope="col">Slug</th>
						<th scope="col">Description</th>
						<th scope="col" class="text-end">Products</th>
						<th scope="col" class="text-end">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($categories as $category): ?>
						<tr>
							<td class="fw-semibold"><?= e($category['name']) ?></td>
							<td><code class="small"><?= e($category['slug']) ?></code></td>
							<td class="small text-muted">
								<?= $category['description'] ? e(character_limiter($category['description'], 70)) : '&mdash;' ?>
							</td>
							<td class="text-end"><span class="badge bg-light text-dark"><?= (int) $category['product_count'] ?></span></td>
							<td class="text-end text-nowrap">
								<a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener"
									href="<?= site_url('category/' . $category['slug']) ?>">View</a>
								<a class="btn btn-sm btn-outline-dark"
									href="<?= site_url('admin/categories/edit/' . (int) $category['id']) ?>">Edit</a>
								<?= form_open('admin/categories/delete/' . (int) $category['id'], array(
									'class'    => 'd-inline',
									'onsubmit' => "return confirm('Delete this category? Its products stay, but become uncategorised.')",
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
<?php endif ?>
