<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edukasi & Info - SIG Minahasa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50">

    <!-- Navbar -->
    <nav class="bg-blue-900 text-white shadow-lg h-16 flex items-center justify-between px-6">
        <div class="flex items-center space-x-3">
            <i data-lucide="map" class="w-6 h-6"></i>
            <div>
                <h1 class="font-bold text-lg leading-none">SIG Minahasa</h1>
                <p class="text-xs text-blue-200">Tanggap Bencana & Evakuasi</p>
            </div>
        </div>

        <div class="hidden md:flex items-center space-x-6">
            <a href="index.php" class="hover:text-blue-300 transition text-sm font-medium">Peta Interaktif</a>
            <span class="text-sm font-medium">Edukasi & Info</span>
            <a href="login.php" class="hover:text-yellow-400 transition text-sm font-medium text-yellow-300">Dashboard Admin</a>
        </div>

        <div class="flex items-center space-x-4">
            <a href="login.php" class="bg-blue-700 hover:bg-blue-600 px-4 py-1.5 rounded text-sm transition">Login Admin</a>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto py-12 px-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Edukasi Bencana & Informasi</h1>
        <p class="text-gray-500 mb-10">Panduan kesiapsiagaan untuk warga Minahasa.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-red-50 p-6 rounded-xl border border-red-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="bg-red-100 p-2 rounded-full text-red-600"><i data-lucide="cloud-rain" class="w-6 h-6"></i></div>
                    <h2 class="text-xl font-bold text-gray-800">Siaga Banjir</h2>
                </div>
                <ul class="space-y-2 text-gray-700 text-sm">
                    <li class="flex gap-2"><i data-lucide="check" class="w-4 h-4 text-green-500"></i> Matikan aliran listrik jika air mulai naik.</li>
                    <li class="flex gap-2"><i data-lucide="check" class="w-4 h-4 text-green-500"></i> Amankan dokumen penting di tempat tinggi.</li>
                    <li class="flex gap-2"><i data-lucide="check" class="w-4 h-4 text-green-500"></i> Pantau info dari BPBD Minahasa.</li>
                    <li class="flex gap-2"><i data-lucide="check" class="w-4 h-4 text-green-500"></i> Segera evakuasi ke titik kumpul sekolah terdekat.</li>
                </ul>
            </div>

            <div class="bg-yellow-50 p-6 rounded-xl border border-yellow-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="bg-yellow-100 p-2 rounded-full text-yellow-600"><i data-lucide="mountain" class="w-6 h-6"></i></div>
                    <h2 class="text-xl font-bold text-gray-800">Siaga Longsor</h2>
                </div>
                <ul class="space-y-2 text-gray-700 text-sm">
                    <li class="flex gap-2"><i data-lucide="check" class="w-4 h-4 text-green-500"></i> Waspada retakan tanah setelah hujan lebat.</li>
                    <li class="flex gap-2"><i data-lucide="check" class="w-4 h-4 text-green-500"></i> Jauhi area tebing curam.</li>
                    <li class="flex gap-2"><i data-lucide="check" class="w-4 h-4 text-green-500"></i> Jika terdengar gemuruh, segera lari ke area terbuka.</li>
                </ul>
            </div>
        </div>

        <div class="mt-12">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Tentang Sistem Ini</h2>
            <p class="text-gray-600 leading-relaxed mb-4">
                Aplikasi ini adalah implementasi dari Proposal "Sistem Informasi Tanggap Bencana Berbasis SIG" oleh Kelompok 5.
                Sistem ini menggabungkan algoritma <strong>Brute-Force Search</strong> untuk pencarian lokasi terdekat secara cepat,
                dan <strong>Contraction Hierarchies</strong> (via OSRM) untuk kalkulasi rute jalan raya yang akurat.
            </p>
            <p class="text-gray-600 leading-relaxed">
                <strong>Tim Pengembang:</strong><br>
                1. Fanuel J. Palandeng<br>
                2. David V. Baridji<br>
                3. Daud A. Lendo
            </p>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
