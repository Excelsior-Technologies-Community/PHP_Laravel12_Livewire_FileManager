<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager Dashboard</title>

    {{-- ✅ Vite (NPM compiled assets) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- File Manager Styles --}}
    @filemanagerStyles

    <style>
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: #0f172a;
            color: #fff;
        }
    </style>
</head>

<body>

    <!-- 🔷 Navbar -->
    <div class="bg-slate-950 px-8 py-4 flex justify-between items-center border-b border-slate-800">
        <h2 class="text-xl font-semibold">📁 File Manager</h2>
        <span class="text-slate-400">Laravel 12 + Livewire</span>
    </div>

    <!-- 🔷 Main Layout -->
    <div class="flex h-[calc(100vh-64px)]">

        <!-- 🔷 Sidebar -->
        <div class="w-56 bg-slate-950 border-r border-slate-800 p-5">
            <h4 class="text-slate-400 mb-4">Navigation</h4>

            <ul class="space-y-4">
                <li>
                    <a href="#" class="block text-white hover:text-blue-400 transition">📂 All Files</a>
                </li>
                <li>
                    <a href="#" class="block text-white hover:text-yellow-400 transition">⭐ Favorites</a>
                </li>
                <li>
                    <a href="#" class="block text-white hover:text-red-400 transition">🗑 Trash</a>
                </li>
            </ul>
        </div>

        <!-- 🔷 Content Area -->
        <div class="flex-1 p-6 overflow-auto">

            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold">Dashboard</h1>
                <p class="text-slate-400">Manage your files and folders easily</p>
            </div>

            <!-- File Manager Card -->
            <div class="bg-slate-950 rounded-xl p-4 border border-slate-800 shadow-lg">
                
                <x-livewire-filemanager />

            </div>

        </div>

    </div>

    {{-- File Manager Scripts --}}
    @filemanagerScripts

</body>
</html>