<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Canned Goods & Processed Meat',
                'description' => 'Corned beef, tuna, sardines, meat loaf, sausage, and potted meats (e.g., Century Tuna, Argentina, CDO, Purefoods).'
            ],
            [
                'name' => 'Instant Noodles & Soups',
                'description' => 'Pancit Canton, instant mami, cup noodles, and soup mixes (e.g., Lucky Me!, Payless, Knorr).'
            ],
            [
                'name' => 'Powdered & Sachet Beverages',
                'description' => '3-in-1 coffee sachets, powdered milk, chocolate drinks, and juice mixes (e.g., Nescafé, Milo, Bear Brand, Tang).'
            ],
            [
                'name' => 'Condiments, Sauces & Seasonings',
                'description' => 'Soy sauce, vinegar, fish sauce (patis), banana ketchup, and seasoning granules (e.g., Datu Puti, Silver Swan, Magic Sarap, Mang Tomas).'
            ],
            [
                'name' => 'Snacks & Chichiriya',
                'description' => 'Local potato chips, corn snacks, nuts, crackers, and native snacks (e.g., Piattos, Nova, Boy Bawang, Oishi).'
            ],
            [
                'name' => 'Biscuits, Bread & Bakery',
                'description' => 'Crackers, cookies, wafer biscuits, loaf bread, and snack cakes (e.g., SkyFlakes, Fita, Rebisco, Fudgee Barr, Gardenia).'
            ],
            [
                'name' => 'Soft Drinks & Ready-to-Drink',
                'description' => 'Carbonated beverages, bottled teas, energy drinks, and sports drinks (e.g., Coca-Cola, Royal, Sprite, C2, Cobra).'
            ],
            [
                'name' => 'Dairy, Cheese & Spreads',
                'description' => 'Processed cheese blocks, butter, margarine, and sandwich spreads (e.g., Eden Cheese, Star Margarine, Cheez Whiz, Lady\'s Choice).'
            ],
            [
                'name' => 'Rice, Grains & Cooking Staples',
                'description' => 'Sacked/packed rice, sugar, flour, cooking oil, and salt (e.g., Sinandomeng, Jasmine, Minola, Golden Fiesta).'
            ],
            [
                'name' => 'Personal Care & Toiletries',
                'description' => 'Shampoo sachets, bath soaps, toothpaste, lotion, and feminine hygiene (e.g., Safeguard, Cream Silk, Colgate, Palmolive).'
            ],
            [
                'name' => 'Laundry & Home Cleaning',
                'description' => 'Detergent powders, fabric conditioners, dishwashing liquids, and bleach (e.g., Surf, Tide, Downy, Joy, Zonrox).'
            ],
            [
                'name' => 'Frozen Goods',
                'description' => 'Hotdogs, longganisa, tocino, lumpia, nuggets, and ice cream (e.g., Tender Juicy, CDO Karne Norte, Selecta).'
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                ]
            );
        }
    }
}