<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel 12 File Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #0f172a; color: #e2e8f0; }
        .file-item { transition: all 0.2s; cursor: pointer; }
        .file-item:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.3); }
        .drop-zone { border: 2px dashed #475569; transition: all 0.3s; }
        .drop-zone.dragover { border-color: #3b82f6; background: rgba(59,130,246,0.05); }
        .selected { background: rgba(59,130,246,0.15); border: 1px solid #3b82f6; }
        .trash-item { opacity: 0.7; border-left: 3px solid #ef4444; }
        .folder-link { cursor: pointer; padding: 8px 12px; border-radius: 8px; transition: 0.2s; }
        .folder-link:hover { background: rgba(59,130,246,0.1); }
        .folder-link.active { background: rgba(59,130,246,0.2); }
        .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        .modal-content { background: #1e293b; border-radius: 16px; padding: 24px; max-width: 90vw; max-height: 90vh; overflow: auto; }
        .btn { padding: 8px 16px; border-radius: 8px; border: none; cursor: pointer; transition: 0.2s; font-weight: 600; }
        .btn-primary { background: #3b82f6; color: white; }
        .btn-primary:hover { background: #2563eb; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; }
        .btn-success { background: #22c55e; color: white; }
        .btn-success:hover { background: #16a34a; }
        .btn-warning { background: #f59e0b; color: white; }
        .btn-warning:hover { background: #d97706; }
        .btn-purple { background: #8b5cf6; color: white; }
        .btn-purple:hover { background: #7c3aed; }
        .btn-sm { padding: 4px 10px; font-size: 12px; }
        .text-muted { color: #94a3b8; }
        .file-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px; }
        .file-list { display: flex; flex-direction: column; gap: 8px; }
        .file-list .file-item { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; }
        .file-list .file-item .file-info { display: flex; align-items: center; gap: 12px; }
        .preview-image { max-width: 100%; max-height: 70vh; border-radius: 8px; }
        .preview-video { max-width: 100%; max-height: 70vh; border-radius: 8px; }
        .preview-pdf { width: 100%; height: 70vh; border-radius: 8px; }
        .preview-text { background: #0f172a; padding: 16px; border-radius: 8px; font-family: monospace; white-space: pre-wrap; max-height: 70vh; overflow: auto; }
    </style>
</head>
<body>

<div class="min-h-screen">
    <!-- Header -->
    <header class="border-b border-slate-800 bg-slate-900">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">📁 File Manager</h1>
                <p class="text-slate-400 text-sm">Laravel 12 + JavaScript</p>
            </div>
            <div class="flex gap-3">
                <button onclick="setView('grid')" id="gridBtn" class="px-3 py-1 rounded bg-blue-600">
                    <i class="fas fa-th"></i>
                </button>
                <button onclick="setView('list')" id="listBtn" class="px-3 py-1 rounded bg-slate-700">
                    <i class="fas fa-list"></i>
                </button>
                <a href="{{ route('filemanager') }}" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg transition">Dashboard</a>
                <a href="/" class="bg-slate-700 hover:bg-slate-600 px-4 py-2 rounded-lg transition">Home</a>
            </div>
        </div>
    </header>

    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 min-h-screen bg-slate-900 border-r border-slate-800 p-6">
            <h2 class="text-slate-400 uppercase text-sm mb-4">Navigation</h2>
            <ul class="space-y-3">
                <li>
                    <a href="#" onclick="loadFiles(); return false;" class="block bg-slate-800 hover:bg-slate-700 px-4 py-3 rounded-lg transition">
                        📂 All Files
                    </a>
                </li>
                <li>
                    <a href="#" onclick="showTrash(); return false;" class="block bg-slate-800 hover:bg-slate-700 px-4 py-3 rounded-lg transition">
                        🗑 Trash
                    </a>
                </li>
                <li>
                    <a href="#" onclick="showShared(); return false;" class="block bg-slate-800 hover:bg-slate-700 px-4 py-3 rounded-lg transition">
                        🔗 Shared
                    </a>
                </li>
            </ul>

            <h2 class="text-slate-400 uppercase text-sm mt-6 mb-4">Folders</h2>
            <ul class="space-y-2" id="folderList">
                <div class="text-slate-500 text-sm">Loading folders...</div>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8" id="statsContainer">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                    <h3 class="text-slate-400 text-sm">Total Files</h3>
                    <p class="text-3xl font-bold mt-2" id="totalFiles">0</p>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                    <h3 class="text-slate-400 text-sm">Images</h3>
                    <p class="text-3xl font-bold mt-2" id="totalImages">0</p>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                    <h3 class="text-slate-400 text-sm">Videos</h3>
                    <p class="text-3xl font-bold mt-2" id="totalVideos">0</p>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                    <h3 class="text-slate-400 text-sm">Documents</h3>
                    <p class="text-3xl font-bold mt-2" id="totalDocuments">0</p>
                </div>
            </div>

            <!-- Search & Actions -->
            <div class="mb-6 flex gap-4 flex-wrap">
                <input type="text" id="searchInput" oninput="searchFiles()" placeholder="Search files..." class="flex-1 bg-slate-900 border border-slate-700 rounded-xl px-5 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 text-white">
                <button onclick="createFolder()" class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg">
                    <i class="fas fa-folder-plus"></i> New Folder
                </button>
                <button onclick="bulkDelete()" id="bulkDeleteBtn" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg hidden">
                    <i class="fas fa-trash"></i> Delete Selected (<span id="selectedCount">0</span>)
                </button>
                <button onclick="bulkDownload()" id="bulkDownloadBtn" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg hidden">
                    <i class="fas fa-download"></i> Download (<span id="selectedCount2">0</span>)
                </button>
                <button onclick="downloadFolder()" id="downloadFolderBtn" class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded-lg hidden">
                    <i class="fas fa-folder"></i> Download Folder
                </button>
            </div>

            <!-- Drop Zone -->
            <div class="drop-zone rounded-2xl p-6 mb-6 text-center" 
                 id="dropZone"
                 ondragover="event.preventDefault(); this.classList.add('dragover')"
                 ondragleave="this.classList.remove('dragover')"
                 ondrop="handleDrop(event)">
                <i class="fas fa-cloud-upload-alt text-4xl mb-2 text-slate-400"></i>
                <p class="text-slate-400">Drag & drop files here to upload</p>
                <input type="file" multiple onchange="uploadFiles(event)" class="hidden" id="fileInput">
                <button onclick="document.getElementById('fileInput').click()" class="mt-2 bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg">
                    <i class="fas fa-upload"></i> Browse Files
                </button>
            </div>

            <!-- File List -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold">File Dashboard</h2>
                        <p class="text-slate-400 text-sm mt-1">Upload, manage, preview and organize files</p>
                    </div>
                    <div>
                        <span id="fileCount" class="text-slate-400">0 files</span>
                    </div>
                </div>

                <div id="fileContainer" class="file-grid">
                    <div class="text-center text-slate-500 py-10 col-span-full">Loading files...</div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal" id="previewModal">
    <div class="modal-content">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold" id="previewTitle">Preview</h3>
            <button onclick="closePreview()" class="text-slate-400 hover:text-white text-2xl">&times;</button>
        </div>
        <div id="previewBody" class="text-center">
            <div class="text-slate-400">Loading...</div>
        </div>
    </div>
</div>

<!-- Share Modal -->
<div class="modal" id="shareModal">
    <div class="modal-content max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">🔗 Share File</h3>
            <button onclick="closeShare()" class="text-slate-400 hover:text-white text-2xl">&times;</button>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Password (Optional)</label>
                <input type="text" id="sharePassword" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Expires In (Hours)</label>
                <input type="number" id="shareExpires" value="24" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white">
            </div>
            <button onclick="confirmShare()" class="w-full bg-green-600 hover:bg-green-700 py-2 rounded-lg">
                <i class="fas fa-share-alt"></i> Generate Share Link
            </button>
            <div id="shareResult" class="hidden">
                <label class="block text-sm font-medium mb-1">Share URL</label>
                <div class="flex gap-2">
                    <input type="text" id="shareUrl" class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-white" readonly>
                    <button onclick="copyShareUrl()" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentView = 'grid';
let currentFolder = null;
let currentFiles = [];
let selectedFiles = [];
let shareFileId = null;
let currentFilter = 'all'; // all, trash, shared

// CSRF Token
const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

// ============ INIT ============
document.addEventListener('DOMContentLoaded', function() {
    loadFiles();
    loadFolders();
    loadStats();
});

// ============ VIEW ============
function setView(view) {
    currentView = view;
    document.getElementById('gridBtn').className = view === 'grid' ? 'px-3 py-1 rounded bg-blue-600' : 'px-3 py-1 rounded bg-slate-700';
    document.getElementById('listBtn').className = view === 'list' ? 'px-3 py-1 rounded bg-blue-600' : 'px-3 py-1 rounded bg-slate-700';
    renderFiles();
}

// ============ LOAD FILES ============
function loadFiles() {
    let url = '/api/files';
    const params = new URLSearchParams();
    if (currentFolder) params.append('folder', currentFolder);
    if (currentFilter === 'trash') params.append('trash', true);
    if (currentFilter === 'shared') params.append('shared', true);
    const search = document.getElementById('searchInput')?.value;
    if (search) params.append('search', search);
    if (params.toString()) url += '?' + params.toString();

    fetch(url)
        .then(res => res.json())
        .then(data => {
            currentFiles = data;
            renderFiles();
            document.getElementById('fileCount').textContent = data.length + ' files';
        })
        .catch(err => console.error('Error loading files:', err));
}

function searchFiles() {
    loadFiles();
}

function showTrash() {
    currentFilter = 'trash';
    currentFolder = null;
    loadFiles();
}

function showShared() {
    currentFilter = 'shared';
    currentFolder = null;
    loadFiles();
}

// ============ LOAD FOLDERS ============
function loadFolders() {
    fetch('/api/folders')
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('folderList');
            if (data.length === 0) {
                container.innerHTML = '<div class="text-slate-500 text-sm">No folders</div>';
                return;
            }
            let html = '';
            data.forEach(folder => {
                html += `
                    <li>
                        <div class="folder-link flex items-center justify-between" 
                             onclick="selectFolder(${folder.id})">
                            <span><i class="fas fa-folder text-yellow-500 mr-2"></i> ${folder.name}</span>
                            <span class="text-xs text-slate-500">${folder.file_count}</span>
                        </div>
                    </li>
                `;
            });
            container.innerHTML = html;
        })
        .catch(err => console.error('Error loading folders:', err));
}

function selectFolder(id) {
    currentFolder = id;
    currentFilter = 'all';
    document.querySelectorAll('.folder-link').forEach(el => el.classList.remove('active'));
    const items = document.querySelectorAll('.folder-link');
    items.forEach(el => {
        if (el.textContent.includes(id)) el.classList.add('active');
    });
    loadFiles();
    document.getElementById('downloadFolderBtn').classList.remove('hidden');
}

// ============ LOAD STATS ============
function loadStats() {
    fetch('/api/stats')
        .then(res => res.json())
        .then(data => {
            document.getElementById('totalFiles').textContent = data.total || 0;
            document.getElementById('totalImages').textContent = data.images || 0;
            document.getElementById('totalVideos').textContent = data.videos || 0;
            document.getElementById('totalDocuments').textContent = data.documents || 0;
        })
        .catch(err => console.error('Error loading stats:', err));
}

// ============ RENDER FILES ============
function renderFiles() {
    const container = document.getElementById('fileContainer');
    if (currentFiles.length === 0) {
        container.innerHTML = '<div class="text-center text-slate-500 py-10 col-span-full">No files found</div>';
        return;
    }

    const isGrid = currentView === 'grid';
    container.className = isGrid ? 'file-grid' : 'file-list';

    let html = '';
    currentFiles.forEach(file => {
        const isSelected = selectedFiles.includes(file.id);
        const isTrash = file.deleted_at !== null;
        const isShared = file.share_token !== null;
        
        const fileHtml = isGrid ? `
            <div class="file-item bg-slate-800 rounded-xl p-4 ${isSelected ? 'selected' : ''} ${isTrash ? 'trash-item' : ''}"
                 onclick="toggleSelect(${file.id})"
                 draggable="true"
                 ondragstart="dragStart(event, 'file', ${file.id})"
                 ondragover="event.preventDefault()"
                 ondrop="drop(event, 'file', ${file.id})">
                <div class="text-center">
                    <i class="${file.file_icon} text-3xl text-blue-400"></i>
                    <div class="mt-2 font-medium truncate" title="${file.name}">${file.name}</div>
                    <div class="text-xs text-slate-500">${file.file_size}</div>
                    ${isShared ? '<div class="text-xs text-green-400"><i class="fas fa-share-alt"></i> Shared</div>' : ''}
                    ${isTrash ? '<div class="text-xs text-red-400"><i class="fas fa-trash"></i> Trash</div>' : ''}
                    <div class="flex justify-center gap-2 mt-3">
                        <button onclick="event.stopPropagation(); previewFile(${file.id})" class="text-slate-400 hover:text-white"><i class="fas fa-eye"></i></button>
                        ${!isTrash ? `<button onclick="event.stopPropagation(); openShare(${file.id})" class="text-green-400 hover:text-green-300"><i class="fas fa-share-alt"></i></button>` : ''}
                        ${!isTrash ? `<a href="/api/files/${file.id}/download" class="text-blue-400 hover:text-blue-300"><i class="fas fa-download"></i></a>` : ''}
                        ${isTrash ? `<button onclick="event.stopPropagation(); restoreFile(${file.id})" class="text-green-400 hover:text-green-300"><i class="fas fa-undo"></i></button>` : ''}
                        <button onclick="event.stopPropagation(); deleteFile(${file.id})" class="text-red-400 hover:text-red-300"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        ` : `
            <div class="file-item bg-slate-800 rounded-xl p-3 ${isSelected ? 'selected' : ''} ${isTrash ? 'trash-item' : ''}"
                 onclick="toggleSelect(${file.id})"
                 draggable="true"
                 ondragstart="dragStart(event, 'file', ${file.id})"
                 ondragover="event.preventDefault()"
                 ondrop="drop(event, 'file', ${file.id})">
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-3">
                        <i class="${file.file_icon} text-xl text-blue-400"></i>
                        <span class="truncate max-w-[200px]">${file.name}</span>
                        <span class="text-xs text-slate-500">${file.file_size}</span>
                        ${isShared ? '<span class="text-xs text-green-400"><i class="fas fa-share-alt"></i> Shared</span>' : ''}
                        ${isTrash ? '<span class="text-xs text-red-400"><i class="fas fa-trash"></i> Trash</span>' : ''}
                    </div>
                    <div class="flex gap-2">
                        <button onclick="event.stopPropagation(); previewFile(${file.id})" class="text-slate-400 hover:text-white"><i class="fas fa-eye"></i></button>
                        ${!isTrash ? `<button onclick="event.stopPropagation(); openShare(${file.id})" class="text-green-400 hover:text-green-300"><i class="fas fa-share-alt"></i></button>` : ''}
                        ${!isTrash ? `<a href="/api/files/${file.id}/download" class="text-blue-400 hover:text-blue-300"><i class="fas fa-download"></i></a>` : ''}
                        ${isTrash ? `<button onclick="event.stopPropagation(); restoreFile(${file.id})" class="text-green-400 hover:text-green-300"><i class="fas fa-undo"></i></button>` : ''}
                        <button onclick="event.stopPropagation(); deleteFile(${file.id})" class="text-red-400 hover:text-red-300"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        `;
        html += fileHtml;
    });

    container.innerHTML = html;
    updateBulkButtons();
}

// ============ SELECT / DESELECT ============
function toggleSelect(id) {
    const index = selectedFiles.indexOf(id);
    if (index > -1) {
        selectedFiles.splice(index, 1);
    } else {
        selectedFiles.push(id);
    }
    updateBulkButtons();
    renderFiles();
}

function updateBulkButtons() {
    const count = selectedFiles.length;
    const deleteBtn = document.getElementById('bulkDeleteBtn');
    const downloadBtn = document.getElementById('bulkDownloadBtn');
    if (count > 0) {
        deleteBtn.classList.remove('hidden');
        downloadBtn.classList.remove('hidden');
        document.getElementById('selectedCount').textContent = count;
        document.getElementById('selectedCount2').textContent = count;
    } else {
        deleteBtn.classList.add('hidden');
        downloadBtn.classList.add('hidden');
    }
}

// ============ CREATE FOLDER ============
function createFolder() {
    const name = prompt('Enter folder name:');
    if (!name) return;

    fetch('/api/folders', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ name, parent_id: currentFolder })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadFolders();
            loadFiles();
        }
    })
    .catch(err => console.error('Error creating folder:', err));
}

// ============ UPLOAD FILES ============
function uploadFiles(event) {
    const files = event.target.files;
    if (!files.length) return;

    const formData = new FormData();
    for (let file of files) {
        formData.append('files[]', file);
    }
    formData.append('folder_id', currentFolder || '');

    fetch('/api/upload', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadFiles();
            loadStats();
            event.target.value = '';
        }
    })
    .catch(err => console.error('Error uploading files:', err));
}

function handleDrop(e) {
    e.preventDefault();
    e.target.classList.remove('dragover');
    const files = e.dataTransfer.files;
    if (!files.length) return;

    const formData = new FormData();
    for (let file of files) {
        formData.append('files[]', file);
    }
    formData.append('folder_id', currentFolder || '');

    fetch('/api/upload', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadFiles();
            loadStats();
        }
    })
    .catch(err => console.error('Error uploading files:', err));
}

// ============ DELETE / RESTORE ============
function deleteFile(id) {
    if (!confirm('Delete this file?')) return;

    fetch('/api/files/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrf }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadFiles();
            loadStats();
        }
    })
    .catch(err => console.error('Error deleting file:', err));
}

function restoreFile(id) {
    fetch('/api/files/' + id + '/restore', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadFiles();
        }
    })
    .catch(err => console.error('Error restoring file:', err));
}

