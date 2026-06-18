/**
 * Envia GPS do entregador para o servidor durante entregas ativas.
 * iOS/Safari exige toque do usuário antes de pedir permissão de localização.
 */
(function () {
    const cfg = window.__motoboyTrack;
    if (!cfg || !cfg.token || !Array.isArray(cfg.deliveries)) {
        return;
    }

    const endpoint = '/m/' + encodeURIComponent(cfg.token) + '/localizacao';
    const active = cfg.deliveries.filter(function (d) {
        return d.delivery_status === 'out_for_delivery';
    });

    if (active.length === 0) {
        return;
    }

    const statusEl = document.getElementById('motoboy-gps-status');
    const startBtn = document.getElementById('motoboy-gps-start');
    let watchId = null;
    let simInterval = null;
    let lastSent = 0;
    let trackingStarted = false;
    const minIntervalMs = 12000;
    const isLocalDev = /^(localhost|127\.0\.0\.1)$/i.test(location.hostname);

    function setStatus(text, ok) {
        if (!statusEl) {
            return;
        }
        statusEl.textContent = text;
        statusEl.classList.toggle('text-emerald-400', !!ok);
        statusEl.classList.toggle('text-amber-400', !ok);
    }

    function hideStartButton() {
        if (startBtn) {
            startBtn.classList.add('hidden');
        }
    }

    function sendPosition(lat, lng, accuracy) {
        const now = Date.now();
        if (now - lastSent < minIntervalMs) {
            return;
        }
        lastSent = now;

        const payload = {
            delivery_id: active[0].delivery_id,
            lat: lat,
            lng: lng,
        };
        if (typeof accuracy === 'number' && !isNaN(accuracy)) {
            payload.accuracy = accuracy;
        }

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
            credentials: 'same-origin',
        })
            .then(function (res) {
                return res.json().catch(function () {
                    return {};
                }).then(function (data) {
                    if (res.ok && data.ok) {
                        setStatus('Localização compartilhada em tempo real', true);
                    } else if (res.status === 419 || (data && data.error === 'csrf')) {
                        setStatus('Sessão expirada — atualize a página do motoboy', false);
                    } else if (res.status === 429) {
                        setStatus('Muitas atualizações — aguardando…', false);
                    } else {
                        setStatus((data && data.message) ? data.message : 'Erro ao enviar GPS (' + res.status + ')', false);
                    }
                });
            })
            .catch(function () {
                setStatus('Sem conexão para enviar localização', false);
            });
    }

    function onPosition(pos) {
        if (!trackingStarted) {
            trackingStarted = true;
            hideStartButton();
        }
        sendPosition(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy);
    }

    function startDevSimulation() {
        if (simInterval) {
            return;
        }
        trackingStarted = true;
        hideStartButton();
        setStatus('Modo dev: posição simulada (GPS bloqueado no navegador)', false);
        var baseLat = -23.5505;
        var baseLng = -46.6333;
        var step = 0;
        simInterval = setInterval(function () {
            step += 1;
            sendPosition(
                baseLat + Math.sin(step / 3) * 0.008,
                baseLng + Math.cos(step / 3) * 0.008,
                25
            );
        }, minIntervalMs);
        sendPosition(baseLat, baseLng, 25);
    }

    function onError(err) {
        var code = err && err.code;
        if (code === 1) {
            setStatus('Permissão negada. Ajustes → Safari → Localização → Permitir', false);
            if (startBtn) {
                startBtn.classList.remove('hidden');
                startBtn.textContent = 'Tentar novamente';
            }
        } else {
            setStatus('Não foi possível obter GPS — verifique o sinal', false);
            if (startBtn) {
                startBtn.classList.remove('hidden');
                startBtn.textContent = 'Tentar novamente';
            }
        }
        if (isLocalDev) {
            startDevSimulation();
        }
    }

    function startTracking() {
        if (!navigator.geolocation) {
            setStatus('Este dispositivo não suporta GPS', false);
            if (isLocalDev) {
                startDevSimulation();
            }
            return;
        }

        if (watchId !== null) {
            return;
        }

        setStatus('Solicitando permissão de localização…', false);

        navigator.geolocation.getCurrentPosition(
            function (pos) {
                onPosition(pos);
                watchId = navigator.geolocation.watchPosition(onPosition, onError, {
                    enableHighAccuracy: true,
                    maximumAge: 10000,
                    timeout: 20000,
                });
            },
            onError,
            {
                enableHighAccuracy: true,
                maximumAge: 0,
                timeout: 20000,
            }
        );
    }

    if (startBtn) {
        startBtn.addEventListener('click', startTracking);
    } else {
        startTracking();
    }

    document.addEventListener('visibilitychange', function () {
        if (!trackingStarted) {
            return;
        }
        if (document.hidden && watchId !== null) {
            navigator.geolocation.clearWatch(watchId);
            watchId = null;
        } else if (!document.hidden && watchId === null && !simInterval) {
            watchId = navigator.geolocation.watchPosition(onPosition, onError, {
                enableHighAccuracy: true,
                maximumAge: 10000,
                timeout: 20000,
            });
        }
    });
})();
