<?php
declare(strict_types=1);

function load_products(): array {
    $file = __DIR__ . '/data/products.json';
    if (!is_file($file)) return [];
    $raw = file_get_contents($file);
    $data = json_decode($raw ?: '[]', true);
    if (!is_array($data)) return [];
    $products = [];
    foreach ($data as $p) {
        if (!is_array($p) || !isset($p['id'], $p['name'], $p['price'])) continue;
        $products[(int)$p['id']] = $p;
    }
    return $products;
}
function save_products(array $products): bool {
    $file = __DIR__ . '/data/products.json';
    $values = array_values($products);
    $json = json_encode($values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return $json !== false && file_put_contents($file, $json, LOCK_EX) !== false;
}
function esc(string $v): string { return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
