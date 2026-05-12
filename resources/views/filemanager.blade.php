<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel 12 File Manager</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    @filemanagerStyles
</head>

<body class="bg-slate-950 text-white min-h-screen">

    <!-- Navbar -->
    <header class="border-b border-slate-800 bg-slate-900">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">

            <div>
                <h1 class="text-2xl font-bold">📁 File Manager</h1>

                <p class="text-slate-400 text-sm">
                    Laravel 12 + Livewire
                </p>
            </div>

            <div class="flex gap-3">

                <a href="{{ route('filemanager') }}"
                    class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg transition">
                    Dashboard
                </a>

                <a href="/"
                    class="bg-slate-700 hover:bg-slate-600 px-4 py-2 rounded-lg transition">
                    Home
                </a>

            </div>

        </div>
    </header>

    <!-- Main Layout -->
    <div class="flex">

        <!-- Sidebar -->
        <aside class="w-64 min-h-screen bg-slate-900 border-r border-slate-800 p-6">

            <h2 class="text-slate-400 uppercase text-sm mb-4">
                Navigation
            </h2>

            <ul class="space-y-3">

                <li>
                    <a href="{{ route('filemanager') }}"
                        class="block bg-slate-800 hover:bg-slate-700 px-4 py-3 rounded-lg transition">
                        📂 All Files
                    </a>
                </li>

                <li>
                    <a href="{{ route('filemanager') }}"
                        class="block bg-slate-800 hover:bg-slate-700 px-4 py-3 rounded-lg transition">
                        ⭐ Favorites
                    </a>
                </li>

                <li>
                    <a href="{{ route('filemanager') }}"
                        class="block bg-slate-800 hover:bg-slate-700 px-4 py-3 rounded-lg transition">
                        🗑 Trash
                    </a>
                </li>

                <li>
                    <a href="{{ route('filemanager') }}"
                        class="block bg-slate-800 hover:bg-slate-700 px-4 py-3 rounded-lg transition">
                        📸 Images
                    </a>
                </li>

                <li>
                    <a href="{{ route('filemanager') }}"
                        class="block bg-slate-800 hover:bg-slate-700 px-4 py-3 rounded-lg transition">
                        🎥 Videos
                    </a>
                </li>

            </ul>

        </aside>

        <!-- Content -->
        <main class="flex-1 p-8">

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                    <h3 class="text-slate-400 text-sm">
                        Total Files
                    </h3>

                    <p class="text-3xl font-bold mt-2">
                        128
                    </p>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                    <h3 class="text-slate-400 text-sm">
                        Images
                    </h3>

                    <p class="text-3xl font-bold mt-2">
                        54
                    </p>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                    <h3 class="text-slate-400 text-sm">
                        Videos
                    </h3>

                    <p class="text-3xl font-bold mt-2">
                        12
                    </p>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                    <h3 class="text-slate-400 text-sm">
                        Documents
                    </h3>

                    <p class="text-3xl font-bold mt-2">
                        62
                    </p>
                </div>

            </div>

            <!-- Search -->
            <div class="mb-6">
                <input
                    type="text"
                    placeholder="Search files..."
                    class="w-full bg-slate-900 border border-slate-700 rounded-xl px-5 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Flash -->
            @if(session('success'))
                <div class="mb-6 bg-green-600 text-white px-5 py-3 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif

            <!-- File Manager -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">

                <div class="flex justify-between items-center mb-6">

                    <div>
                        <h2 class="text-2xl font-bold">
                            File Dashboard
                        </h2>

                        <p class="text-slate-400 text-sm mt-1">
                            Upload, manage, preview and organize files
                        </p>
                    </div>

                </div>

                <!-- File Manager Component -->
                <x-livewire-filemanager />

            </div>

        </main>

    </div>

    @livewireScripts
    @filemanagerScripts

</body>
</html>