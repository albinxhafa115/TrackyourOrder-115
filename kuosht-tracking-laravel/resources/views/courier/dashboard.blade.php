<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - KUOSHT GPS Tracking</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyANJ2fkdGVWfHOuqROARQFATWlDQ8i-cfM&libraries=geometry,places"></script>
    <style>
        #map { height: 500px; width: 100%; }
        .order-card { cursor: pointer; transition: all 0.3s; }
        .order-card:hover { background-color: #f9fafb; transform: translateY(-2px); }
        .order-card.active { background-color: #EFF6FF; border-left: 4px solid #3B82F6; }
        .distance-badge { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <nav class="bg-indigo-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-white">📍 KUOSHT GPS Tracking</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-white">👤 {{ $courier->name }}</span>
                    <span class="px-3 py-1 bg-green-500 text-white rounded-full text-sm">
                        {{ ucfirst($courier->status) }}
                    </span>
                    <form method="POST" action="{{ route('courier.logout') }}">
                        @csrf
                        <button type="submit" class="text-white hover:text-gray-200 font-medium">
                            Dil →
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white overflow-hidden shadow-lg rounded-lg border-l-4 border-blue-500">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Sot</dt>
                            <dd class="mt-1 text-3xl font-bold text-gray-900">{{ $stats['total_today'] }}</dd>
                        </div>
                        <div class="text-4xl">📦</div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-lg border-l-4 border-green-500">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <dt class="text-sm font-medium text-gray-500 truncate">Të Dërguara</dt>
                            <dd class="mt-1 text-3xl font-bold text-green-600">{{ $stats['delivered'] }}</dd>
                        </div>
                        <div class="text-4xl">✅</div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-lg border-l-4 border-blue-500">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <dt class="text-sm font-medium text-gray-500 truncate">Në Rrugë</dt>
                            <dd class="mt-1 text-3xl font-bold text-blue-600">{{ $stats['in_transit'] }}</dd>
                        </div>
                        <div class="text-4xl">🚚</div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-lg border-l-4 border-yellow-500">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <dt class="text-sm font-medium text-gray-500 truncate">Në Pritje</dt>
                            <dd class="mt-1 text-3xl font-bold text-yellow-600">{{ $stats['pending'] }}</dd>
                        </div>
                        <div class="text-4xl">⏳</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map Section -->
        <div class="bg-white shadow-lg overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-bold text-gray-900">
                    🗺️ Harta e Dërgesave
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Lokacionet e porosive dhe pozicioni juaj aktual
                </p>
            </div>
            <div class="px-4 py-5">
                <div id="map" class="rounded-lg border-2 border-gray-200"></div>
            </div>
        </div>

        <!-- Orders List -->
        <div class="bg-white shadow-lg overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-bold text-gray-900">
                    📋 Porositë e Sotme
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $todayOrders->count() }} porosi të caktuara për ju sot
                </p>
            </div>
            <div class="border-t border-gray-200">
                <ul id="orders-list" class="divide-y divide-gray-200">
                    @forelse($todayOrders as $order)
                    <li class="order-card px-4 py-4 sm:px-6" data-order-id="{{ $order->id }}">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-bold text-indigo-600 truncate">
                                            🏷️ {{ $order->order_number }}
                                        </p>
                                        <span class="distance-display distance-badge text-white text-xs px-2 py-1 rounded-full font-semibold">
                                            Calculating...
                                        </span>
                                    </div>
                                    <div class="ml-2 flex-shrink-0">
                                        @if($order->status === 'delivered')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                ✅ Delivered
                                            </span>
                                        @elseif($order->status === 'in_transit')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                🚚 In Transit
                                            </span>
                                        @elseif($order->status === 'confirmed')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                ⏳ Confirmed
                                            </span>
                                        @else
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-sm text-gray-700">
                                    <div class="flex items-center">
                                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                                        </svg>
                                        <span class="font-medium">{{ $order->customer_name }}</span>
                                    </div>

                                    <div class="flex items-center">
                                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                                        </svg>
                                        {{ $order->customer_phone }}
                                    </div>

                                    <div class="flex items-center">
                                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="font-bold text-gray-900">€{{ number_format($order->order_value, 2) }}</span>
                                        <span class="ml-1 text-xs text-gray-500">({{ ucfirst($order->payment_method) }})</span>
                                    </div>
                                </div>

                                <div class="mt-2 flex items-start">
                                    <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-sm text-gray-600">{{ $order->delivery_address }}</span>
                                </div>

                                <div class="mt-3 grid grid-cols-2 gap-2">
                                    <button onclick="openTracking('{{ route('tracking.show', ['token' => $order->tracking_token]) }}')"
                                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-semibold text-sm transition duration-150 flex items-center justify-center gap-2">
                                        🗺️ Shiko Hartën
                                    </button>
                                    <a href="tel:{{ $order->customer_phone }}"
                                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold text-sm transition duration-150 flex items-center justify-center gap-2">
                                        📞 Telefono
                                    </a>
                                </div>


                                @if($order->status !== 'delivered' && $order->status !== 'refused' && $order->status !== 'postponed')
                                <div class="mt-3 pt-3 border-t border-gray-200">
                                    <p class="text-xs text-gray-500 mb-2 font-medium">VEPRIMET:</p>
                                    <div class="grid grid-cols-3 gap-2">
                                        <button onclick="updateOrderStatus({{ $order->id }}, 'delivered')"
                                                class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg font-semibold text-xs transition duration-150">
                                            ✅ E Dorëzuar
                                        </button>
                                        <button onclick="showRefuseDialog({{ $order->id }})"
                                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg font-semibold text-xs transition duration-150">
                                            ❌ E Refuzuar
                                        </button>
                                        <button onclick="showPostponeDialog({{ $order->id }})"
                                                class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-2 rounded-lg font-semibold text-xs transition duration-150">
                                            📅 Shtyj
                                        </button>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </li>
                    @empty
                    <li class="px-4 py-8 sm:px-6 text-center">
                        <div class="text-gray-400 text-5xl mb-3">📭</div>
                        <p class="text-gray-500 font-medium">Nuk ka porosi për sot</p>
                        <p class="text-sm text-gray-400 mt-1">Porositë e reja do të shfaqen këtu</p>
                    </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <script>
        let map, directionsService, directionsRenderer;
        let courierMarker, courierPosition;
        let orderMarkers = {};
        let currentRoute = null;

        // Initialize Google Map
        function initMap() {
            const prishtina = { lat: 42.6629, lng: 21.1655 };

            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 13,
                center: prishtina,
                mapTypeControl: true,
                streetViewControl: true,
                fullscreenControl: true,
                zoomControl: true
            });

            directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer({
                map: map,
                suppressMarkers: false,
                polylineOptions: {
                    strokeColor: '#4F46E5',
                    strokeWeight: 5
                }
            });

            @if($courier->current_lat && $courier->current_lng)
            courierPosition = {
                lat: {{ $courier->current_lat }},
                lng: {{ $courier->current_lng }}
            };

            // Custom courier marker (blue car icon)
            courierMarker = new google.maps.Marker({
                position: courierPosition,
                map: map,
                title: '{{ $courier->name }}',
                icon: {
                    path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                    scale: 6,
                    fillColor: '#3B82F6',
                    fillOpacity: 1,
                    strokeColor: '#1E40AF',
                    strokeWeight: 2,
                    rotation: 0
                },
                zIndex: 1000
            });

            const courierInfoWindow = new google.maps.InfoWindow({
                content: `
                    <div style="padding: 10px;">
                        <strong style="color: #3B82F6; font-size: 16px;">📍 {{ $courier->name }}</strong><br>
                        <span style="color: #6B7280; font-size: 14px;">Pozicioni Juaj</span>
                    </div>
                `
            });

            courierMarker.addListener('click', () => {
                courierInfoWindow.open(map, courierMarker);
            });

            map.setCenter(courierPosition);
            @endif

            // Add order markers
            @foreach($activeOrders as $order)
            addOrderMarker(
                {{ $order->id }},
                {{ $order->delivery_lat }},
                {{ $order->delivery_lng }},
                '{{ $order->order_number }}',
                '{{ $order->customer_name }}',
                '{{ $order->customer_phone }}',
                '{{ addslashes($order->delivery_address) }}',
                {{ $order->order_value }},
                '{{ $order->payment_method }}',
                '{{ $order->status }}'
            );
            @endforeach

            // Calculate distances and sort orders
            calculateDistances();
        }

        function addOrderMarker(id, lat, lng, orderNumber, customerName, phone, address, value, paymentMethod, status) {
            const position = { lat: parseFloat(lat), lng: parseFloat(lng) };
            const icon = status === 'delivered'
                ? 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
                : 'http://maps.google.com/mapfiles/ms/icons/red-dot.png';
            const emoji = status === 'delivered' ? '✅' : '📦';

            const marker = new google.maps.Marker({
                position: position,
                map: map,
                title: orderNumber,
                icon: icon,
                animation: google.maps.Animation.DROP
            });

            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div style="min-width: 250px; padding: 10px;">
                        <strong style="color: #4F46E5; font-size: 16px;">${emoji} ${orderNumber}</strong><br>
                        <div style="margin-top: 10px; font-size: 14px;">
                            <strong>👤 ${customerName}</strong><br>
                            📞 ${phone}<br>
                            📍 ${address}<br>
                            💰 <strong>€${value.toFixed(2)}</strong> (${paymentMethod})<br>
                            <span style="display: inline-block; margin-top: 5px; padding: 4px 8px; background-color: ${status === 'delivered' ? '#D1FAE5' : '#FEF3C7'}; color: ${status === 'delivered' ? '#065F46' : '#92400E'}; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                ${status}
                            </span>
                        </div>
                        <button onclick="showDirections(${lat}, ${lng}, '${address}', ${id})"
                                style="margin-top: 10px; width: 100%; padding: 8px; background-color: #4F46E5; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                            🗺️ Shiko Udhëzuesi
                        </button>
                    </div>
                `
            });

            marker.addListener('click', () => {
                infoWindow.open(map, marker);
            });

            orderMarkers[id] = { marker, position, orderNumber, customerName, address };
        }

        function showDirections(destLat, destLng, address, orderId) {
            if (!courierPosition) {
                alert('Pozicioni juaj nuk është i disponueshëm.');
                return;
            }

            const destination = { lat: parseFloat(destLat), lng: parseFloat(destLng) };

            const request = {
                origin: courierPosition,
                destination: destination,
                travelMode: google.maps.TravelMode.DRIVING
            };

            directionsService.route(request, (result, status) => {
                if (status === 'OK') {
                    directionsRenderer.setDirections(result);

                    const route = result.routes[0].legs[0];
                    const distance = route.distance.text;
                    const duration = route.duration.text;

                    // Show route info
                    const infoWindow = new google.maps.InfoWindow({
                        content: `
                            <div style="padding: 10px;">
                                <strong style="font-size: 16px;">🗺️ Udhëzuesi për: ${address}</strong><br>
                                <div style="margin-top: 10px;">
                                    📏 Distanca: <strong>${distance}</strong><br>
                                    ⏱️ Kohëzgjatja: <strong>${duration}</strong>
                                </div>
                            </div>
                        `,
                        position: destination
                    });
                    infoWindow.open(map);
                } else {
                    alert('Nuk mund të gjendet rruga: ' + status);
                }
            });
        }

        function openTracking(url) {
            window.open(url, '_blank');
        }

        function calculateDistances() {
            if (!courierPosition || !google.maps.geometry) return;

            const orderElements = document.querySelectorAll('[data-order-id]');
            const distances = [];

            orderElements.forEach(element => {
                const orderId = element.getAttribute('data-order-id');
                const orderData = orderMarkers[orderId];

                if (orderData) {
                    const distance = google.maps.geometry.spherical.computeDistanceBetween(
                        new google.maps.LatLng(courierPosition.lat, courierPosition.lng),
                        new google.maps.LatLng(orderData.position.lat, orderData.position.lng)
                    );

                    const distanceKm = (distance / 1000).toFixed(2);
                    distances.push({ element, distance: parseFloat(distanceKm), orderId });

                    // Add distance badge to order card
                    const badge = element.querySelector('.distance-display');
                    if (badge) {
                        badge.textContent = `${distanceKm} km`;
                    }
                }
            });

            // Sort orders by distance
            distances.sort((a, b) => a.distance - b.distance);

            const ordersList = document.getElementById('orders-list');
            if (ordersList) {
                distances.forEach(({ element }) => {
                    ordersList.appendChild(element);
                });
            }
        }

        // Update courier position every 5 minutes
        function updateCourierPosition() {
            if (!navigator.geolocation) {
                console.log('Geolocation is not supported');
                return;
            }

            navigator.geolocation.getCurrentPosition((position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                // Update position on server
                fetch('/api/tracking/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        latitude: lat,
                        longitude: lng
                    })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Position updated:', data);

                    // Update courier marker on map
                    if (courierMarker && courierPosition) {
                        courierPosition = { lat, lng };
                        courierMarker.setPosition(courierPosition);
                    }

                    // Show call customer popup if nearby
                    if (data.show_call_popup && data.order) {
                        showCallCustomerPopup(data.order);
                    }

                    // Recalculate distances
                    calculateDistances();
                })
                .catch(error => console.error('Error updating position:', error));
            }, (error) => {
                console.error('Geolocation error:', error);
            });
        }

        function showCallCustomerPopup(order) {
            const popup = `
                <div id="call-popup" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 9999; display: flex; align-items: center; justify-center;">
                    <div style="background: white; padding: 30px; border-radius: 15px; max-width: 400px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.3);">
                        <div style="font-size: 60px; margin-bottom: 20px;">🚨</div>
                        <h3 style="font-size: 24px; font-weight: bold; color: #1F2937; margin-bottom: 15px;">Ju jeni afër!</h3>
                        <p style="color: #6B7280; margin-bottom: 10px;">Order: <strong>${order.order_number}</strong></p>
                        <p style="color: #6B7280; margin-bottom: 10px;">Klient: <strong>${order.customer_name}</strong></p>
                        <p style="color: #6B7280; margin-bottom: 20px;">Adresa: ${order.delivery_address}</p>
                        <div style="display: flex; gap: 10px; margin-top: 20px;">
                            <a href="tel:${order.customer_phone}"
                               style="flex: 1; background: linear-gradient(135deg, #10B981, #059669); color: white; padding: 15px; border-radius: 10px; text-decoration: none; font-weight: bold; display: block;">
                                📞 Telefono Klientin
                            </a>
                            <button onclick="document.getElementById('call-popup').remove()"
                                    style="flex: 1; background: #E5E7EB; color: #374151; padding: 15px; border-radius: 10px; border: none; font-weight: bold; cursor: pointer;">
                                Më vonë
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', popup);
        }

        function updateOrderStatus(orderId, status, notes = '', postponedDate = '') {
            fetch(`/orders/${orderId}/update-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    status: status,
                    delivery_notes: notes,
                    postponed_date: postponedDate
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Statusi u përditësua me sukses!');
                    window.location.reload();
                } else {
                    alert('❌ Gabim: ' + (data.message || 'Nuk mund të përditësohet statusi'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Gabim në përditësimin e statusit');
            });
        }

        function showRefuseDialog(orderId) {
            const reason = prompt('Arsyeja e refuzimit:');
            if (reason) {
                updateOrderStatus(orderId, 'refused', reason);
            }
        }

        function showPostponeDialog(orderId) {
            const dateInput = prompt('Data e re (format: YYYY-MM-DD):');
            if (dateInput) {
                const reason = prompt('Arsyeja e shtyrjes:');
                updateOrderStatus(orderId, 'postponed', reason, dateInput);
            }
        }

        // Initialize map when page loads
        window.addEventListener('load', initMap);

        // Update position every 5 minutes (300000ms)
        setInterval(updateCourierPosition, 300000);

        // Update position immediately on load
        setTimeout(updateCourierPosition, 3000);
    </script>
</body>
</html>
