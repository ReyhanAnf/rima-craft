<?php

declare(strict_types=1);

return [
    'categories' => [
        [
            'title' => 'Dashboard',
            'items' => [
                [
                    'name' => 'Dashboard',
                    'route' => 'dashboard',
                    'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                    'permission' => 'view-dashboard',
                ],
            ]
        ],
        [
            'title' => 'Transaksi & Penjualan',
            'items' => [
                [
                    'name' => 'Penjualan',
                    'route' => 'sales.index',
                    'icon' => 'M9 8h6m-6 2h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                    'permission' => 'view-sales',
                ],
                [
                    'name' => 'Pesanan Online',
                    'route' => 'orders.index',
                    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
                    'permission' => 'view-orders',
                ],
            ]
        ],
        [
            'title' => 'Operasional & Produksi',
            'items' => [
                [
                    'name' => 'Bahan Baku',
                    'route' => 'materials.index',
                    'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
                    'permission' => 'view-materials',
                ],
                [
                    'name' => 'Katalog Produk',
                    'route' => 'products.index',
                    'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                    'permission' => 'view-products',
                ],
                [
                    'name' => 'Produksi',
                    'route' => 'productions.index',
                    'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
                    'permission' => 'view-productions',
                ],
                [
                    'name' => 'Penyesuaian Stok',
                    'route' => 'stock-adjustments.index',
                    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                    'permission' => 'adjust-stock',
                ],
            ]
        ],
        [
            'title' => 'Keuangan & Data',
            'items' => [
                [
                    'name' => 'Buku Kas',
                    'route' => 'finance.index',
                    'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                    'permission' => 'view-finance',
                ],
                [
                    'name' => 'Pembelian',
                    'route' => 'purchases.index',
                    'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
                    'permission' => 'view-purchases',
                ],
                [
                    'name' => 'Buku Kontak',
                    'route' => 'contacts.index',
                    'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                    'permission' => 'view-contacts',
                ],
                [
                    'name' => 'Galeri Foto',
                    'route' => 'galleries.index',
                    'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
                    'permission' => 'view-gallery',
                ],
            ]
        ],
        [
            'title' => 'Pengaturan',
            'items' => [
                [
                    'name' => 'Daftar Pengguna',
                    'route' => 'users.index',
                    'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                    'permission' => 'manage-users',
                ],
                [
                    'name' => 'Metode Pembayaran',
                    'route' => 'payment-methods.index',
                    'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
                    'permission' => 'view-settings',
                ],
                [
                    'name' => 'Pengaturan Web',
                    'route' => 'settings.index',
                    'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065',
                    'permission' => 'view-settings',
                ],
            ]
        ]
    ]
];
