<?php
// Highlight the sidebar entry matching the first admin URI segment.
$section = $this->uri->segment(2) ?: 'dashboard';

$nav = array(
	'dashboard'  => array('label' => 'Dashboard',  'url' => 'admin'),
	'products'   => array('label' => 'Products',   'url' => 'admin/products'),
	'categories' => array('label' => 'Categories', 'url' => 'admin/categories'),
	'orders'     => array('label' => 'Orders',     'url' => 'admin/orders'),
	'users'      => array('label' => 'Users',      'url' => 'admin/users'),
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= e($title) ?> &middot; Jinjong Admin</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet">
</head>
<body class="admin-body">

<nav class="navbar navbar-dark bg-dark sticky-top">
	<div class="container-fluid">
		<a class="navbar-brand fw-bold" href="<?= site_url('admin') ?>">JINJONG <span class="fw-normal opacity-75">admin</span></a>
		<div class="d-flex align-items-center gap-3">
			<a class="link-light small text-decoration-none" href="<?= site_url('shop') ?>" target="_blank" rel="noopener">View shop &nearr;</a>
			<div class="dropdown">
				<a class="link-light small text-decoration-none dropdown-toggle" href="#" role="button"
					data-bs-toggle="dropdown" aria-expanded="false"><?= e($current_user['name']) ?></a>
				<ul class="dropdown-menu dropdown-menu-end">
					<li><a class="dropdown-item" href="<?= site_url('account') ?>">My account</a></li>
					<li><hr class="dropdown-divider"></li>
					<li><a class="dropdown-item" href="<?= site_url('logout') ?>">Sign out</a></li>
				</ul>
			</div>
		</div>
	</div>
</nav>

<div class="container-fluid">
	<div class="row">
		<aside class="col-lg-2 admin-sidebar border-end py-3">
			<ul class="nav flex-column nav-pills">
				<?php foreach ($nav as $key => $item): ?>
					<li class="nav-item">
						<a class="nav-link <?= $section === $key ? 'active' : 'link-dark' ?>"
							href="<?= site_url($item['url']) ?>"><?= e($item['label']) ?></a>
					</li>
				<?php endforeach ?>
			</ul>
		</aside>

		<main class="col-lg-10 py-4 px-lg-4">
			<?php $this->load->view('partials/flash') ?>
			<?php $this->load->view($content_view) ?>
		</main>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
