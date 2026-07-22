<?php
// resources/views/pages/products/index.blade.php

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Livewire\Component;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public ?int $categoryId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryId(): void
    {
        $this->resetPage();
    }

    public function trackProduct(int $productId): void
    {
        // Attach the global product to the user's tracked list with default values
        Auth::user()
            ->trackedProducts()
            ->syncWithoutDetaching([
                $productId => [
                    'price' => 0.0,
                    'is_tracked' => true,
                    'purchase_unit' => 'piece',
                    'pieces_per_bulk' => 1,
                ],
            ]);
    }

    public function untrack(int $productId): void
    {
        Auth::user()->trackedProducts()->detach($productId);
    }

    public function with(): array
    {
        // Base query for all approved global products
        $query = Product::query()
            ->where('status', 'approved')
            ->with([
                'category',
                'trackedByUsers' => function ($q) {
                    // Eager load only the current user's pivot data if they track it
                    $q->where('user_id', Auth::id());
                },
            ])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'ilike', "%{$this->search}%")->orWhere('barcode', 'ilike', "%{$this->search}%");
                });
            })
            ->when($this->categoryId, function ($q) {
                $q->where('category_id', $this->categoryId);
            })
            ->orderBy('name');

        // Get total count of all approved global products for the header text
        $totalApprovedCount = Product::where('status', 'approved')->count();

        return [
            'products' => $query->paginate(12),
            'categories' => Category::orderBy('name')->get(),
            'totalCount' => $totalApprovedCount,
        ];
    }
};
?>

