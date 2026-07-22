<?php
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return $this->redirect('/login', navigate: true);
    }
};
?>

<aside x-show="sidebarOpen" x-transition:enter="transition-transform ease-out duration-300"
    x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
    x-transition:leave="transition-transform ease-in duration-200" x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    class="fixed inset-y-0 left-0 z-30 w-64 bg-white dark:bg-[#1C1C1F] border-r border-slate-200 dark:border-[#2E2E32] flex flex-col shadow-2xl lg:shadow-none lg:static lg:block shrink-0 h-screen transition-colors"
    x-cloak>
    <!-- Admin Brand Header -->
    <div class="h-16 flex items-center px-6 border-b border-slate-200 dark:border-[#2E2E32] shrink-0">
        <div
            class="w-8 h-8 rounded-lg bg-[#F2B705] flex items-center justify-center text-slate-900 font-black text-sm shadow-sm mr-3">
            ₱
        </div>
        <span class="text-base font-bold tracking-tight text-slate-900 dark:text-white">PesoScan</span>
    </div>

    <!-- Navigation Links Grouped -->
    <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-8">

        <!-- MAIN SECTION -->
        <div>
            <p class="px-2 mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-[#A1A1AA]">
                Main
            </p>
            <div class="space-y-1">
                <!-- Active Link -->
                <a href="#"
                    class="flex items-center px-3 py-2 text-sm font-bold text-[#0E6B4C] bg-green-50 dark:bg-[#0E6B4C]/10 rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="truncate">Dashboard</span>
                </a>

                <!-- Inactive Link -->
                <a href="#"
                    class="flex items-center px-3 py-2 text-sm font-semibold text-slate-600 dark:text-[#A1A1AA] hover:bg-slate-100 dark:hover:bg-[#2E2E32] hover:text-slate-900 dark:hover:text-white rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="truncate">Active Trips</span>
                </a>
            </div>
        </div>

        <!-- CATALOG SECTION -->
        <div>
            <p class="px-2 mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-[#A1A1AA]">
                Catalog
            </p>
            <div class="space-y-1">
                <a href="#"
                    class="flex items-center px-3 py-2 text-sm font-semibold text-slate-600 dark:text-[#A1A1AA] hover:bg-slate-100 dark:hover:bg-[#2E2E32] hover:text-slate-900 dark:hover:text-white rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <span class="truncate">Products</span>
                </a>

                <a href="{{ route('admin.categories') }}"
                    class="flex items-center px-3 py-2 text-sm font-semibold text-slate-600 dark:text-[#A1A1AA] hover:bg-slate-100 dark:hover:bg-[#2E2E32] hover:text-slate-900 dark:hover:text-white rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span class="truncate">Categories</span>
                </a>

                <a href="#"
                    class="flex items-center px-3 py-2 text-sm font-semibold text-slate-600 dark:text-[#A1A1AA] hover:bg-slate-100 dark:hover:bg-[#2E2E32] hover:text-slate-900 dark:hover:text-white rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span class="truncate">Stores</span>
                </a>
            </div>
        </div>

        <!-- SYSTEM SECTION -->
        <div>
            <p class="px-2 mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-[#A1A1AA]">
                System
            </p>
            <div class="space-y-1">
                <a href="#"
                    class="flex items-center px-3 py-2 text-sm font-semibold text-slate-600 dark:text-[#A1A1AA] hover:bg-slate-100 dark:hover:bg-[#2E2E32] hover:text-slate-900 dark:hover:text-white rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="truncate">Users</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- User Account Section / Footer -->
    <div class="p-4 border-t border-slate-200 dark:border-[#2E2E32] shrink-0 bg-slate-50/50 dark:bg-[#101012]/30">
        <div class="flex items-center justify-between group">

            <!-- User Info -->
            <div class="flex items-center gap-3 overflow-hidden">
                <div
                    class="w-9 h-9 rounded-full bg-[#F2B705] text-slate-900 flex items-center justify-center text-sm font-black shrink-0 shadow-sm border border-[#F2B705]/50">
                    {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                </div>
                <div class="truncate">
                    <p class="text-sm font-bold text-slate-900 dark:text-white truncate">
                        {{ auth()->user()->name ?? 'Administrator' }}
                    </p>
                    <p
                        class="text-[10px] font-semibold text-slate-500 dark:text-[#A1A1AA] truncate uppercase tracking-wider">
                        {{ auth()->user()->role ?? 'Admin Role' }}
                    </p>
                </div>
            </div>

            <!-- Logout Button (Icon) -->
            <button wire:click="logout"
                class="p-2 text-slate-400 dark:text-[#A1A1AA] hover:text-[#E2601F] dark:hover:text-[#E2601F] hover:bg-orange-50 dark:hover:bg-[#E2601F]/10 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-[#E2601F]"
                aria-label="Log Out" title="Log Out">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </button>

        </div>
    </div>
</aside>
