<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share Expired</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-white min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-2xl p-8 text-center">
        <div class="text-6xl mb-4">⏰</div>
        <h1 class="text-2xl font-bold mb-2">Share Link Expired</h1>
        <p class="text-slate-400 mb-6">This share link has expired or been removed.</p>
        <a href="{{ route('filemanager') }}" class="bg-blue-600 hover:bg-blue-700 px-6 py-2 rounded-lg transition inline-block">
            <i class="fas fa-arrow-left"></i> Back to File Manager
        </a>
    </div>
</body>
</html>