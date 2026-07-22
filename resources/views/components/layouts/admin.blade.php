<?php

use Livewire\Component;

new class extends Component {
    //
};
?>

<div x-data="{ sidebarOpen: window.innerWidth >= 1024 }" class="min-h-screen flex overflow-hidden font-sans">
    <livewire:admin.sidebar />

    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">

        <header
            class="h-16 shrink-0 flex items-center justify-between px-4 sm:px-6 bg-white dark:bg-[#1C1C1F] border-b border-slate-200 dark:border-[#2E2E32] z-10 transition-colors">

            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen"
                    class="p-2 rounded-lg text-slate-500 dark:text-[#A1A1AA] hover:bg-slate-100 dark:hover:bg-[#2E2E32] focus:outline-none transition-colors lg:hidden"
                    aria-label="Toggle Sidebar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <div class="flex items-center gap-3 sm:gap-5">
                <button @click="darkMode = !darkMode"
                    class="p-2 rounded-lg text-slate-500 dark:text-[#A1A1AA] hover:bg-slate-100 dark:hover:bg-[#2E2E32] focus:outline-none transition-colors">
                    <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                    </svg>
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                    </svg>
                </button>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
            {{ $slot }}
        </main>
    </div>

    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-20 lg:hidden"
        x-transition.opacity x-cloak></div>
</div>
