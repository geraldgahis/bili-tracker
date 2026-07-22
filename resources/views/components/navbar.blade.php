<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return $this->redirect('/', navigate: true);
    }
};
?>

<nav x-data="{ mobileMenuOpen: false }"
    class="sticky top-0 z-40 w-full bg-white/90 dark:bg-[#1C1C1F]/90 backdrop-blur-md border-b border-slate-200 dark:border-[#2E2E32] transition-colors duration-200">

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <!-- Left: Logo -->
            <div class="shrink-0 flex items-center">
                <a href="/dashboard" wire:navigate class="flex items-center gap-2.5 group focus:outline-none">
                    <div
                        class="flex items-center justify-center w-8 h-8 rounded-lg bg-[#0E6B4C] text-white shadow-sm group-hover:bg-opacity-90 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">
                        Price<span class="text-[#0E6B4C] dark:text-[#F2B705]">Tracker</span>
                    </span>
                </a>
            </div>

            <!-- Middle/Right: Desktop Navigation Links -->
            <div class="hidden md:flex flex-1 items-center justify-end md:mr-8 space-x-1">
                <a href="/dashboard" wire:navigate
                    class="px-3 py-2 text-sm font-bold rounded-lg text-[#0E6B4C] dark:text-[#F2B705] bg-[#0E6B4C]/10 dark:bg-[#F2B705]/10 transition-colors">
                    Dashboard
                </a>
                <a href="/products" wire:navigate
                    class="px-3 py-2 text-sm font-semibold rounded-lg text-slate-600 dark:text-[#A1A1AA] hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-[#2E2E32]/50 transition-colors">
                    Products
                </a>
                <a href="/categories" wire:navigate
                    class="px-3 py-2 text-sm font-semibold rounded-lg text-slate-600 dark:text-[#A1A1AA] hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-[#2E2E32]/50 transition-colors">
                    Categories
                </a>
                <a href="/stores" wire:navigate
                    class="px-3 py-2 text-sm font-semibold rounded-lg text-slate-600 dark:text-[#A1A1AA] hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-[#2E2E32]/50 transition-colors">
                    Stores
                </a>
            </div>

            <!-- Far Right: Actions & Dark Mode Toggler -->
            <div class="flex items-center gap-3">

                <!-- Dark Mode Toggler -->
                <button type="button" @click="darkMode = !darkMode"
                    class="p-2 rounded-full bg-slate-50 dark:bg-[#101012] border border-slate-200 dark:border-[#2E2E32] text-slate-500 dark:text-[#A1A1AA] hover:text-slate-900 dark:hover:text-white transition shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0E6B4C] dark:focus:ring-offset-[#1C1C1F]"
                    aria-label="Toggle Dark Mode">

                    <svg x-show="darkMode" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                    </svg>

                    <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                    </svg>
                </button>

                <!-- Profile Dropdown (Desktop) -->
                @auth
                    <div x-data="{ profileOpen: false }" class="relative hidden sm:block">
                        <button type="button" @click="profileOpen = !profileOpen" @click.away="profileOpen = false"
                            class="w-8 h-8 rounded-full border border-slate-200 dark:border-[#2E2E32] bg-slate-100 dark:bg-[#101012] overflow-hidden focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0E6B4C] dark:focus:ring-offset-[#1C1C1F] transition-shadow">
                            <svg class="w-full h-full text-slate-400 dark:text-[#52525B] mt-1" fill="currentColor"
                                viewBox="0 0 24 24">
                                <path
                                    d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="profileOpen" x-cloak x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-48 bg-white dark:bg-[#1C1C1F] border border-slate-200 dark:border-[#2E2E32] rounded-xl shadow-lg py-1 z-50 overflow-hidden">

                            <div class="px-4 py-2 border-b border-slate-100 dark:border-[#2E2E32]">
                                <p
                                    class="text-[10px] font-bold text-slate-500 dark:text-[#A1A1AA] uppercase tracking-wider">
                                    Signed in as</p>
                                <p class="text-sm font-bold text-slate-900 dark:text-white truncate mt-0.5">
                                    {{ Auth::user()->name }}</p>
                            </div>

                            <div class="py-1">
                                <a href="/profile" wire:navigate
                                    class="block px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#2E2E32] transition-colors">
                                    Account Settings
                                </a>

                                <!-- Admin Only Links -->
                                @if (Auth::user()->is_admin)
                                    <a href="/approvals" wire:navigate
                                        class="block px-4 py-2 text-sm font-bold text-[#E2601F] hover:bg-slate-50 dark:hover:bg-[#2E2E32] transition-colors">
                                        Pending Approvals
                                    </a>
                                @endif
                            </div>

                            <div class="border-t border-slate-100 dark:border-[#2E2E32] pt-1">
                                <button wire:click="logout"
                                    class="w-full text-left block px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#2E2E32] transition-colors">
                                    Sign Out
                                </button>
                            </div>
                        </div>
                    </div>
                @endauth

                <!-- Mobile menu button -->
                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden p-2 rounded-lg text-slate-500 dark:text-[#A1A1AA] hover:bg-slate-100 dark:hover:bg-[#2E2E32] focus:outline-none focus:ring-2 focus:ring-[#0E6B4C] transition">
                    <span class="sr-only">Open main menu</span>
                    <svg x-show="!mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="mobileMenuOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Panel -->
    <div x-show="mobileMenuOpen" x-cloak x-collapse
        class="md:hidden border-t border-slate-200 dark:border-[#2E2E32] bg-white dark:bg-[#1C1C1F]">
        <div class="px-4 py-3 space-y-1">
            <a href="/dashboard" wire:navigate
                class="block px-3 py-2.5 rounded-lg text-sm font-bold text-[#0E6B4C] dark:text-[#F2B705] bg-[#0E6B4C]/10 dark:bg-[#F2B705]/10">
                Dashboard
            </a>
            <a href="/products" wire:navigate
                class="block px-3 py-2.5 rounded-lg text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#2E2E32]">
                Products
            </a>
            <a href="/categories" wire:navigate
                class="block px-3 py-2.5 rounded-lg text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#2E2E32]">
                Categories
            </a>
            <a href="/stores" wire:navigate
                class="block px-3 py-2.5 rounded-lg text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#2E2E32]">
                Stores
            </a>

            @auth
                <!-- Mobile User Profile Section -->
                <div class="pt-4 mt-2 border-t border-slate-100 dark:border-[#2E2E32]">

                    <div class="px-3 mb-3">
                        <p class="text-sm font-bold text-slate-900 dark:text-white">{{ Auth::user()->name }}</p>
                        <p class="text-xs font-medium text-slate-500 dark:text-[#A1A1AA]">{{ Auth::user()->email }}</p>
                    </div>

                    <a href="/profile" wire:navigate
                        class="block px-3 py-2.5 rounded-lg text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#2E2E32]">
                        Account Settings
                    </a>

                    @if (Auth::user()->is_admin)
                        <a href="/approvals" wire:navigate
                            class="block px-3 py-2.5 rounded-lg text-sm font-bold text-[#E2601F] hover:bg-slate-50 dark:hover:bg-[#2E2E32]">
                            Pending Approvals
                        </a>
                    @endif

                    <button type="button" wire:click="logout"
                        class="w-full text-left block px-3 py-2.5 rounded-lg text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#2E2E32]">
                        Sign Out
                    </button>
                </div>
            @endauth
        </div>
    </div>
</nav>