function bulkDelete() {
    if (!selectedFiles.length) return;
    if (!confirm('Delete ' + selectedFiles.length + ' files?')) return;

    fetch('/api/files/bulk-delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ ids: selectedFiles })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            selectedFiles = [];
            loadFiles();
            loadStats();
            updateBulkButtons();
        }
    })
    .catch(err => console.error('Error bulk deleting:', err));
}

function bulkDownload() {
    if (!selectedFiles.length) return;
    window.location.href = '/api/files/bulk-download?ids=' + selectedFiles.join(',');
}

// ============ DOWNLOAD FOLDER ============
function downloadFolder() {
    if (!currentFolder) return;
    window.location.href = '/api/folders/' + currentFolder + '/download';
}

// ============ DRAG & DROP ============
function dragStart(e, type, id) {
    e.dataTransfer.setData('text/plain', JSON.stringify({ type, id }));
}

function drop(e, targetType, targetId) {
    e.preventDefault();
    const data = JSON.parse(e.dataTransfer.getData('text/plain'));
    if (targetType !== 'folder') return;

    fetch('/api/move', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({
            type: data.type,
            id: data.id,
            target_type: targetType,
            target_id: targetId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadFiles();
            loadFolders();
        }
    })
    .catch(err => console.error('Error moving item:', err));
}

