<?php $editing = $category !== NULL ?>

<nav aria-label="breadcrumb">
	<ol class="breadcrumb small">
		<li class="breadcrumb-item"><a href="<?= site_url('admin/categories') ?>">Categories</a></li>
		<li class="breadcrumb-item active" aria-current="page"><?= $editing ? 'Edit' : 'New' ?></li>
	</ol>
</nav>

<h1 class="h3 mb-3"><?= $editing ? 'Edit category' : 'New category' ?></h1>

<div class="row">
	<div class="col-lg-7">
		<div class="card shadow-sm">
			<div class="card-body">
				<?= form_open($editing ? 'admin/categories/edit/' . (int) $category['id'] : 'admin/categories/create') ?>
					<div class="mb-3">
						<label class="form-label" for="name">Name</label>
						<input type="text" class="form-control" id="name" name="name"
							value="<?= set_value('name', $editing ? $category['name'] : '') ?>" required autofocus>
					</div>

					<div class="mb-3">
						<label class="form-label" for="slug">URL slug</label>
						<input type="text" class="form-control" id="slug" name="slug"
							value="<?= set_value('slug', $editing ? $category['slug'] : '') ?>"
							placeholder="Generated from the name if left blank">
					</div>

					<div class="mb-3">
						<label class="form-label" for="description">Description</label>
						<textarea class="form-control" id="description" name="description" rows="4"><?= set_value('description', $editing ? $category['description'] : '') ?></textarea>
					</div>

					<button class="btn btn-dark" type="submit"><?= $editing ? 'Save changes' : 'Create category' ?></button>
					<a class="btn btn-outline-secondary" href="<?= site_url('admin/categories') ?>">Cancel</a>
				<?= form_close() ?>
			</div>
		</div>
	</div>
</div>
