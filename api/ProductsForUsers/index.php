<?php
// api\ProductsForUsers\index.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../../config/database.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM all_products_list ORDER BY id DESC LIMIT 12");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [];

    foreach($products as $product) {
        $price = isset($product['price']) ? floatval($product['price']) : 0;
        $oldPrice = isset($product['old_price']) ? floatval($product['old_price']) : 0;
        $discount = 0;

        // division-by-zero check lagaya
        if($oldPrice > $price && $oldPrice > 0) {
            $discount = round((($oldPrice - $price) / $oldPrice) * 100);
        }

        // --- IMAGE FIX START ---
        $image = 'https://via.placeholder.com/300x300?text=No+Image';
        
        // Admin panel se JSON text aata hai, use decode karke pehli image nikalenge
        if (!empty($product['images'])) {
            $gallery = json_decode($product['images'], true);
            if (is_array($gallery) && isset($gallery[0])) {
                // Agar add.php wala dynamic format hai to ['url'] nikalo, nahi to direct string le lo
                $image = is_array($gallery[0]) ? ($gallery[0]['url'] ?? $image) : $gallery[0];
            }
        }
        // --- IMAGE FIX END ---

        $response[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'brand' => $product['brand'] ?? 'ELDURATO',
            'price' => $price,
            'old_price' => $oldPrice,
            'discount' => $discount,
            'image' => $image,
            'description' => $product['description']
        ];
    }

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "data" => $response
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "डेटा फ़ेच करने में दिक्कत हुई: " . $e->getMessage()
    ]);
}
?>