<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Krijo Porosi - KUOSHT</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyANJ2fkdGVWfHOuqROARQFATWlDQ8i-cfM&libraries=places,geometry"></script>
    <style>
        #map { height: 400px; width: 100%; }
        .location-status {
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
            <h1 class="text-4xl font-bold text-indigo-900 mb-2">📦 KUOSHT Delivery</h1>
            <p class="text-gray-600">Krijo porosinë tënde</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                <h2 class="text-2xl font-bold text-white">Detajet e Porosisë</h2>
            </div>

            <form action="{{ route('customer.store') }}" method="POST" id="orderForm" class="p-6 space-y-6">
                @csrf

                <!-- Customer Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Informacioni Personal</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Emri dhe Mbiemri *
                            </label>
                            <input type="text"
                                   id="customer_name"
                                   name="customer_name"
                                   value="{{ old('customer_name') }}"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="Emri Mbiemri">
                            @error('customer_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                Numri i Telefonit *
                            </label>
                            <input type="tel"
                                   id="customer_phone"
                                   name="customer_phone"
                                   value="{{ old('customer_phone') }}"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="+383 XX XXX XXX">
                            @error('customer_phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email *
                            </label>
                            <input type="email"
                                   id="customer_email"
                                   name="customer_email"
                                   value="{{ old('customer_email') }}"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="email@example.com">
                            @error('customer_email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Delivery Location -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📍 Lokacioni i Dorëzimit</h3>

                    <!-- GPS Button -->
                    <div class="mb-4">
                        <button type="button"
                                id="getCurrentLocation"
                                class="w-full bg-green-600 hover:bg-green-700 text-white px-6 py-4 rounded-xl font-bold transition duration-150 flex items-center justify-center gap-3 shadow-lg hover:shadow-xl">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <span>📡 Përdor Lokacionin Aktual (GPS)</span>
                        </button>
                        <p class="text-xs text-gray-500 mt-2 text-center">Kjo do të kërkojë leje për të aksesuar GPS-në tuaj</p>
                    </div>

                    <!-- Map -->
                    <div class="mb-4">
                        <div id="map" class="rounded-lg border-2 border-gray-300"></div>
                        <p class="text-sm text-gray-600 mt-2">
                            💡 Lëviz hartën dhe kliko për të zgjedhur lokacionin e saktë të dorëzimit
                        </p>
                    </div>

                    <!-- Address Field -->
                    <div class="mb-4">
                        <label for="delivery_address" class="block text-sm font-medium text-gray-700 mb-2">
                            Adresa e Dorëzimit *
                        </label>
                        <textarea id="delivery_address"
                                  name="delivery_address"
                                  required
                                  rows="2"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                  placeholder="Shkruaj adresen e saktë...">{{ old('delivery_address') }}</textarea>
                        @error('delivery_address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Hidden Location Inputs -->
                    <input type="hidden" id="delivery_lat" name="delivery_lat" value="{{ old('delivery_lat') }}">
                    <input type="hidden" id="delivery_lng" name="delivery_lng" value="{{ old('delivery_lng') }}">

                    <!-- Location Status -->
                    <div id="locationStatus" class="hidden bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="font-semibold text-green-800">✅ Lokacioni u zgjedh me sukses!</p>
                                <p class="text-sm text-green-700" id="coordinates"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Shënime Shtesë (Opsionale)
                    </label>
                    <textarea id="notes"
                              name="notes"
                              rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                              placeholder="P.sh: Dera e dytë, kutia e zezë, etj...">{{ old('notes') }}</textarea>
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <button type="submit"
                            id="submitBtn"
                            disabled
                            class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-8 py-4 rounded-xl font-bold text-lg transition duration-150 shadow-xl hover:shadow-2xl">
                        🚀 Dërgo Porosinë
                    </button>
                    <p class="text-sm text-gray-500 mt-2 text-center">
                        <span id="submitWarning" class="text-red-600 font-semibold">⚠️ Ju lutem zgjidhni lokacionin në hartë</span>
                    </p>
                </div>
            </form>
        </div>

        <!-- Info Card -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <h3 class="font-semibold text-blue-900 mb-2">ℹ️ Si funksionon?</h3>
            <ul class="space-y-1 text-sm text-blue-800">
                <li>1. Mbush të dhënat personale</li>
                <li>2. Kliko "Përdor Lokacionin Aktual" për GPS automatik ose kliko në hartë</li>
                <li>3. Kontrollo që adresa dhe lokacioni janë të sakta</li>
                <li>4. Dërgo porosinë dhe merr tracking link në email!</li>
            </ul>
        </div>
    </div>

    <script>
        let map, marker, selectedLocation = null;
        const defaultLocation = { lat: 42.6629, lng: 21.1655 }; // Prishtina

        function initMap() {
            // Initialize map
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 13,
                center: defaultLocation,
                disableDefaultUI: false,
            });

            // Add click listener on map
            map.addListener('click', function(event) {
                setLocation(event.latLng.lat(), event.latLng.lng());
            });

            // Initialize marker
            marker = new google.maps.Marker({
                map: map,
                draggable: true,
                animation: google.maps.Animation.DROP
            });

            // Add drag listener on marker
            marker.addListener('dragend', function(event) {
                setLocation(event.latLng.lat(), event.latLng.lng());
            });
        }

        function setLocation(lat, lng) {
            selectedLocation = { lat, lng };

            // Update marker position
            marker.setPosition(selectedLocation);
            marker.setVisible(true);

            // Center map on location
            map.setCenter(selectedLocation);

            // Update hidden inputs
            document.getElementById('delivery_lat').value = lat;
            document.getElementById('delivery_lng').value = lng;

            // Show status
            document.getElementById('locationStatus').classList.remove('hidden');
            document.getElementById('coordinates').textContent =
                `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;

            // Enable submit button
            document.getElementById('submitBtn').disabled = false;
            document.getElementById('submitWarning').classList.add('hidden');

            // Get address from coordinates (reverse geocoding)
            reverseGeocode(lat, lng);
        }

        function reverseGeocode(lat, lng) {
            const geocoder = new google.maps.Geocoder();
            const latlng = { lat, lng };

            geocoder.geocode({ location: latlng }, (results, status) => {
                if (status === 'OK' && results[0]) {
                    document.getElementById('delivery_address').value = results[0].formatted_address;
                }
            });
        }

        // Get Current Location
        document.getElementById('getCurrentLocation').addEventListener('click', function() {
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="location-status">📡 Duke marrë lokacionin...</span>';

            if (!navigator.geolocation) {
                alert('❌ Shfletuesi juaj nuk mbështet GPS!');
                btn.disabled = false;
                btn.innerHTML = '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg><span>📡 Përdor Lokacionin Aktual (GPS)</span>';
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    setLocation(position.coords.latitude, position.coords.longitude);
                    btn.disabled = false;
                    btn.innerHTML = '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg><span>✅ Lokacion i Marrë!</span>';

                    setTimeout(() => {
                        btn.innerHTML = '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg><span>📡 Përdor Lokacionin Aktual (GPS)</span>';
                    }, 2000);
                },
                function(error) {
                    let errorMessage = 'Gabim në marrjen e lokacionit!';
                    if (error.code === error.PERMISSION_DENIED) {
                        errorMessage = 'Ju refuzuat qasjen në GPS. Ju lutem aktivizoni lejen në settings!';
                    } else if (error.code === error.POSITION_UNAVAILABLE) {
                        errorMessage = 'Lokacioni nuk është i disponueshëm!';
                    } else if (error.code === error.TIMEOUT) {
                        errorMessage = 'Kërkesa për GPS ka skaduar!';
                    }
                    alert('❌ ' + errorMessage);
                    btn.disabled = false;
                    btn.innerHTML = '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg><span>📡 Përdor Lokacionin Aktual (GPS)</span>';
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        });

        // Form validation
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            if (!selectedLocation) {
                e.preventDefault();
                alert('❌ Ju lutem zgjidhni lokacionin në hartë!');
                document.getElementById('getCurrentLocation').scrollIntoView({ behavior: 'smooth' });
            }
        });

        // Initialize map on load
        window.addEventListener('load', initMap);
    </script>
</body>
</html>
