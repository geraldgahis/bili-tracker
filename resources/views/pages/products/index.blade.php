<?php
// resources/views/pages/products/index.blade.php
// Route: Route::livewire('/products', 'pages::products.index')->name('products.index');

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

    // Untrack / remove the product from the user's personal list
    public function untrack(int $productId): void
    {
        Auth::user()->trackedProducts()->detach($productId);
    }

    public function with(): array
    {
        $query = Auth::user()
            ->trackedProducts()
            ->with(['category'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'ilike', "%{$this->search}%")
                        ->orWhere('barcode', 'ilike', "%{$this->search}%")
                        ->orWhere('user_products.custom_name', 'ilike', "%{$this->search}%");
                });
            })
            ->when($this->categoryId, function ($q) {
                $q->where('category_id', $this->categoryId);
            })
            ->orderBy('products.name');

        return [
            'trackedProducts' => $query->paginate(10),
            'categories' => Category::orderBy('name')->get(),
        ];
    }
};
?>

<div class="min-h-screen bg-slate-50 dark:bg-[#101012] transition-colors duration-200">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

        <!-- Header & Add Button -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Tracked Products</h2>
                <p class="text-sm font-medium text-slate-500 dark:text-[#A1A1AA] mt-1">Manage your scanned items, custom
                    pricing, and stores.</p>
            </div>

            <a href="{{ route('products.create') }}" wire:navigate
                class="inline-flex items-center justify-center gap-2 bg-[#0E6B4C] hover:bg-opacity-90 text-white text-sm font-bold px-4 py-2.5 rounded-lg shadow-sm transition active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0E6B4C] dark:focus:ring-offset-[#101012]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                </svg>
                Scan / Add Product
            </a>
        </div>

        <!-- Filters: Search and Category Filter -->
        <div class="flex flex-col sm:flex-row gap-3 mb-6">
            <div class="relative flex-1 max-w-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400 dark:text-[#A1A1AA]" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="Search by name or barcode..."
                    class="w-full pl-10 pr-4 py-2 bg-white dark:bg-[#1C1C1F] border border-slate-200 dark:border-[#2E2E32] text-slate-900 dark:text-white rounded-lg text-sm shadow-sm placeholder-slate-400 dark:placeholder-[#A1A1AA] focus:outline-none focus:ring-2 focus:ring-[#F2B705] focus:border-transparent transition-colors" />
            </div>

            <div class="w-full sm:w-64">
                <select wire:model.live="categoryId"
                    class="w-full px-3 py-2 bg-white dark:bg-[#1C1C1F] border border-slate-200 dark:border-[#2E2E32] text-slate-900 dark:text-white rounded-lg text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-[#F2B705] focus:border-transparent transition-colors">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Products Table -->
        <div class="bg-white dark:bg-[#1C1C1F] border border-slate-200 dark:border-[#2E2E32] rounded-xl shadow-sm overflow-hidden transition-colors duration-200"
            wire:loading.class="opacity-60 transition-opacity duration-200">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead
                        class="bg-slate-50/80 dark:bg-[#101012]/80 border-b border-slate-200 dark:border-[#2E2E32] text-xs font-bold text-slate-500 dark:text-[#A1A1AA] uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-4">Product</th>
                            <th class="px-5 py-4">Barcode / Size</th>
                            <th class="px-5 py-4">Store & Price</th>
                            <th class="px-5 py-4">Category</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-[#2E2E32]">
                        @forelse ($trackedProducts as $product)
                            <tr wire:key="tracked-product-{{ $product->id }}"
                                class="hover:bg-slate-50 dark:hover:bg-[#2E2E32]/40 transition-colors duration-150">

                                <!-- Product Name & Image -->
                                <td class="px-5 py-3.5 flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-[#101012] border border-slate-200 dark:border-[#2E2E32] overflow-hidden shrink-0 flex items-center justify-center">
                                        @if ($product->image_path)
                                            <img src="{{ asset('storage/' . $product->image_path) }}"
                                                alt="{{ $product->name }}" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-5 h-5 text-slate-400 dark:text-slate-600" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-[#F4F4F5]">
                                            {{ $product->pivot->custom_name ?: $product->name }}
                                        </div>
                                        @if ($product->pivot->custom_name)
                                            <div class="text-[11px] text-slate-400 dark:text-[#A1A1AA]">
                                                Global: {{ $product->name }}
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Barcode & Size -->
                                <td class="px-5 py-3.5">
                                    <div class="font-mono text-xs text-slate-700 dark:text-slate-300">
                                        {{ $product->barcode ?: 'No barcode' }}
                                    </div>
                                    @if ($product->size)
                                        <div class="text-[11px] font-medium text-slate-400 dark:text-[#A1A1AA]">
                                            Size: {{ $product->size }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Store & Price Info -->
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-slate-900 dark:text-[#F4F4F5]">
                                        ₱{{ number_format($product->pivot->price, 2) }}
                                        <span class="text-xs font-normal text-slate-500 dark:text-[#A1A1AA]">
                                            / {{ $product->pivot->purchase_unit }}
                                            ({{ $product->pivot->pieces_per_bulk }} pcs)
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 dark:text-[#A1A1AA]">
                                        {{ $product->pivot->store->name ?? 'No store specified' }}
                                    </div>
                                </td>

                                <!-- Category -->
                                <td class="px-5 py-3.5 text-sm text-slate-600 dark:text-slate-300">
                                    {{ $product->category->name ?? 'Uncategorized' }}
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-3.5 text-right space-x-3">
                                    <button wire:click="untrack({{ $product->id }})"
                                        wire:confirm="Remove this product from your tracked list?"
                                        class="text-xs font-bold text-[#E2601F] hover:text-opacity-80 transition-colors focus:outline-none">
                                        Untrack
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5"
                                    class="px-5 py-12 text-center text-sm font-medium text-slate-500 dark:text-[#A1A1AA]">
                                    You aren't tracking any products yet. Click <span
                                        class="text-[#0E6B4C] dark:text-[#F2B705] font-bold">Scan / Add Product</span>
                                    to get started.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Links -->
        <div class="mt-4">
            {{ $trackedProducts->links() }}
        </div>

    </div>
</div>
