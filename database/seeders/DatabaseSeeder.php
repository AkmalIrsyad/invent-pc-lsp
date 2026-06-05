<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create viewer user
        $viewer = User::create([
            'name' => 'Viewer User',
            'email' => 'viewer@viewer.com',
            'password' => bcrypt('password'),
            'role' => 'viewer',
            'email_verified_at' => now(),
        ]);

        // Create categories
        $categories = [
            ['name' => 'RAM', 'description' => 'Random Access Memory - DDR4, DDR5'],
            ['name' => 'SSD', 'description' => 'Solid State Drive - SATA, NVMe'],
            ['name' => 'HDD', 'description' => 'Hard Disk Drive - SATA'],
            ['name' => 'Motherboard', 'description' => 'Papan induk - Intel, AMD'],
            ['name' => 'Fan & Cooler', 'description' => 'Kipas pendingin & heatsink'],
            ['name' => 'PSU', 'description' => 'Power Supply Unit'],
            ['name' => 'VGA', 'description' => 'Video Graphics Adapter / GPU'],
            ['name' => 'Casing', 'description' => 'Casing / chassis komputer'],
            ['name' => 'Kabel', 'description' => 'Kabel data, power, HDMI, dll'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // Create sample products
        $products = [
            ['code' => 'RAM-001', 'name' => 'Kingston Fury DDR4 8GB', 'category_id' => 1, 'description' => 'DDR4 3200MHz CL16', 'initial_stock' => 50],
            ['code' => 'RAM-002', 'name' => 'Corsair Vengeance DDR5 16GB', 'category_id' => 1, 'description' => 'DDR5 5600MHz CL36', 'initial_stock' => 30],
            ['code' => 'SSD-001', 'name' => 'Samsung 980 Pro 1TB', 'category_id' => 2, 'description' => 'NVMe PCIe 4.0', 'initial_stock' => 40],
            ['code' => 'SSD-002', 'name' => 'WD Blue SN570 500GB', 'category_id' => 2, 'description' => 'NVMe PCIe 3.0', 'initial_stock' => 60],
            ['code' => 'HDD-001', 'name' => 'Seagate Barracuda 1TB', 'category_id' => 3, 'description' => 'SATA 7200RPM', 'initial_stock' => 45],
            ['code' => 'HDD-002', 'name' => 'WD Blue 2TB', 'category_id' => 3, 'description' => 'SATA 5400RPM', 'initial_stock' => 25],
            ['code' => 'MOBO-001', 'name' => 'ASUS ROG Strix B550-F', 'category_id' => 4, 'description' => 'AMD AM4, ATX', 'initial_stock' => 20],
            ['code' => 'MOBO-002', 'name' => 'MSI MAG B660M Mortar', 'category_id' => 4, 'description' => 'Intel LGA1700, mATX', 'initial_stock' => 15],
            ['code' => 'FAN-001', 'name' => 'Noctua NH-D15', 'category_id' => 5, 'description' => 'Tower CPU Cooler', 'initial_stock' => 35],
            ['code' => 'FAN-002', 'name' => 'DeepCool AK620', 'category_id' => 5, 'description' => 'Dual Tower Cooler', 'initial_stock' => 28],
            ['code' => 'PSU-001', 'name' => 'Corsair RM750x', 'category_id' => 6, 'description' => '750W 80+ Gold Modular', 'initial_stock' => 22],
            ['code' => 'VGA-001', 'name' => 'NVIDIA RTX 4060', 'category_id' => 7, 'description' => '8GB GDDR6', 'initial_stock' => 10],
            ['code' => 'VGA-002', 'name' => 'AMD RX 7600', 'category_id' => 7, 'description' => '8GB GDDR6', 'initial_stock' => 12],
            ['code' => 'CASE-001', 'name' => 'NZXT H510 Flow', 'category_id' => 8, 'description' => 'ATX Mid Tower', 'initial_stock' => 18],
            ['code' => 'KBL-001', 'name' => 'Kabel SATA Data Pack 5pcs', 'category_id' => 9, 'description' => 'SATA III 6Gbps', 'initial_stock' => 100],
        ];

        foreach ($products as $prod) {
            Product::create($prod);
        }

        // Create sample transactions for the current month
        $sampleTransactions = [
            ['product_id' => 1, 'user_id' => 1, 'type' => 'masuk', 'quantity' => 20, 'notes' => 'Restok dari supplier', 'transaction_date' => now()->subDays(10)],
            ['product_id' => 3, 'user_id' => 1, 'type' => 'keluar', 'quantity' => 5, 'notes' => 'Pengiriman ke cabang A', 'transaction_date' => now()->subDays(9)],
            ['product_id' => 5, 'user_id' => 1, 'type' => 'masuk', 'quantity' => 15, 'notes' => 'Stok baru dari distributor', 'transaction_date' => now()->subDays(8)],
            ['product_id' => 7, 'user_id' => 1, 'type' => 'keluar', 'quantity' => 3, 'notes' => 'Perakitan PC custom', 'transaction_date' => now()->subDays(7)],
            ['product_id' => 12, 'user_id' => 1, 'type' => 'keluar', 'quantity' => 2, 'notes' => 'Penjualan retail', 'transaction_date' => now()->subDays(6)],
            ['product_id' => 2, 'user_id' => 1, 'type' => 'masuk', 'quantity' => 10, 'notes' => 'Restok DDR5', 'transaction_date' => now()->subDays(5)],
            ['product_id' => 9, 'user_id' => 1, 'type' => 'keluar', 'quantity' => 8, 'notes' => 'Order customer bulk', 'transaction_date' => now()->subDays(4)],
            ['product_id' => 11, 'user_id' => 1, 'type' => 'masuk', 'quantity' => 5, 'notes' => 'Restok PSU', 'transaction_date' => now()->subDays(3)],
            ['product_id' => 4, 'user_id' => 1, 'type' => 'keluar', 'quantity' => 10, 'notes' => 'Pengiriman cabang B', 'transaction_date' => now()->subDays(2)],
            ['product_id' => 13, 'user_id' => 1, 'type' => 'masuk', 'quantity' => 8, 'notes' => 'Restok VGA AMD', 'transaction_date' => now()->subDays(1)],
            ['product_id' => 6, 'user_id' => 1, 'type' => 'keluar', 'quantity' => 5, 'notes' => 'Penjualan HDD', 'transaction_date' => now()],
            ['product_id' => 14, 'user_id' => 1, 'type' => 'masuk', 'quantity' => 12, 'notes' => 'Restok casing', 'transaction_date' => now()],
        ];

        // Also add some transactions for previous months for chart data
        for ($i = 1; $i <= 6; $i++) {
            $sampleTransactions[] = [
                'product_id' => rand(1, 15),
                'user_id' => 1,
                'type' => 'masuk',
                'quantity' => rand(5, 30),
                'notes' => 'Restok bulan lalu',
                'transaction_date' => now()->subMonths($i)->subDays(rand(1, 15)),
            ];
            $sampleTransactions[] = [
                'product_id' => rand(1, 15),
                'user_id' => 1,
                'type' => 'keluar',
                'quantity' => rand(3, 15),
                'notes' => 'Penjualan bulan lalu',
                'transaction_date' => now()->subMonths($i)->subDays(rand(1, 15)),
            ];
        }

        foreach ($sampleTransactions as $txn) {
            Transaction::create($txn);
        }
    }
}
