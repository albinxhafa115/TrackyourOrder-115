<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courier Login - KUOSHT GPS Tracking</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    KUOSHT GPS Tracking
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Kyçuni si Kurier
                </p>
            </div>

            <form class="mt-8 space-y-6" action="{{ route('courier.login') }}" method="POST">
                @csrf

                @if ($errors->any())
                    <div class="rounded-md bg-red-50 p-4">
                        <div class="flex">
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">
                                    @foreach ($errors->all() as $error)
                                        {{ $error }}
                                    @endforeach
                                </h3>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="email" class="sr-only">Email</label>
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            required 
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                            placeholder="Email"
                            value="{{ old('email', 'leart@kuosht.com') }}"
                        >
                    </div>
                    <div>
                        <label for="password" class="sr-only">Fjalëkalimi</label>
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            required 
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                            placeholder="Fjalëkalimi"
                            value="courier123"
                        >
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            id="remember" 
                            name="remember" 
                            type="checkbox" 
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        >
                        <label for="remember" class="ml-2 block text-sm text-gray-900">
                            Më mbaj mend
                        </label>
                    </div>
                </div>

                <div>
                    <button 
                        type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        KYÇU
                    </button>
                </div>

                <div class="text-center text-sm text-gray-500">
                    <p>Të dhëna testimi:</p>
                    <p class="mt-1"><strong>Email:</strong> leart@kuosht.com</p>
                    <p><strong>Password:</strong> courier123</p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