<div>
    <style>
        @media (min-width: 640px) {
            .tag-notch::before {
                content: '';
                position: absolute;
                top: 14px;
                left: 14px;
                width: 12px;
                height: 12px;
                border-radius: 9999px;
                background: #f8fafc;
                box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.15);
                z-index: 10;
            }

            :is(.dark .tag-notch)::before {
                background: #101012;
            }

            .tag-notch::after {
                content: '';
                position: absolute;
                top: 8px;
                left: 8px;
                width: 24px;
                height: 24px;
                border-radius: 9999px;
                background: transparent;
                border: 2px dashed currentColor;
                opacity: 0.15;
                z-index: 5;
            }
        }
    </style>

    <div
        class="min-h-screen bg-slate-50 dark:bg-[#101012] text-slate-800 dark:text-[#F4F4F5] transition-colors duration-200">

        <!-- Header -->
        <header
            class="sticky top-16 z-30 bg-white/90 dark:bg-[#1C1C1F]/90 backdrop-blur-md border-b border-slate-200 dark:border-[#2E2E32]">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h1 class="font-extrabold text-xl sm:text-2xl tracking-tight text-slate-900 dark:text-white">
                            Product Catalog
                        </h1>
                        <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-[#A1A1AA] mt-0.5">
                            Browse all approved products and track them for your store
                        </p>
                    </div>

                    <a href="{{ route('products.create') }}" wire:navigate
                        class="shrink-0 inline-flex items-center justify-center gap-2 bg-[#0E6B4C] hover:bg-opacity-90 text-white text-sm font-bold px-4 py-2 sm:px-5 sm:py-2.5 rounded-lg shadow-sm transition active:scale-[0.98]">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="hidden sm:inline">Add Product</span>
                    </a>
                </div>

                <!-- Search Input -->
                <div class="mt-4 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400 dark:text-[#A1A1AA]" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Search by name or barcode..."
                        class="w-full pl-10 pr-10 py-2.5 bg-slate-50 dark:bg-[#101012] border border-slate-200 dark:border-[#2E2E32] rounded-xl text-sm shadow-sm placeholder-slate-400 dark:placeholder-[#A1A1AA] focus:outline-none focus:ring-2 focus:ring-[#F2B705] focus:border-transparent transition" />

                    @if (strlen($search) > 0)
                        <button wire:click="$set('search', '')"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                </div>

                <!-- Categories -->
                <div x-data="{ showAll: false }">
                    <div :class="showAll ? 'flex-wrap' : 'flex-nowrap overflow-x-auto custom-scrollbar'"
                        class="mt-3 flex items-center gap-2 pb-1 -mx-1 p-1">
                        <button wire:click="$set('categoryId', null)"
                            class="{{ is_null($categoryId) ? 'bg-[#0E6B4C] text-white border-[#0E6B4C]' : 'bg-white dark:bg-[#101012] text-slate-600 dark:text-[#A1A1AA] border-slate-200 dark:border-[#2E2E32]' }} shrink-0 whitespace-nowrap px-3.5 py-1.5 rounded-full text-xs font-bold border transition-colors">
                            All
                        </button>
                        @foreach ($categories as $category)
                            <button x-show="showAll || {{ $loop->index }} < 5"
                                wire:click="$set('categoryId', {{ $category->id }})"
                                class="{{ $categoryId === $category->id ? 'bg-[#0E6B4C] text-white border-[#0E6B4C]' : 'bg-white dark:bg-[#101012] text-slate-600 dark:text-[#A1A1AA] border-slate-200 dark:border-[#2E2E32]' }} shrink-0 whitespace-nowrap px-3.5 py-1.5 rounded-full text-xs font-bold border transition-colors">
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <div class="flex items-center justify-between mb-5">
                <p class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-[#A1A1AA]">
                    Showing <span class="text-slate-900 dark:text-white">{{ $products->count() }}</span>
                    of <span class="text-slate-900 dark:text-white">{{ $totalCount }}</span> approved products
                </p>
            </div>

            <!-- Loading Spinner -->
            <div wire:loading wire:target="search, categoryId, page" class="w-full">
                <div
                    class="flex flex-col items-center justify-center text-center py-20 px-6 border border-dashed border-slate-300 dark:border-[#2E2E32] rounded-2xl bg-white/50 dark:bg-[#1C1C1F]/50">
                    <svg class="animate-spin w-10 h-10 text-[#0E6B4C] dark:text-[#F2B705] mb-4" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <p class="font-bold text-lg text-slate-700 dark:text-white">Fetching products...</p>
                    <p class="text-sm text-slate-500 dark:text-[#A1A1AA] mt-2 max-w-sm">
                        <span class="font-bold text-[#0E6B4C] dark:text-[#F2B705]">Did you know?</span> The first
                        product ever scanned by a barcode was a pack of Wrigley's Juicy Fruit chewing gum in 1974.
                    </p>
                </div>
            </div>

            <!-- Grid -->
            <div wire:loading.remove wire:target="search, categoryId, page"
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-2.5 sm:gap-5">
                @forelse($products as $product)
                    @php
                        // Check if the current user has tracked this item
                        $userPivot = $product->trackedByUsers->first();
                        $isTracked = !is_null($userPivot);
                    @endphp
                    <article wire:key="product-{{ $product->id }}"
                        class="tag-notch group relative bg-white dark:bg-[#1C1C1F] border border-slate-200 dark:border-[#2E2E32] text-slate-500 dark:text-[#A1A1AA] rounded-xl sm:rounded-2xl shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden flex flex-row sm:flex-col items-center sm:items-stretch gap-3 sm:gap-0 p-2.5 sm:p-0">

                        <!-- Image -->
                        <div
                            class="relative shrink-0 w-16 h-16 sm:w-full sm:h-40 rounded-lg sm:rounded-none flex items-center justify-center bg-slate-50 dark:bg-[#101012] border border-slate-200 dark:border-[#2E2E32] sm:border-0 sm:border-b overflow-hidden">
                            @if ($product->image_path)
                                <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}"
                                    class="w-full h-full object-cover">
                            @else
                                <svg class="w-6 h-6 sm:w-10 sm:h-10 text-slate-300 dark:text-[#3A3A3F]" fill="none"
                                    stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375C2.754 3.75 2.25 4.254 2.25 4.875v1.5c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                            @endif
                            <span
                                class="hidden sm:inline-block absolute top-2.5 right-2.5 text-[10px] font-bold uppercase tracking-wide px-2 py-1 rounded-full bg-[#0E6B4C]/10 text-[#0E6B4C] dark:bg-[#F2B705]/10 dark:text-[#F2B705]">
                                {{ $product->category->name ?? 'None' }}
                            </span>
                        </div>

                        <!-- Body -->
                        <div
                            class="min-w-0 flex-1 flex flex-row sm:flex-col items-center sm:items-stretch gap-2 sm:gap-0 sm:p-4">
                            <div class="min-w-0 flex-1 sm:flex-none">
                                <h3 class="font-bold text-sm text-slate-900 dark:text-white leading-snug line-clamp-2">
                                    {{ $isTracked && $userPivot->custom_name ? $userPivot->custom_name : $product->name }}
                                </h3>
                                <p
                                    class="text-xs font-medium text-slate-400 dark:text-[#71717A] mt-0.5 sm:mt-1 truncate">
                                    @if ($product->size)
                                        <span>{{ $product->size }}</span>
                                    @endif
                                </p>
                            </div>

                            <div
                                class="hidden sm:block mt-3 mb-3 border-t border-dashed border-slate-200 dark:border-[#2E2E32]">
                            </div>

                            <!-- Price & Actions -->
                            <div
                                class="shrink-0 flex flex-col items-end sm:flex-row sm:items-end sm:justify-between sm:mt-auto">
                                <p
                                    class="font-mono font-semibold text-sm sm:text-lg text-[#0E6B4C] dark:text-[#F2B705]">
                                    ₱{{ number_format($isTracked ? $userPivot->price : 0.0, 2) }}
                                </p>

                                @if ($isTracked)
                                    <button wire:click="untrack({{ $product->id }})"
                                        wire:confirm="Remove this from your tracked list?"
                                        class="hidden sm:inline text-xs font-bold text-[#E2601F] hover:underline">
                                        Untrack
                                    </button>
                                @else
                                    <button wire:click="trackProduct({{ $product->id }})"
                                        class="hidden sm:inline text-xs font-bold text-[#0E6B4C] dark:text-[#F2B705] hover:underline">
                                        + Track
                                    </button>
                                @endif
                            </div>

                            <p
                                class="hidden sm:block font-mono text-[10px] text-slate-300 dark:text-[#52525B] mt-3 tracking-wider">
                                {{ $product->barcode ?: 'NO BARCODE' }}
                            </p>
                        </div>
                    </article>
                @empty
                    <div
                        class="col-span-full flex flex-col items-center justify-center text-center py-20 px-6 border border-dashed border-slate-300 dark:border-[#2E2E32] rounded-2xl">
                        <p class="font-bold text-slate-700 dark:text-white">No approved products found</p>
                        <p class="text-sm text-slate-500 dark:text-[#A1A1AA] mt-1">Try adjusting your filters or search
                            terms.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $products->links() }}
            </div>
        </main>
    </div>
</div>
