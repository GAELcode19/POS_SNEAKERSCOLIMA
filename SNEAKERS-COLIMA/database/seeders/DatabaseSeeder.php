<?php

namespace Database\Seeders;

use App\Models\CartCancellation;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin General',
            'email' => 'admin@sneakerscolima.com',
            'password' => Hash::make('password'),
            'role' => 'gerencia',
            'status' => 'activo',
            'hired_at' => '2022-01-15',
            'weekly_hours_target' => 45,
        ]);

        $juan = User::create([
            'name' => 'Juan García',
            'email' => 'juan@sneakerscolima.com',
            'password' => Hash::make('password'),
            'role' => 'asesor_ventas',
            'status' => 'activo',
            'hired_at' => '2023-03-01',
            'weekly_hours_target' => 40,
        ]);

        $maria = User::create([
            'name' => 'María López',
            'email' => 'maria@sneakerscolima.com',
            'password' => Hash::make('password'),
            'role' => 'asesor_ventas',
            'status' => 'activo',
            'hired_at' => '2023-06-15',
            'weekly_hours_target' => 40,
        ]);

        $carlos = User::create([
            'name' => 'Carlos Méndez',
            'email' => 'carlos@sneakerscolima.com',
            'password' => Hash::make('password'),
            'role' => 'supervisor',
            'status' => 'descanso',
            'hired_at' => '2022-08-01',
            'weekly_hours_target' => 45,
        ]);

        $ana = User::create([
            'name' => 'Ana Rodríguez',
            'email' => 'ana@sneakerscolima.com',
            'password' => Hash::make('password'),
            'role' => 'asesor_ventas',
            'status' => 'desconectado',
            'hired_at' => '2024-01-10',
            'weekly_hours_target' => 40,
        ]);

        $diego = User::create([
            'name' => 'Diego Flores',
            'email' => 'diego@sneakerscolima.com',
            'password' => Hash::make('password'),
            'role' => 'cajero',
            'status' => 'activo',
            'hired_at' => '2023-11-01',
            'weekly_hours_target' => 40,
        ]);

        $weekStart = now()->startOfWeek();
        $employees = [$juan, $maria, $carlos, $ana, $diego];
        $weeklyHours = [38.5, 40, 28, 16, 32];

        foreach ($employees as $i => $emp) {
            $totalHours = $weeklyHours[$i];
            $daysWorked = min(5, (int) ceil($totalHours / 8));
            $hoursPerDay = $totalHours / $daysWorked;

            for ($d = 0; $d < $daysWorked; $d++) {
                $day = $weekStart->copy()->addDays($d);
                Shift::create([
                    'employee_id' => $emp->id,
                    'started_at' => $day->copy()->setHour(9),
                    'ended_at' => $day->copy()->setHour(9)->addHours($hoursPerDay),
                    'hours_logged' => round($hoursPerDay, 2),
                ]);
            }
        }

        $products = [
            ['name' => 'Nike Air Force 1 \'07', 'brand' => 'Nike', 'category' => 'Lifestyle', 'colorway' => 'Blanco/Blanco', 'price' => 2500, 'stock' => 5, 'sku' => 'NK-AF1-07-W'],
            ['name' => 'Jordan 1 Retro High OG', 'brand' => 'Jordan', 'category' => 'Basketball', 'colorway' => 'Chicago', 'price' => 3200, 'stock' => 3, 'sku' => 'JD-1RH-OG-CH'],
            ['name' => 'Adidas Yeezy Boost 350 V2', 'brand' => 'Adidas', 'category' => 'Lifestyle', 'colorway' => 'Zebra', 'price' => 4500, 'stock' => 2, 'sku' => 'AD-YZ350-ZB'],
            ['name' => 'Nike Dunk Low', 'brand' => 'Nike', 'category' => 'Lifestyle', 'colorway' => 'Panda', 'price' => 2200, 'stock' => 7, 'sku' => 'NK-DNK-LW-PN'],
            ['name' => 'New Balance 990v5', 'brand' => 'New Balance', 'category' => 'Running', 'colorway' => 'Grey', 'price' => 2800, 'stock' => 4, 'sku' => 'NB-990V5-GR'],
            ['name' => 'Adidas Ultraboost 22', 'brand' => 'Adidas', 'category' => 'Running', 'colorway' => 'Core Black', 'price' => 3100, 'stock' => 6, 'sku' => 'AD-UB22-CB'],
            ['name' => 'Nike Air Max 90', 'brand' => 'Nike', 'category' => 'Lifestyle', 'colorway' => 'Triple Black', 'price' => 2600, 'stock' => 4, 'sku' => 'NK-AM90-TB'],
            ['name' => 'Nike Kyrie 4', 'brand' => 'Nike', 'category' => 'Basketball', 'colorway' => 'Black/Red', 'price' => 3200, 'stock' => 2, 'sku' => 'NK-KY4-BR'],
            ['name' => 'Jordan 4 Retro', 'brand' => 'Jordan', 'category' => 'Basketball', 'colorway' => 'Military Black', 'price' => 3800, 'stock' => 3, 'sku' => 'JD-4R-MB'],
            ['name' => 'Nike Air Max 97', 'brand' => 'Nike', 'category' => 'Lifestyle', 'colorway' => 'Silver Bullet', 'price' => 3400, 'stock' => 5, 'sku' => 'NK-AM97-SB'],
            ['name' => 'New Balance 550', 'brand' => 'New Balance', 'category' => 'Lifestyle', 'colorway' => 'White/Green', 'price' => 2400, 'stock' => 8, 'sku' => 'NB-550-WG'],
            ['name' => 'Adidas Samba OG', 'brand' => 'Adidas', 'category' => 'Lifestyle', 'colorway' => 'White/Black', 'price' => 2200, 'stock' => 6, 'sku' => 'AD-SMB-WB'],
        ];

        foreach ($products as $p) {
            Product::create($p);
        }

        $allProducts = Product::all();
        $todaySales = [
            ['employee' => $juan, 'customer' => 'Roberto Sánchez', 'product_idx' => 1, 'size' => '28', 'status' => 'completada', 'is_online' => true, 'order' => 'SCO-1024'],
            ['employee' => $maria, 'customer' => 'Ana Martínez', 'product_idx' => 0, 'size' => '25', 'status' => 'en_proceso', 'is_online' => true, 'order' => 'SCO-1025'],
            ['employee' => $juan, 'customer' => 'Luis Ramírez', 'product_idx' => 2, 'size' => '27', 'status' => 'pendiente', 'is_online' => true, 'order' => 'SCO-1026'],
            ['employee' => $diego, 'customer' => null, 'product_idx' => 3, 'size' => '26', 'status' => 'completada', 'is_online' => false, 'order' => 'SCO-1027'],
            ['employee' => $juan, 'customer' => null, 'product_idx' => 4, 'size' => '28', 'status' => 'completada', 'is_online' => false, 'order' => 'SCO-1028'],
            ['employee' => $maria, 'customer' => null, 'product_idx' => 5, 'size' => '27', 'status' => 'completada', 'is_online' => false, 'order' => 'SCO-1029'],
            ['employee' => $juan, 'customer' => null, 'product_idx' => 6, 'size' => '26', 'status' => 'completada', 'is_online' => false, 'order' => 'SCO-1030'],
            ['employee' => $diego, 'customer' => null, 'product_idx' => 7, 'size' => '27', 'status' => 'completada', 'is_online' => false, 'order' => 'SCO-1031'],
            ['employee' => $maria, 'customer' => null, 'product_idx' => 0, 'size' => '25', 'status' => 'completada', 'is_online' => false, 'order' => 'SCO-1032'],
            ['employee' => $juan, 'customer' => 'Pedro Gómez', 'product_idx' => 9, 'size' => '27', 'status' => 'completada', 'is_online' => true, 'order' => 'SCO-1033'],
            ['employee' => $maria, 'customer' => null, 'product_idx' => 10, 'size' => '26', 'status' => 'completada', 'is_online' => false, 'order' => 'SCO-1034'],
            ['employee' => $juan, 'customer' => null, 'product_idx' => 11, 'size' => '28', 'status' => 'completada', 'is_online' => false, 'order' => 'SCO-1035'],
            ['employee' => $diego, 'customer' => null, 'product_idx' => 3, 'size' => '27', 'status' => 'completada', 'is_online' => false, 'order' => 'SCO-1036'],
            ['employee' => $juan, 'customer' => 'Sofía Torres', 'product_idx' => 8, 'size' => '25', 'status' => 'completada', 'is_online' => true, 'order' => 'SCO-1037'],
        ];

        $hour = 9;
        foreach ($todaySales as $s) {
            $product = $allProducts[$s['product_idx']];
            $sale = Sale::create([
                'order_number' => $s['order'],
                'employee_id' => $s['employee']->id,
                'total' => $product->price,
                'payment_method' => $s['is_online'] ? 'en_linea' : 'efectivo',
                'status' => $s['status'],
                'is_online' => $s['is_online'],
                'customer_name' => $s['customer'],
                'created_at' => today()->setHour($hour)->addMinutes(rand(0, 55)),
            ]);
            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'size' => $s['size'],
                'quantity' => 1,
                'price' => $product->price,
            ]);
            $hour = min(18, $hour + rand(0, 1));
        }

        CartCancellation::create([
            'employee_id' => $juan->id,
            'product_id' => $allProducts[7]->id,
            'size' => '27',
            'price' => $allProducts[7]->price,
            'reason' => 'Cliente cambió de opinión',
            'alert_level' => 'bajo',
            'created_at' => today()->setHour(14)->setMinute(30),
        ]);
        CartCancellation::create([
            'employee_id' => $juan->id,
            'product_id' => $allProducts[6]->id,
            'size' => '26',
            'price' => $allProducts[6]->price,
            'reason' => null,
            'alert_level' => 'alto',
            'created_at' => today()->setHour(11)->setMinute(15),
        ]);
        CartCancellation::create([
            'employee_id' => $diego->id,
            'product_id' => $allProducts[3]->id,
            'size' => '28',
            'price' => $allProducts[3]->price,
            'reason' => null,
            'alert_level' => 'medio',
            'created_at' => today()->setHour(15)->setMinute(45),
        ]);
    }
}
