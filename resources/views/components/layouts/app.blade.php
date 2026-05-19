<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XGrow Workspace</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @livewireStyles
    
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    
    <style> 
        body { font-family: 'Plus Jakarta Sans', sans-serif; } 
        /* Global Premium Scrollbar Styling */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[#F4F7F6] text-gray-800 antialiased h-screen flex flex-col overflow-hidden">
    
    <div class="flex-1 flex flex-col min-h-0 w-full overflow-hidden">
        {{ $slot }}
    </div>

    @livewireScripts
</body>
</html>