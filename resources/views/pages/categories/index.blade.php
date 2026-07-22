<?php
// resources/views/pages/categories/index.blade.php
// Route: Route::livewire('/admin/categories', 'pages::categories.index')->name('categories.index');

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\WithPagination;
use Livewire\Component;

new class extends Component {
    use WithPagination;

    // ---------- list state ----------
    public string $search = '';

    // ---------- form state (shared by create + edit) ----------
    public ?int $editingId = null;
    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public bool $slugLocked = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedName(string $value): void
    {
        if (!$this->slugLocked) {
            $this->slug = Str::slug($value);
        }
    }

    public function updatedSlug(): void
    {
        $this->slugLocked = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => 'required|min:2|max:50',
            'slug' => ['required', 'max:60', Rule::unique('categories', 'slug')->ignore($this->editingId)],
            'description' => 'nullable|string|max:1000',
        ]);

        Category::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'description' => $validated['description'] ?: null,
            ],
        );

        $this->resetForm();
        $this->dispatch('category-saved');
    }

    public function delete(int $id): void
    {
        $category = Category::withCount('products')->findOrFail($id);

        if ($category->products_count > 0) {
            $this->addError('delete', "Can't delete \"{$category->name}\" — it still has {$category->products_count} product(s).");
            return;
        }

        $category->delete();
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'slug', 'description', 'slugLocked']);
        $this->resetValidation();
    }

    public function with(): array
    {
        return [
            'categories' => Category::withCount('products')
                // Changed 'like' to 'ilike' for PostgreSQL case-insensitive search
                ->when($this->search, fn($q) => $q->where('name', 'ilike', "%{$this->search}%"))
                ->orderBy('name')
                ->paginate(10),
        ];
    }
};
?>

