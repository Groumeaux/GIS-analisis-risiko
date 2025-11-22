<?php
require_once 'config.php';

// Connect to Database
$pdo = getDBConnection();

// Helper function
function fetchData($pdo, $table) {
    try {
        $result = $pdo->query("SELECT * FROM $table");
        return $result->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Fetch data
$banjirData = fetchData($pdo, 'banjir');
$longsorData = fetchData($pdo, 'longsor');
$sekolahData = fetchData($pdo, 'sekolah');
$rsData = fetchData($pdo, 'rs');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIG Tanggap Bencana Minahasa</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>

    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        window.appData = {
            banjir: <?php echo json_encode($banjirData, JSON_NUMERIC_CHECK); ?>,
            longsor: <?php echo json_encode($longsorData, JSON_NUMERIC_CHECK); ?>,
            sekolah: <?php echo json_encode($sekolahData, JSON_NUMERIC_CHECK); ?>,
            rs: <?php echo json_encode($rsData, JSON_NUMERIC_CHECK); ?>
        };
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        #map { height: calc(100vh - 65px); width: 100%; z-index: 0; }
        .leaflet-control-attribution { background: rgba(255,255,255,0.9) !important; border-radius: 4px; margin: 5px !important; }
        .leaflet-routing-container { display: none; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-50 overflow-hidden h-screen flex flex-col">

    <nav class="bg-white border-b border-gray-200 shadow-sm h-[65px] flex-none z-50 relative">
        <div class="max-w-[1920px] mx-auto px-6 h-full flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-blue-600 p-2 rounded-lg text-white shadow-md shadow-blue-500/30">
                    <i data-lucide="map" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="font-bold text-gray-900 text-lg leading-tight">SIG Minahasa</h1>
                    <p class="text-xs text-gray-500 font-medium">Tanggap Bencana & Evakuasi</p>
                </div>
            </div>

            <div class="hidden md:flex items-center gap-1 bg-gray-100/50 p-1 rounded-lg border border-gray-200/60">
                <button onclick="switchView('map')" id="nav-map" class="px-4 py-1.5 rounded-md text-sm font-semibold text-blue-700 bg-white shadow-sm transition-all">
                    Peta Interaktif
                </button>
                <button onclick="switchView('info')" id="nav-info" class="px-4 py-1.5 rounded-md text-sm font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-200/50 transition-all">
                    Edukasi & Info
                </button>
            </div>

            <div>
                <a href="admin.php" class="group flex items-center gap-2 px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold rounded-lg transition-all shadow-lg shadow-gray-900/20">
                    <i data-lucide="lock" class="w-4 h-4 text-gray-400 group-hover:text-white transition-colors"></i>
                    <span>Login Admin</span>
                </a>
            </div>
        </div>
    </nav>

    <main class="flex-grow relative w-full h-full overflow-hidden">

        <div id="map-view" class="w-full h-full absolute inset-0">
            <div id="map"></div>

            <button onclick="toggleSidebar(true)" id="sidebar-open-btn" class="absolute top-4 left-4 z-10 bg-white p-3 rounded-xl shadow-lg border border-gray-200 text-gray-700 hover:text-blue-600 hover:border-blue-300 transition-all hidden group">
                <i data-lucide="panel-left-open" class="w-6 h-6"></i>
                <span class="absolute left-14 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                    Buka Panel
                </span>
            </button>

            <div id="sidebar-panel" class="absolute top-4 left-4 z-20 w-[380px] max-h-[calc(100%-2rem)] flex flex-col bg-white/95 backdrop-blur-sm border border-gray-200 shadow-2xl rounded-2xl overflow-hidden transition-transform duration-300 ease-in-out transform translate-x-0">
                
                <div class="px-6 py-4 border-b border-gray-100 bg-white flex justify-between items-center">
                    <div>
                        <h2 class="font-bold text-gray-900 text-lg">Panel Kontrol</h2>
                        <p class="text-xs text-gray-500">Kelola lapisan data peta</p>
                    </div>
                    <button onclick="toggleSidebar(false)" class="p-2 hover:bg-gray-100 rounded-lg text-gray-400 hover:text-gray-700 transition-colors" title="Sembunyikan Panel">
                        <i data-lucide="chevrons-left" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="flex-grow overflow-y-auto no-scrollbar p-6">
                    
                    <div id="layer-controls" class="space-y-3">
                        <label class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-xl cursor-pointer hover:border-blue-300 hover:shadow-md transition-all group">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-red-50 text-red-500 flex items-center justify-center border border-red-100 group-hover:scale-110 transition-transform">
                                    <i data-lucide="cloud-rain" class="w-4 h-4"></i>
                                </div>
                                <span class="font-semibold text-sm text-gray-700">Rawan Banjir</span>
                            </div>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="toggle-banjir" checked class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                            </div>
                        </label>

                        <label class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-xl cursor-pointer hover:border-blue-300 hover:shadow-md transition-all group">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-orange-50 text-orange-500 flex items-center justify-center border border-orange-100 group-hover:scale-110 transition-transform">
                                    <i data-lucide="mountain" class="w-4 h-4"></i>
                                </div>
                                <span class="font-semibold text-sm text-gray-700">Rawan Longsor</span>
                            </div>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="toggle-longsor" checked class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                            </div>
                        </label>

                        <label class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-xl cursor-pointer hover:border-blue-300 hover:shadow-md transition-all group">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center border border-blue-100 group-hover:scale-110 transition-transform">
                                    <i data-lucide="school" class="w-4 h-4"></i>
                                </div>
                                <span class="font-semibold text-sm text-gray-700">Sekolah (Posko)</span>
                            </div>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="toggle-sekolah" checked class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                            </div>
                        </label>

                        <label class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-xl cursor-pointer hover:border-blue-300 hover:shadow-md transition-all group">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-green-50 text-green-500 flex items-center justify-center border border-green-100 group-hover:scale-110 transition-transform">
                                    <i data-lucide="hospital" class="w-4 h-4"></i>
                                </div>
                                <span class="font-semibold text-sm text-gray-700">Rumah Sakit</span>
                            </div>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="toggle-rs" checked class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                            </div>
                        </label>

                        <div class="mt-6 pt-4 border-t border-dashed border-gray-200 text-center">
                            <p class="text-xs text-gray-400 italic flex items-center justify-center gap-1">
                                <i data-lucide="mouse-pointer" class="w-3 h-3"></i>
                                Klik pada peta untuk analisis risiko
                            </p>
                        </div>
                    </div>

                    <div id="analysis-panel" class="hidden flex-col h-full">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-gray-800">Hasil Analisis</h3>
                            <button onclick="closeAnalysis()" class="p-1 hover:bg-gray-100 rounded-full transition-colors">
                                <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                            </button>
                        </div>

                        <div id="risk-badge" class="text-center py-5 px-4 rounded-xl mb-5 bg-gray-50 border border-gray-100">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Tingkat Risiko Lokasi</p>
                            <div id="risk-level-text" class="text-2xl font-extrabold text-gray-800">MENGHITUNG...</div>
                        </div>

                        <div class="space-y-3">
                            <div class="bg-blue-50/50 border border-blue-100 p-4 rounded-xl relative overflow-hidden group hover:border-blue-200 transition-all">
                                <div class="absolute top-0 right-0 bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-bl-lg">Rute Biru</div>
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-blue-600 shadow-sm shrink-0"><i data-lucide="school" class="w-5 h-5"></i></div>
                                    <div>
                                        <p class="text-xs font-bold text-blue-600 uppercase tracking-wide mb-0.5">Posko Terdekat</p>
                                        <p class="font-bold text-gray-900 leading-tight mb-1" id="res-school-name">-</p>
                                        <p class="text-xs text-gray-500 font-mono" id="res-school-dist">Jarak: -</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-green-50/50 border border-green-100 p-4 rounded-xl relative overflow-hidden group hover:border-green-200 transition-all">
                                <div class="absolute top-0 right-0 bg-green-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-bl-lg">Rute Hijau</div>
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-green-600 shadow-sm shrink-0"><i data-lucide="hospital" class="w-5 h-5"></i></div>
                                    <div>
                                        <p class="text-xs font-bold text-green-600 uppercase tracking-wide mb-0.5">RS Terdekat</p>
                                        <p class="font-bold text-gray-900 leading-tight mb-1" id="res-rs-name">-</p>
                                        <p class="text-xs text-gray-500 font-mono" id="res-rs-dist">Jarak: -</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div id="info-view" class="hidden w-full h-full overflow-y-auto bg-gray-50">
            
            <div class="bg-blue-900 border-b border-blue-800 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 p-12 opacity-5 pointer-events-none">
                    <i data-lucide="map" class="w-96 h-96"></i>
                </div>

                <div class="max-w-5xl mx-auto py-20 px-6 text-center relative z-10">
                    <div class="inline-flex items-center justify-center p-3 bg-blue-800/50 border border-blue-700 rounded-2xl mb-6 shadow-lg">
                        <i data-lucide="book-open" class="w-8 h-8 text-blue-200"></i>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4 tracking-tight">Pusat Edukasi Bencana</h1>
                    <p class="text-lg text-blue-200 max-w-2xl mx-auto font-medium">Panduan lengkap kesiapsiagaan dan informasi teknis mengenai Sistem Informasi Geografis Minahasa.</p>
                </div>
            </div>

            <div class="max-w-6xl mx-auto py-12 px-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                    
                    <div class="bg-white rounded-2xl shadow-sm border border-red-100 overflow-hidden hover:shadow-md transition-all group">
                        <div class="bg-red-50 px-8 py-6 border-b border-red-100 flex items-center gap-4">
                            <div class="p-3 bg-white text-red-500 rounded-xl shadow-sm group-hover:scale-110 transition-transform duration-300">
                                <i data-lucide="cloud-rain" class="w-8 h-8"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-red-900">Siaga Banjir</h2>
                                <p class="text-red-600 text-sm font-medium">Protokol Keselamatan Air</p>
                            </div>
                        </div>
                        <div class="p-8">
                            <ul class="space-y-4">
                                <li class="flex items-start gap-4">
                                    <div class="mt-1 bg-red-100 p-1 rounded-full text-red-600"><i data-lucide="zap-off" class="w-4 h-4"></i></div>
                                    <div>
                                        <span class="font-bold text-gray-800 block">Matikan Listrik</span>
                                        <span class="text-sm text-gray-600">Segera putuskan aliran listrik dari meteran utama.</span>
                                    </div>
                                </li>
                                <li class="flex items-start gap-4">
                                    <div class="mt-1 bg-red-100 p-1 rounded-full text-red-600"><i data-lucide="file-text" class="w-4 h-4"></i></div>
                                    <div>
                                        <span class="font-bold text-gray-800 block">Amankan Dokumen</span>
                                        <span class="text-sm text-gray-600">Simpan dokumen penting di tempat tinggi dan kedap air.</span>
                                    </div>
                                </li>
                                <li class="flex items-start gap-4">
                                    <div class="mt-1 bg-red-100 p-1 rounded-full text-red-600"><i data-lucide="log-out" class="w-4 h-4"></i></div>
                                    <div>
                                        <span class="font-bold text-gray-800 block">Evakuasi Segera</span>
                                        <span class="text-sm text-gray-600">Menuju titik kumpul (Sekolah) terdekat.</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-orange-100 overflow-hidden hover:shadow-md transition-all group">
                        <div class="bg-orange-50 px-8 py-6 border-b border-orange-100 flex items-center gap-4">
                            <div class="p-3 bg-white text-orange-500 rounded-xl shadow-sm group-hover:scale-110 transition-transform duration-300">
                                <i data-lucide="mountain" class="w-8 h-8"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-orange-900">Siaga Longsor</h2>
                                <p class="text-orange-600 text-sm font-medium">Protokol Pergeseran Tanah</p>
                            </div>
                        </div>
                        <div class="p-8">
                            <ul class="space-y-4">
                                <li class="flex items-start gap-4">
                                    <div class="mt-1 bg-orange-100 p-1 rounded-full text-orange-600"><i data-lucide="alert-triangle" class="w-4 h-4"></i></div>
                                    <div>
                                        <span class="font-bold text-gray-800 block">Waspada Retakan</span>
                                        <span class="text-sm text-gray-600">Perhatikan retakan tanah atau tembok setelah hujan lebat.</span>
                                    </div>
                                </li>
                                <li class="flex items-start gap-4">
                                    <div class="mt-1 bg-orange-100 p-1 rounded-full text-orange-600"><i data-lucide="move-up-right" class="w-4 h-4"></i></div>
                                    <div>
                                        <span class="font-bold text-gray-800 block">Jauhi Tebing</span>
                                        <span class="text-sm text-gray-600">Hindari area di bawah tebing curam saat cuaca buruk.</span>
                                    </div>
                                </li>
                                <li class="flex items-start gap-4">
                                    <div class="mt-1 bg-orange-100 p-1 rounded-full text-orange-600"><i data-lucide="ear" class="w-4 h-4"></i></div>
                                    <div>
                                        <span class="font-bold text-gray-800 block">Dengar Gemuruh</span>
                                        <span class="text-sm text-gray-600">Jika terdengar gemuruh, segera lari ke area terbuka.</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-900 rounded-2xl shadow-xl overflow-hidden text-white relative mb-12">
                    <div class="absolute top-0 right-0 p-12 opacity-10 pointer-events-none">
                        <i data-lucide="cpu" class="w-64 h-64"></i>
                    </div>
                    <div class="p-8 md:p-12 relative z-10">
                        <div class="grid md:grid-cols-2 gap-8">
                            <div>
                                <h2 class="text-2xl font-bold mb-4">Tentang Sistem Ini</h2>
                                <p class="text-blue-100 leading-relaxed mb-6">
                                    Aplikasi ini membantu masyarakat Minahasa dalam mitigasi risiko bencana menggunakan teknologi pemetaan modern.
                                </p>
                                <div class="space-y-3">
                                    <div class="flex items-center gap-3">
                                        <i data-lucide="search" class="text-blue-300 w-5 h-5"></i>
                                        <span class="text-sm"><strong class="text-white">Brute-Force Algorithm:</strong> Pencarian radius cepat.</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i data-lucide="navigation" class="text-blue-300 w-5 h-5"></i>
                                        <span class="text-sm"><strong class="text-white">OSRM Routing:</strong> Kalkulasi rute jalan raya.</span>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-blue-800/50 rounded-xl p-6 border border-blue-700/50">
                                <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                                    <i data-lucide="users" class="w-5 h-5"></i> Tim Pengembang
                                </h3>
                                <ul class="space-y-3 text-sm text-blue-100">
                                    <li class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-blue-400 rounded-full"></div> Fanuel J. Palandeng
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-blue-400 rounded-full"></div> David V. Baridji
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-blue-400 rounded-full"></div> Daud A. Lendo
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center text-gray-400 text-sm pb-8 border-t border-gray-200 pt-8">
                    &copy; <?php echo date('Y'); ?> SIG Minahasa. Dibuat untuk Kemanusiaan.
                </div>
            </div>
        </div>
    </main>

    <script>
        // --- SIDEBAR TOGGLE LOGIC ---
        function toggleSidebar(show) {
            const sidebar = document.getElementById('sidebar-panel');
            const openBtn = document.getElementById('sidebar-open-btn');
            
            if(show) {
                sidebar.classList.remove('-translate-x-[150%]'); 
                openBtn.classList.add('hidden'); 
            } else {
                sidebar.classList.add('-translate-x-[150%]'); 
                openBtn.classList.remove('hidden'); 
            }
        }

        // --- UI LOGIC ---
        function switchView(viewId) {
            document.getElementById('map-view').classList.add('hidden');
            document.getElementById('info-view').classList.add('hidden');
            document.getElementById('nav-map').className = "px-4 py-1.5 rounded-md text-sm font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-200/50 transition-all";
            document.getElementById('nav-info').className = "px-4 py-1.5 rounded-md text-sm font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-200/50 transition-all";

            document.getElementById(viewId + '-view').classList.remove('hidden');
            document.getElementById('nav-' + viewId).className = "px-4 py-1.5 rounded-md text-sm font-semibold text-blue-700 bg-white shadow-sm transition-all";

            if(viewId === 'map' && map) setTimeout(() => map.invalidateSize(), 100);
        }

        // --- MAP LOGIC ---
        const map = L.map('map', { zoomControl: false }).setView([1.3113, 124.9078], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { 
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap' 
        }).addTo(map);

        const layers = {
            banjir: L.layerGroup().addTo(map),
            longsor: L.layerGroup().addTo(map),
            sekolah: L.layerGroup().addTo(map),
            rs: L.layerGroup().addTo(map)
        };

        const createIcon = (color) => L.divIcon({
            className: 'custom-pin',
            html: `<div style="background-color:${color}; width:16px; height:16px; border:3px solid white; border-radius:50%; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);"></div>`,
            iconSize: [16, 16], iconAnchor: [8, 8]
        });

        const icons = {
            banjir: createIcon('#ef4444'),
            longsor: createIcon('#f97316'),
            sekolah: createIcon('#2563eb'),
            rs: createIcon('#16a34a')
        };

        function refreshLayers() {
            Object.values(layers).forEach(l => l.clearLayers());
            ['banjir', 'longsor', 'sekolah', 'rs'].forEach(type => {
                if(window.appData && Array.isArray(window.appData[type])) {
                    window.appData[type].forEach(item => {
                        const lat = parseFloat(item.lat);
                        const lng = parseFloat(item.lng);
                        if(!isNaN(lat) && !isNaN(lng)) {
                            L.marker([lat, lng], {icon: icons[type]})
                            .bindPopup(`<div class="font-sans"><h3 class="font-bold text-gray-900">${item.nama}</h3><p class="text-sm text-gray-600 mt-1">${item.keterangan || ''}</p></div>`)
                            .addTo(layers[type]);
                        }
                    });
                }
            });
        }

        document.getElementById('toggle-banjir').addEventListener('change', e => e.target.checked ? layers.banjir.addTo(map) : layers.banjir.remove());
        document.getElementById('toggle-longsor').addEventListener('change', e => e.target.checked ? layers.longsor.addTo(map) : layers.longsor.remove());
        document.getElementById('toggle-sekolah').addEventListener('change', e => e.target.checked ? layers.sekolah.addTo(map) : layers.sekolah.remove());
        document.getElementById('toggle-rs').addEventListener('change', e => e.target.checked ? layers.rs.addTo(map) : layers.rs.remove());

        let userMarker, radiusCircle, routeControlSchool, routeControlRS;

        map.on('click', function(e) {
            if(userMarker) map.removeLayer(userMarker);
            if(radiusCircle) map.removeLayer(radiusCircle);

            userMarker = L.marker(e.latlng).addTo(map);
            radiusCircle = L.circle(e.latlng, { color: '#ef4444', fillColor: '#ef4444', fillOpacity: 0.1, weight: 1, radius: 2000 }).addTo(map);

            let riskCount = 0;
            const radiusMeter = 2000;
            ['banjir', 'longsor'].forEach(t => {
                if(window.appData[t]) window.appData[t].forEach(p => {
                    if(e.latlng.distanceTo([p.lat, p.lng]) <= radiusMeter) riskCount++;
                });
            });

            const riskText = document.getElementById('risk-level-text');
            const riskBadge = document.getElementById('risk-badge');
            riskBadge.className = "text-center py-5 px-4 rounded-xl mb-5 border transition-all duration-300";

            if(riskCount === 0) {
                riskText.innerText = "RENDAH";
                riskBadge.classList.add("bg-green-50", "border-green-100", "text-green-800");
            } else if (riskCount < 3) {
                riskText.innerText = "SEDANG";
                riskBadge.classList.add("bg-yellow-50", "border-yellow-100", "text-yellow-800");
            } else {
                riskText.innerText = "TINGGI";
                riskBadge.classList.add("bg-red-50", "border-red-100", "text-red-800");
            }

            const findNearest = (data) => {
                if(!data || data.length === 0) return null;
                let nearest = null, minDist = Infinity;
                data.forEach(p => {
                    let dist = e.latlng.distanceTo([p.lat, p.lng]);
                    if(dist < minDist) { minDist = dist; nearest = {...p, dist: dist, latLng: L.latLng(p.lat, p.lng)}; }
                });
                return nearest;
            };

            const nSchool = findNearest(window.appData.sekolah);
            const nRS = findNearest(window.appData.rs);

            document.getElementById('res-school-name').innerText = nSchool ? nSchool.nama : '-';
            document.getElementById('res-school-dist').innerText = nSchool ? (nSchool.dist/1000).toFixed(2) + ' km' : '-';
            document.getElementById('res-rs-name').innerText = nRS ? nRS.nama : '-';
            document.getElementById('res-rs-dist').innerText = nRS ? (nRS.dist/1000).toFixed(2) + ' km' : '-';

            if(routeControlSchool) map.removeControl(routeControlSchool);
            if(nSchool) {
                routeControlSchool = L.Routing.control({
                    waypoints: [e.latlng, nSchool.latLng],
                    lineOptions: { styles: [{color: '#2563eb', opacity: 0.8, weight: 6}] },
                    show: false, createMarker: () => null
                }).on('routesfound', function(r) {
                    const d = r.routes[0].summary.totalDistance;
                    const t = Math.round(r.routes[0].summary.totalTime / 60);
                    document.getElementById('res-school-dist').innerText = `${(d/1000).toFixed(2)} km (Rute: ${t} mnt)`;
                }).addTo(map);
            }

            if(routeControlRS) map.removeControl(routeControlRS);
            if(nRS) {
                routeControlRS = L.Routing.control({
                    waypoints: [e.latlng, nRS.latLng],
                    lineOptions: { styles: [{color: '#16a34a', opacity: 0.8, weight: 6}] },
                    show: false, createMarker: () => null
                }).on('routesfound', function(r) {
                    const d = r.routes[0].summary.totalDistance;
                    const t = Math.round(r.routes[0].summary.totalTime / 60);
                    document.getElementById('res-rs-dist').innerText = `${(d/1000).toFixed(2)} km (Rute: ${t} mnt)`;
                }).addTo(map);
            }

            if (document.getElementById('layer-controls').classList.contains('hidden') === false) {
                 document.getElementById('layer-controls').classList.add('hidden');
                 document.getElementById('analysis-panel').classList.remove('hidden');
                 document.getElementById('analysis-panel').classList.add('flex');
            }
            
            toggleSidebar(true);
        });

        function closeAnalysis() {
            document.getElementById('analysis-panel').classList.add('hidden');
            document.getElementById('analysis-panel').classList.remove('flex');
            document.getElementById('layer-controls').classList.remove('hidden');
            if(userMarker) map.removeLayer(userMarker);
            if(radiusCircle) map.removeLayer(radiusCircle);
            if(routeControlSchool) map.removeControl(routeControlSchool);
            if(routeControlRS) map.removeControl(routeControlRS);
        }

        refreshLayers();
        lucide.createIcons();
    </script>
</body>
</html>