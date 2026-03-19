# PHP_Laravel12_Livewire_FileManager

## Introduction

In modern web applications, efficient file and media management is a crucial requirement. This project demonstrates how to build a dynamic and user-friendly File Manager using Laravel 12 and Livewire.

The application provides a seamless experience for uploading, organizing, previewing, and managing files without page reloads. It leverages the power of Livewire for reactive interfaces and integrates a robust third-party file manager package to simplify development.

This project is designed to reflect real-world implementation practices and is suitable for learning, portfolio, and practical use cases.

---

##  Project Overview

PHP_Laravel12_Livewire_FileManager is a full-featured file management system built using Laravel 12, Livewire, and Spatie Media Library.

The project enables users to efficiently manage files and folders through an intuitive and responsive interface. It supports real-time interactions, file previews, and structured storage management without requiring complex frontend frameworks.

Key technologies used in this project include:

- Laravel 12 (Backend Framework)
- Livewire (Reactive UI without JavaScript)
- Livewire File Manager Package (Core file management system)
- Spatie Media Library (File handling and storage)
- Tailwind CSS (Modern UI styling)

This project follows Laravel’s MVC architecture and demonstrates clean integration of third-party packages into a scalable system.

---

##  Key Features

-  Complete File & Folder Management System  
-  Drag & Drop File Upload Support  
-  Advanced Search for Files and Folders  
-  Real-Time UI Updates using Livewire (No Page Reload)  
-  Dark Mode User Interface  
-  Copy File URL for Easy Sharing  
-  Image Preview and Thumbnail Generation  
-  Integration with Spatie Media Library for File Storage  
-  Optional ACL (Access Control Layer) for File Security  
-  REST API Support for External Integration  
-  Modern UI built with Tailwind CSS  
-  Responsive Design for Better User Experience   

---

##  Requirements

Before running this project, ensure the following:

- PHP >= 8.2  
- Composer installed  
- Laravel 12  
- Node.js & NPM (required for frontend assets)  

---

##  Installation Steps

## Step 1: Create Laravel Project

```bash
composer create-project laravel/laravel PHP_Laravel12_Livewire_FileManager "12.*"
cd PHP_Laravel12_Livewire_FileManager
```
---

## Step 2: Database Setup

Update .env

```.env
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=livewire_filemanager
DB_USERNAME=root
DB_PASSWORD=
```
Run Migration Command:

```bash
php artisan migrate
```

## Step 3: Install Livewire

```bash
composer require livewire/livewire
```
---

## Step 4: Install File Manager Package

```bash
composer require livewire-filemanager/filemanager
```

---

## Step 5: Publish Package Migrations

```bash
php artisan vendor:publish --tag=livewire-filemanager-migrations
```

---

## Step 6: Install Spatie Media Library

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
```

---

## Step 7: Run Database Migrations

```bash
php artisan migrate
```

This will create required tables such as:

- folders

- media

---

## Step 8: Create Storage Link

```bash
php artisan storage:link
```

---

## Step 9: Install Frontend Dependencies (NPM)

This project uses Tailwind CSS and Vite for frontend styling and asset compilation.

### Install Node Modules

```bash
npm install
npm run dev
```

---

## Step 10: Configure Tailwind CSS

Open the following file:

resources/css/app.css

Add the required Tailwind import and include the file manager views:

```
@import 'tailwindcss';

@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';
@source '../**/*.blade.php';
@source '../**/*.js';

/*  ADD THIS LINE */
@source '../../vendor/livewire-filemanager/filemanager/resources/views/**/*.blade.php';

@theme {
    --font-sans: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji',
        'Segoe UI Symbol', 'Noto Color Emoji';
}
```

---

## Step 11: Enable ACL (Security)

Publish config:

```bash
php artisan vendor:publish --tag=livewire-filemanager-config
```

Then open:

config/livewire-filemanager.php

Set:

```
'acl_enabled' => true,
```

---

## Step 12: Add File Manager to Blade View

File: resources/views/filemanager.blade.php

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager Dashboard</title>

    {{--  Vite (NPM compiled assets) --}}
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

    <!--  Navbar -->
    <div class="bg-slate-950 px-8 py-4 flex justify-between items-center border-b border-slate-800">
        <h2 class="text-xl font-semibold">📁 File Manager</h2>
        <span class="text-slate-400">Laravel 12 + Livewire</span>
    </div>

    <!--  Main Layout -->
    <div class="flex h-[calc(100vh-64px)]">

        <!--  Sidebar -->
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

        <!--  Content Area -->
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
```

