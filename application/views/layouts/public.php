<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= e($title) ?> &middot; Jinjong</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
	<div class="container">
		<a class="navbar-brand fw-bold" href="<?= site_url('shop') ?>">TESTSTORE</a>

		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"
			aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse" id="nav">
			<ul class="navbar-nav me-auto">
				<?php if ($current_user): ?>
					<li class="nav-item"><a class="nav-link" href="<?= site_url('shop') ?>">Shop</a></li>
				<?php endif ?>
			</ul>

			<?php if ($current_user): ?>
				<form class="d-flex me-lg-3 my-2 my-lg-0" role="search" action="<?= site_url('shop') ?>" method="get">
					<input class="form-control form-control-sm me-2" type="search" name="q"
						value="<?= e($this->input->get('q', TRUE)) ?>" placeholder="Search products" aria-label="Search products">
					<button class="btn btn-sm btn-outline-light" type="submit">Search</button>
				</form>
			<?php endif ?>

			<ul class="navbar-nav align-items-lg-center">
				<?php if ($current_user): ?>
					<li class="nav-item">
						<a class="nav-link position-relative" href="<?= site_url('cart') ?>">
							Cart
							<span class="badge rounded-pill bg-warning text-dark" data-cart-count
								<?= $cart_count > 0 ? '' : 'hidden' ?>><?= (int) $cart_count ?></span>
						</a>
					</li>
				<?php endif ?>

				<?php if ($current_user): ?>
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							<?= e($current_user['name']) ?>
						</a>
						<ul class="dropdown-menu dropdown-menu-end">
							<?php if ($current_user['role'] === 'admin'): ?>
								<li><a class="dropdown-item" href="<?= site_url('admin') ?>">Admin dashboard</a></li>
								<li><hr class="dropdown-divider"></li>
							<?php endif ?>
							<li><a class="dropdown-item" href="<?= site_url('account') ?>">My account</a></li>
							<li><a class="dropdown-item" href="<?= site_url('account/orders') ?>">My orders</a></li>
							<li><hr class="dropdown-divider"></li>
							<li><a class="dropdown-item" href="<?= site_url('logout') ?>">Sign out</a></li>
						</ul>
					</li>
				<?php else: ?>
					<li class="nav-item"><a class="nav-link" href="<?= site_url('login') ?>">Sign in</a></li>
				<?php endif ?>
			</ul>
		</div>
	</div>
</nav>

<main class="container py-4 flex-grow-1">
	<?php $this->load->view('partials/flash') ?>
	<?php $this->load->view($content_view) ?>
</main>

<footer class="bg-dark text-secondary py-4 mt-auto">
	<div class="container small d-flex justify-content-between flex-wrap gap-2">
		<span>&copy; <?= date('Y') ?> Jinjong</span>
		<span>Built on CodeIgniter <?= CI_VERSION ?></span>
	</div>
</footer>

<!-- Background add-to-cart feedback lands here. -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toasts" aria-live="polite" aria-atomic="true"></div>

<script>
// CSRF names the front end needs. The cookie is not HttpOnly, so the script
// can always recover the current token from it - see assets/js/app.js.
window.JINJONG = {
	csrfField : <?= json_encode($this->security->get_csrf_token_name()) ?>,
	csrfCookie: <?= json_encode(config_item('cookie_prefix') . config_item('csrf_cookie_name')) ?>
};
</script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
</html>
