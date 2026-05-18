<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - XGrow Internal System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased selection:bg-black selection:text-white">

    <div class="min-h-screen flex flex-col justify-center items-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <!-- Logo / Brand -->
            <h2 class="text-center text-3xl font-bold tracking-tight text-gray-900">
                XGrow Studio
            </h2>
            <p class="mt-2 text-center text-sm text-gray-500">
                Internal Workspace System
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-2xl sm:px-10 border border-gray-100">
                
                <form class="space-y-6" action="{{ url('/login') }}" method="POST">
                    @csrf
                    
                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <div class="mt-1">
                            <input id="email" name="email" type="email" autocomplete="email" required 
                                class="block w-full appearance-none rounded-lg border border-gray-200 px-4 py-3 placeholder-gray-400 shadow-sm focus:border-black focus:outline-none focus:ring-black sm:text-sm transition duration-200"
                                placeholder="name@xgrow.com">
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <div class="mt-1">
                            <input id="password" name="password" type="password" autocomplete="current-password" required 
                                class="block w-full appearance-none rounded-lg border border-gray-200 px-4 py-3 placeholder-gray-400 shadow-sm focus:border-black focus:outline-none focus:ring-black sm:text-sm transition duration-200"
                                placeholder="••••••••">
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox" 
                                class="h-4 w-4 rounded border-gray-300 text-black focus:ring-black">
                            <label for="remember" class="ml-2 block text-sm text-gray-600">Remember me</label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit" 
                            class="flex w-full justify-center rounded-lg bg-black py-3 px-4 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 transition duration-200">
                            Sign in to Workspace
                        </button>
                    </div>
                </form>
                
            </div>
        </div>
    </div>

</body>
</html>