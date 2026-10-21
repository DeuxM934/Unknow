<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/lib.php';
$products = load_products();
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
$notice = '';
$page = $_GET['page'] ?? 'shop';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        if ($id && isset($products[$id]) && $products[$id]['stock'] > 0) {
            $_SESSION['cart'][$id] = min(20, (int)($_SESSION['cart'][$id] ?? 0) + 1);
            $notice = 'Article ajouté au panier.';
        } else $notice = 'Cet article est indisponible.';
        $page = 'shop';
    } elseif ($action === 'update') {
        foreach (($_POST['qty'] ?? []) as $id => $qty) {
            $id = (int)$id; $qty = max(0, min(20, (int)$qty));
            if (!isset($products[$id])) continue;
            if ($qty === 0) unset($_SESSION['cart'][$id]);
            else $_SESSION['cart'][$id] = min($qty, (int)$products[$id]['stock']);
        }
        $notice = 'Panier mis à jour.'; $page = 'cart';
    } elseif ($action === 'remove') {
        unset($_SESSION['cart'][(int)($_POST['product_id'] ?? 0)]);
        $notice = 'Article retiré.'; $page = 'cart';
    }
}
$count = array_sum($_SESSION['cart']);
$total = 0;
foreach ($_SESSION['cart'] as $id => $qty) if (isset($products[$id])) $total += $products[$id]['price'] * $qty;
function euro(float $n): string { return number_format($n, 2, ',', ' ') . ' €'; }
function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="UNKNOW — vêtements streetwear, pièces sélectionnées et silhouettes affirmées.">
<title>UNKNOW® — Streetwear</title><link rel="stylesheet" href="/style.css">
</head>
<body>
<header class="header">
  <a class="brand" href="index.php">UNKNOW<span>®</span></a>
  <nav><a href="index.php#shop">SHOP</a><a href="index.php#story">MANIFESTO</a><a class="cart-link" href="index.php?page=cart">BAG (<?= (int)$count ?>)</a></nav>
</header>
<?php if ($notice): ?><div class="notice"><?= h($notice) ?></div><?php endif; ?>

<?php if ($page === 'shop'): ?>
<section class="hero">
  <div class="hero-noise"></div>
  <div class="hero-copy"><p class="eyebrow">DROP 001 — EVERYDAY UNIFORM</p><h1>NOT MADE<br>TO <em>FIT IN.</em></h1><p class="hero-sub">Des essentiels streetwear. Une attitude à part. Trouve ta propre silhouette.</p><a class="button button-light" href="#shop">SHOP THE DROP <span>↘</span></a></div>
  <div class="hero-visual"><div class="hero-circle"></div><div class="poster"><span>U.</span><b>UN<br>KNOWN</b><small>OUTSIDE THE LINES<br>EST. 2026</small></div><div class="hero-stamp">NO RULES<br>JUST FORM.</div><div class="hero-index">001 / 026</div></div>
  <div class="hero-bottom">INDEPENDENT STREETWEAR <span>—</span> BUILT FOR THE UNKNOWN</div>
</section>
<section class="ticker"><div>UNKNOW® &nbsp; / &nbsp; MOVE DIFFERENT &nbsp; / &nbsp; UNKNOW® &nbsp; / &nbsp; MOVE DIFFERENT &nbsp; / &nbsp; UNKNOW® &nbsp; / &nbsp; MOVE DIFFERENT &nbsp; / &nbsp;</div></section>
<section class="shop section" id="shop">
  <div class="section-head"><div><p class="eyebrow">THE CURRENT DROP</p><h2>NEW ARRIVALS<span> (<?= count($products) ?>)</span></h2></div><p>Pièces sélectionnées pour le quotidien.<br>Quantités limitées selon les stocks.</p></div>
  <div class="products">
  <?php foreach ($products as $id => $p): ?>
    <article class="product">
      <div class="product-image <?= h($p['tone'] ?? 'tone-black') ?>">
        <?php if (!empty($p['image'])): ?><img src="<?= h($p['image']) ?>" alt="<?= h($p['name']) ?>">
        <?php else: ?><div class="garment-art"><span><?= h($p['mark'] ?? 'U.') ?></span><b><?= h($p['art'] ?? 'UNKNOW') ?></b></div><?php endif; ?>
        <span class="product-tag"><?= h($p['tag'] ?? 'DROP 001') ?></span>
        <?php if ((int)$p['stock'] <= 0): ?><span class="sold-out">SOLD OUT</span><?php endif; ?>
      </div>
      <div class="product-meta"><div><h3><?= h($p['name']) ?></h3><p><?= h($p['category']) ?></p></div><strong><?= euro((float)$p['price']) ?></strong></div>
      <form method="post"><input type="hidden" name="action" value="add"><input type="hidden" name="product_id" value="<?= (int)$id ?>"><button class="add-button" <?= (int)$p['stock'] <= 0 ? 'disabled' : '' ?>><?= (int)$p['stock'] <= 0 ? 'ÉPUISÉ' : 'AJOUTER AU PANIER +' ?></button></form>
    </article>
  <?php endforeach; ?>
  </div>
