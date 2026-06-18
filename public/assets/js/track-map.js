/**
 * Mapa de rastreio ao vivo com Google Maps.
 */
window.dfInitTrackMap = function () {
    const cfg = window.__trackMapConfig;
    if (!cfg || !cfg.showLiveMap || typeof google === 'undefined' || !google.maps) {
        return;
    }

    const mapEl = document.getElementById('track-map');
    const statusEl = document.getElementById('track-map-status');
    if (!mapEl) {
        return;
    }

    const token = cfg.token;
    let lastStatus = cfg.lastStatus;
    const terminal = ['entregue', 'cancelado'];
    const mapDestination = cfg.mapDestination;

    const startLat = mapDestination && mapDestination.lat ? mapDestination.lat : -23.5505;
    const startLng = mapDestination && mapDestination.lng ? mapDestination.lng : -46.6333;

    const map = new google.maps.Map(mapEl, {
        center: { lat: startLat, lng: startLng },
        zoom: mapDestination ? 15 : 12,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: true,
        gestureHandling: 'cooperative',
    });

    let destMarker = null;
    let riderMarker = null;
    let routeLine = null;

    function makeMarker(position, label, title) {
        return new google.maps.Marker({
            position: position,
            map: map,
            title: title,
            label: { text: label, fontSize: '18px', fontWeight: '700' },
        });
    }

    if (mapDestination && mapDestination.lat && mapDestination.lng) {
        destMarker = makeMarker(
            { lat: mapDestination.lat, lng: mapDestination.lng },
            '🏠',
            'Seu endereço'
        );
    }

    function setMapStatus(text) {
        if (statusEl) {
            statusEl.textContent = text;
        }
    }

    function fitBounds() {
        const bounds = new google.maps.LatLngBounds();
        let has = false;
        if (destMarker) {
            bounds.extend(destMarker.getPosition());
            has = true;
        }
        if (riderMarker) {
            bounds.extend(riderMarker.getPosition());
            has = true;
        }
        if (has) {
            map.fitBounds(bounds, { top: 48, right: 48, bottom: 48, left: 48 });
            if (map.getZoom() > 16) {
                map.setZoom(16);
            }
        }
    }

    async function pollLocation() {
        if (terminal.includes(lastStatus)) {
            return;
        }
        try {
            const res = await fetch('/api/pedido/' + encodeURIComponent(token) + '/localizacao', {
                headers: { Accept: 'application/json' },
            });
            const data = await res.json();
            if (!data || !data.ok || !data.trackable) {
                setMapStatus('Aguardando entregador…');
                return;
            }

            if (data.destination && data.destination.lat && data.destination.lng) {
                const pos = { lat: data.destination.lat, lng: data.destination.lng };
                if (!destMarker) {
                    destMarker = makeMarker(pos, '🏠', 'Seu endereço');
                } else {
                    destMarker.setPosition(pos);
                }
            }

            const m = data.motoboy;
            if (m && typeof m.lat === 'number' && typeof m.lng === 'number') {
                const pos = { lat: m.lat, lng: m.lng };
                if (!riderMarker) {
                    riderMarker = makeMarker(pos, '🛵', 'Entregador');
                } else {
                    riderMarker.setPosition(pos);
                }

                if (destMarker) {
                    const path = [pos, destMarker.getPosition()];
                    if (routeLine) {
                        routeLine.setPath(path);
                    } else {
                        routeLine = new google.maps.Polyline({
                            path: path,
                            strokeColor: '#059669',
                            strokeWeight: 4,
                            strokeOpacity: 0.75,
                            map: map,
                        });
                    }
                }

                setMapStatus(m.stale ? 'Última posição há alguns minutos' : 'Atualizado agora');
                fitBounds();
            } else {
                setMapStatus('Aguardando GPS do entregador…');
                if (destMarker) {
                    fitBounds();
                }
            }
        } catch (e) {
            setMapStatus('Sem conexão com o mapa');
        }
    }

    pollLocation();
    setInterval(pollLocation, 10000);
};

(function () {
    const cfg = window.__trackMapConfig;
    if (!cfg || !cfg.showLiveMap) {
        return;
    }

    async function pollStatus() {
        if (['entregue', 'cancelado'].includes(cfg.lastStatus)) {
            return;
        }
        try {
            const res = await fetch('/api/pedido/' + encodeURIComponent(cfg.token) + '/status', {
                headers: { Accept: 'application/json' },
            });
            const data = await res.json();
            if (data && data.ok && data.status && data.status !== cfg.lastStatus) {
                location.reload();
            }
        } catch (e) {}
    }

    if (!['entregue', 'cancelado'].includes(cfg.lastStatus)) {
        setInterval(pollStatus, 12000);
    }

    if (!cfg.googleMapsKey && cfg.showLiveMap) {
        const statusEl = document.getElementById('track-map-status');
        if (statusEl) {
            statusEl.textContent = 'Configure GOOGLE_MAPS_API_KEY no servidor';
        }
    }
})();
