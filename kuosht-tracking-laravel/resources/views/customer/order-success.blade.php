<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Porosia u Krijua - KUOSHT</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <!-- Success Card -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-8 py-12 text-center">
                <div class="mb-4">
                    <svg class="w-24 h-24 mx-auto text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold text-white mb-2">✅ Porosia u Krijua!</h1>
                <p class="text-xl text-green-100">Faleminderit për besimin tuaj!</p>
            </div>

            <!-- Order Details -->
            <div class="p-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">📦 Detajet e Porosisë</h2>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-3 border-b border-gray-200">
                            <span class="text-gray-600 font-medium">Numri i Porosisë:</span>
                            <span class="text-lg font-bold text-indigo-600">{{ $order->order_number }}</span>
                        </div>

                        <div class="flex justify-between items-center py-3 border-b border-gray-200">
                            <span class="text-gray-600 font-medium">Emri:</span>
                            <span class="font-semibold text-gray-900">{{ $order->customer_name }}</span>
                        </div>

                        <div class="flex justify-between items-center py-3 border-b border-gray-200">
                            <span class="text-gray-600 font-medium">Email:</span>
                            <span class="font-semibold text-gray-900">{{ $order->customer_email }}</span>
                        </div>

                        <div class="flex justify-between items-center py-3 border-b border-gray-200">
                            <span class="text-gray-600 font-medium">Telefoni:</span>
                            <span class="font-semibold text-gray-900">{{ $order->customer_phone }}</span>
                        </div>

                        <div class="flex justify-between items-center py-3 border-b border-gray-200">
                            <span class="text-gray-600 font-medium">Adresa:</span>
                            <span class="font-semibold text-gray-900">{{ $order->delivery_address }}</span>
                        </div>

                        <div class="flex justify-between items-center py-3 border-b border-gray-200">
                            <span class="text-gray-600 font-medium">Data e Dorëzimit:</span>
                            <span class="font-semibold text-gray-900">{{ $order->scheduled_date->format('d M Y') }}</span>
                        </div>

                        <div class="flex justify-between items-center py-3 border-b border-gray-200">
                            <span class="text-gray-600 font-medium">Statusi:</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800">
                                ⏳ {{ ucfirst($order->status) }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center py-3">
                            <span class="text-gray-600 font-medium">Kurieri:</span>
                            <span class="font-semibold text-gray-900">{{ $order->courier->name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Tracking Link -->
                <div class="bg-indigo-50 border-2 border-indigo-200 rounded-xl p-6 mb-6">
                    <h3 class="font-bold text-indigo-900 mb-3 text-lg">🔗 Tracking Link</h3>
                    <p class="text-sm text-indigo-700 mb-3">Ruaj këtë link për të ndjekur porosinë tënde në kohë reale:</p>
                    <div class="flex gap-2">
                        <input type="text"
                               id="trackingUrl"
                               value="{{ route('tracking.show', $order->tracking_token) }}"
                               readonly
                               class="flex-1 px-4 py-3 bg-white border-2 border-indigo-300 rounded-lg font-mono text-sm">
                        <button onclick="copyTrackingUrl()"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-bold transition duration-150">
                            📋 Kopjo
                        </button>
                    </div>
                    <p class="text-xs text-indigo-600 mt-2">💡 Një link është dërguar edhe në email-in tuaj!</p>
                </div>

                <!-- Action Buttons -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="{{ route('tracking.show', $order->tracking_token) }}"
                       class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-6 py-4 rounded-xl font-bold text-center transition duration-150 shadow-lg hover:shadow-xl">
                        🗺️ Shiko Tracking
                    </a>
                    <a href="{{ route('customer.create') }}"
                       class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-4 rounded-xl font-bold text-center transition duration-150 shadow-lg hover:shadow-xl">
                        ➕ Porosi e Re
                    </a>
                </div>
            </div>
        </div>

        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
            <p class="text-sm text-blue-800">
                📧 <strong>Email-i u dërgua!</strong> Kontrollo inbox-in tuaj për detaje të porosisë dhe tracking link.
            </p>
        </div>
    </div>

    <script>
        function copyTrackingUrl() {
            const input = document.getElementById('trackingUrl');
            input.select();
            input.setSelectionRange(0, 99999); // For mobile

            try {
                document.execCommand('copy');
                alert('✅ Link-u u kopjua në clipboard!');
            } catch (err) {
                // Fallback for modern browsers
                navigator.clipboard.writeText(input.value).then(() => {
                    alert('✅ Link-u u kopjua në clipboard!');
                }).catch(() => {
                    alert('❌ Gabim në kopjimin e link-ut!');
                });
            }
        }
    </script>
</body>
</html>