// ============ PREVIEW ============
function previewFile(id) {
    const modal = document.getElementById('previewModal');
    const body = document.getElementById('previewBody');
    const title = document.getElementById('previewTitle');

    modal.classList.add('active');
    body.innerHTML = '<div class="text-slate-400">Loading...</div>';

    const file = currentFiles.find(f => f.id === id);
    if (file) title.textContent = file.name;

    fetch('/api/files/' + id + '/preview')
        .then(res => {
            const contentType = res.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return res.json().then(data => {
                    if (data.content) {
                        body.innerHTML = `<div class="preview-text">${data.content}</div>`;
                    } else {
                        body.innerHTML = '<div class="text-slate-400">Preview not available</div>';
                    }
                });
            } else {
                return res.blob().then(blob => {
                    const url = URL.createObjectURL(blob);
                    const type = blob.type;
                    if (type.startsWith('image/')) {
                        body.innerHTML = `<img src="${url}" class="preview-image" alt="Preview">`;
                    } else if (type.startsWith('video/')) {
                        body.innerHTML = `<video controls class="preview-video"><source src="${url}" type="${type}"></video>`;
                    } else if (type === 'application/pdf') {
                        body.innerHTML = `<iframe src="${url}" class="preview-pdf"></iframe>`;
                    } else {
                        body.innerHTML = `<div class="text-slate-400">Preview not available for this file type</div>`;
                    }
                });
            }
        })
        .catch(err => {
            body.innerHTML = '<div class="text-red-400">Error loading preview</div>';
            console.error('Error previewing file:', err);
        });
}

