<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TiendynFood Admin — Iniciar Sesión</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: '#FF6B35',
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-lg p-8">

            {{-- Logo / Header --}}
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4" style="background-color: #FF6B35;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">TiendynFood Admin</h1>
                <p class="text-sm text-gray-500 mt-1">Acceso exclusivo para dueños de restaurante</p>
            </div>

            {{-- Error general --}}
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-6 text-sm">
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Formulario --}}
            <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Correo electrónico
                    </label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="correo@ejemplo.com"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:border-transparent transition @error('email') border-red-400 @enderror"
                        style="focus-ring-color: #FF6B35;"
                        onfocus="this.style.boxShadow='0 0 0 3px rgba(255,107,53,0.25)'; this.style.borderColor='#FF6B35';"
                        onblur="this.style.boxShadow=''; this.style.borderColor='';"
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Contraseña
                    </label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none transition"
                        onfocus="this.style.boxShadow='0 0 0 3px rgba(255,107,53,0.25)'; this.style.borderColor='#FF6B35';"
                        onblur="this.style.boxShadow=''; this.style.borderColor='';"
                    >
                </div>

                <div class="flex items-center">
                    <input id="remember" type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 accent-orange-500">
                    <label for="remember" class="ml-2 text-sm text-gray-600">Recordarme</label>
                </div>

                <button
                    type="submit"
                    class="w-full py-2.5 px-4 text-white font-semibold rounded-lg transition-opacity hover:opacity-90 active:opacity-80 text-sm"
                    style="background-color: #FF6B35;"
                >
                    Iniciar sesión
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">
            &copy; {{ date('Y') }} TiendynFood. Todos los derechos reservados.
        </p>
    </div>

</body>
</html>
