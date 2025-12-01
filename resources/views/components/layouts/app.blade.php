<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAN SOBOA - {{ $title ?? 'Accueil' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            green: '#00853f',
                            yellow: '#fdef42',
                            red: '#e31b23',
                            dark: '#1a1a1a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
        }
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%2300853f' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="bg-gray-100 bg-pattern flex flex-col min-h-screen">

    <!-- Navbar -->
    <nav class="bg-brand-green text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 font-bold text-2xl tracking-wider">
                        <span class="text-brand-yellow">CAN</span> SOBOA
                    </div>
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-4">
                            <a href="/" class="hover:bg-brand-dark px-3 py-2 rounded-md text-sm font-medium transition">Accueil</a>
                            <a href="/matches" class="hover:bg-brand-dark px-3 py-2 rounded-md text-sm font-medium transition">Matchs</a>
                            <a href="/leaderboard" class="hover:bg-brand-dark px-3 py-2 rounded-md text-sm font-medium transition">Classement</a>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-brand-yellow font-bold text-sm">
                        <span class="hidden sm:inline">Points:</span> 120 pts
                    </div>
                    <div class="h-8 w-8 rounded-full bg-white text-brand-green flex items-center justify-center font-bold border-2 border-brand-yellow">
                        U
                    </div>
                </div>
            </div>
        </div>
        <!-- Mobile Menu (Simple) -->
        <div class="md:hidden flex justify-around bg-brand-dark py-2 text-xs">
             <a href="/" class="text-white">Accueil</a>
             <a href="/matches" class="text-white">Matchs</a>
             <a href="/leaderboard" class="text-white">Classement</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow container mx-auto px-4 py-8">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-brand-dark text-white py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-sm text-gray-400">&copy; {{ date('Y') }} CAN SOBOA. Tous droits réservés.</p>
        </div>
    </footer>

</body>
</html>
