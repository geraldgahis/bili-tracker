<?php
use Livewire\Component;

new class extends Component {
    // Dashboard data logic goes here
};
?>

<!-- Alpine State: Manages Sidebar & Dark Mode globally -->
<div x-data="{
    sidebarOpen: window.innerWidth >= 1024,
    darkMode: document.documentElement.classList.contains('dark')
}" x-init="$watch('darkMode', val => {
    document.documentElement.classList.toggle('dark', val);
});"
    class="min-h-screen flex bg-slate-50 dark:bg-[#101012] text-slate-800 dark:text-[#F4F4F5] transition-colors duration-200 overflow-hidden font-sans">

    <!-- Inject the Sidebar -->
    <livewire:admin.sidebar />

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">

        <!-- Top Header Bar -->
        <header
            class="h-16 shrink-0 flex items-center justify-between px-4 sm:px-6 bg-white dark:bg-[#1C1C1F] border-b border-slate-200 dark:border-[#2E2E32] z-10 transition-colors">

            <!-- Left: Sidebar Toggle & Title -->
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen"
                    class="p-2 rounded-lg text-slate-500 dark:text-[#A1A1AA] hover:bg-slate-100 dark:hover:bg-[#2E2E32] focus:outline-none transition-colors"
                    aria-label="Toggle Sidebar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h2 class="text-lg font-bold tracking-tight">System Overview</h2>
            </div>

            <!-- Right: Dark Mode Toggle & Profile -->
            <div class="flex items-center gap-3 sm:gap-5">

                <!-- Dark Mode Toggler -->
                <button @click="darkMode = !darkMode"
                    class="p-2 rounded-lg text-slate-500 dark:text-[#A1A1AA] hover:bg-slate-100 dark:hover:bg-[#2E2E32] focus:outline-none transition-colors"
                    aria-label="Toggle Dark Mode">
                    <!-- Sun Icon (Shows in Dark Mode) -->
                    <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                    </svg>
                    <!-- Moon Icon (Shows in Light Mode) -->
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                    </svg>
                </button>

                <!-- Divider -->
                <div class="h-6 w-px bg-slate-200 dark:bg-[#2E2E32]"></div>

                <!-- User Profile -->
                <div class="flex items-center gap-2.5">
                    <span class="text-sm font-semibold hidden sm:block">{{ auth()->user()->name ?? 'Admin' }}</span>
                    <div
                        class="w-8 h-8 rounded-full bg-[#F2B705] text-slate-900 flex items-center justify-center text-xs font-black shadow-sm">
                        {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                    </div>
                </div>
            </div>
        </header>

        <!-- Scrollable Dashboard Content -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6">

            <!-- Key Metrics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 mb-6">
                <div
                    class="bg-white dark:bg-[#1C1C1F] rounded-xl shadow-sm border border-slate-200 dark:border-[#2E2E32] p-5">
                    <p class="text-[11px] font-bold text-slate-500 dark:text-[#A1A1AA] uppercase tracking-wider">Total
                        Users</p>
                    <p class="text-2xl font-black mt-1">1,248</p>
                </div>
                <div
                    class="bg-white dark:bg-[#1C1C1F] rounded-xl shadow-sm border border-slate-200 dark:border-[#2E2E32] p-5">
                    <p class="text-[11px] font-bold text-slate-500 dark:text-[#A1A1AA] uppercase tracking-wider">Global
                        Products</p>
                    <p class="text-2xl font-black mt-1">45,912</p>
                </div>
                <div
                    class="bg-white dark:bg-[#1C1C1F] rounded-xl shadow-sm border border-slate-200 dark:border-[#2E2E32] p-5">
                    <p class="text-[11px] font-bold text-slate-500 dark:text-[#A1A1AA] uppercase tracking-wider">Active
                        Shopping Sessions</p>
                    <p class="text-2xl font-black text-[#0E6B4C] mt-1">34</p>
                </div>
            </div>

            <!-- Main Data Table Panel -->
            <div
                class="bg-white dark:bg-[#1C1C1F] rounded-xl shadow-sm border border-slate-200 dark:border-[#2E2E32] overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-[#2E2E32]">
                    <h3 class="text-sm font-bold">Recent User Activity</h3>
                </div>
                <div class="p-5">
                    <div
                        class="h-64 flex items-center justify-center border-2 border-dashed border-slate-200 dark:border-[#2E2E32] rounded-lg bg-slate-50 dark:bg-[#101012]">
                        <p class="text-xs font-medium text-slate-500 dark:text-[#A1A1AA]">Livewire data table renders
                            here.</p>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- Mobile Overlay (Closes sidebar when clicking outside on mobile) -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-20 lg:hidden"
        x-transition.opacity x-cloak></div>
</div>
