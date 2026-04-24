<?php

require_once __DIR__ . '/../../config/auth.php';
$nombreSistema = defined('SYSTEM_NAME') ? SYSTEM_NAME : 'ElZapato';

$catalogProducts = [
	['image' => '1.jpg',  'name' => 'Nova Street Sand',   'category' => 'casual',     'badge' => 'Nuevo',      'price' => 42.90, 'tone' => 'negro',  'size' => '39', 'desc' => 'Silueta urbana con acabado limpio y presencia ligera para uso diario.'],
	['image' => '2.jpg',  'name' => 'Urban Drift White',  'category' => 'sneakers',   'badge' => 'Top',        'price' => 48.50, 'tone' => 'blanco', 'size' => '41', 'desc' => 'Diseño minimalista con perfil moderno y máxima comodidad visual.'],
	['image' => '3.jpg',  'name' => 'Café Motion Pro',    'category' => 'formal',     'badge' => 'Elegante',   'price' => 59.99, 'tone' => 'blanco',   'size' => '42', 'desc' => 'Ideal para oficina y eventos con una línea sobria de alto impacto.'],
	['image' => '4.jpg',  'name' => 'Pulse Runner Gray',  'category' => 'deportivo',  'badge' => 'Runner',     'price' => 54.00, 'tone' => 'blanco',   'size' => '40', 'desc' => 'Estética deportiva con textura dinámica y sensación de movimiento.'],
	['image' => '5.jpg',  'name' => 'Velvet Walk Rose',   'category' => 'casual',     'badge' => 'Soft',       'price' => 46.75, 'tone' => 'negro',   'size' => '38', 'desc' => 'Modelo fresco con tono suave y look contemporáneo para salidas urbanas.'],
	['image' => '6.jpg',  'name' => 'Black Edge Formal',  'category' => 'formal',     'badge' => 'Premium',    'price' => 64.50, 'tone' => 'negro',  'size' => '43', 'desc' => 'Acabado clásico renovado con porte serio y detalles refinados.'],
	['image' => '7.jpg',  'name' => 'Cloud Step Mono',    'category' => 'sneakers',   'badge' => 'Trend',      'price' => 51.20, 'tone' => 'mono',   'size' => '40', 'desc' => 'Inspiración streetwear con perfiles redondeados y presencia actual.'],
	['image' => '8.jpg',  'name' => 'Amber Trek Low',     'category' => 'deportivo',  'badge' => 'Activo',     'price' => 57.40, 'tone' => 'mono',  'size' => '42', 'desc' => 'Construcción visual robusta y lista para destacar en movimiento.'],
	['image' => '9.jpg',  'name' => 'Soft Line Cream',    'category' => 'casual',     'badge' => 'Fresh',      'price' => 43.30, 'tone' => 'rojo',  'size' => '39', 'desc' => 'Línea delicada, versátil y pensada para looks limpios y naturales.'],
	['image' => '10.jpg', 'name' => 'Titan Office Brown', 'category' => 'formal',     'badge' => 'Office',     'price' => 61.00, 'tone' => 'negro', 'size' => '44', 'desc' => 'Carácter firme para jornadas profesionales con estilo definido.'],
	['image' => '11.jpg', 'name' => 'Flash Knit Neon',    'category' => 'deportivo',  'badge' => 'Impacto',    'price' => 58.90, 'tone' => 'negro',   'size' => '41', 'desc' => 'Diseño enérgico, visualmente veloz y con acento juvenil atrevido.'],
	['image' => '12.jpg', 'name' => 'Metro Layer Ivory',  'category' => 'sneakers',   'badge' => 'Street',     'price' => 49.80, 'tone' => 'negro',  'size' => '40', 'desc' => 'Capas suaves y perfil editorial para combinar con cualquier outfit.'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Catálogo público de ElZapato con estilos casuales, deportivos, sneakers y formales.">
	<title>Catálogo | ElZapato</title>
	<link rel="stylesheet" href="/ElZapato/Assets/css/catalogo.css?v=20260417">
	<link rel="stylesheet" href="/ElZapato/Assets/css/components/dev-modal.css?v=20260423">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

	<?php include $_SERVER['DOCUMENT_ROOT'] . '/ElZapato/src/views/layouts/header.php'; ?>

	<main class="catalog-page">
		<section class="trend-rail" id="catalogTrends" aria-label="Tendencias del catálogo">
			<div class="trend-track">
				<span>Casual Flow</span>
				<span>Sneaker Culture</span>
				<span>Editorial Motion</span>
				<span>Soft Luxury</span>
				<span>Urban Layers</span>
				<span>Color Statements</span>
				<span>Formal Essentials</span>
				<span>Casual Flow</span>
				<span>Sneaker Culture</span>
				<span>Editorial Motion</span>
			</div>
		</section>

		<section class="catalog-shell reveal-up" id="catalogCollection">
			<div class="catalog-shell-head">
				<div>
					<span class="section-mini">Colección visual</span>
					<h2>Modelos para todos los estilos</h2>
					<p>Encuentra tu estilo perfecto entre nuestra selección de calzado.</p>
				</div>

				<div class="catalog-tools">
					<div class="catalog-search">
						<i class="fas fa-search"></i>
						<input type="text" id="catalogSearch" placeholder="Buscar modelo o tono...">
					</div>
				</div>
			</div>

			<div class="filter-pills" id="catalogFilters">
				<button class="filter-pill active" data-filter="all">Todo</button>
				<button class="filter-pill" data-filter="casual">Casual</button>
				<button class="filter-pill" data-filter="sneakers">Sneakers</button>
				<button class="filter-pill" data-filter="deportivo">Deportivo</button>
				<button class="filter-pill" data-filter="formal">Formal</button>
			</div>

			<div class="catalog-grid" id="catalogGrid">
				<?php foreach ($catalogProducts as $index => $product): ?>
					<article
						class="catalog-card reveal-scale"
						data-category="<?= htmlspecialchars($product['category']) ?>"
						data-name="<?= htmlspecialchars(strtolower($product['name'])) ?>"
						data-tone="<?= htmlspecialchars(strtolower($product['tone'])) ?>"
						data-image="/ElZapato/Assets/img/productos/<?= htmlspecialchars($product['image']) ?>"
						data-size="<?= htmlspecialchars($product['size']) ?>"
						data-color="<?= htmlspecialchars(ucfirst($product['tone'])) ?>"
						data-price="<?= $product['price'] ?>"
						data-fullcategory="<?= htmlspecialchars(ucfirst($product['category'])) ?>"
					>
						<div class="catalog-card-glow"></div>
						<div class="catalog-card-media">
							<img src="/ElZapato/Assets/img/productos/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
							<span class="catalog-badge"><?= htmlspecialchars($product['badge']) ?></span>
							<button class="catalog-quick-view js-open-detail" type="button" aria-label="Ver detalle de <?= htmlspecialchars($product['name']) ?>">
								<i class="fas fa-arrow-up-right-from-square"></i>
							</button>
						</div>
						<div class="catalog-card-body">
							<div class="catalog-meta-row">
								<span class="catalog-category"><?= ucfirst(htmlspecialchars($product['category'])) ?></span>
								<span class="catalog-tone"><?= ucfirst(htmlspecialchars($product['tone'])) ?></span>
							</div>
							<h3><?= htmlspecialchars($product['name']) ?></h3>
							<p><?= htmlspecialchars($product['desc']) ?></p>
							<div class="catalog-card-footer">
								<strong>$<?= number_format($product['price'], 2) ?></strong>
								<a href="#" class="catalog-link js-open-detail">Ver detalle <i class="fas fa-arrow-right"></i></a>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="catalog-showcase reveal-up">
			<div class="showcase-copy">
				<span class="section-mini">Lo nuevo</span>
				<h2>Una experiencia que mezcla deporte, estilo y tecnología</h2>
				<p>
					Los nuevos Nike New Slides marcan una ola de tendencia más moderna:
					suaves, perfectos para toda la familia y mucho más premium.
				</p>
				<ul>
					<li><i class="fas fa-check"></i> Comodidad excepcional</li>
					<li><i class="fas fa-check"></i> Diseño moderno y versátil</li>
					<li><i class="fas fa-check"></i> Tecnología avanzada</li>
				</ul>
			</div>
			<div class="showcase-panel">
				<div class="showcase-stack stack-1"></div>
				<div class="showcase-stack stack-2"></div>
				<div class="showcase-main-card">
					<img src="/ElZapato/Assets/img/jaja.jpg" alt="Producto destacado del catálogo">
					<div class="showcase-label">Tendencia</div>
				</div>
			</div>
		</section>
	</main>

	<div class="catalog-modal" id="catalogDetailModal" aria-hidden="true">
		<div class="catalog-modal-backdrop" data-close-modal="true"></div>
		<div class="catalog-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="catalogModalTitle">
			<button class="catalog-modal-close" type="button" aria-label="Cerrar detalle" data-close-modal="true">
				<i class="fas fa-times"></i>
			</button>

			<div class="catalog-modal-media">
				<div class="catalog-modal-zoom-wrap" id="catalogZoomWrap">
					<img id="catalogModalImage" src="" alt="Imagen del producto">
				</div>
				<div class="catalog-zoom-controls">
					<button type="button" class="zoom-btn" id="zoomOutBtn" aria-label="Reducir zoom">
						<i class="fas fa-magnifying-glass-minus"></i>
					</button>
					<button type="button" class="zoom-btn" id="zoomResetBtn">Reset</button>
					<button type="button" class="zoom-btn" id="zoomInBtn" aria-label="Aumentar zoom">
						<i class="fas fa-magnifying-glass-plus"></i>
					</button>
				</div>
			</div>

			<div class="catalog-modal-body">
				<span class="catalog-modal-kicker">Detalle del producto</span>
				<h3 id="catalogModalTitle">Producto</h3>
				<div class="catalog-modal-specs">
					<p><strong>Categoría:</strong> <span id="catalogModalCategory">--</span></p>
					<p><strong>Talla:</strong> <span id="catalogModalSize">--</span></p>
					<p><strong>Color:</strong> <span id="catalogModalColor">--</span></p>
				</div>
				<div class="catalog-modal-price-badge" id="catalogModalPrice">--</div>
			</div>
		</div>
	</div>

	<footer class="catalog-footer">
        <p>&copy; <?php echo date('Y'); ?> <?php echo $nombreSistema; ?> - Catálogo de Inspiración Visual</p>
        <button type="button" onclick="openDevModal()" class="btn-devs">Desarrollado por</button>
    </footer>

	<?php include $_SERVER['DOCUMENT_ROOT'] . '/ElZapato/src/views/layouts/dev-team-modal.php'; ?>
	<script src="/ElZapato/Assets/js/dev-modal.js?v=20260423"></script>

	<script>
		const filterButtons = document.querySelectorAll('.filter-pill');
		const cards = document.querySelectorAll('.catalog-card');
		const searchInput = document.getElementById('catalogSearch');
		const detailTriggers = document.querySelectorAll('.js-open-detail');
		const detailModal = document.getElementById('catalogDetailModal');
		const modalTitle = document.getElementById('catalogModalTitle');
		const modalSize = document.getElementById('catalogModalSize');
		const modalColor = document.getElementById('catalogModalColor');
		const modalPrice = document.getElementById('catalogModalPrice');
		const modalCategory = document.getElementById('catalogModalCategory');
		const modalImage = document.getElementById('catalogModalImage');
		const zoomWrap = document.getElementById('catalogZoomWrap');
		const zoomInBtn = document.getElementById('zoomInBtn');
		const zoomOutBtn = document.getElementById('zoomOutBtn');
		const zoomResetBtn = document.getElementById('zoomResetBtn');
		let currentZoom = 1;

		function applyCatalogFilters() {
			const active = document.querySelector('.filter-pill.active')?.dataset.filter || 'all';
			const term = (searchInput?.value || '').toLowerCase().trim();

			cards.forEach(card => {
				const category = card.dataset.category || '';
				const name = card.dataset.name || '';
				const tone = card.dataset.tone || '';
				const matchesFilter = active === 'all' || category === active;
				const matchesSearch = !term || name.includes(term) || tone.includes(term) || category.includes(term);
				card.style.display = (matchesFilter && matchesSearch) ? '' : 'none';
			});
		}

		filterButtons.forEach(button => {
			button.addEventListener('click', () => {
				filterButtons.forEach(btn => btn.classList.remove('active'));
				button.classList.add('active');
				applyCatalogFilters();
			});
		});

		searchInput?.addEventListener('input', applyCatalogFilters);

		function clampZoom(value) {
			return Math.min(3, Math.max(1, value));
		}

		function applyZoom() {
			if (!modalImage) return;
			modalImage.style.transform = `scale(${currentZoom})`;
		}

		function setZoom(value) {
			currentZoom = clampZoom(value);
			applyZoom();
		}

		function resetZoom() {
			if (!modalImage) return;
			currentZoom = 1;
			modalImage.style.transformOrigin = 'center center';
			applyZoom();
		}

		function openDetailModal(card) {
			if (!detailModal || !card || !modalImage) return;

			modalTitle.textContent = card.querySelector('h3')?.textContent?.trim() || 'Producto';
			modalPrice.textContent = '$' + (parseFloat(card.dataset.price).toFixed(2));
			modalCategory.textContent = card.dataset.fullcategory || '--';
			modalSize.textContent = card.dataset.size || '--';
			modalColor.textContent = card.dataset.color || '--';
			modalImage.src = card.dataset.image || '';
			modalImage.alt = modalTitle.textContent;

			resetZoom();
			detailModal.classList.add('is-open');
			detailModal.setAttribute('aria-hidden', 'false');
			document.body.classList.add('modal-open');
		}

		function closeDetailModal() {
			if (!detailModal) return;
			detailModal.classList.remove('is-open');
			detailModal.setAttribute('aria-hidden', 'true');
			document.body.classList.remove('modal-open');
		}

		detailTriggers.forEach(trigger => {
			trigger.addEventListener('click', (event) => {
				event.preventDefault();
				const card = event.currentTarget.closest('.catalog-card');
				openDetailModal(card);
			});
		});

		detailModal?.addEventListener('click', (event) => {
			if (event.target.closest('[data-close-modal="true"]')) {
				closeDetailModal();
			}
		});

		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && detailModal?.classList.contains('is-open')) {
				closeDetailModal();
			}
		});

		zoomInBtn?.addEventListener('click', () => setZoom(currentZoom + 0.2));
		zoomOutBtn?.addEventListener('click', () => setZoom(currentZoom - 0.2));
		zoomResetBtn?.addEventListener('click', resetZoom);

		zoomWrap?.addEventListener('wheel', (event) => {
			event.preventDefault();
			setZoom(currentZoom + (event.deltaY < 0 ? 0.12 : -0.12));
		}, { passive: false });

		zoomWrap?.addEventListener('mousemove', (event) => {
			if (!modalImage || currentZoom <= 1) return;
			const rect = zoomWrap.getBoundingClientRect();
			const x = ((event.clientX - rect.left) / rect.width) * 100;
			const y = ((event.clientY - rect.top) / rect.height) * 100;
			modalImage.style.transformOrigin = `${x}% ${y}%`;
		});

		zoomWrap?.addEventListener('mouseleave', () => {
			if (!modalImage || currentZoom <= 1) return;
			modalImage.style.transformOrigin = 'center center';
		});

		cards.forEach(card => {
			card.addEventListener('mousemove', (e) => {
				const rect = card.getBoundingClientRect();
				const x = e.clientX - rect.left;
				const y = e.clientY - rect.top;
				card.style.setProperty('--mx', `${x}px`);
				card.style.setProperty('--my', `${y}px`);
			});
		});

		const revealObserver = new IntersectionObserver((entries) => {
			entries.forEach(entry => {
				if (entry.isIntersecting) {
					entry.target.classList.add('is-visible');
				}
			});
		}, { threshold: 0.14 });

		document.querySelectorAll('.reveal-up, .reveal-scale').forEach(item => revealObserver.observe(item));
	</script>

</body>
</html>
