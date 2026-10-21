<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../lib.php';
if (empty($_SESSION['admin_authenticated'])) { header('Location: login.php'); exit; }
$products = load_products();
$message = '';
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) {
        $message = 'Jeton de sécurité invalide. Recharge la page.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'save') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim((string)($_POST['name'] ?? ''));
            $category = trim((string)($_POST['category'] ?? ''));
            $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);
            $stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT);
            $tone = preg_replace('/[^a-z0-9-]/', '', strtolower((string)($_POST['tone'] ?? 'tone-black')));
            $tag = trim((string)($_POST['tag'] ?? 'DROP 001'));
            $image = trim((string)($_POST['image'] ?? ''));
            if ($name === '' || $category === '' || $price === false || $price < 0 || $stock === false || $stock < 0 || ($image !== '' && !preg_match('~^https://~i', $image))) {
                $message = 'Vérifie les champs. Les images doivent être une URL HTTPS.';
            } else {
                if ($id <= 0) $id = empty($products) ? 1 : max(array_keys($products)) + 1;
                $products[$id] = ['id'=>$id,'name'=>$name,'price'=>(float)$price,'category'=>$category,'stock'=>(int)$stock,'tone'=>$tone ?: 'tone-black','tag'=>$tag,'mark'=>'U.','art'=>'UNKNOW','image'=>$image];
                $message = save_products($products) ? 'Produit enregistré.' : 'Échec de sauvegarde. Vérifie les droits d’écriture du dossier data/.';
                $products = load_products();
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            unset($products[$id]);
            $message = save_products($products) ? 'Produit supprimé.' : 'Échec de sauvegarde.';
            $products = load_products();
        }
    }
}
$editId = (int)($_GET['edit'] ?? 0);
$edit = $products[$editId] ?? ['id'=>0,'name'=>'','price'=>'','category'=>'T-SHIRTS','stock'=>0,'tone'=>'tone-black','tag'=>'DROP 001','image'=>''];
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>UNKNOW® — Dashboard</title><link rel="stylesheet" href="../style.css"></head><body><header class="header"><a class="brand" href="../index.php">UNKNOW<span>®</span></a><nav><a href="../index.php">VOIR LE SHOP ↗</a><a href="logout.php">DÉCONNEXION</a></nav></header><section class="admin-wrap"><p class="eyebrow">CONTROL ROOM / PRIVATE</p><h1>PRODUCTS<span>.</span></h1><?php if ($message): ?><p class="notice"><?= esc($message) ?></p><?php endif; ?>
<div class="admin-grid"><form method="post" class="admin-form"><h2><?= $edit['id'] ? 'MODIFIER LE PRODUIT' : 'AJOUTER UN PRODUIT' ?></h2><input type="hidden" name="csrf" value="<?= esc($_SESSION['csrf']) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><label>NOM<input name="name" value="<?= esc((string)$edit['name']) ?>" required></label><label>CATÉGORIE<input name="category" value="<?= esc((string)$edit['category']) ?>" required></label><label>PRIX (€)<input type="number" name="price" min="0" step="0.01" value="<?= esc((string)$edit['price']) ?>" required></label><label>STOCK<input type="number" name="stock" min="0" step="1" value="<?= (int)$edit['stock'] ?>" required></label><label>COULEUR VISUELLE<input name="tone" value="<?= esc((string)$edit['tone']) ?>" placeholder="tone-black"></label><label>ÉTIQUETTE<input name="tag" value="<?= esc((string)$edit['tag']) ?>"></label><label>URL DE PHOTO (HTTPS)<input name="image" type="url" value="<?= esc((string)$edit['image']) ?>" placeholder="https://..."></label><button class="button button-light">ENREGISTRER ↗</button><a class="text-link" href="dashboard.php">NOUVEAU PRODUIT</a></form>
<div><h2>CATALOGUE (<?= count($products) ?>)</h2><div class="admin-products"><?php foreach ($products as $p): ?><article class="admin-product"><div class="admin-thumb <?= esc((string)($p['tone'] ?? 'tone-black')) ?>"><?= esc((string)($p['mark'] ?? 'U.')) ?></div><div class="admin-product-info"><strong><?= esc((string)$p['name']) ?></strong><span><?= number_format((float)$p['price'],2,',',' ') ?> € · Stock : <?= (int)$p['stock'] ?></span><div class="admin-actions"><a href="?edit=<?= (int)$p['id'] ?>">MODIFIER</a><form method="post" onsubmit="return confirm('Supprimer ce produit ?')"><input type="hidden" name="csrf" value="<?= esc($_SESSION['csrf']) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><button>SUPPRIMER</button></form></div></div></article><?php endforeach; ?></div></div></div>
<p class="warning">Important : ce tableau de bord sauvegarde les produits dans un fichier JSON. Sur Vercel, le système de fichiers est généralement éphémère : les changements ne sont pas persistants. Pour une boutique hébergée, connecte une base de données (ex. PostgreSQL/Supabase) et un stockage d'images.</p></section></body></html>
