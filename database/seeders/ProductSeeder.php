<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'id' => 1,
                'category_id' => 1,
                'name' => 'Ryzen 5 7500F',
                'brand' => 'AMD',
                'warranty_duration' => 36,
                'product_image_url' => 'products/XMrgh6Fvkuge6SWBKdCV0jkyudORQYX82IxRyfEp.jpg',
                'service_center_name' => 'AMD Service Center Manila',
                'service_center_address' => 'Ayala Ave, Makati, Metro Manila',
            ],
            [
                'id' => 2,
                'category_id' => 3,
                'name' => 'RTX 4060 TI',
                'brand' => 'GIGABYTE',
                'warranty_duration' => 36,
                'product_image_url' => 'products/j4AfbTexR6Vuf0UZGEfvzqEV9NLHz4n0uMitkrCR.png',
                'service_center_name' => 'GIGABYTE Service Center Cebu',
                'service_center_address' => 'Osmeña Blvd, Cebu City, Cebu',
            ],
            [
                'id' => 3,
                'category_id' => 5,
                'name' => 'Razer Blackshark V2',
                'brand' => 'Razer',
                'warranty_duration' => 24,
                'product_image_url' => 'products/LwFLUtLFXSbxyi9ZSYQDwr3KIFAq3BqUyVHD6vb2.png',
                'service_center_name' => 'Razer Service Center Manila',
                'service_center_address' => 'Ortigas Center, Pasig, Metro Manila',
            ],
            [
                'id' => 4,
                'category_id' => 5,
                'name' => 'Unveils Mercury V75 Pro Keyboard',
                'brand' => 'GravaStar',
                'warranty_duration' => 12,
                'product_image_url' => 'products/4V4aQe2ObzmiGiwlPK4tiPm66VoPfcGtWFwASSF9.png',
                'service_center_name' => 'GravStar Service Center Cebu',
                'service_center_address' => 'Colon St, Cebu City, Cebu',
            ],
            [
                'id' => 5,
                'category_id' => 2,
                'name' => 'Zephyrus M16',
                'brand' => 'ROG',
                'warranty_duration' => 48,
                'product_image_url' => 'products/8tm4FYpJRKM076uRsmfU6rSiLCUllWQEhGYtvXDg.jpg',
                'service_center_name' => 'ROG Service Center Manila',
                'service_center_address' => 'Taft Ave, Manila, Metro Manila',
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(['id' => $product['id']], $product);
        }
    }
}