function closePreview() {
    document.getElementById('previewModal').classList.remove('active');
}

// ============ SHARE ============
function openShare(id) {
    shareFileId = id;
    document.getElementById('sharePassword').value = '';
    document.getElementById('shareExpires').value = '24';
    document.getElementById('shareResult').classList.add('hidden');
    document.getElementById('shareModal').classList.add('active');
}

function closeShare() {
    document.getElementById('shareModal').classList.remove('active');
}

function confirmShare() {
    const password = document.getElementById('sharePassword').value;
    const expiresIn = parseInt(document.getElementById('shareExpires').value) || 24;

    fetch('/api/files/' + shareFileId + '/share', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ password: password || null, expires_in: expiresIn })
    })
    .then(res => res.json())
    .then(data => {
        if (data.share_url) {
            document.getElementById('shareUrl').value = data.share_url;
            document.getElementById('shareResult').classList.remove('hidden');
            loadFiles();
        }
    })
    .catch(err => console.error('Error sharing file:', err));
}

function copyShareUrl() {
    const url = document.getElementById('shareUrl');
    url.select();
    document.execCommand('copy');
    alert('Share URL copied to clipboard!');
}

// ============ CLOSE MODALS ON ESCAPE ============
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePreview();
        closeShare();
    }
});

// ============ CLICK OUTSIDE MODAL TO CLOSE ============
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
        }
    });
});
</script>

</body>
</html>