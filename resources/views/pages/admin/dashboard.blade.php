<?php
// resources/views/pages/dashboard.blade.php
// Route: Route::livewire('/dashboard', 'pages::dashboard')->name('dashboard')->middleware('auth');

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use Livewire\Attributes\Title;

new #[Title('Dashboard')] class extends Component {
    public function with(): array
    {
        $user = Auth::user();

        // Stats
        $trackedCount = $user->trackedProducts()->count();
        $storeCount = $user->trackedProducts()->whereNotNull('user_products.store_id')->distinct('user_products.store_id')->count('user_products.store_id');

        // Admin-only stats
        $pendingApprovals = 0;
        if ($user->is_admin) {
            $pendingApprovals = Product::where('status', 'pending')->count();
        }

        // Recent Activity
        $recentProducts = $user
            ->trackedProducts()
            ->with(['category'])
            ->orderByPivot('created_at', 'desc')
            ->take(4)
            ->get();

        return [
            'user' => $user,
            'trackedCount' => $trackedCount,
            'storeCount' => $storeCount,
            'pendingApprovals' => $pendingApprovals,
            'recentProducts' => $recentProducts,
        ];
    }
};
?>

<div class="min-h-screen bg-slate-50 dark:bg-[#101012] transition-colors duration-200">
    <!-- Main Content Wrapper: Full width up to 5xl, centered, no sidebar -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full space-y-8">

        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    Welcome back, {{ explode(' ', $user->name)[0] }}!
                </h2>
                <p class="text-sm font-medium text-slate-500 dark:text-[#A1A1AA] mt-1">
                    Here is what's happening with your tracked products today.
                </p>
            </div>

            <a href="{{ route('products.create') }}" wire:navigate
                class="inline-flex items-center justify-center gap-2 bg-[#0E6B4C] hover:bg-opacity-90 text-white text-sm font-bold px-5 py-2.5 rounded-lg shadow-sm transition active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0E6B4C] dark:focus:ring-offset-[#101012]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Track New Product
            </a>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 {{ $user->is_admin ? 'lg:grid-cols-3' : 'lg:grid-cols-2' }} gap-5">
            <!-- Stat 1: Total Tracked -->
            <div
                class="bg-white dark:bg-[#1C1C1F] border border-slate-200 dark:border-[#2E2E32] rounded-xl shadow-sm p-6 flex items-center gap-4 transition-colors duration-200">
                <div
                    class="w-12 h-12 rounded-full bg-[#0E6B4C]/10 dark:bg-[#F2B705]/10 text-[#0E6B4C] dark:text-[#F2B705] flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-500 dark:text-[#A1A1AA] uppercase tracking-wider">Tracked
                        Items</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                        {{ number_format($trackedCount) }}</p>
                </div>
            </div>

            <!-- Stat 2: Stores Saved -->
            <div
                class="bg-white dark:bg-[#1C1C1F] border border-slate-200 dark:border-[#2E2E32] rounded-xl shadow-sm p-6 flex items-center gap-4 transition-colors duration-200">
                <div
                    class="w-12 h-12 rounded-full bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-500 dark:text-[#A1A1AA] uppercase tracking-wider">Saved
                        Stores</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                        {{ number_format($storeCount) }}</p>
                </div>
            </div>

            <!-- Stat 3: Pending Approvals (Admin Only) -->
            @if ($user->is_admin)
                <div
                    class="bg-white dark:bg-[#1C1C1F] border border-slate-200 dark:border-[#2E2E32] rounded-xl shadow-sm p-6 flex items-center gap-4 transition-colors duration-200 relative overflow-hidden">
                    @if ($pendingApprovals > 0)
                        <div class="absolute top-0 right-0 w-2 h-full bg-[#E2601F]"></div>
                    @endif
                    <div
                        class="w-12 h-12 rounded-full bg-[#E2601F]/10 text-[#E2601F] flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-500 dark:text-[#A1A1AA] uppercase tracking-wider">Pending
                            Review</p>
                        <div class="flex items-baseline gap-2 mt-0.5">
                            <p class="text-2xl font-black text-slate-900 dark:text-white">
                                {{ number_format($pendingApprovals) }}</p>
                            @if ($pendingApprovals > 0)
                                <a href="#" class="text-xs font-bold text-[#E2601F] hover:underline">Review now
                                    →</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Two-Column Layout for Desktop -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left Column: Recently Tracked (Takes up 2/3 space) -->
            <div class="lg:col-span-2 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Recently Tracked</h3>
                    <a href="{{ route('products.index') }}" wire:navigate
                        class="text-sm font-bold text-[#0E6B4C] dark:text-[#F2B705] hover:underline">View all →</a>
                </div>

                <div
                    class="bg-white dark:bg-[#1C1C1F] border border-slate-200 dark:border-[#2E2E32] rounded-xl shadow-sm overflow-hidden transition-colors duration-200">
                    <ul class="divide-y divide-slate-100 dark:divide-[#2E2E32]">
                        @forelse($recentProducts as $product)
                            <li
                                class="p-5 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-[#2E2E32]/30 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-12 h-12 rounded-lg bg-slate-100 dark:bg-[#101012] border border-slate-200 dark:border-[#2E2E32] flex items-center justify-center shrink-0 overflow-hidden">
                                        @if ($product->image_path)
                                            <img src="{{ asset('storage/' . $product->image_path) }}"
                                                alt="{{ $product->name }}" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-6 h-6 text-slate-400 dark:text-slate-600" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900 dark:text-[#F4F4F5]">
                                            {{ $product->pivot->custom_name ?: $product->name }}</p>
                                        <p class="text-xs text-slate-500 dark:text-[#A1A1AA] mt-0.5">
                                            {{ $product->pivot->store->name ?? 'No store specified' }} •
                                            {{ $product->pivot->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">
                                        ₱{{ number_format($product->pivot->price, 2) }}</p>
                                    <p
                                        class="text-[11px] font-medium text-slate-500 dark:text-[#A1A1AA] uppercase mt-0.5">
                                        / {{ $product->pivot->purchase_unit }}</p>
                                </div>
                            </li>
                        @empty
                            <li class="p-8 text-center">
                                <p class="text-sm font-medium text-slate-500 dark:text-[#A1A1AA]">No products tracked
                                    yet.</p>
                                <a href="{{ route('products.create') }}" wire:navigate
                                    class="text-sm font-bold text-[#0E6B4C] dark:text-[#F2B705] mt-2 inline-block">Scan
                                    your first barcode</a>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Right Column: Quick Utilities (Takes up 1/3 space) -->
            <div class="space-y-4">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Quick Links</h3>

                <div class="grid grid-cols-1 gap-3">
                    <a href="/categories" wire:navigate
                        class="group bg-white dark:bg-[#1C1C1F] border border-slate-200 dark:border-[#2E2E32] rounded-xl p-4 flex items-center justify-between hover:border-[#0E6B4C] dark:hover:border-[#F2B705] transition-colors shadow-sm">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-full bg-slate-100 dark:bg-[#101012] text-slate-600 dark:text-slate-400 flex items-center justify-center group-hover:text-[#0E6B4C] dark:group-hover:text-[#F2B705] transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                            </div>
                            <span
                                class="text-sm font-bold text-slate-700 dark:text-slate-300 group-hover:text-[#0E6B4C] dark:group-hover:text-[#F2B705] transition-colors">Manage
                                Categories</span>
                        </div>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>

                    <a href="/stores" wire:navigate
                        class="group bg-white dark:bg-[#1C1C1F] border border-slate-200 dark:border-[#2E2E32] rounded-xl p-4 flex items-center justify-between hover:border-[#0E6B4C] dark:hover:border-[#F2B705] transition-colors shadow-sm">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-full bg-slate-100 dark:bg-[#101012] text-slate-600 dark:text-slate-400 flex items-center justify-center group-hover:text-[#0E6B4C] dark:group-hover:text-[#F2B705] transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                            </div>
                            <span
                                class="text-sm font-bold text-slate-700 dark:text-slate-300 group-hover:text-[#0E6B4C] dark:group-hover:text-[#F2B705] transition-colors">Manage
                                Stores</span>
                        </div>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
