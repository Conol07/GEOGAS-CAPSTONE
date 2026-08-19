@extends('layouts.app')
@section('title', 'Station Map')

@section('content')
<h4 class="mb-1">Interactive Station Map</h4>
<p class="text-muted-gg mb-3">Locate participating gasoline stations across Manolo Fortich, Bukidnon.</p>

<div class="gg-map-shell">
    <div class="gg-map-controls">
        <div class="input-group" style="max-width:420px;">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted-gg"></i></span>
            <input type="text" id="mapSearch" class="form-control border-start-0" placeholder="Search stations on the map">
        </div>
    </div>
    <div class="gg-map-layout">
        <div id="mainMap" class="gg-map-canvas"></div>
        <div class="gg-map-list" id="mapStationList">
            {{-- populated by JS from the same station data driving the map --}}
        </div>
    </div>
</div>

@push('scripts')
<script>
    const stations = @json($stations);
    const map = L.map('mainMap').setView([8.3696, 124.8642], 14); // centered on Manolo Fortich, Bukidnon
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);

    function gasStationIcon(selected = false) {
        return L.divIcon({
            className: '',
            html: `<div class="gg-marker-pin${selected ? ' selected' : ''}"><i class="bi bi-fuel-pump-fill"></i></div>`,
            iconSize: [28, 28],
            iconAnchor: [14, 28],
            popupAnchor: [0, -26],
        });
    }

    const markers = {};
    let activeMarkerId = null;

    function setActiveMarker(id) {
        if (activeMarkerId !== null && markers[activeMarkerId]) {
            markers[activeMarkerId].setIcon(gasStationIcon(false));
        }
        markers[id].setIcon(gasStationIcon(true));
        activeMarkerId = id;
    }

    function popupHtml(s) {
        const price = s.latest_approved_price;
        const priceHtml = price
            ? `<div class="price-line">Gasoline: <strong>₱${Number(price.gasoline_price).toFixed(2)}</strong></div>
               <div class="price-line">Diesel: <strong>₱${Number(price.diesel_price).toFixed(2)}</strong></div>
               <div class="text-muted-gg" style="font-size:.72rem;">Updated ${price.effective_date}</div>`
            : '<div class="text-muted-gg" style="font-size:.8rem;">No verified price yet</div>';
        return `<div class="gg-popup">
                    <h6>${s.station_name}</h6>
                    <div class="addr">${s.address}</div>
                    ${priceHtml}
                    <a href="/stations/${s.id}">View Details →</a>
                </div>`;
    }

    const listEl = document.getElementById('mapStationList');

    function renderList(filtered) {
        listEl.innerHTML = '';
        filtered.forEach(s => {
            const price = s.latest_approved_price;
            const item = document.createElement('div');
            item.className = 'gg-map-list-item';
            item.innerHTML = `<div class="name">${s.station_name}</div>
                               <div class="addr"><i class="bi bi-geo-alt-fill"></i> ${s.address}</div>
                               ${price ? `<div class="price">₱${Number(price.gasoline_price).toFixed(2)} / L gasoline</div>` : ''}`;
            item.addEventListener('click', () => {
                map.setView([s.latitude, s.longitude], 17);
                setActiveMarker(s.id);
                markers[s.id].openPopup();
            });
            listEl.appendChild(item);
        });
    }

    stations.forEach(s => {
        markers[s.id] = L.marker([s.latitude, s.longitude], { icon: gasStationIcon(false) })
            .addTo(map)
            .bindPopup(popupHtml(s));
        markers[s.id].on('click', () => setActiveMarker(s.id));
    });
    renderList(stations);

    document.getElementById('mapSearch').addEventListener('input', function () {
        const term = this.value.toLowerCase();
        const filtered = stations.filter(s =>
            s.station_name.toLowerCase().includes(term) || s.address.toLowerCase().includes(term)
        );
        renderList(filtered);
    });
</script>
@endpush
@endsection
