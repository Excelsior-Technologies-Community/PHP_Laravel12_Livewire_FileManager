<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shared File - {{ $file->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-950 text-white min-h-screen flex items-center justify-center p-6">
    <div class="max-w-2xl w-full bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl">
        <div class="text-center">
            <div class="text-6xl mb-4">
                <i class="fas {{ $file->file_icon }} text-blue-400"></i>
            </div>
            <h1 class="text-2xl font-bold mb-2">{{ $file->name }}</h1>
            <p class="text-slate-400">{{ $file->file_size }} • {{ $file->mime_type }}</p>
            
            @if(str_contains($file->mime_type, 'image'))
                <div class="mt-4">
                    <img src="{{ asset('storage/uploads/' . $file->file_name) }}" alt="{{ $file->name }}" class="max-h-96 mx-auto rounded-lg">
                </div>
            @elseif(str_contains($file->mime_type, 'video'))
                <div class="mt-4">
                    <video controls class="w-full rounded-lg">
                        <source src="{{ asset('storage/uploads/' . $file->file_name) }}" type="{{ $file->mime_type }}">
                    </video>
                </div>
            @elseif($file->mime_type === 'application/pdf')
                <div class="mt-4">
                    <iframe src="{{ asset('storage/uploads/' . $file->file_name) }}" class="w-full h-96 rounded-lg"></iframe>
                </div>
            @else
                <div class="mt-4 p-4 bg-slate-800 rounded-lg text-left">
                    <pre class="text-sm text-slate-300 whitespace-pre-wrap max-h-96 overflow-auto">{{ file_get_contents(storage_path('app/public/uploads/' . $file->file_name)) }}</pre>
                </div>
            @endif

            <div class="mt-6 flex gap-3 justify-center">
                <a href="{{ route('filemanager') }}" class="bg-blue-600 hover:bg-blue-700 px-6 py-2 rounded-lg transition">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <a href="{{ asset('storage/uploads/' . $file->file_name) }}" download class="bg-green-600 hover:bg-green-700 px-6 py-2 rounded-lg transition">
                    <i class="fas fa-download"></i> Download
                </a>
            </div>
        </div>
    </div>
</body>
</html>