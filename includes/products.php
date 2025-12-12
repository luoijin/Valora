<?php
// includes/products.php
$products = [
    1 => [
        'id' => 1,
        'title' => 'Off-Shoulder Gown',
        'slug' => 'off-shoulder-gown',
        'price' => 55000,
        'currency' => '₱',
        'category' => 'Bridal Gown',
        'rating' => 4.6,
        'reviews' => 20,
        'image' => 'assets/images/gown1.jpg',
        'images' => [
            '../assets/images/gown1.jpg',
            '../assets/images/gown2.jpg',
            '../assets/images/gown3.jpg'
        ],
        'description' => 'Elegant off-shoulder bridal gown with tulle overlay and delicate embroidery. Dry clean only.',
        'fabric' => 'Satin',
        'stock' => 5
    ],
    2 => [
        'id' => 2,
        'title' => 'Classic Ballgown',
        'slug' => 'classic-ballgown',
        'price' => 49500,
        'currency' => '₱',
        'category' => 'Bridal Gown',
        'rating' => 4.8,
        'reviews' => 12,
        'image' => 'assets/images/gown2.jpg',
        'images' => [
            '../assets/images/gown2.jpg',
            '../assets/images/gown1.jpg'
        ],
        'description' => 'Timeless ballgown perfect for a fairy-tale ceremony.',
        'fabric' => 'Chiffon',
        'stock' => 8
    ],
    3 => [
        'id' => 3,
        'title' => 'Lace Mermaid Dress',
        'slug' => 'lace-mermaid-dress',
        'price' => 68500,
        'currency' => '₱',
        'category' => 'Wedding Dress',
        'rating' => 4.7,
        'reviews' => 9,
        'image' => 'assets/images/gown3.jpg',
        'images' => [
            '../assets/images/gown3.jpg',
            '../assets/images/gown1.jpg'
        ],
        'description' => 'Figure-hugging mermaid dress with lace detailing.',
        'fabric' => 'Lace',
        'stock' => 4
    ],
    // Add more products here...
];
