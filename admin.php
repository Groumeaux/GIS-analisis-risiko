<?php
require_once 'config.php';
requireLogin();

// 1. Connect to Database
$pdo = getDBConnection();

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';

// 2. Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'] ?? '';
    $action = $_POST['action'] ?? '';

    $validTables = ['banjir', 'longsor', 'sekolah', 'rs'];
    if (!in_array($category, $validTables)) {
        $message = 'Kategori tidak valid!';
        $messageType = 'error';
    } else {
        if ($action === 'add') {
            $nama = trim($_POST['nama'] ?? '');
            $lat = $_POST['lat'] ?? '';
            $lng = $_POST['lng'] ?? '';
            $keterangan = trim($_POST['keterangan'] ?? '');
            $newId = uniqid($category . '_');

            if ($nama && $lat && $lng) {
                try {
                    if ($category === 'banjir') {
                        $level = $_POST['level'] ?? 'Sedang';
                        $tanggal = date('Y-m-d');
                        $stmt = $pdo->prepare("INSERT INTO banjir (id, nama, lat, lng, keterangan, level, tanggal) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$newId, $nama, $lat, $lng, $keterangan, $level, $tanggal]);
                    } elseif ($category === 'longsor') {
                        $level = $_POST['level'] ?? 'Sedang';
                        $stmt = $pdo->prepare("INSERT INTO longsor (id, nama, lat, lng, keterangan, level) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$newId, $nama, $lat, $lng, $keterangan, $level]);
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO $category (id, nama, lat, lng, keterangan) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$newId, $nama, $lat, $lng, $keterangan]);
                    }
                    $message = 'Data berhasil disimpan!';
                    $messageType = 'success';
                } catch (PDOException $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'error';
                }
            } else {
                $message = 'Nama dan Koordinat wajib diisi!';
                $messageType = 'error';
            }
        } elseif ($action === 'delete' && isset($_POST['id'])) {
            $id = $_POST['id'];
            try {
                $stmt = $pdo->prepare("DELETE FROM $category WHERE id = ?");
                $stmt->execute([$id]);
                $message = 'Data berhasil dihapus!';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'Gagal menghapus: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// 3. Configuration & Data Fetching
$currentTab = $_GET['tab'] ?? 'banjir';
$validTabs = ['banjir', 'longsor', 'sekolah', 'rs'];
if (!in_array($currentTab, $validTabs)) $currentTab = 'banjir';

// --- COLOR CONFIGURATION ---
// Defines the specific styling for each category to match index.php
$categoryConfig = [
    'banjir' => [
        'label' => 'Banjir', 
        'icon' => 'cloud-rain', 
        'tab_active' => 'bg-red-600 text-white shadow-red-200', 
        'header_bg' => 'bg-red-50', 
        'header_text' => 'text-red-800', 
        'border' => 'border-red-100',
        'icon_bg' => 'bg-red-100',
        'icon_text' => 'text-red-600',
        'btn_bg' => 'bg-red-600 hover:bg-red-700 shadow-red-500/30'
    ],
    'longsor' => [
        'label' => 'Longsor', 
        'icon' => 'mountain', 
        'tab_active' => 'bg-orange-500 text-white shadow-orange-200', 
        'header_bg' => 'bg-orange-50', 
        'header_text' => 'text-orange-800', 
        'border' => 'border-orange-100',
        'icon_bg' => 'bg-orange-100',
        'icon_text' => 'text-orange-600',
        'btn_bg' => 'bg-orange-500 hover:bg-orange-600 shadow-orange-500/30'
    ],
    'sekolah' => [
        'label' => 'Sekolah', 
        'icon' => 'school', 
        'tab_active' => 'bg-blue-600 text-white shadow-blue-200', 
        'header_bg' => 'bg-blue-50', 
        'header_text' => 'text-blue-800', 
        'border' => 'border-blue-100',
        'icon_bg' => 'bg-blue-100',
        'icon_text' => 'text-blue-600',
        'btn_bg' => 'bg-blue-600 hover:bg-blue-700 shadow-blue-500/30'
    ],
    'rs' => [
        'label' => 'Rumah Sakit', 
        'icon' => 'hospital', 
        'tab_active' => 'bg-green-600 text-white shadow-green-200', 
        'header_bg' => 'bg-green-50', 
        'header_text' => 'text-green-800', 
        'border' => 'border-green-100',
        'icon_bg' => 'bg-green-100',
        'icon_text' => 'text-green-600',
        'btn_bg' => 'bg-green-600 hover:bg-green-700 shadow-green-500/30'
    ]
];

$activeConfig = $categoryConfig[$currentTab];

$currentData = [];
try {
    $stmt = $pdo->query("SELECT * FROM $currentTab ORDER BY nama ASC");
    $currentData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $currentData = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SIG Minahasa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .leaflet-container { z-index: 0; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <div class="min-h-screen flex flex-col">
        <nav class="bg-white border-b border-gray-200 px-6 py-3 flex justify-between items-center sticky top-0 z-50 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="bg-blue-600 p-2 rounded-lg text-white">
                    <i data-lucide="database" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="font-bold text-lg leading-tight text-gray-900">SIG Minahasa</h1>
                    <p class="text-xs text-gray-500 font-medium">Database Manager</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="hidden md:block text-right mr-2">
                    <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
                    <p class="text-xs text-gray-500">Administrator</p>
                </div>
                <a href="index.php" class="text-gray-500 hover:text-blue-600 transition text-sm font-medium flex items-center gap-1">
                    <i data-lucide="external-link" class="w-4 h-4"></i> Lihat Peta
                </a>
                <a href="?logout=1" class="bg-red-50 text-red-600 hover:bg-red-100 px-4 py-2 rounded-lg text-sm font-bold transition">
                    Logout
                </a>
            </div>
        </nav>

        <div class="flex-1 max-w-7xl mx-auto w-full p-6">
            
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-xl border <?php echo $messageType === 'success' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700'; ?> flex items-center gap-3 shadow-sm">
                    <i data-lucide="<?php echo $messageType === 'success' ? 'check-circle' : 'alert-circle'; ?>" class="w-5 h-5"></i>
                    <span class="font-medium"><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white p-1.5 rounded-xl border border-gray-200 shadow-sm inline-flex mb-8 overflow-x-auto max-w-full space-x-1">
                <?php foreach($categoryConfig as $key => $cfg): 
                    $isActive = $currentTab === $key;
                    // Use specific active class if active, else generic gray
                    $tabClass = $isActive ? $cfg['tab_active'] . ' shadow-md' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50';
                    $iconColor = $isActive ? 'text-white' : 'text-gray-400';
                ?>
                    <a href="?tab=<?php echo $key; ?>" class="flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold whitespace-nowrap transition-all duration-200 <?php echo $tabClass; ?>">
                        <i data-lucide="<?php echo $cfg['icon']; ?>" class="w-4 h-4 <?php echo $iconColor; ?>"></i>
                        <?php echo $cfg['label']; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <div class="lg:col-span-4">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden sticky top-24">
                        
                        <div class="<?php echo $activeConfig['header_bg']; ?> px-6 py-4 border-b <?php echo $activeConfig['border']; ?> flex items-center gap-3">
                            <div class="p-2 rounded-lg bg-white/60 <?php echo $activeConfig['header_text']; ?>">
                                <i data-lucide="<?php echo $activeConfig['icon']; ?>" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h2 class="font-bold <?php echo $activeConfig['header_text']; ?> leading-none">Tambah Data</h2>
                                <p class="text-xs <?php echo $activeConfig['header_text']; ?> opacity-80 mt-1">Kategori: <?php echo $activeConfig['label']; ?></p>
                            </div>
                        </div>

                        <div class="p-6">
                            <form method="POST" class="space-y-5">
                                <input type="hidden" name="category" value="<?php echo $currentTab; ?>">
                                <input type="hidden" name="action" value="add">

                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Nama Lokasi</label>
                                    <input type="text" name="nama" required class="w-full bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 block p-2.5 outline-none transition" placeholder="Nama tempat...">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Pilih Lokasi</label>
                                    <div id="preview-map" class="w-full h-48 rounded-lg border border-gray-200 z-0 overflow-hidden relative"></div>
                                    <p class="text-[10px] text-gray-400 mt-1 text-right">Geser pin atau klik peta</p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Latitude</label>
                                        <input type="number" step="any" name="lat" id="lat-input" required class="w-full bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-lg focus:ring-blue-500 block p-2.5 font-mono">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Longitude</label>
                                        <input type="number" step="any" name="lng" id="lng-input" required class="w-full bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-lg focus:ring-blue-500 block p-2.5 font-mono">
                                    </div>
                                </div>

                                <?php if ($currentTab === 'banjir' || $currentTab === 'longsor'): ?>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Level Risiko</label>
                                        <select name="level" class="w-full bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-lg focus:ring-blue-500 block p-2.5">
                                            <option value="Rendah">Rendah</option>
                                            <option value="Sedang" selected>Sedang</option>
                                            <option value="Tinggi">Tinggi</option>
                                        </select>
                                    </div>
                                <?php endif; ?>

                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Keterangan</label>
                                    <textarea name="keterangan" rows="3" class="w-full bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-lg focus:ring-blue-500 block p-2.5 resize-none"></textarea>
                                </div>

                                <button type="submit" class="w-full text-white font-bold rounded-lg text-sm px-5 py-3 transition-all duration-200 shadow-lg <?php echo $activeConfig['btn_bg']; ?>">
                                    Simpan ke Database
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col h-full">
                        
                        <div class="<?php echo $activeConfig['header_bg']; ?> px-6 py-5 border-b <?php echo $activeConfig['border']; ?> flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <div class="p-1.5 rounded-md bg-white/60 <?php echo $activeConfig['header_text']; ?>">
                                    <i data-lucide="list" class="w-4 h-4"></i>
                                </div>
                                <h2 class="font-bold <?php echo $activeConfig['header_text']; ?> text-lg">Data Tersimpan</h2>
                            </div>
                            <span class="bg-white/80 <?php echo $activeConfig['header_text']; ?> text-xs font-bold px-2.5 py-0.5 rounded border <?php echo $activeConfig['border']; ?> uppercase"><?php echo $activeConfig['label']; ?></span>
                        </div>
                        
                        <div class="overflow-x-auto flex-grow">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-500 uppercase bg-gray-50/50 border-b border-gray-100">
                                    <tr>
                                        <th class="px-6 py-3 font-bold">Nama & Icon</th>
                                        <th class="px-6 py-3 font-bold">Koordinat</th>
                                        <th class="px-6 py-3 font-bold">Info</th>
                                        <?php if ($currentTab === 'banjir' || $currentTab === 'longsor'): ?><th class="px-6 py-3 font-bold">Level</th><?php endif; ?>
                                        <th class="px-6 py-3 font-bold text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php if (empty($currentData)): ?>
                                        <tr>
                                            <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                                <div class="flex flex-col items-center justify-center">
                                                    <div class="bg-gray-50 p-4 rounded-full mb-3">
                                                        <i data-lucide="<?php echo $activeConfig['icon']; ?>" class="w-8 h-8 opacity-20"></i>
                                                    </div>
                                                    <p>Database kosong untuk kategori ini.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($currentData as $item): ?>
                                            <tr class="bg-white hover:bg-blue-50/50 transition group">
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="p-2 rounded-full <?php echo $activeConfig['icon_bg'] . ' ' . $activeConfig['icon_text']; ?> border <?php echo $activeConfig['border']; ?>">
                                                            <i data-lucide="<?php echo $activeConfig['icon']; ?>" class="w-4 h-4"></i>
                                                        </div>
                                                        <span class="font-medium text-gray-900"><?php echo htmlspecialchars($item['nama']); ?></span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 font-mono text-xs text-gray-500">
                                                    <?php echo number_format($item['lat'], 4); ?>, <br>
                                                    <?php echo number_format($item['lng'], 4); ?>
                                                </td>
                                                <td class="px-6 py-4 text-gray-500 max-w-xs truncate">
                                                    <?php echo htmlspecialchars($item['keterangan'] ?? '-'); ?>
                                                </td>
                                                <?php if (isset($item['level'])): ?>
                                                    <td class="px-6 py-4">
                                                        <?php 
                                                        $badgeColor = match($item['level']) {
                                                            'Tinggi' => 'bg-red-100 text-red-700 border-red-200',
                                                            'Sedang' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                                            default => 'bg-green-100 text-green-700 border-green-200'
                                                        };
                                                        ?>
                                                        <span class="<?php echo $badgeColor; ?> text-xs font-bold px-2.5 py-0.5 rounded border">
                                                            <?php echo htmlspecialchars($item['level']); ?>
                                                        </span>
                                                    </td>
                                                <?php endif; ?>
                                                <td class="px-6 py-4 text-right">
                                                    <form method="POST" class="inline-block" onsubmit="return confirm('Hapus permanen dari database?')">
                                                        <input type="hidden" name="category" value="<?php echo $currentTab; ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                        <button type="submit" class="text-gray-400 hover:text-red-600 hover:bg-red-50 p-2 rounded-full transition">
                                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        document.addEventListener('DOMContentLoaded', function() {
            const defaultLat = 1.3113; 
            const defaultLng = 124.9078;
            
            const map = L.map('preview-map').setView([defaultLat, defaultLng], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            // Dynamic Marker Color based on Category
            let markerColor = 'blue'; 
            const category = "<?php echo $currentTab; ?>";
            if(category === 'banjir') markerColor = '#ef4444'; // Red
            if(category === 'longsor') markerColor = '#f97316'; // Orange
            if(category === 'sekolah') markerColor = '#2563eb'; // Blue
            if(category === 'rs') markerColor = '#16a34a'; // Green

            const customIcon = L.divIcon({
                className: 'bg-transparent',
                html: `<div style="background-color:${markerColor};" class="w-6 h-6 rounded-full border-2 border-white shadow-lg cursor-pointer"></div>`,
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });

            let marker = L.marker([defaultLat, defaultLng], { 
                draggable: true,
                icon: customIcon 
            }).addTo(map);
            
            const latInput = document.getElementById('lat-input');
            const lngInput = document.getElementById('lng-input');

            function updateInputs(lat, lng) {
                latInput.value = lat.toFixed(6);
                lngInput.value = lng.toFixed(6);
            }

            marker.on('dragend', function(e) {
                const pos = marker.getLatLng();
                updateInputs(pos.lat, pos.lng);
            });

            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                updateInputs(e.latlng.lat, e.latlng.lng);
            });

            const manualUpdate = () => {
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);
                if (!isNaN(lat) && !isNaN(lng)) {
                    marker.setLatLng([lat, lng]);
                    map.panTo([lat, lng]);
                }
            };
            latInput.addEventListener('input', manualUpdate);
            lngInput.addEventListener('input', manualUpdate);

            setTimeout(() => { map.invalidateSize(); }, 200);
        });
    </script>
</body>
</html>