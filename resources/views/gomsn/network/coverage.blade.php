<x-app-layout>
    <!-- ============================================================== -->
    <!-- LOAD LOCAL LEAFLET ASSETS FIRST BEFORE ALPINE PARSES DOM       -->
    <!-- ============================================================== -->
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}" />
    <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>

    <!-- Custom CSS for Leaflet & High-Tech FTTH Map Visualizations -->
    <style>
        .leaflet-container {
            font-family: inherit;
        }
        /* Leaflet Controls Dark Mode */
        .dark .leaflet-bar {
            border-color: #334155 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4) !important;
        }
        .dark .leaflet-bar a {
            background-color: #1e293b !important;
            color: #f1f5f9 !important;
            border-bottom: 1px solid #334155 !important;
        }
        .dark .leaflet-bar a:hover {
            background-color: #334155 !important;
            color: #38bdf8 !important;
        }

        /* Custom Glowing Pulsing Target Ring */
        .ftth-target-marker {
            position: relative;
        }
        .ftth-pulse-ring {
            position: absolute;
            top: -14px;
            left: -14px;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: rgba(225, 29, 72, 0.4);
            animation: ftthRipple 1.6s cubic-bezier(0.24, 0, 0.38, 1) infinite;
            pointer-events: none;
        }
        @keyframes ftthRipple {
            0% {
                transform: scale(0.35);
                opacity: 1;
            }
            100% {
                transform: scale(1.6);
                opacity: 0;
            }
        }
        /* Animated Dash Polyline for Optical Cable Following Road Curves */
        .ftth-cable-line {
            stroke-dasharray: 8, 8;
            animation: ftthDash 1.2s linear infinite;
        }
        @keyframes ftthDash {
            to {
                stroke-dashoffset: -16;
            }
        }
        /* Custom Leaflet Tooltip */
        .ftth-odp-tooltip {
            background-color: rgba(15, 23, 42, 0.95) !important;
            color: #ffffff !important;
            border: 1px solid rgba(45, 212, 191, 0.5) !important;
            border-radius: 8px !important;
            padding: 4px 10px !important;
            font-weight: 700 !important;
            font-size: 11px !important;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.35) !important;
        }
        .ftth-odp-tooltip::before {
            border-top-color: rgba(15, 23, 42, 0.95) !important;
        }
        /* Leaflet Popups */
        .leaflet-popup-content-wrapper {
            border-radius: 14px !important;
            padding: 4px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.25) !important;
        }
        .dark .leaflet-popup-content-wrapper {
            background: #1e293b !important;
            color: #f1f5f9 !important;
            border: 1px solid #334155 !important;
        }
        .dark .leaflet-popup-tip {
            background: #1e293b !important;
        }

        /* Slim Custom Scrollbar for Left Panel */
        .ftth-panel-scroll::-webkit-scrollbar {
            width: 5px;
        }
        .ftth-panel-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .ftth-panel-scroll::-webkit-scrollbar-thumb {
            background: rgba(100, 116, 139, 0.3);
            border-radius: 9999px;
        }
        .ftth-panel-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.5);
        }
    </style>

    <!-- ============================================================== -->
    <!-- ALPINE JS CONTROLLER LOGIC WITH ROAD-FOLLOWING ROUTING         -->
    <!-- ============================================================== -->
    <script>
        window.ftthCoverageApp = function(allOdps, metrics, initialTarget) {
            return {
                allOdps: allOdps || [],
                metrics: metrics || {},
                rawInput: '',
                isSearching: false,
                isLocating: false,
                isRouting: false,
                gpsAccuracy: null,
                targetCoord: null, // [lat, lng]
                targetLabel: 'Titik Lokasi Target',
                copiedCoords: false,
                copiedSummary: false,
                suggestions: [],

                // Filters
                filterRadius: 500, // meter
                filterOnlyAvailableSlot: false,
                showCircles: true,

                // Computed ODP states
                nearbyOdps: [],
                nearestOdp: null,
                selectedOdp: null,
                roadRouteDistance: null, // meter from real turn-by-turn road geometry

                // Map objects
                map: null,
                currentTile: (localStorage.getItem('gomsn_theme') === 'dark' || document.documentElement.classList.contains('dark')) ? 'dark' : 'street',
                userSelectedTile: false,
                tileLayers: {},
                targetMarker: null,
                odpMarkersGroup: null,
                cablePolyline: null,
                circle150: null,
                circle300: null,
                mouseCoordText: 'Arahkan kursor ke peta...',

                // Road route fallback factor
                ROAD_FACTOR: 1.28,
                OPTICAL_LOSS_PER_KM: 0.35, // dB/km
                CABLE_SLACK: 18, // meter

                get activeOdp() {
                    return this.selectedOdp || this.nearestOdp;
                },

                get activeDistance() {
                    if (this.roadRouteDistance !== null && this.roadRouteDistance > 0) {
                        return this.roadRouteDistance;
                    }
                    if (!this.activeOdp) return 0;
                    return this.activeOdp.calculatedRouteDist || 0;
                },

                get activeStraightDistance() {
                    if (!this.activeOdp) return 0;
                    return this.activeOdp.calculatedStraightDist || 0;
                },

                initApp() {
                    this.$nextTick(() => {
                        this.initLeafletMap();

                        // Listen for theme switch events
                        window.addEventListener('gomsn-theme-changed', (e) => {
                            const isDark = e && e.detail && e.detail.dark;
                            if (!this.userSelectedTile) {
                                this.setTileLayer(isDark ? 'dark' : 'street', false);
                            }
                        });

                        // Set initial target jika dilempar dari controller query param
                        if (initialTarget && initialTarget.lat && initialTarget.lng) {
                            this.setTargetLocation(initialTarget.lat, initialTarget.lng, initialTarget.label || 'Titik Calon Pelanggan');
                        }
                    });
                },

                initLeafletMap() {
                    if (typeof L === 'undefined') {
                        console.error('Leaflet library L is not loaded.');
                        return;
                    }

                    const defaultCenter = (this.metrics.default_center && this.metrics.default_center.lat)
                        ? [this.metrics.default_center.lat, this.metrics.default_center.lng]
                        : [-6.917464, 107.619123];

                    const mapContainer = document.getElementById('coverageMap');
                    if (!mapContainer) return;

                    this.map = L.map(mapContainer, {
                        center: defaultCenter,
                        zoom: 15,
                        zoomControl: true,
                        attributionControl: false
                    });

                    // Define Tile Providers
                    this.tileLayers.street = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    });

                    this.tileLayers.satellite = L.layerGroup([
                        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                            maxZoom: 19
                        }),
                        L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
                            maxZoom: 19
                        })
                    ]);

                    this.tileLayers.dark = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                        maxZoom: 19,
                        subdomains: 'abcd'
                    });

                    // Apply tile according to active mode
                    const initialLayer = this.tileLayers[this.currentTile] || this.tileLayers.street;
                    initialLayer.addTo(this.map);

                    this.odpMarkersGroup = L.layerGroup().addTo(this.map);

                    // Event: Klik di sembarang area peta untuk menaruh Target Pin
                    this.map.on('click', (e) => {
                        this.setTargetLocation(e.latlng.lat, e.latlng.lng, 'Titik Pilihan Peta');
                    });

                    this.map.on('mousemove', (e) => {
                        this.mouseCoordText = e.latlng.lat.toFixed(5) + ', ' + e.latlng.lng.toFixed(5);
                    });

                    this.renderAllOdpMarkers();

                    // Force Leaflet to recalculate container geometry
                    setTimeout(() => { if (this.map) this.map.invalidateSize(); }, 200);
                    setTimeout(() => { if (this.map) this.map.invalidateSize(); }, 600);
                },

                setTileLayer(type, manual = true) {
                    if (manual) this.userSelectedTile = true;
                    if (this.currentTile === type && this.tileLayers[type] && this.map && this.map.hasLayer(this.tileLayers[type])) return;
                    if (this.tileLayers[this.currentTile] && this.map && this.map.hasLayer(this.tileLayers[this.currentTile])) {
                        this.map.removeLayer(this.tileLayers[this.currentTile]);
                    }
                    if (this.tileLayers[type] && this.map) {
                        this.tileLayers[type].addTo(this.map);
                        this.currentTile = type;
                    }
                },

                resetTarget() {
                    this.targetCoord = null;
                    this.rawInput = '';
                    this.nearbyOdps = [];
                    this.nearestOdp = null;
                    this.selectedOdp = null;
                    this.roadRouteDistance = null;
                    if (this.targetMarker && this.map) {
                        this.map.removeLayer(this.targetMarker);
                        this.targetMarker = null;
                    }
                    if (this.cablePolyline && this.map) {
                        this.map.removeLayer(this.cablePolyline);
                        this.cablePolyline = null;
                    }
                    if (this.circle150 && this.map) {
                        this.map.removeLayer(this.circle150);
                        this.circle150 = null;
                    }
                    if (this.circle300 && this.map) {
                        this.map.removeLayer(this.circle300);
                        this.circle300 = null;
                    }
                    this.resetMapView();
                },

                renderAllOdpMarkers() {
                    if (!this.odpMarkersGroup) return;
                    this.odpMarkersGroup.clearLayers();

                    this.allOdps.forEach(odp => {
                        const isAvailable = odp.has_slot;
                        const bgColor = isAvailable ? '#10b981' : '#f43f5e';
                        const pulseColor = isAvailable ? 'rgba(16, 185, 129, 0.45)' : 'rgba(244, 63, 94, 0.45)';

                        const odpIcon = L.divIcon({
                            className: 'ftth-odp-marker',
                            html: `
                                <div style="position: relative; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                    <svg viewBox="0 0 24 24" style="width: 28px; height: 28px; filter: drop-shadow(0 2px 5px ${pulseColor});">
                                        <path fill="${bgColor}" stroke="#ffffff" stroke-width="1.8" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                                        <circle cx="12" cy="9" r="3.2" fill="#ffffff"/>
                                    </svg>
                                </div>
                            `,
                            iconSize: [28, 28],
                            iconAnchor: [14, 28],
                            popupAnchor: [0, -26]
                        });

                        const marker = L.marker([odp.lat, odp.lng], { icon: odpIcon });

                        marker.bindTooltip(odp.nama_odp, {
                            permanent: false,
                            direction: 'top',
                            className: 'ftth-odp-tooltip'
                        });

                        marker.on('click', () => {
                            let distInfo = '';
                            if (this.targetCoord) {
                                const stDist = this.calculateHaversine(this.targetCoord[0], this.targetCoord[1], odp.lat, odp.lng);
                                const rtDist = Math.round(stDist * this.ROAD_FACTOR);
                                distInfo = `
                                    <div style="margin-top: 8px; padding: 6px 10px; border-radius: 8px; background: rgba(15, 23, 42, 0.05); font-size: 11.5px;">
                                        <div style="font-weight: 700;">Jarak Rute Tiang: <span style="color: #0891b2;">~${rtDist} meter</span></div>
                                        <div style="font-size: 10.5px; opacity: 0.7;">(Lurus: ${Math.round(stDist)}m &bull; Dropcore: ~${Math.round(rtDist + 18)}m)</div>
                                    </div>
                                `;
                            }

                            const popupHtml = `
                                <div style="font-family: inherit; min-width: 210px; padding: 4px;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; border-bottom: 1px solid rgba(148, 163, 184, 0.2); padding-bottom: 6px; margin-bottom: 8px;">
                                        <strong style="font-size: 13px;">${odp.nama_odp}</strong>
                                        <span style="background: ${odp.has_slot ? '#d1fae5' : '#ffe4e6'}; color: ${odp.has_slot ? '#065f46' : '#9f1239'}; font-size: 10.5px; font-weight: 800; padding: 2px 7px; border-radius: 9999px;">
                                            ${odp.has_slot ? odp.port_tersedia + ' Slot' : 'Penuh'}
                                        </span>
                                    </div>
                                    <div style="font-size: 11.5px; line-height: 1.6; opacity: 0.9;">
                                        <div><strong>OLT:</strong> ${odp.nama_olt} (${odp.kode_olt})</div>
                                        <div><strong>PON:</strong> ${odp.nama_pon}</div>
                                        <div><strong>Port:</strong> ${odp.port_terpakai} / ${odp.port_max} Terpakai</div>
                                    </div>
                                    ${distInfo}
                                    <button type="button"
                                            onclick="window.__selectOdpFromPopup(${odp.id})"
                                            style="margin-top: 10px; width: 100%; padding: 6px 10px; border-radius: 8px; background: #0891b2; color: #ffffff; font-weight: 700; font-size: 11.5px; border: none; cursor: pointer; transition: opacity 0.2s;">
                                        Jadikan Target ODP Ini
                                    </button>
                                </div>
                            `;

                            marker.bindPopup(popupHtml).openPopup();
                        });

                        marker.addTo(this.odpMarkersGroup);
                    });

                    window.__selectOdpFromPopup = (id) => {
                        const target = this.allOdps.find(o => o.id === id);
                        if (target) {
                            this.selectOdp(target);
                            this.map.closePopup();
                        }
                    };
                },

                setTargetLocation(lat, lng, label = 'Titik Calon Pelanggan') {
                    this.targetCoord = [lat, lng];
                    this.targetLabel = label;
                    this.rawInput = lat.toFixed(6) + ', ' + lng.toFixed(6);

                    if (this.targetMarker) {
                        this.targetMarker.setLatLng([lat, lng]);
                    } else {
                        const targetIcon = L.divIcon({
                            className: 'ftth-target-marker',
                            html: `
                                <div style="position: relative; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;">
                                    <div class="ftth-pulse-ring"></div>
                                    <svg viewBox="0 0 24 24" style="width: 34px; height: 34px; filter: drop-shadow(0 4px 8px rgba(225,29,72,0.65));">
                                        <path fill="#e11d48" stroke="#ffffff" stroke-width="2" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                                        <circle cx="12" cy="9" r="3.5" fill="#ffffff"/>
                                    </svg>
                                </div>
                            `,
                            iconSize: [38, 38],
                            iconAnchor: [19, 36],
                            popupAnchor: [0, -34]
                        });

                        this.targetMarker = L.marker([lat, lng], {
                            icon: targetIcon,
                            draggable: true,
                            zIndexOffset: 1000
                        }).addTo(this.map);

                        this.targetMarker.on('dragend', (e) => {
                            const newPos = e.target.getLatLng();
                            this.setTargetLocation(newPos.lat, newPos.lng, 'Titik Pilihan Peta (Digeser)');
                        });
                    }

                    this.selectedOdp = null;
                    this.roadRouteDistance = null;
                    this.recalculateAllDistances();
                    this.updateVisualizations();
                },

                recalculateAllDistances() {
                    if (!this.targetCoord) return;
                    const [tLat, tLng] = this.targetCoord;

                    const calculated = this.allOdps.map(odp => {
                        const stDist = this.calculateHaversine(tLat, tLng, odp.lat, odp.lng);
                        const rtDist = stDist * this.ROAD_FACTOR;
                        return {
                            ...odp,
                            calculatedStraightDist: stDist,
                            calculatedRouteDist: rtDist
                        };
                    });

                    let filtered = calculated.filter(odp => {
                        const matchRadius = odp.calculatedRouteDist <= this.filterRadius;
                        const matchSlot = this.filterOnlyAvailableSlot ? odp.has_slot : true;
                        return matchRadius && matchSlot;
                    });

                    filtered.sort((a, b) => a.calculatedRouteDist - b.calculatedRouteDist);
                    this.nearbyOdps = filtered.slice(0, 5);

                    if (filtered.length > 0) {
                        this.nearestOdp = filtered[0];
                    } else {
                        calculated.sort((a, b) => a.calculatedRouteDist - b.calculatedRouteDist);
                        this.nearestOdp = calculated[0] || null;
                    }
                },

                applyOdpFilters() {
                    this.recalculateAllDistances();
                    this.updateVisualizations();
                },

                selectOdp(odp) {
                    this.selectedOdp = odp;
                    this.roadRouteDistance = null;
                    this.updateVisualizations();

                    if (this.map && odp) {
                        this.map.panTo([odp.lat, odp.lng], { animate: true });
                    }
                },

                // ==========================================================
                // FITUR UTAMA: JALUR MENGIKUTI KONTUR JALAN RAYA (OSRM ROUTING)
                // ==========================================================
                async updateVisualizations() {
                    if (!this.targetCoord || !this.activeOdp || !this.map) return;

                    const [tLat, tLng] = this.targetCoord;
                    const oLat = this.activeOdp.lat;
                    const oLng = this.activeOdp.lng;
                    const initialDist = this.activeDistance;
                    const lineColor = this.getTierColor(initialDist);

                    // Gambar garis sementara (garis lurus) terlebih dahulu selagi mengambil rute jalan turn-by-turn
                    if (this.cablePolyline) {
                        this.map.removeLayer(this.cablePolyline);
                    }
                    this.cablePolyline = L.polyline([[tLat, tLng], [oLat, oLng]], {
                        color: lineColor,
                        weight: 3.5,
                        opacity: 0.6,
                        dashArray: '8, 8',
                        className: 'ftth-cable-line'
                    }).addTo(this.map);

                    this.updateCoverageCircles(oLat, oLng);

                    // Jika user tidak memilih manual ODP tertentu, evaluasi kandidat ODP terdekat
                    // agar sistem memilih ODP dengan rute jalan raya riil terpendek (bukan sekadar garis lurus)
                    if (!this.selectedOdp && this.nearbyOdps.length > 1) {
                        this.fetchBestRoute(tLat, tLng, this.nearbyOdps);
                    } else {
                        this.fetchStreetRoute(tLat, tLng, oLat, oLng);
                    }
                },

                updateCoverageCircles(oLat, oLng) {
                    if (this.circle150) this.map.removeLayer(this.circle150);
                    if (this.circle300) this.map.removeLayer(this.circle300);

                    if (this.showCircles) {
                        this.circle150 = L.circle([oLat, oLng], {
                            radius: 150,
                            color: '#10b981',
                            fillColor: '#10b981',
                            fillOpacity: 0.08,
                            weight: 1.5,
                            dashArray: '4, 4'
                        }).addTo(this.map);

                        this.circle300 = L.circle([oLat, oLng], {
                            radius: 300,
                            color: '#f59e0b',
                            fillColor: '#f59e0b',
                            fillOpacity: 0.04,
                            weight: 1.5,
                            dashArray: '6, 6'
                        }).addTo(this.map);
                    }
                },

                async fetchBestRoute(lat1, lng1, candidateList) {
                    if (!candidateList || candidateList.length === 0) return;
                    this.isRouting = true;
                    try {
                        const candidatesData = candidateList.slice(0, 6).map(c => ({
                            id: c.id,
                            lat: c.lat,
                            lng: c.lng,
                            has_slot: Boolean(c.has_slot)
                        }));

                        const res = await fetch(`{{ route('gomsn.network.coverage.best_route') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify({
                                origin: `${lat1},${lng1}`,
                                candidates: candidatesData
                            })
                        });

                        if (res.ok) {
                            const data = await res.json();
                            if (data && data.success) {
                                // Update jarak jalan sebenarnya untuk seluruh kandidat
                                if (data.distances && typeof data.distances === 'object') {
                                    this.nearbyOdps.forEach(odp => {
                                        if (data.distances[odp.id]) {
                                            odp.calculatedRouteDist = data.distances[odp.id];
                                        }
                                    });
                                    this.nearbyOdps.sort((a, b) => a.calculatedRouteDist - b.calculatedRouteDist);
                                }

                                const bestOdp = this.allOdps.find(o => o.id === data.best_odp_id);
                                if (bestOdp) {
                                    this.nearestOdp = bestOdp;
                                }

                                this.roadRouteDistance = Math.round(data.best_distance);
                                const updatedLineColor = this.getTierColor(this.roadRouteDistance);

                                if (this.cablePolyline) {
                                    this.map.removeLayer(this.cablePolyline);
                                }

                                if (Array.isArray(data.best_geometry) && data.best_geometry.length > 0) {
                                    this.cablePolyline = L.polyline(data.best_geometry, {
                                        color: updatedLineColor,
                                        weight: 4.5,
                                        opacity: 0.95,
                                        dashArray: '6, 6',
                                        className: 'ftth-cable-line'
                                    }).addTo(this.map);

                                    if (this.activeOdp) {
                                        this.updateCoverageCircles(this.activeOdp.lat, this.activeOdp.lng);
                                    }

                                    const bounds = this.cablePolyline.getBounds();
                                    this.map.fitBounds(bounds, { padding: [60, 60], maxZoom: 17 });
                                }
                                return;
                            }
                        }
                    } catch (e) {
                        console.warn('Gagal memuat best route multi-kandidat, fallback ke single route:', e);
                    } finally {
                        this.isRouting = false;
                    }

                    if (this.activeOdp) {
                        this.fetchStreetRoute(lat1, lng1, this.activeOdp.lat, this.activeOdp.lng);
                    }
                },

                async fetchStreetRoute(lat1, lng1, lat2, lng2) {
                    this.isRouting = true;
                    try {
                        // Coba via endpoint proxy lokal /gomsn/network/coverage/route
                        const proxyUrl = `{{ route('gomsn.network.coverage.route') }}?origin=${lat1},${lng1}&dest=${lat2},${lng2}`;
                        let res = await fetch(proxyUrl, { headers: { 'Accept': 'application/json' } });
                        let data = null;

                        if (res.ok) {
                            data = await res.json();
                        } else {
                            // Fallback langsung ke router.project-osrm.org jika proxy lokal gagal
                            const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${lng1},${lat1};${lng2},${lat2}?overview=full&geometries=geojson`;
                            const osrmRes = await fetch(osrmUrl);
                            if (osrmRes.ok) {
                                const osrmData = await osrmRes.json();
                                if (osrmData && osrmData.routes && osrmData.routes[0]) {
                                    const coords = osrmData.routes[0].geometry.coordinates.map(pt => [pt[1], pt[0]]);
                                    data = {
                                        success: true,
                                        distance: osrmData.routes[0].distance,
                                        geometry: coords
                                    };
                                }
                            }
                        }

                        if (data && data.success && Array.isArray(data.geometry) && data.geometry.length > 0) {
                            // Update jarak jalan sesungguhnya dari OSRM
                            this.roadRouteDistance = Math.round(data.distance);
                            const updatedLineColor = this.getTierColor(this.roadRouteDistance);

                            // Gantikan garis lurus dengan garis yang persis mengikuti kelokan jalan raya
                            if (this.cablePolyline) {
                                this.map.removeLayer(this.cablePolyline);
                            }

                            this.cablePolyline = L.polyline(data.geometry, {
                                color: updatedLineColor,
                                weight: 4.5,
                                opacity: 0.95,
                                dashArray: '6, 6',
                                className: 'ftth-cable-line'
                            }).addTo(this.map);

                            // Zoom agar seluruh jalur jalan terlihat nyaman di layar
                            const bounds = this.cablePolyline.getBounds();
                            this.map.fitBounds(bounds, { padding: [60, 60], maxZoom: 17 });
                        }
                    } catch (e) {
                        console.warn('Gagal memuat rute jalan, menggunakan estimasi kontur jalan:', e);
                    } finally {
                        this.isRouting = false;
                    }
                },

                toggleCoverageCircles() {
                    this.showCircles = !this.showCircles;
                    this.updateVisualizations();
                },

                focusToTarget() {
                    if (this.targetCoord && this.map) {
                        this.map.setView(this.targetCoord, 17, { animate: true });
                    }
                },

                fitToAllOdps() {
                    if (!this.map || this.allOdps.length === 0) return;
                    const latlngs = this.allOdps.map(o => [o.lat, o.lng]);
                    if (this.targetCoord) latlngs.push(this.targetCoord);
                    this.map.fitBounds(L.latLngBounds(latlngs), { padding: [40, 40] });
                },

                calculateHaversine(lat1, lon1, lat2, lon2) {
                    const R = 6371e3;
                    const φ1 = lat1 * Math.PI / 180;
                    const φ2 = lat2 * Math.PI / 180;
                    const Δφ = (lat2 - lat1) * Math.PI / 180;
                    const Δλ = (lon2 - lon1) * Math.PI / 180;

                    const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                              Math.cos(φ1) * Math.cos(φ2) *
                              Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

                    return R * c;
                },

                calculateDropcoreNeed(routeDistance) {
                    return Math.round(routeDistance + this.CABLE_SLACK);
                },

                calculateLoss(routeDistance) {
                    const loss = (routeDistance / 1000) * this.OPTICAL_LOSS_PER_KM;
                    return loss.toFixed(2);
                },

                processSmartInput() {
                    const text = this.rawInput.trim();
                    if (!text) return;

                    const atMatch = text.match(/@(-?\d+\.?\d*),(-?\d+\.?\d*)/);
                    if (atMatch) {
                        this.setTargetLocation(parseFloat(atMatch[1]), parseFloat(atMatch[2]), 'Dari URL Google Maps');
                        return;
                    }

                    const qMatch = text.match(/[?&](?:q|ll|query|destination|daddr)=(-?\d+\.?\d*)[,\s]+(-?\d+\.?\d*)/);
                    if (qMatch) {
                        this.setTargetLocation(parseFloat(qMatch[1]), parseFloat(qMatch[2]), 'Dari URL Google Maps');
                        return;
                    }

                    const rawMatch = text.match(/^(-?\d+[\.,]?\d*)[\s,;]+(-?\d+[\.,]?\d*)$/);
                    if (rawMatch) {
                        let lat = parseFloat(rawMatch[1].replace(',', '.'));
                        let lng = parseFloat(rawMatch[2].replace(',', '.'));

                        if (lat >= 95.0 && lat <= 141.5 && lng >= -11.5 && lng <= 6.5) {
                            const tmp = lat; lat = lng; lng = tmp;
                        }
                        if (lat >= 5.5 && lat <= 8.5 && lng >= 105.0 && lng <= 112.0) {
                            lat = -lat;
                        }

                        this.setTargetLocation(lat, lng, 'Koordinat Manual');
                        return;
                    }

                    this.geocodeAddress(text);
                },

                async geocodeAddress(query) {
                    this.isSearching = true;
                    try {
                        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=id&limit=1`;
                        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        const data = await res.json();
                        if (data && data.length > 0) {
                            const item = data[0];
                            this.setTargetLocation(parseFloat(item.lat), parseFloat(item.lon), item.display_name.split(',')[0]);
                        } else {
                            if (window.Swal) {
                                Swal.fire({ icon: 'info', title: 'Alamat Tidak Ditemukan', text: 'Coba masukkan koordinat angka (contoh: -6.936, 107.590) atau klik langsung di peta.' });
                            } else {
                                alert('Alamat tidak ditemukan. Silakan klik langsung pada peta.');
                            }
                        }
                    } catch (e) {
                        console.error('Geocoding error:', e);
                    } finally {
                        this.isSearching = false;
                    }
                },

                async handleSearchSuggestions() {
                    const q = this.rawInput.trim();
                    if (q.length < 3 || q.includes('http') || /^-?\d/.test(q)) {
                        this.suggestions = [];
                        return;
                    }
                    try {
                        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&countrycodes=id&limit=4`;
                        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        const data = await res.json();
                        this.suggestions = data || [];
                    } catch (e) {
                        this.suggestions = [];
                    }
                },

                selectSuggestion(item) {
                    this.suggestions = [];
                    this.setTargetLocation(parseFloat(item.lat), parseFloat(item.lon), item.display_name.split(',')[0]);
                },

                getCurrentLocation() {
                    if (!navigator.geolocation) {
                        alert('Browser Anda tidak mendukung Geolocation GPS.');
                        return;
                    }
                    this.isLocating = true;
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            this.isLocating = false;
                            this.gpsAccuracy = Math.round(pos.coords.accuracy);
                            this.setTargetLocation(pos.coords.latitude, pos.coords.longitude, 'Lokasi GPS Saya Saat Ini');
                        },
                        (err) => {
                            this.isLocating = false;
                            let msg = 'Gagal mendeteksi lokasi GPS.';
                            if (err.code === 1) msg = 'Izin akses lokasi ditolak oleh pengguna.';
                            if (window.Swal) {
                                Swal.fire({ icon: 'warning', title: 'GPS Tidak Terdeteksi', text: msg });
                            } else {
                                alert(msg);
                            }
                        },
                        { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
                    );
                },

                getTierTitle(dist) {
                    if (dist <= 150) return 'Sangat Direkomendasikan';
                    if (dist <= 250) return 'Tercover Standar ISP';
                    if (dist <= 300) return 'Batas Maksimal Tarikan';
                    return 'Di Luar Jangkauan / Butuh ODP';
                },

                getTierDescription(dist) {
                    if (dist <= 150) return 'Kualitas redaman sempurna & pasang baru instan.';
                    if (dist <= 250) return 'Toleransi normal FTTH dengan dropcore 1-core.';
                    if (dist <= 300) return 'Batas maksimal tiang. Wajib survey lintasan jalan.';
                    return 'Jarak > 300 meter. Risiko redaman tinggi. Perlu ODP baru.';
                },

                getTierClass(dist) {
                    if (dist <= 150) return 'bg-emerald-50 text-emerald-950 border-emerald-300 dark:bg-emerald-950/70 dark:text-emerald-200 dark:border-emerald-700/80';
                    if (dist <= 250) return 'bg-sky-50 text-sky-950 border-sky-300 dark:bg-sky-950/70 dark:text-sky-200 dark:border-sky-700/80';
                    if (dist <= 300) return 'bg-amber-50 text-amber-950 border-amber-300 dark:bg-amber-950/70 dark:text-amber-200 dark:border-amber-700/80';
                    return 'bg-rose-50 text-rose-950 border-rose-300 dark:bg-rose-950/70 dark:text-rose-200 dark:border-rose-700/80';
                },

                getTierIconBg(dist) {
                    if (dist <= 150) return 'bg-emerald-600 text-white';
                    if (dist <= 250) return 'bg-sky-600 text-white';
                    if (dist <= 300) return 'bg-amber-500 text-white';
                    return 'bg-rose-600 text-white';
                },

                getTierColor(dist) {
                    if (dist <= 150) return '#10b981';
                    if (dist <= 250) return '#0284c7';
                    if (dist <= 300) return '#f59e0b';
                    return '#e11d48';
                },

                getGoogleMapsRouteUrl() {
                    if (!this.targetCoord || !this.activeOdp) return '#';
                    return `https://www.google.com/maps/dir/?api=1&origin=${this.targetCoord[0]},${this.targetCoord[1]}&destination=${this.activeOdp.lat},${this.activeOdp.lng}&travelmode=driving`;
                },

                copyTargetCoords() {
                    if (!this.targetCoord) return;
                    const text = `${this.targetCoord[0].toFixed(6)}, ${this.targetCoord[1].toFixed(6)}`;
                    navigator.clipboard.writeText(text);
                    this.copiedCoords = true;
                    setTimeout(() => { this.copiedCoords = false; }, 2000);
                },

                copyEvaluationSummary() {
                    if (!this.targetCoord || !this.activeOdp) return;
                    const text = `*HASIL EVALUASI COVERAGE FTTH (GoMSN)*\n` +
                        `• Koordinat Target: ${this.targetCoord[0].toFixed(6)}, ${this.targetCoord[1].toFixed(6)}\n` +
                        `• ODP Terdekat: ${this.activeOdp.nama_odp}\n` +
                        `• OLT / PON: ${this.activeOdp.nama_olt} / ${this.activeOdp.nama_pon}\n` +
                        `• Jarak Jalur Jalan: ${Math.round(this.activeDistance)} meter (Garis lurus: ${Math.round(this.activeStraightDistance)}m)\n` +
                        `• Est. Dropcore: ${this.calculateDropcoreNeed(this.activeDistance)} meter (+18m slack)\n` +
                        `• Kapasitas Port: ${this.activeOdp.port_terpakai}/${this.activeOdp.port_max} (${this.activeOdp.has_slot ? 'Ada Slot' : 'Penuh'})\n` +
                        `• Status Kelayakan: ${this.getTierTitle(this.activeDistance)}\n` +
                        `• Navigasi Google Maps: ${this.getGoogleMapsRouteUrl()}`;

                    navigator.clipboard.writeText(text);
                    this.copiedSummary = true;
                    setTimeout(() => { this.copiedSummary = false; }, 2500);

                    if (window.Swal) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Ringkasan Tersalin ke Clipboard',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }
                },

                requestNetworkExpansion() {
                    if (window.Swal) {
                        Swal.fire({
                            title: 'Permintaan Perluasan Jaringan ODP',
                            html: `
                                <div style="text-align: left; font-size: 13px;">
                                    <p>Lokasi ini berada pada jarak <strong>${Math.round(this.activeDistance)}m</strong> dari ODP terdekat (<strong>${this.activeOdp ? this.activeOdp.nama_odp : '-'}</strong>).</p>
                                    <p style="margin-top: 8px;">Kirim data prospek ini ke tim Perencanaan Jaringan (NOC &amp; Project FTTH) untuk agenda penambahan tiang/ODP baru?</p>
                                </div>
                            `,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#0891b2',
                            cancelButtonColor: '#64748b',
                            confirmButtonText: 'Kirim Permintaan',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Prospek Tercatat',
                                    text: 'Koordinat lokasi telah diteruskan ke daftar prospek perluasan ODP.'
                                });
                            }
                        });
                    }
                }
            };
        };
    </script>

    <!-- ============================================================== -->
    <!-- MAIN INTERACTIVE APP CONTAINER                                 -->
    <!-- ============================================================== -->
    <div class="space-y-4 max-w-[1600px] mx-auto pb-10 px-2 sm:px-4"
         x-data="ftthCoverageApp(@js($odps), @js($metrics), @js($initialTarget))"
         x-init="initApp()">

        <!-- ============================================================== -->
        <!-- 1. TOP HEADER BANNER (METALLIC OBSIDIAN & TEAL NOC)            -->
        <!-- ============================================================== -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-400/35 shadow-[0_6px_24px_rgba(2,43,58,0.4)] p-3.5 sm:p-4 select-none"
             style="background: linear-gradient(135deg, #022330 0%, #033644 35%, #074b59 70%, #085565 100%) !important;">

            <!-- Subtle pattern & gloss -->
            <div class="absolute inset-0 opacity-15 bg-[radial-gradient(#2dd4bf_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>
            <div class="absolute inset-x-0 top-0 h-[2px] bg-gradient-to-r from-transparent via-[#5eead4] to-transparent pointer-events-none"></div>

            <div class="relative z-10 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3 sm:gap-4">
                <!-- Left: Title, Badges & Breadcrumb -->
                <div class="flex items-center gap-3 min-w-0">
                    <a href="{{ route('gomsn.network.index') }}"
                       class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white flex items-center justify-center shrink-0 shadow-xs transition"
                       title="Kembali ke Manajemen OLT, PON & ODP">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>

                    <div class="w-10 h-10 rounded-xl bg-cyan-500/25 border border-cyan-300/40 flex items-center justify-center text-cyan-200 shrink-0 shadow-inner">
                        <svg class="w-5 h-5 text-cyan-200 drop-shadow-[0_0_8px_rgba(45,212,191,0.7)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 style="color: #ffffff !important;" class="text-base sm:text-lg font-black tracking-tight flex items-center gap-2">
                                <span>Cek Coverage Lokasi ke ODP Terdekat</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9.5px] font-black uppercase bg-emerald-400/25 text-emerald-200 border border-emerald-300/40">
                                    Presisi FTTH
                                </span>
                            </h1>
                        </div>
                        <p style="color: #a7f3d0 !important;" class="text-xs mt-0.5 truncate font-medium">
                            Jalur mengikuti jalan raya turn-by-turn, kapasitas port, estimasi dropcore &amp; status pasang baru.
                        </p>
                    </div>
                </div>

                <!-- Right: Metrics Status Bar -->
                <div class="flex items-center gap-2 sm:gap-2.5 flex-nowrap shrink-0 overflow-x-auto pb-0.5">
                    <!-- Total ODP -->
                    <div style="background: rgba(0, 0, 0, 0.45) !important; border: 1px solid rgba(255, 255, 255, 0.2) !important;"
                         class="px-2.5 sm:px-3 py-1.5 rounded-xl backdrop-blur-md flex items-center gap-2 shadow-xs shrink-0">
                        <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                        <div class="text-left">
                            <span style="color: rgba(204, 251, 241, 0.8) !important;" class="text-[9px] uppercase font-bold tracking-wider block leading-tight">Total Node</span>
                            <span style="color: #ffffff !important;" class="font-mono font-black text-xs" x-text="allOdps.length + ' ODP'"></span>
                        </div>
                    </div>

                    <!-- Port Tersedia -->
                    <div style="background: rgba(0, 0, 0, 0.45) !important; border: 1px solid rgba(16, 185, 129, 0.4) !important;"
                         class="px-2.5 sm:px-3 py-1.5 rounded-xl backdrop-blur-md flex items-center gap-2 shadow-xs shrink-0">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <div class="text-left">
                            <span style="color: rgba(167, 243, 208, 0.8) !important;" class="text-[9px] uppercase font-bold tracking-wider block leading-tight">Port Tersedia</span>
                            <span style="color: #6ee7b7 !important;" class="font-mono font-black text-xs" x-text="metrics.total_available_ports + ' Port'"></span>
                        </div>
                    </div>

                    <!-- Batas Rekomendasi -->
                    <div style="background: rgba(0, 0, 0, 0.45) !important; border: 1px solid rgba(245, 158, 11, 0.4) !important;"
                         class="hidden md:flex px-2.5 sm:px-3 py-1.5 rounded-xl backdrop-blur-md items-center gap-2 shadow-xs shrink-0">
                        <svg class="w-3.5 h-3.5 text-amber-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-left">
                            <span style="color: rgba(254, 243, 199, 0.8) !important;" class="text-[9px] uppercase font-bold tracking-wider block leading-tight">Batas Standar</span>
                            <span style="color: #fde68a !important;" class="font-mono font-black text-xs">&le; 150m (Max 300m)</span>
                        </div>
                    </div>

                    <!-- Link Buka Tabel ODP -->
                    <a href="{{ route('gomsn.network.index') }}"
                       style="background: linear-gradient(135deg, #06b6d4 0%, #14b8a6 100%) !important; color: #022330 !important;"
                       class="px-3 py-1.5 rounded-xl font-black text-xs flex items-center gap-1.5 transition shadow-xs hover:brightness-110 active:scale-95 cursor-pointer shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <span>Kelola ODP</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 2. MAIN 2-COLUMN WORKSPACE: LEFT CONTROL + RIGHT LEAFLET MAP   -->
        <!-- ============================================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">

            <!-- ---------------------------------------------------------- -->
            <!-- KOLOM KIRI (lg:col-span-5): INPUT, EVALUASI & ALTERNATIF   -->
            <!-- ---------------------------------------------------------- -->
            <div class="lg:col-span-5 space-y-3.5 ftth-panel-scroll lg:max-h-[calc(100vh-170px)] lg:overflow-y-auto lg:pr-1">

                <!-- CARD A: SMART LOCATION INPUT ENGINE -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-[0_4px_20px_rgb(15,23,42,0.04)] p-4 space-y-3.5">
                    <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="flex h-2.5 w-2.5 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
                            </span>
                            <h2 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100">
                                1. Masukkan Titik Lokasi Calon Pelanggan
                            </h2>
                        </div>
                        <span class="text-[10.5px] font-semibold text-slate-400 dark:text-slate-500">Target Pin</span>
                    </div>

                    <!-- Smart Input Text Field -->
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300">
                            Koordinat, Link Google Maps, atau Nama Jalan:
                        </label>
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>

                                <input type="text"
                                       x-model="rawInput"
                                       @keydown.enter.prevent="processSmartInput()"
                                       @input.debounce.400ms="handleSearchSuggestions()"
                                       placeholder="Contoh: -6.936988, 107.59045 atau link Maps..."
                                       class="w-full pl-9 pr-3 py-2 text-xs font-mono rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 placeholder:font-sans focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-600 transition">
                            </div>

                            <!-- Tombol Cek -->
                            <button type="button"
                                    @click="processSmartInput()"
                                    :disabled="!rawInput.trim() || isSearching"
                                    class="px-3.5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 disabled:opacity-50 text-white font-bold text-xs flex items-center gap-1.5 shrink-0 shadow-xs transition cursor-pointer">
                                <svg x-show="!isSearching" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <svg x-show="isSearching" x-cloak class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Cek</span>
                            </button>
                        </div>

                        <!-- Dropdown Hasil Saran Geocoding Nominatim -->
                        <div x-show="suggestions.length > 0"
                             x-cloak
                             @click.outside="suggestions = []"
                             class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-xl overflow-hidden text-xs max-h-48 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700 z-50">
                            <template x-for="item in suggestions" :key="item.place_id">
                                <button type="button"
                                        @click="selectSuggestion(item)"
                                        class="w-full text-left px-3.5 py-2 hover:bg-cyan-50 dark:hover:bg-slate-700/60 transition flex items-start gap-2">
                                    <svg class="w-3.5 h-3.5 text-cyan-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-800 dark:text-slate-100 truncate" x-text="item.display_name.split(',')[0]"></p>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate" x-text="item.display_name"></p>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Action Shortcut Buttons: GPS & Click-on-Map Tips -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <!-- GPS Browser Geolocation Button -->
                        <button type="button"
                                @click="getCurrentLocation()"
                                :disabled="isLocating"
                                class="py-2 px-3 rounded-xl border border-cyan-500/30 bg-cyan-50/80 hover:bg-cyan-100 dark:bg-cyan-950/40 dark:hover:bg-cyan-900/60 text-cyan-800 dark:text-cyan-200 font-bold text-xs flex items-center justify-center gap-2 transition cursor-pointer disabled:opacity-60 shadow-xs">
                            <svg x-show="!isLocating" class="w-3.5 h-3.5 text-cyan-600 dark:text-cyan-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <svg x-show="isLocating" x-cloak class="w-3.5 h-3.5 text-cyan-600 animate-spin shrink-0" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span x-text="isLocating ? 'Mencari GPS...' : 'GPS Lokasi Saya'"></span>
                            <span x-show="gpsAccuracy" x-cloak class="px-1.5 py-0.2 rounded text-[9px] font-mono bg-cyan-200/80 dark:bg-cyan-800 text-cyan-900 dark:text-cyan-100"
                                  x-text="'±' + gpsAccuracy + 'm'"></span>
                        </button>

                        <!-- Tip Drag / Klik Pin -->
                        <div class="py-2 px-3 rounded-xl bg-slate-100/90 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/80 text-[11px] text-slate-600 dark:text-slate-300 flex items-center justify-center gap-1.5 select-none shadow-xs">
                            <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="truncate">Klik peta / geser pin</span>
                        </div>
                    </div>

                    <!-- Target Coordinate Info Pill -->
                    <div x-show="targetCoord" x-cloak class="p-2.5 rounded-xl bg-cyan-50/70 dark:bg-slate-800/90 border border-cyan-200/80 dark:border-slate-700 flex items-center justify-between gap-2 text-xs shadow-xs">
                        <div class="min-w-0">
                            <span class="text-[9.5px] uppercase font-bold text-cyan-800 dark:text-cyan-300 block tracking-wider">Titik Target Terpilih:</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-100 truncate block text-[11.5px]"
                                  x-text="targetCoord ? targetCoord[0].toFixed(6) + ', ' + targetCoord[1].toFixed(6) : '-'"></span>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <button type="button"
                                    @click="copyTargetCoords()"
                                    class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-medium text-[11px] flex items-center gap-1 transition shadow-xs">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                <span x-text="copiedCoords ? 'Tersalin!' : 'Salin'"></span>
                            </button>
                            <button type="button"
                                    @click="resetTarget()"
                                    title="Hapus / Reset Titik Target"
                                    class="p-1.5 rounded-lg bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 hover:bg-rose-50 dark:hover:bg-rose-950/50 text-slate-400 hover:text-rose-600 dark:hover:text-rose-300 transition shadow-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Radius & Slot Filters -->
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2 text-xs">
                        <div class="flex items-center gap-1.5">
                            <span class="text-slate-500 dark:text-slate-400 text-[11px] font-medium shrink-0">Radius:</span>
                            <select x-model.number="filterRadius"
                                    @change="applyOdpFilters()"
                                    class="py-1 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold focus:ring-1 focus:ring-cyan-500">
                                <option value="150">150m (Standar Ideal)</option>
                                <option value="300">300m (Batas Maksimal)</option>
                                <option value="500">500m (Jangkauan Luas)</option>
                                <option value="1000">1.000m (1 Km)</option>
                                <option value="999999">Semua ODP (Tanpa Batas)</option>
                            </select>
                        </div>

                        <label class="flex items-center gap-2 cursor-pointer select-none text-[11px] text-slate-600 dark:text-slate-300 font-medium">
                            <input type="checkbox"
                                   x-model="filterOnlyAvailableSlot"
                                   @change="applyOdpFilters()"
                                   class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                            <span>Hanya ODP ada slot port</span>
                        </label>
                    </div>
                </div>

                <!-- CARD B1: PROMPT PLACEHOLDER JIKA BELUM MEMILIH LOKASI -->
                <div x-show="!targetCoord"
                     class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-6 text-center space-y-3 shadow-xs">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-cyan-50 dark:bg-cyan-950/50 border border-cyan-200 dark:border-cyan-800 flex items-center justify-center text-cyan-600 dark:text-cyan-400 shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="max-w-xs mx-auto">
                        <h3 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100">Pilih Titik di Peta</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                            Klik di peta atau masukkan koordinat untuk menghitung jalur kabel yang <strong>mengikuti jalan raya</strong> dan kapasitas ODP.
                        </p>
                    </div>
                </div>

                <!-- CARD B2: COVERAGE EVALUATION DECISION CARD (PRIMARY RESULT) -->
                <div x-show="targetCoord && nearestOdp" x-cloak
                     class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-[0_4px_20px_rgb(15,23,42,0.06)] p-4 space-y-3.5 transition-all">

                    <!-- Header Evaluasi & Tier Badge -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[9.5px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                2. Hasil Evaluasi Kelayakan FTTH
                            </span>
                            <span class="text-[11px] font-mono font-bold text-slate-500"
                                  x-text="'ODP #' + (selectedOdp ? selectedOdp.id : (nearestOdp ? nearestOdp.id : ''))"></span>
                        </div>

                        <!-- Big Tier Status Badge (Dark & Light Mode Friendly) -->
                        <div :class="getTierClass(activeDistance)"
                             class="p-3 rounded-xl border flex items-center justify-between gap-3 shadow-xs">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                                     :class="getTierIconBg(activeDistance)">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                                        <template x-if="activeDistance <= 150">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </template>
                                        <template x-if="activeDistance > 150 && activeDistance <= 250">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </template>
                                        <template x-if="activeDistance > 250 && activeDistance <= 300">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </template>
                                        <template x-if="activeDistance > 300">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        </template>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-black text-xs sm:text-sm tracking-tight"
                                        x-text="getTierTitle(activeDistance)"></h3>
                                    <p class="text-[10.5px] opacity-90 truncate font-medium"
                                       x-text="getTierDescription(activeDistance)"></p>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <div class="flex items-center gap-1 justify-end">
                                    <svg x-show="isRouting" class="w-3.5 h-3.5 animate-spin text-cyan-600" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span class="font-mono font-black text-base sm:text-lg block leading-none"
                                          x-text="Math.round(activeDistance) + ' m'"></span>
                                </div>
                                <span class="text-[9px] uppercase tracking-wider opacity-80 font-bold block mt-0.5">Jalur Jalan</span>
                            </div>
                        </div>
                    </div>

                    <!-- Selected ODP Information Details -->
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700 space-y-3">
                        <div class="flex items-center justify-between gap-2 pb-2 border-b border-slate-200/60 dark:border-slate-700/60">
                            <div class="min-w-0">
                                <span class="text-[9.5px] uppercase font-bold text-slate-400 block">ODP Terpilih</span>
                                <h4 class="font-extrabold text-xs sm:text-sm text-slate-900 dark:text-white truncate"
                                    x-text="activeOdp ? activeOdp.nama_odp : '-'"></h4>
                            </div>

                            <!-- Port Status Badge -->
                            <span :class="activeOdp && activeOdp.has_slot ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800' : 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800'"
                                  class="px-2.5 py-0.5 rounded-full border text-[10.5px] font-bold flex items-center gap-1.5 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full"
                                      :class="activeOdp && activeOdp.has_slot ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                <span x-text="activeOdp && activeOdp.has_slot ? 'Ada Slot Port' : 'Port Penuh'"></span>
                            </span>
                        </div>

                        <!-- 4-Grid Metrik FTTH Presisi -->
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <!-- Metric 1: Jarak Rute Jalan -->
                            <div class="p-2.5 rounded-lg bg-white dark:bg-slate-700/60 border border-slate-100 dark:border-slate-700">
                                <span class="text-[9.5px] text-slate-400 block leading-tight">Jalur Jalan Raya</span>
                                <span class="font-mono font-bold text-slate-800 dark:text-slate-100 text-xs sm:text-sm"
                                      x-text="Math.round(activeDistance) + ' meter'"></span>
                            </div>

                            <!-- Metric 2: Estimasi Dropcore (+18m slack) -->
                            <div class="p-2.5 rounded-lg bg-white dark:bg-slate-700/60 border border-slate-100 dark:border-slate-700">
                                <span class="text-[9.5px] text-slate-400 block leading-tight">Est. Dropcore (+18m)</span>
                                <span class="font-mono font-bold text-cyan-600 dark:text-cyan-300 text-xs sm:text-sm"
                                      x-text="calculateDropcoreNeed(activeDistance) + ' meter'"></span>
                            </div>

                            <!-- Metric 3: Kapasitas Port -->
                            <div class="p-2.5 rounded-lg bg-white dark:bg-slate-700/60 border border-slate-100 dark:border-slate-700">
                                <span class="text-[9.5px] text-slate-400 block leading-tight">Port Terpakai</span>
                                <span class="font-mono font-bold text-slate-800 dark:text-slate-100 text-xs sm:text-sm"
                                      x-text="activeOdp ? (activeOdp.port_terpakai + ' / ' + activeOdp.port_max + ' Port') : '-'"></span>
                            </div>

                            <!-- Metric 4: Estimasi Redaman Optik Tambahan -->
                            <div class="p-2.5 rounded-lg bg-white dark:bg-slate-700/60 border border-slate-100 dark:border-slate-700">
                                <span class="text-[9.5px] text-slate-400 block leading-tight">Est. Redaman Kabel</span>
                                <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400 text-xs sm:text-sm"
                                      x-text="calculateLoss(activeDistance) + ' dB'"></span>
                            </div>
                        </div>

                        <!-- OLT, PON & Lokasi Information Bar -->
                        <div class="pt-1.5 border-t border-slate-200/60 dark:border-slate-700/60 text-[10.5px] text-slate-500 dark:text-slate-400 flex flex-wrap items-center justify-between gap-1.5">
                            <div>
                                <span class="font-semibold text-slate-700 dark:text-slate-200" x-text="activeOdp ? activeOdp.nama_olt : '-'"></span>
                                <span class="text-slate-400 mx-1">&bull;</span>
                                <span x-text="activeOdp ? activeOdp.nama_pon : '-'"></span>
                            </div>
                            <span class="font-mono text-[10px] text-slate-400"
                                  x-text="activeOdp ? (activeOdp.lat.toFixed(5) + ', ' + activeOdp.lng.toFixed(5)) : ''"></span>
                        </div>
                    </div>

                    <!-- Action Buttons: Google Maps Route + WhatsApp Copier -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 pt-0.5">
                        <a :href="getGoogleMapsRouteUrl()"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="flex-1 py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white hover:bg-slate-50 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-100 font-bold text-xs flex items-center justify-center gap-1.5 transition shadow-xs">
                            <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                            </svg>
                            <span>Buka Rute Maps</span>
                        </a>

                        <button type="button"
                                @click="copyEvaluationSummary()"
                                class="flex-1 py-2 px-3 rounded-xl border border-cyan-500/30 bg-cyan-50 hover:bg-cyan-100/80 dark:bg-cyan-950/50 dark:hover:bg-cyan-900/60 text-cyan-800 dark:text-cyan-200 font-bold text-xs flex items-center justify-center gap-1.5 transition cursor-pointer">
                            <svg class="w-4 h-4 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                            </svg>
                            <span x-text="copiedSummary ? 'Laporan Tersalin!' : 'Salin Info Sales'"></span>
                        </button>
                    </div>

                    <!-- Direct Action: Pasang Baru atau Perluasan Jaringan -->
                    <template x-if="activeDistance <= 300 && activeOdp && activeOdp.has_slot">
                        <a :href="activeOdp.url_detail ? activeOdp.url_detail : '{{ route('gomsn.network.index') }}'"
                           class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs flex items-center justify-center gap-2 transition shadow-md shadow-emerald-900/20">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            <span>Daftarkan Pelanggan ke ODP Ini</span>
                        </a>
                    </template>

                    <template x-if="activeDistance > 300 || (activeOdp && !activeOdp.has_slot)">
                        <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 text-xs text-rose-800 dark:text-rose-200 flex flex-col sm:flex-row items-center justify-between gap-2.5">
                            <span class="leading-relaxed text-[11.5px]">
                                <strong x-text="activeDistance > 300 ? 'Jarak jalan melebihi 300m.' : 'Port ODP terdekat penuh.'"></strong>
                                Butuh perluasan ODP baru untuk pasang baru di lokasi ini.
                            </span>
                            <button type="button"
                                    @click="requestNetworkExpansion()"
                                    class="py-1.5 px-3 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shrink-0 transition">
                                Form Permintaan ODP
                            </button>
                        </div>
                    </template>
                </div>

                <!-- CARD C: TOP 3-5 ALTERNATIVE NEARBY ODPs LIST -->
                <div x-show="targetCoord && nearbyOdps.length > 1" x-cloak
                     class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-[0_4px_20px_rgb(15,23,42,0.04)] p-4 space-y-2.5">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            <h3 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100">
                                Alternatif ODP Terdekat Lainnya
                            </h3>
                        </div>
                        <span class="text-[10.5px] font-mono text-slate-400" x-text="nearbyOdps.length + ' ODP'"></span>
                    </div>

                    <div class="space-y-2 max-h-56 overflow-y-auto pr-1 ftth-panel-scroll">
                        <template x-for="(item, idx) in nearbyOdps" :key="item.id">
                            <div class="p-2.5 rounded-xl border transition-all flex items-center justify-between gap-3 text-xs"
                                 :class="activeOdp && activeOdp.id === item.id ? 'border-cyan-500 bg-cyan-50/60 dark:bg-cyan-950/40 shadow-xs' : 'border-slate-100 dark:border-slate-800 hover:border-slate-200 dark:hover:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40'">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-extrabold text-slate-800 dark:text-slate-100 truncate" x-text="item.nama_odp"></span>
                                        <span :class="item.has_slot ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300'"
                                              class="text-[9px] font-bold px-1.5 py-0.2 rounded"
                                              x-text="item.has_slot ? (item.port_tersedia + ' slot') : 'Penuh'"></span>
                                    </div>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 truncate">
                                        <span class="font-semibold text-slate-600 dark:text-slate-300" x-text="item.nama_olt"></span> &bull; <span x-text="item.nama_pon"></span>
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <div class="text-right">
                                        <span class="font-mono font-bold text-slate-800 dark:text-slate-100 block text-[11px]"
                                              x-text="'~' + Math.round(item.calculatedRouteDist) + 'm'"></span>
                                    </div>

                                    <button type="button"
                                            @click="selectOdp(item)"
                                            :disabled="activeOdp && activeOdp.id === item.id"
                                            :class="activeOdp && activeOdp.id === item.id ? 'bg-cyan-600 text-white' : 'bg-white dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600'"
                                            class="py-1 px-2.5 rounded-lg font-bold text-[10.5px] transition cursor-pointer">
                                        <span x-text="activeOdp && activeOdp.id === item.id ? 'Aktif' : 'Pilih'"></span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Petunjuk Standar Tarikan FTTH Info Box -->
                <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-3.5 space-y-2.5 text-xs shadow-xs">
                    <div class="flex items-center gap-2 font-bold text-slate-800 dark:text-slate-100 text-xs">
                        <div class="w-5 h-5 rounded-md bg-cyan-100 dark:bg-cyan-950/70 text-cyan-600 dark:text-cyan-400 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span>Standar Kebijakan Jarak Tarikan FTTH</span>
                    </div>
                    <div class="space-y-1.5 text-[11px] text-slate-600 dark:text-slate-300 pt-0.5">
                        <div class="flex items-start gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 mt-1 shrink-0"></span>
                            <p><strong class="text-emerald-700 dark:text-emerald-400">&le; 150 m:</strong> Sangat Direkomendasikan. Redaman optik prima, pasang baru instan.</p>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="w-2 h-2 rounded-full bg-sky-500 mt-1 shrink-0"></span>
                            <p><strong class="text-sky-700 dark:text-sky-400">151 - 250 m:</strong> Tercover Standar ISP toleransi normal dropcore 1-core.</p>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500 mt-1 shrink-0"></span>
                            <p><strong class="text-amber-700 dark:text-amber-400">251 - 300 m:</strong> Batas Maksimal toleransi tiang. Wajib survey lintasan jalan.</p>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500 mt-1 shrink-0"></span>
                            <p><strong class="text-rose-700 dark:text-rose-400">&gt; 300 m:</strong> Di Luar Jangkauan. Redaman tinggi, butuh perluasan ODP baru.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ---------------------------------------------------------- -->
            <!-- KOLOM KANAN (lg:col-span-7): INTERACTIVE LEAFLET MAP CANVAS-->
            <!-- ---------------------------------------------------------- -->
            <div class="lg:col-span-7">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-[0_4px_24px_rgb(15,23,42,0.06)] overflow-hidden flex flex-col">

                    <!-- Top Map Toolbar: Layers + Quick Centering + Legend -->
                    <div class="p-2 sm:px-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/90 dark:bg-slate-900 flex flex-wrap items-center justify-between gap-2 z-10 select-none">
                        <!-- Layer Mode Switcher -->
                        <div class="inline-flex rounded-xl p-0.5 bg-slate-200/80 dark:bg-slate-800 text-xs font-semibold">
                            <button type="button"
                                    @click="setTileLayer('street')"
                                    :class="currentTile === 'street' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'"
                                    class="py-1 px-2.5 rounded-lg transition cursor-pointer flex items-center gap-1.5 text-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                </svg>
                                <span>Peta Jalan</span>
                            </button>

                            <button type="button"
                                    @click="setTileLayer('satellite')"
                                    :class="currentTile === 'satellite' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'"
                                    class="py-1 px-2.5 rounded-lg transition cursor-pointer flex items-center gap-1.5 text-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
                                </svg>
                                <span>Satelit</span>
                            </button>

                            <button type="button"
                                    @click="setTileLayer('dark')"
                                    :class="currentTile === 'dark' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'"
                                    class="py-1 px-2.5 rounded-lg transition cursor-pointer flex items-center gap-1.5 text-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                </svg>
                                <span>Dark NOC</span>
                            </button>
                        </div>

                        <!-- Map View Toggles & Actions -->
                        <div class="flex items-center gap-2">
                            <!-- Toggle Circles 150m/300m -->
                            <button type="button"
                                    @click="toggleCircles()"
                                    :class="showCircles ? 'bg-cyan-50 dark:bg-cyan-950/60 text-cyan-800 dark:text-cyan-200 border-cyan-400 dark:border-cyan-700' : 'bg-white dark:bg-slate-800 text-slate-500 border-slate-200 dark:border-slate-700'"
                                    class="py-1 px-2.5 rounded-xl border font-bold text-xs flex items-center gap-1.5 transition cursor-pointer shadow-xs">
                                <span class="w-2 h-2 rounded-full" :class="showCircles ? 'bg-cyan-500' : 'bg-slate-300 dark:bg-slate-600'"></span>
                                <span>Radius 150/300m</span>
                            </button>

                            <!-- Focus Target Button -->
                            <button type="button"
                                    x-show="targetCoord"
                                    x-cloak
                                    @click="focusTarget()"
                                    class="py-1 px-2.5 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs flex items-center gap-1.5 transition cursor-pointer shadow-xs">
                                <svg class="w-3.5 h-3.5 text-rose-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Fokus Target</span>
                            </button>

                            <!-- Reset Zoom Button -->
                            <button type="button"
                                    @click="resetMapView()"
                                    class="py-1 px-2.5 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs flex items-center gap-1.5 transition cursor-pointer shadow-xs">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                                </svg>
                                <span>Reset Zoom</span>
                            </button>
                        </div>
                    </div>

                    <!-- Canvas Map Container (Guaranteed Explicit Height) -->
                    <div class="relative w-full bg-slate-100 dark:bg-slate-950" style="min-height: 600px; height: calc(100vh - 210px);">
                        <div id="coverageMap" style="width: 100%; height: 100%; min-height: 600px; position: absolute; inset: 0; z-index: 1;"></div>

                        <!-- Routing Loading Indicator overlay on map -->
                        <div x-show="isRouting" x-cloak
                             class="absolute top-3 left-1/2 -translate-x-1/2 z-[400] px-3 py-1.5 rounded-full bg-slate-900/90 text-cyan-300 border border-cyan-500/40 shadow-xl backdrop-blur-md text-[11px] font-bold flex items-center gap-2 pointer-events-none">
                            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>Menghitung Jalur Jalan Tiang...</span>
                        </div>

                        <!-- Bottom Left Map Floating Legend -->
                        <div class="absolute bottom-3 left-3 z-[400] p-2.5 rounded-xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-slate-200/80 dark:border-slate-800 shadow-xl text-[10px] space-y-1.5 select-none">
                            <span class="font-bold text-slate-800 dark:text-slate-200 block text-[10.5px] mb-1">Keterangan Node &amp; Jalur:</span>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-emerald-500 border border-white shadow-xs"></span>
                                <span class="text-slate-600 dark:text-slate-300">ODP Ada Slot Port</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-rose-500 border border-white shadow-xs"></span>
                                <span class="text-slate-600 dark:text-slate-300">ODP Port Penuh</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-rose-600 animate-ping"></span>
                                <span class="text-slate-600 dark:text-slate-300">Titik Target Calon Pelanggan</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3.5 h-0.5 border-t-2 border-dashed border-emerald-500"></span>
                                <span class="text-slate-600 dark:text-slate-300 font-semibold">Jalur Kabel (Mengikuti Jalan)</span>
                            </div>
                        </div>

                        <!-- Bottom Right Floating Coordinate Cursor -->
                        <div class="absolute bottom-3 right-3 z-[400] px-2.5 py-1 rounded-lg bg-black/75 backdrop-blur-xs text-white font-mono text-[10px] hidden sm:block pointer-events-none"
                             x-text="mouseCoordText">
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
