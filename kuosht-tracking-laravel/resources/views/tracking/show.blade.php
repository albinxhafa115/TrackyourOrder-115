<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Track Order - {{ $order->order_number }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyANJ2fkdGVWfHOuqROARQFATWlDQ8i-cfM&libraries=geometry"></script>
    <style>
        #tracking-map { height: 400px; width: 100%; }
        .pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-indigo-900 mb-2">📦 KUOSHT Tracking</h1>
            <p class="text-gray-600">Track your delivery in real-time</p>
            @if(config('app.debug'))
                <p class="text-xs text-red-500 mt-2">DEBUG: isCourier = {{ $isCourier ? 'true' : 'false' }} | Auth = {{ auth('courier')->check() ? 'yes' : 'no' }}</p>
            @endif
        </div>

        <!-- Order Info Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                <h2 class="text-2xl font-bold text-white">Order #{{ $order->order_number }}</h2>
            </div>

            <div class="p-6">
                <!-- Status Badge -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <span id="status-badge" class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold
                            @if($order->status === 'delivered') bg-green-100 text-green-800
                            @elseif($order->status === 'nearby') bg-orange-100 text-orange-800 pulse
                            @elseif(in_array($order->status, ['picked_up', 'in_transit'])) bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            @if($order->status === 'delivered') ✅ Delivered
                            @elseif($order->status === 'nearby') 🚨 Driver Nearby!
                            @elseif($order->status === 'in_transit') 🚚 In Transit
                            @elseif($order->status === 'picked_up') 📦 Picked Up
                            @else ⏳ {{ ucfirst($order->status) }}
                            @endif
                        </span>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Estimated Arrival</p>
                        <p id="eta" class="text-2xl font-bold text-indigo-600">
                            {{ $order->eta ? $order->eta->format('H:i') : '--:--' }}
                        </p>
                    </div>
                </div>

                <!-- Delivery Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="flex items-start space-x-3">
                        <svg class="h-6 w-6 text-indigo-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Delivery Address</p>
                            <p class="text-base text-gray-900">{{ $order->delivery_address }}</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <svg class="h-6 w-6 text-indigo-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Courier</p>
                            <p class="text-base text-gray-900">{{ $order->courier->name }}</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <svg class="h-6 w-6 text-indigo-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                            <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Distance</p>
                            <p id="distance" class="text-base text-gray-900 font-semibold">
                                {{ $order->distance_to_delivery ? number_format($order->distance_to_delivery, 2) . ' km' : 'Calculating...' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <svg class="h-6 w-6 text-indigo-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Scheduled Date</p>
                            <p class="text-base text-gray-900">{{ $order->scheduled_date->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Last Update -->
                <div class="text-center py-3 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600">
                        Last updated: <span id="last-update" class="font-semibold">
                            {{ $order->courier->last_location_update ? $order->courier->last_location_update->diffForHumans() : 'Not available' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Map Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900">🗺️ Live Location</h3>
                <p class="text-sm text-gray-600 mt-1">Track your courier's current location</p>
            </div>
            <div class="p-4">
                <div id="tracking-map" class="rounded-lg border-2 border-gray-200"></div>
            </div>
        </div>

        @if($isCourier && !in_array($order->status, ['delivered', 'refused', 'postponed']))
        <!-- Courier Action Buttons -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600">
                <h3 class="text-lg font-bold text-white">⚡ Veprimet e Korierit</h3>
            </div>
            <div class="p-6">
                <!-- Start Order Button -->
                <button id="start-order-btn" onclick="startOrder()"
                        class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white px-8 py-6 rounded-2xl font-bold text-lg transition duration-150 flex items-center justify-center gap-3 shadow-2xl hover:shadow-3xl transform hover:-translate-y-1 mb-6">
                    <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                    </svg>
                    <span>📧 Nis Porosinë & Dërgo Email</span>
                </button>

                <div class="border-t-2 border-gray-200 mb-6"></div>

                <p class="text-sm text-gray-600 mb-4 font-medium">PËRDITËSO STATUSIN:</p>
                <div class="grid grid-cols-3 gap-4">
                    <button onclick="updateOrderStatus('delivered')"
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-4 rounded-xl font-bold text-sm transition duration-150 flex flex-col items-center justify-center gap-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        ✅ E Dorëzuar
                    </button>
                    <button onclick="showRefuseDialog()"
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-4 rounded-xl font-bold text-sm transition duration-150 flex flex-col items-center justify-center gap-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                        ❌ E Refuzuar
                    </button>
                    <button onclick="showPostponeDialog()"
                            class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-4 rounded-xl font-bold text-sm transition duration-150 flex flex-col items-center justify-center gap-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                        </svg>
                        📅 Shtyj
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- Progress Timeline -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900">📋 Delivery Progress</h3>
            </div>
            <div class="p-6">
                <ol class="relative border-l-2 border-indigo-200 ml-3">
                    <li class="mb-10 ml-6">
                        <span class="absolute flex items-center justify-center w-8 h-8 bg-green-100 rounded-full -left-4 ring-4 ring-white">
                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </span>
                        <h4 class="flex items-center mb-1 text-base font-semibold text-gray-900">Order Confirmed</h4>
                        <p class="text-sm font-normal text-gray-500">Your order has been confirmed and assigned to a courier.</p>
                    </li>
                    <li class="mb-10 ml-6">
                        <span class="absolute flex items-center justify-center w-8 h-8
                            {{ in_array($order->status, ['picked_up', 'in_transit', 'nearby', 'delivered']) ? 'bg-green-100' : 'bg-gray-100' }}
                            rounded-full -left-4 ring-4 ring-white">
                            @if(in_array($order->status, ['picked_up', 'in_transit', 'nearby', 'delivered']))
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            @else
                                <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                            @endif
                        </span>
                        <h4 class="flex items-center mb-1 text-base font-semibold text-gray-900">Picked Up</h4>
                        <p class="text-sm font-normal text-gray-500">Package has been picked up by the courier.</p>
                    </li>
                    <li class="mb-10 ml-6">
                        <span class="absolute flex items-center justify-center w-8 h-8
                            {{ in_array($order->status, ['in_transit', 'nearby', 'delivered']) ? 'bg-green-100' : 'bg-gray-100' }}
                            rounded-full -left-4 ring-4 ring-white">
                            @if(in_array($order->status, ['in_transit', 'nearby', 'delivered']))
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            @else
                                <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                            @endif
                        </span>
                        <h4 class="flex items-center mb-1 text-base font-semibold text-gray-900">In Transit</h4>
                        <p class="text-sm font-normal text-gray-500">Package is on the way to your location.</p>
                    </li>
                    <li class="ml-6">
                        <span class="absolute flex items-center justify-center w-8 h-8
                            {{ $order->status === 'delivered' ? 'bg-green-100' : 'bg-gray-100' }}
                            rounded-full -left-4 ring-4 ring-white">
                            @if($order->status === 'delivered')
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            @else
                                <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                            @endif
                        </span>
                        <h4 class="flex items-center mb-1 text-base font-semibold text-gray-900">Delivered</h4>
                        <p class="text-sm font-normal text-gray-500">Package successfully delivered to your address.</p>
                    </li>
                </ol>
            </div>
        </div>
    </div>

    <script>
        let map, courierMarker, deliveryMarker, trackingToken, directionsService, directionsRenderer;

        trackingToken = '{{ $order->tracking_token }}';

        function initMap() {
            const deliveryLocation = {
                lat: {{ $order->delivery_lat }},
                lng: {{ $order->delivery_lng }}
            };

            map = new google.maps.Map(document.getElementById('tracking-map'), {
                zoom: 14,
                center: deliveryLocation,
                disableDefaultUI: false,
            });

            // Initialize directions service and renderer
            directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer({
                map: map,
                suppressMarkers: true, // We'll use our custom markers
                polylineOptions: {
                    strokeColor: '#3B82F6',
                    strokeWeight: 5,
                    strokeOpacity: 0.7
                }
            });

            // Delivery location marker (red house)
            deliveryMarker = new google.maps.Marker({
                position: deliveryLocation,
                map: map,
                title: 'Delivery Location',
                icon: {
                    url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
                },
                label: {
                    text: '🏠',
                    fontSize: '24px'
                }
            });

            @if($order->courier->current_lat && $order->courier->current_lng)
            const courierLocation = {
                lat: {{ $order->courier->current_lat }},
                lng: {{ $order->courier->current_lng }}
            };

            // Courier marker (blue car)
            courierMarker = new google.maps.Marker({
                position: courierLocation,
                map: map,
                title: 'Courier Location',
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

            // Show directions from courier to delivery location
            showDirections(courierLocation, deliveryLocation);

            // Fit map to show both markers
            const bounds = new google.maps.LatLngBounds();
            bounds.extend(deliveryLocation);
            bounds.extend(courierLocation);
            map.fitBounds(bounds);
            @endif

            // Start auto-refresh
            setInterval(updateTrackingData, 10000); // Every 10 seconds
        }

        function showDirections(origin, destination) {
            const request = {
                origin: origin,
                destination: destination,
                travelMode: google.maps.TravelMode.DRIVING
            };

            directionsService.route(request, function(result, status) {
                if (status === 'OK') {
                    directionsRenderer.setDirections(result);
                } else {
                    console.error('Directions request failed:', status);
                }
            });
        }

        function updateTrackingData() {
            fetch(`/api/tracking/${trackingToken}`)
                .then(response => response.json())
                .then(data => {
                    // Update courier position
                    if (data.courier.current_lat && data.courier.current_lng) {
                        const newPosition = {
                            lat: parseFloat(data.courier.current_lat),
                            lng: parseFloat(data.courier.current_lng)
                        };

                        if (courierMarker) {
                            courierMarker.setPosition(newPosition);
                        }
                    }

                    // Update UI elements
                    document.getElementById('distance').textContent =
                        data.order.distance ? `${parseFloat(data.order.distance).toFixed(2)} km` : 'Calculating...';

                    document.getElementById('eta').textContent = data.order.eta || '--:--';

                    document.getElementById('last-update').textContent = data.courier.last_update || 'Not available';

                    // Update status badge if changed
                    updateStatusBadge(data.order.status);
                })
                .catch(error => console.error('Error updating tracking data:', error));
        }

        function updateStatusBadge(status) {
            const badge = document.getElementById('status-badge');
            let badgeClass = '';
            let badgeText = '';

            switch(status) {
                case 'delivered':
                    badgeClass = 'bg-green-100 text-green-800';
                    badgeText = '✅ Delivered';
                    break;
                case 'nearby':
                    badgeClass = 'bg-orange-100 text-orange-800 pulse';
                    badgeText = '🚨 Driver Nearby!';
                    break;
                case 'in_transit':
                    badgeClass = 'bg-blue-100 text-blue-800';
                    badgeText = '🚚 In Transit';
                    break;
                case 'picked_up':
                    badgeClass = 'bg-blue-100 text-blue-800';
                    badgeText = '📦 Picked Up';
                    break;
                default:
                    badgeClass = 'bg-gray-100 text-gray-800';
                    badgeText = `⏳ ${status.charAt(0).toUpperCase() + status.slice(1)}`;
            }

            badge.className = `inline-flex items-center px-4 py-2 rounded-full text-sm font-bold ${badgeClass}`;
            badge.textContent = badgeText;
        }

        @if($isCourier)
        // Courier action functions
        function startOrder() {
            if (!confirm('Dëshiron të nisësh porosinë dhe të dërgosh email te klienti?')) {
                return;
            }

            const btn = document.getElementById('start-order-btn');
            btn.disabled = true;
            btn.innerHTML = '<span>⏳ Duke dërguar...</span>';

            fetch(`/orders/{{ $order->id }}/send-email`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Email u dërgua me sukses te klienti!\n📧 ' + data.email);
                    btn.innerHTML = '<span>✅ Email i Dërguar</span>';
                    btn.classList.remove('from-purple-600', 'to-indigo-600');
                    btn.classList.add('bg-green-600');
                } else {
                    alert('❌ Gabim: ' + (data.message || 'Ndodhi një gabim'));
                    btn.disabled = false;
                    btn.innerHTML = '<svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path></svg><span>📧 Nis Porosinë & Dërgo Email</span>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Gabim gjatë dërgimit të emailit!');
                btn.disabled = false;
                btn.innerHTML = '<svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path></svg><span>📧 Nis Porosinë & Dërgo Email</span>';
            });
        }

        function updateOrderStatus(status) {
            if (!confirm(`Konfirmo që porosia është ${status === 'delivered' ? 'dorëzuar' : status}?`)) {
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            console.log('CSRF Token:', csrfToken);
            console.log('Order ID:', {{ $order->id }});
            console.log('Status:', status);

            fetch(`/orders/{{ $order->id }}/update-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);

                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Error response:', text);
                        throw new Error(`HTTP ${response.status}: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Success data:', data);
                if (data.success) {
                    alert('✅ Statusi u përditësua me sukses!');
                    location.reload();
                } else {
                    alert('❌ Gabim: ' + (data.message || data.error || 'Ndodhi një gabim'));
                }
            })
            .catch(error => {
                console.error('Catch error:', error);
                alert('❌ Gabim gjatë përditësimit të statusit!\n\n' + error.message);
            });
        }

        function showRefuseDialog() {
            const notes = prompt('Shkruaj arsyen e refuzimit:');
            if (notes === null) return;

            fetch(`/orders/{{ $order->id }}/update-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    status: 'refused',
                    delivery_notes: notes
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Porosia u shënua si e refuzuar!');
                    location.reload();
                } else {
                    alert('❌ Gabim: ' + (data.message || 'Ndodhi një gabim'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Gabim gjatë përditësimit!');
            });
        }

        function showPostponeDialog() {
            const date = prompt('Shkruaj datën e re (YYYY-MM-DD):');
            if (!date) return;

            const notes = prompt('Shkruaj arsyen e shtyrjes:');

            fetch(`/orders/{{ $order->id }}/update-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    status: 'postponed',
                    postponed_date: date,
                    delivery_notes: notes
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Porosia u shtye për: ' + date);
                    location.reload();
                } else {
                    alert('❌ Gabim: ' + (data.message || 'Ndodhi një gabim'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Gabim gjatë shtyrjes!');
            });
        }
        @endif

        window.addEventListener('load', initMap);
    </script>
</body>
</html>
