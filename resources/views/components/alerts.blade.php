<!-- {{-- ============================================================
     ALERTS COMPONENT — alerts.blade.php
     Usage: @include('components.alerts')
     ============================================================ --}}

{{-- Add this to your app.css or a <style> tag in your layout --}} -->
@once
<style>
    @keyframes drain {
        from { width: 100%; }
        to   { width: 0%; }
    }
    .alert-drain {
        animation: drain 4s linear forwards;
    }
</style>
@endonce


{{-- SUCCESS --}}
@if(session('success'))
<div
    x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => show = false, 4000)"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-1"
    class="relative flex items-start gap-3 bg-green-50 border-l-[3px] border-green-500 text-green-900 px-4 py-3.5 rounded-md overflow-hidden mb-4"
>
    <svg class="w-4 h-4 text-green-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <div class="flex-1 min-w-0">
        <p class="text-[10px] font-mono font-medium tracking-widest uppercase text-green-600 mb-0.5">Success</p>
        <p class="text-sm font-medium">{{ session('success') }}</p>
    </div>
    <button @click="show = false" class="text-green-500 hover:text-green-800 transition opacity-40 hover:opacity-90 mt-0.5 flex-shrink-0">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
    <div class="absolute bottom-0 left-0 h-0.5 bg-green-500 alert-drain"></div>
</div>
@endif


{{-- ERROR --}}
@if($errors->any())
<div
    x-data="{ show: true }"
    x-show="show"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-1"
    class="flex items-start gap-3 bg-red-50 border-l-[3px] border-red-500 text-red-900 px-4 py-3.5 rounded-md mb-4"
>
    <svg class="w-4 h-4 text-red-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <div class="flex-1 min-w-0">
        <p class="text-[10px] font-mono font-medium tracking-widest uppercase text-red-600 mb-0.5">Error</p>
        @if($errors->count() === 1)
            <p class="text-sm font-medium">{{ $errors->first() }}</p>
        @else
            <p class="text-sm font-medium mb-2">Please fix the following issues:</p>
            <ul class="space-y-1">
                @foreach($errors->all() as $error)
                    <li class="flex items-start gap-2 text-sm">
                        <span class="font-mono text-[11px] text-red-400 mt-0.5 flex-shrink-0">—</span>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
    <button @click="show = false" class="text-red-500 hover:text-red-800 transition opacity-40 hover:opacity-90 mt-0.5 flex-shrink-0">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
@endif


{{-- WARNING --}}
@if(session('warning'))
<div
    x-data="{ show: true }"
    x-show="show"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-1"
    class="flex items-start gap-3 bg-yellow-50 border-l-[3px] border-yellow-500 text-yellow-900 px-4 py-3.5 rounded-md mb-4"
>
    <svg class="w-4 h-4 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
    <div class="flex-1 min-w-0">
        <p class="text-[10px] font-mono font-medium tracking-widest uppercase text-yellow-600 mb-0.5">Warning</p>
        <p class="text-sm font-medium">{{ session('warning') }}</p>
    </div>
    <button @click="show = false" class="text-yellow-500 hover:text-yellow-800 transition opacity-40 hover:opacity-90 mt-0.5 flex-shrink-0">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
@endif


{{-- INFO --}}
@if(session('info'))
<div
    x-data="{ show: true }"
    x-show="show"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-1"
    class="flex items-start gap-3 bg-blue-50 border-l-[3px] border-blue-500 text-blue-900 px-4 py-3.5 rounded-md mb-4"
>
    <svg class="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <div class="flex-1 min-w-0">
        <p class="text-[10px] font-mono font-medium tracking-widest uppercase text-blue-600 mb-0.5">Info</p>
        <p class="text-sm font-medium">{{ session('info') }}</p>
    </div>
    <button @click="show = false" class="text-blue-500 hover:text-blue-800 transition opacity-40 hover:opacity-90 mt-0.5 flex-shrink-0">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
@endif



<!-- // Success
return back()->with('success', 'Record saved successfully.');

// Warning
return back()->with('warning', 'Your session expires soon.');

// Info
return back()->with('info', 'A new update is available.');

// Error (via validation — automatic)
return back()->withErrors($validator);

// Error (manual)
return back()->withErrors(['Something went wrong.']); -->