</section>
<section class="manifesto section" id="story"><p class="eyebrow">OUR MANIFESTO / 001</p><h2>DON'T FOLLOW<br>THE <em>FRAME.</em></h2><p>UNKNOW est un projet indépendant inspiré par la rue, les contrastes et les gens qui tracent leur propre chemin. Remplace ce texte par l'histoire réelle de ta marque.</p><a href="mailto:contact@unknow.example" class="text-link">CONTACT THE BRAND ↗</a></section>

<?php elseif ($page === 'cart'): ?>
<section class="inner-page"><p class="eyebrow">YOUR SELECTION</p><h1>YOUR BAG<span>.</span></h1>
<?php if (!$count): ?><p>Ton panier est vide pour le moment.</p><a class="button button-light" href="index.php#shop">RETOURNER AU SHOP</a>
<?php else: ?>
<form method="post"><input type="hidden" name="action" value="update"><div class="cart-items">
<?php foreach ($_SESSION['cart'] as $id => $qty): if (!isset($products[$id])) continue; $p=$products[$id]; ?>
<div class="cart-item"><div class="cart-thumb <?= h($p['tone'] ?? 'tone-black') ?>"><?= h($p['mark'] ?? 'U.') ?></div><div><h3><?= h($p['name']) ?></h3><p><?= euro((float)$p['price']) ?> · <?= h($p['category']) ?></p><small>Stock disponible : <?= (int)$p['stock'] ?></small></div><label>QTÉ <input type="number" name="qty[<?= (int)$id ?>]" min="0" max="<?= min(20, (int)$p['stock']) ?>" value="<?= (int)$qty ?>"></label><strong><?= euro((float)$p['price'] * $qty) ?></strong><button class="remove" type="submit" form="remove-<?= (int)$id ?>">RETIRER</button></div>
<?php endforeach; ?></div><button class="add-button" type="submit">METTRE À JOUR LE PANIER</button></form>
<?php foreach ($_SESSION['cart'] as $id=>$qty): ?><form id="remove-<?= (int)$id ?>" method="post" class="hidden"><input type="hidden" name="action" value="remove"><input type="hidden" name="product_id" value="<?= (int)$id ?>"></form><?php endforeach; ?>
<div class="total-line"><span>SOUS-TOTAL</span><strong><?= euro((float)$total) ?></strong></div><p class="muted">Livraison calculée lors de la commande. Le paiement n'est pas encore activé dans cette version.</p><a class="button button-light" href="index.php?page=checkout">PASSER À LA COMMANDE ↗</a>
<?php endif; ?></section>

<?php elseif ($page === 'checkout'): ?>
<section class="inner-page narrow"><p class="eyebrow">CHECKOUT / TEST MODE</p><h1>FINALISER<span>.</span></h1><p class="warning">Les paiements ne sont pas encore connectés. Cette page est un aperçu : aucune donnée bancaire ne doit être saisie.</p><div class="total-line"><span>TOTAL INDICATIF</span><strong><?= euro((float)$total) ?></strong></div><div class="payment-options"><button disabled>PAYPAL — À CONFIGURER</button><button disabled>CARTE BANCAIRE — À CONFIGURER</button></div><p class="muted">Avant d'accepter des commandes réelles, il faut connecter les prestataires côté serveur et créer une gestion de commandes sécurisée.</p></section>

<?php else: ?>
<section class="inner-page"><h1>PAGE INTROUVABLE<span>.</span></h1><a class="button button-light" href="index.php">RETOUR AU SHOP</a></section>
<?php endif; ?>
<footer class="footer"><a class="brand" href="index.php">UNKNOW<span>®</span></a><div><a href="mailto:contact@unknow.example">CONTACT</a><a href="index.php#story">MANIFESTO</a><a href="admin/login.php">ADMIN</a></div><small>© <?= date('Y') ?> UNKNOW® — Prototype. Mentions légales et CGV à ajouter avant toute vente réelle.</small></footer>
</body></html>