<div class="min-h-screen bg-slate-50 dark:bg-[#101012] transition-colors duration-200" x-data="{ open: false }">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-2">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Categories</h2>
                <p class="text-sm font-medium text-slate-500 dark:text-[#A1A1AA] mt-1">Manage product classifications for
                    the app.</p>
            </div>

            <button
                x-on:click="
                    open = true;
                    $wire.editingId = null;
                    $wire.name = '';
                    $wire.slug = '';
                    $wire.description = '';
                    $wire.slugLocked = false;
                "
                class="inline-flex items-center justify-center gap-2 bg-[#0E6B4C] hover:bg-opacity-90 text-white text-sm font-bold px-4 py-2.5 rounded-lg shadow-sm transition active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0E6B4C] dark:focus:ring-offset-[#101012]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                </svg>
                Add Category
            </button>
        </div>

        <div class="mb-6 mt-6 max-w-sm">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400 dark:text-[#A1A1AA]" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="Search categories..."
                    class="w-full pl-10 pr-4 py-2 bg-white dark:bg-[#1C1C1F] border border-slate-200 dark:border-[#2E2E32] text-slate-900 dark:text-white rounded-lg text-sm shadow-sm placeholder-slate-400 dark:placeholder-[#A1A1AA] focus:outline-none focus:ring-2 focus:ring-[#F2B705] focus:border-transparent transition-colors" />
            </div>
        </div>

        @error('delete')
            <div
                class="bg-[#E2601F]/10 border border-[#E2601F]/20 text-[#E2601F] text-sm font-medium rounded-lg px-4 py-3 mb-6 flex items-start gap-3">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>{{ $message }}</span>
            </div>
        @enderror

        <div class="bg-white dark:bg-[#1C1C1F] border border-slate-200 dark:border-[#2E2E32] rounded-xl shadow-sm overflow-hidden transition-colors duration-200"
            wire:loading.class="opacity-60 transition-opacity duration-200">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead
                        class="bg-slate-50/80 dark:bg-[#101012]/80 border-b border-slate-200 dark:border-[#2E2E32] text-xs font-bold text-slate-500 dark:text-[#A1A1AA] uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-4">Name</th>
                            <th class="px-5 py-4">Slug</th>
                            <th class="px-5 py-4 text-center">Products</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-[#2E2E32]">
                        @forelse ($categories as $category)
                            <tr wire:key="category-{{ $category->id }}"
                                class="hover:bg-slate-50 dark:hover:bg-[#2E2E32]/40 transition-colors duration-150">
                                <td class="px-5 py-3.5">
                                    <span
                                        class="font-bold text-slate-900 dark:text-[#F4F4F5]">{{ $category->name }}</span>
                                </td>
                                <td class="px-5 py-3.5 font-mono text-xs text-slate-500 dark:text-[#A1A1AA]">
                                    {{ $category->slug }}
                                </td>
                                <td
                                    class="px-5 py-3.5 text-center font-mono text-xs text-slate-600 dark:text-[#F4F4F5]">
                                    {{ $category->products_count }}
                                </td>
                                <td class="px-5 py-3.5 text-right space-x-3">
                                    <button
                                        x-on:click="
                                            open = true;
                                            $wire.editingId = {{ $category->id }};
                                            $wire.name = @js($category->name);
                                            $wire.slug = @js($category->slug);
                                            $wire.description = @js($category->description ?? '');
                                            $wire.slugLocked = true;
                                        "
                                        class="text-xs font-bold text-[#0E6B4C] hover:text-opacity-80 transition-colors focus:outline-none">
                                        Edit
                                    </button>
                                    <button wire:click="delete({{ $category->id }})"
                                        wire:confirm="Delete this category? This can't be undone."
                                        class="text-xs font-bold text-[#E2601F] hover:text-opacity-80 transition-colors focus:outline-none">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4"
                                    class="px-5 py-12 text-center text-sm font-medium text-slate-500 dark:text-[#A1A1AA]">
                                    No categories found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $categories->links() }}
        </div>

        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">

            <div x-show="open" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="w-full max-w-md bg-white dark:bg-[#1C1C1F] border border-slate-200 dark:border-[#2E2E32] rounded-2xl shadow-2xl overflow-hidden transition-colors duration-200">

                <div
                    class="px-6 py-4 border-b border-slate-200 dark:border-[#2E2E32] flex items-center justify-between bg-slate-50/50 dark:bg-[#101012]/50">
                    <h3 class="font-bold text-lg text-slate-900 dark:text-white tracking-tight">
                        {{ $editingId ? 'Edit Category' : 'Add Category' }}
                    </h3>
                    <button x-on:click="open = false; $wire.cancel()"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors focus:outline-none"
                        aria-label="Close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="save" class="p-6 space-y-5">

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-500 dark:text-[#A1A1AA] uppercase tracking-wide mb-1.5">
                            Category Name
                        </label>
                        <input type="text" wire:model.live.debounce.300ms="name" placeholder="e.g. Frozen Goods"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#101012] border border-slate-200 dark:border-[#2E2E32] text-slate-900 dark:text-white rounded-lg text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-[#F2B705] focus:border-transparent transition-colors" />
                        @error('name')
                            <p class="text-[#E2601F] text-xs font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-500 dark:text-[#A1A1AA] uppercase tracking-wide mb-1.5">
                            Slug
                        </label>
                        <input type="text" wire:model.live.debounce.300ms="slug"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#101012] border border-slate-200 dark:border-[#2E2E32] text-slate-900 dark:text-white font-mono rounded-lg text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-[#F2B705] focus:border-transparent transition-colors" />
                        <p class="text-[11px] font-medium text-slate-500 dark:text-[#A1A1AA] mt-1.5">
                            Auto-generated from the name. You can edit this manually.
                        </p>
                        @error('slug')
                            <p class="text-[#E2601F] text-xs font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-500 dark:text-[#A1A1AA] uppercase tracking-wide mb-1.5">
                            Description <span
                                class="text-slate-400 dark:text-slate-500 font-medium normal-case ml-1">(Optional)</span>
                        </label>
                        <textarea wire:model.live.debounce.300ms="description" rows="3" placeholder="Briefly describe this category..."
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#101012] border border-slate-200 dark:border-[#2E2E32] text-slate-900 dark:text-white rounded-lg text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-[#F2B705] focus:border-transparent transition-colors resize-none"></textarea>
                        @error('description')
                            <p class="text-[#E2601F] text-xs font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-[#2E2E32]">
                        <button type="button" x-on:click="open = false; $wire.cancel()"
                            class="px-4 py-2.5 border border-slate-200 dark:border-[#2E2E32] text-slate-600 dark:text-slate-300 text-sm font-bold rounded-lg hover:bg-slate-50 dark:hover:bg-[#2E2E32] transition-colors focus:outline-none focus:ring-2 focus:ring-slate-200 dark:focus:ring-[#2E2E32]">
                            Cancel
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="save"
                            x-on:click="open = false"
                            class="px-5 py-2.5 bg-[#0E6B4C] hover:bg-opacity-90 text-white text-sm font-bold rounded-lg shadow-sm transition disabled:opacity-50 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0E6B4C] dark:focus:ring-offset-[#1C1C1F]">
                            <span wire:loading.remove wire:target="save">Save Category</span>
                            <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