---

## Step 13: Web Routes

File: routes/web.php

```php
<?php

use Illuminate\Support\Facades\Route;
use LivewireFilemanager\Filemanager\Http\Controllers\Files\FileController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/file-manager', function () {
    return view('filemanager');
});

Route::get('/files/{path}', [FileController::class, 'show'])
    ->where('path', '.*')
    ->name('assets.show');
```
---

## Step 14: Update media.blade.php

File: vendor/livewire-filemanager/filemanager/resources/views/components/elements/media.blade.php

Replace This Part:

```php
@if($media->hasGeneratedConversion('thumbnail'))
    <img src="{{ $media->getUrl('thumbnail') }}" class="mx-auto shadow border p-1 bg-white max-w-20 max-h-20 mb-2" alt="folder">
@else
    <x-dynamic-component id="icon-{{ $key }}" :component="'livewire-filemanager::icons.mimes.' . getFileType($media->mime_type)" class="mx-auto w-16 h-16 mb-2.5" />
@endif
```
With This:

```php
       @if(str_starts_with($media->mime_type, 'image'))
        <img src="{{ $media->getUrl() }}"
            class="mx-auto shadow border p-1 bg-white max-w-20 max-h-20 mb-2"
            alt="image">
        @elseif($media->hasGeneratedConversion('thumbnail'))
        <img src="{{ $media->getUrl('thumbnail') }}"
            class="mx-auto shadow border p-1 bg-white max-w-20 max-h-20 mb-2">
        @else
        <x-dynamic-component id="icon-{{ $key }}" :component="'livewire-filemanager::icons.mimes.' . getFileType($media->mime_type)" class="mx-auto w-16 h-16 mb-2.5" />
        @endif
```

```
Note: Modifying vendor files is not recommended for production environments. This step is included for demonstration and learning purposes only. In real-world applications, this should be handled via overrides or custom components.
```
---

## Step 15: Queue Setup

Thumbnail generation runs via queue.

Either run worker:

```bash
php artisan queue:work
```

OR disable queue in .env:

```.env
QUEUE_CONNECTION=sync
```

---

## Step 16: Run Development Server

### Terminal 1:

```bash
php artisan serve
```

Then open:

```bash
http://127.0.0.1:8000/file-manager
```

### Terminal 2:

```bash
npm run dev
```
---

## Output

<img src="screenshots/Screenshot 2026-03-19 115637.png" width="1000">

---

## Project Structure

```
PHP_Laravel12_Livewire_FileManager/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   │
│   ├── Models/
│   │   └── User.php
│   │
│   └── Providers/
│
├── bootstrap/
│
├── config/
│   ├── app.php
│   ├── database.php
│   ├── filesystems.php
│   ├── queue.php
│   └── livewire-filemanager.php   ← (published config)
│
├── database/
│   ├── migrations/
│   │   ├── xxxx_create_users_table.php
│   │   ├── xxxx_create_folders_table.php   ← (file manager)
│   │   ├── xxxx_add_user_id_to_folders.php
│   │   └── xxxx_create_media_table.php     ← (Spatie)
│   │
│   └── seeders/
│
├── public/
│   ├── index.php
│   ├── storage/   ← (linked to storage/app/public)
│   └── build/     ← (Vite compiled assets)
│
├── resources/
│   ├── css/
│   │   └── app.css
│   │
│   ├── js/
│   │   └── app.js
│   │
│   └── views/
│       ├── filemanager.blade.php   ← (your custom UI)
│       └── welcome.blade.php
│
├── routes/
│   └── web.php
│
├── storage/
│   ├── app/
│   │   └── public/
│   │       └── 1/   ← (uploaded files stored here)
│   │
│   ├── framework/
│   └── logs/
│
├── vendor/   ← (all packages)
│   ├── livewire-filemanager/
│   └── spatie/
│
├── .env
├── artisan
├── composer.json
├── package.json
├── vite.config.js
└── README.md
```

---

Your PHP_Laravel12_Livewire_FileManager Project is now ready!


