<?php
// resources/views/livewire/products/create.blade.php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Product;
use App\Models\Category;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;

new #[Title('Scan / Add Product')] class extends Component {
    use WithFileUploads;

    public string $barcode = '';
    public string $pendingBarcode = '';
    public bool $showRegisterConfirm = false;
    public bool $manualEntry = false;

    // --- Global Product Tracking ---
    public bool $isExistingGlobal = false;
    public ?Product $foundProduct = null;

    // --- Global Product Fields (Used if New) ---
    public string $name = '';
    public string $description = '';
    public string $size = '';
    public ?int $category_id = null;
    public $imageUpload = null;
    public $barcodeImageUpload = null; // New requirement

    // --- User-Specific Pivot Fields (Always Used) ---
    public $price = '';
    public ?int $store_id = null;
    public string $custom_name = '';
    public string $purchase_unit = 'piece';
    public int $pieces_per_bulk = 1;

    public string $statusMessage = '';
    public string $statusType = '';

    public function with(): array
    {
        return [
            'categories' => Category::orderBy('name')->get(),
            'stores' => Store::orderBy('name')->get(),
        ];
    }

    public function scanBarcode(): void
    {
        $barcode = trim($this->barcode);
        if (empty($barcode)) {
            return;
        }
        $this->lookupBarcode($barcode);
    }

    #[On('barcode-scanned')]
    public function handleCameraScan($barcode): void
    {
        $this->barcode = $barcode;
        $this->lookupBarcode($barcode);
    }

    private function lookupBarcode(string $barcode): void
    {
        $product = Product::where('barcode', $barcode)->first();

        if ($product) {
            $this->foundProduct = $product;
            $this->isExistingGlobal = true;
            $this->showRegisterConfirm = false;
            $this->manualEntry = false;
            $this->pendingBarcode = '';

            $this->statusMessage = "Found \"{$product->name}\" in the global catalog. Enter your specific purchase details below.";
            $this->statusType = 'success';
        } else {
            $this->foundProduct = null;
            $this->isExistingGlobal = false;
            $this->pendingBarcode = $barcode;
            $this->showRegisterConfirm = true;
            $this->statusMessage = '';
            $this->statusType = '';
        }

        $this->dispatch('stop-camera');
    }

    public function confirmRegister(): void
    {
        $this->barcode = $this->pendingBarcode;
        $this->pendingBarcode = '';
        $this->showRegisterConfirm = false;
        $this->manualEntry = false;

        $this->statusMessage = 'New barcode detected. Please provide photos to verify this product.';
        $this->statusType = 'info';
        $this->dispatch('stop-camera');
    }

    public function cancelRegister(): void
    {
        $this->newScan();
    }

    public function startManualEntry(): void
    {
        $this->newScan();
        $this->manualEntry = true;
        $this->statusMessage = 'Manual Entry: No barcode required. Provide a product photo for verification.';
        $this->statusType = 'info';
    }

    public function save(): void
    {
        if (!Auth::check()) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        // 1. Pivot Validation (Always Required)
        $rules = [
            'price' => 'required|numeric|min:0',
            'store_id' => 'nullable|exists:stores,id',
            'custom_name' => 'nullable|string|max:255',
            'purchase_unit' => 'required|string|max:50',
            'pieces_per_bulk' => 'required|integer|min:1',
        ];

        // 2. Global Validation (Images required if new)
        if (!$this->isExistingGlobal) {
            $rules['name'] = 'required|string|max:255';
            $rules['description'] = 'nullable|string';
            $rules['size'] = 'nullable|string|max:50';
            $rules['category_id'] = 'required|exists:categories,id';
            $rules['imageUpload'] = 'required|image|max:2048'; // Product Image always required for new items

            if (!$this->manualEntry) {
                // If it has a barcode, the barcode image is required to verify it
                $rules['barcode'] = 'required|string|max:255|unique:products,barcode';
                $rules['barcodeImageUpload'] = 'required|image|max:2048';
            } else {
                // Tingi-tingi has no barcode
                $rules['barcode'] = 'nullable|string|max:255|unique:products,barcode';
            }
        }

        $validated = $this->validate($rules);
        $this->resetErrorBag('general');

        DB::beginTransaction();
        try {
            // Step 1: Resolve the Product
            if ($this->isExistingGlobal) {
                $product = $this->foundProduct;
            } else {
                $productData = [
                    'barcode' => !$this->manualEntry ? $this->barcode : null,
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'size' => $validated['size'] ?? null,
                    'category_id' => $validated['category_id'],
                    'created_by' => Auth::id(),
                    'status' => Auth::user()->is_admin ? 'approved' : 'pending', // Explicitly queue it for Admin Approval
                ];

                $productData['image_path'] = $this->imageUpload->store('products', 'public');

                if (!$this->manualEntry && $this->barcodeImageUpload) {
                    $productData['barcode_image_path'] = $this->barcodeImageUpload->store('barcodes', 'public');
                }

                $product = Product::create($productData);
            }

            // Step 2: Prevent duplicate pivot entries
            $pivotExists = DB::table('user_products')
                ->where([
                    'user_id' => Auth::id(),
                    'product_id' => $product->id,
                    'store_id' => $validated['store_id'] ?? null,
                    'purchase_unit' => $validated['purchase_unit'],
                    'pieces_per_bulk' => $validated['pieces_per_bulk'],
                ])
                ->exists();

            if ($pivotExists) {
                DB::rollBack();
                $this->addError('general', 'You are already tracking this exact product unit at this specific store. Edit your existing tracker instead.');
                return;
            }

            // Step 3: Attach to user_product pivot
            Auth::user()
                ->trackedProducts()
                ->attach($product->id, [
                    'store_id' => $validated['store_id'] ?? null,
                    'custom_name' => $validated['custom_name'] ?? null,
                    'purchase_unit' => $validated['purchase_unit'],
                    'pieces_per_bulk' => $validated['pieces_per_bulk'],
                    'price' => $validated['price'],
                    'is_tracked' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::commit();

            $successName = $validated['custom_name'] ?? $product->name;
            $wasNew = !$this->isExistingGlobal;

            $this->newScan();

            if ($wasNew) {
                $this->statusMessage = "Successfully submitted \"{$successName}\" for admin review! You can track it locally right now.";
            } else {
                $this->statusMessage = "Successfully tracked \"{$successName}\".";
            }
            $this->statusType = 'success';
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function newScan(): void
    {
        $this->barcode = '';
        $this->pendingBarcode = '';
        $this->showRegisterConfirm = false;
        $this->manualEntry = false;
        $this->isExistingGlobal = false;
        $this->foundProduct = null;

        $this->name = '';
        $this->description = '';
        $this->size = '';
        $this->category_id = null;
        $this->imageUpload = null;
        $this->barcodeImageUpload = null;

        $this->price = '';
        $this->store_id = null;
        $this->custom_name = '';
        $this->purchase_unit = 'piece';
        $this->pieces_per_bulk = 1;

        $this->statusMessage = '';
        $this->statusType = '';
        $this->resetErrorBag();

        $this->dispatch('stop-camera');
    }
};
?>

<div class="min-h-screen bg-slate-50 dark:bg-[#101012] transition-colors duration-200 py-8">
    <div class="max-w-3xl px-4 sm:px-6 mx-auto w-full space-y-6">

        <div class="bg-white dark:bg-[#1C1C1F] border border-slate-200 dark:border-[#2E2E32] rounded-xl shadow-sm p-6 sm:p-8 transition-colors duration-200"
            x-data="barcodeScanner()" x-init="initScanner()">

            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Scan & Track</h2>
                    <p class="text-sm font-medium text-slate-500 dark:text-[#A1A1AA] mt-1">Look up a barcode or register
                        a new product.</p>
                </div>
                <button @click="toggleCamera()" type="button"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-bold rounded-lg shadow-sm transition active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-[#101012]"
                    :class="isScanning ?
                        'bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 dark:bg-red-900/20 dark:border-red-900/50 dark:text-red-400 focus:ring-red-500' :
                        'bg-[#0E6B4C] hover:bg-opacity-90 text-white focus:ring-[#0E6B4C]'">
                    <span x-text="isScanning ? 'Stop Camera' : 'Start Camera'"></span>
                </button>
            </div>

            <!-- Camera Viewport -->
            <div x-show="isScanning" style="display: none;"
                class="relative overflow-hidden border-2 border-dashed border-slate-300 dark:border-[#2E2E32] rounded-xl bg-slate-50 dark:bg-[#101012] w-full min-h-[250px] mb-6"
                wire:ignore>
                <div id="reader" class="w-full h-full [&_video]:object-cover [&_video]:rounded-lg [&_video]:w-full">
                </div>
            </div>

            <div x-show="cameraError" style="display: none;"
                class="p-4 mb-6 text-sm text-[#E2601F] bg-[#E2601F]/10 border border-[#E2601F]/20 rounded-lg font-medium">
                <span class="font-bold block mb-1">Camera Error: <span x-text="cameraError"></span></span>
                Ensure you are using an HTTPS connection and have granted permissions.
            </div>

            <!-- Manual Barcode Input -->
            <form wire:submit.prevent="scanBarcode" class="mb-6">
                <label for="barcode"
                    class="block text-xs font-bold text-slate-500 dark:text-[#A1A1AA] uppercase tracking-wide mb-2">Barcode</label>
                <input wire:model="barcode" type="text" id="barcode" autocomplete="off" autofocus
                    class="w-full px-4 py-3 bg-slate-50 dark:bg-[#101012] border border-slate-200 dark:border-[#2E2E32] text-slate-900 dark:text-white rounded-lg text-sm shadow-sm placeholder-slate-400 dark:placeholder-[#A1A1AA] focus:outline-none focus:ring-2 focus:ring-[#0E6B4C] focus:border-transparent transition-colors"
                    placeholder="Scan or type a barcode, then press Enter...">
                @error('barcode')
                    <p class="mt-1.5 text-xs font-semibold text-[#E2601F]">{{ $message }}</p>
                @enderror
            </form>

            <div class="text-center mb-6">
                <button type="button" wire:click="startManualEntry"
                    class="text-sm font-semibold text-slate-500 dark:text-[#A1A1AA] hover:text-[#0E6B4C] dark:hover:text-[#F2B705] transition-colors underline decoration-dotted underline-offset-4">
                    No barcode? Add a tingi-tingi / unlabelled product instead
                </button>
            </div>

            <!-- Status Messages -->
            @if ($statusMessage)
                <div
                    class="rounded-lg p-4 mb-6 border font-medium text-sm {{ $statusType === 'success' ? 'bg-[#0E6B4C]/10 border-[#0E6B4C]/20 text-[#0E6B4C] dark:text-[#F2B705]' : 'bg-blue-50 border-blue-100 text-blue-800 dark:bg-blue-900/20 dark:border-blue-900/30 dark:text-blue-400' }}">
                    {{ $statusMessage }}
                </div>
            @endif

            @error('general')
                <div
                    class="rounded-lg p-4 mb-6 border font-medium text-sm bg-[#E2601F]/10 border-[#E2601F]/20 text-[#E2601F]">
                    {{ $message }}
                </div>
            @enderror

            <!-- Confirmation modal for Unrecognized Barcode -->
            @if ($showRegisterConfirm)
                <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                    wire:key="register-confirm-modal">
                    <div
                        class="w-full max-w-sm bg-white dark:bg-[#1C1C1F] border border-slate-200 dark:border-[#2E2E32] rounded-2xl shadow-2xl p-6">
                        <h3 class="font-bold text-lg text-slate-900 dark:text-white mb-2">Register new product?</h3>
                        <p class="text-sm text-slate-500 dark:text-[#A1A1AA] mb-6">
                            The barcode <span
                                class="font-mono font-bold text-slate-900 dark:text-white">{{ $pendingBarcode }}</span>
                            isn't in the global catalog yet. Do you want to submit it for verification?
                        </p>
                        <div class="flex gap-3">
                            <button type="button" wire:click="cancelRegister"
                                class="flex-1 px-4 py-2.5 border border-slate-200 dark:border-[#2E2E32] text-slate-600 dark:text-slate-300 text-sm font-bold rounded-lg hover:bg-slate-50 dark:hover:bg-[#2E2E32] transition-colors focus:outline-none focus:ring-2 focus:ring-slate-200 dark:focus:ring-[#2E2E32]">
                                Cancel
                            </button>
                            <button type="button" wire:click="confirmRegister"
                                class="flex-1 px-4 py-2.5 bg-[#0E6B4C] hover:bg-opacity-90 text-white text-sm font-bold rounded-lg shadow-sm transition active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0E6B4C] dark:focus:ring-offset-[#1C1C1F]">
                                Yes, submit it
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Main Entry Form -->
            @if (($barcode || $manualEntry) && !$showRegisterConfirm)
                <form wire:submit.prevent="save"
                    class="pt-6 mt-2 border-t border-slate-200 dark:border-[#2E2E32] space-y-8"
                    enctype="multipart/form-data">

                    <!-- Section 1: Global Product Details (Only if completely new) -->
                    @if (!$isExistingGlobal)
                        <div class="space-y-5">
                            <h3 class="text-sm font-bold text-[#0E6B4C] dark:text-[#F2B705] uppercase tracking-wide">1.
                                Submit for Global Catalog</h3>
                            <p class="text-xs text-slate-500 dark:text-[#A1A1AA] -mt-3">This information will be sent to
                                admins for approval.</p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-500 dark:text-[#A1A1AA] uppercase tracking-wide mb-1.5">Product
                                        Name <span class="text-[#E2601F]">*</span></label>
                                    <input wire:model="name" type="text"
                                        class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#101012] border border-slate-200 dark:border-[#2E2E32] text-slate-900 dark:text-white rounded-lg text-sm shadow-sm focus:ring-[#0E6B4C] focus:border-transparent transition-colors">
                                    @error('name')
                                        <p class="mt-1.5 text-xs font-semibold text-[#E2601F]">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-500 dark:text-[#A1A1AA] uppercase tracking-wide mb-1.5">Size
                                        <span class="normal-case font-medium ml-1 text-slate-400">(e.g.,
                                            80ml)</span></label>
                                    <input wire:model="size" type="text"
                                        class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#101012] border border-slate-200 dark:border-[#2E2E32] text-slate-900 dark:text-white rounded-lg text-sm shadow-sm focus:ring-[#0E6B4C] focus:border-transparent transition-colors">
                                    @error('size')
                                        <p class="mt-1.5 text-xs font-semibold text-[#E2601F]">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 dark:text-[#A1A1AA] uppercase tracking-wide mb-1.5">Category
                                    <span class="text-[#E2601F]">*</span></label>
                                <select wire:model="category_id"
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#101012] border border-slate-200 dark:border-[#2E2E32] text-slate-900 dark:text-white rounded-lg text-sm shadow-sm focus:ring-[#0E6B4C] focus:border-transparent transition-colors">
                                    <option value="">-- Select Category --</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <p class="mt-1.5 text-xs font-semibold text-[#E2601F]">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Upload Verification Grid -->
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 gap-5 p-4 bg-slate-50 dark:bg-[#101012] border border-slate-200 dark:border-[#2E2E32] rounded-lg">

                                <!-- Product Image (Always Required) -->
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-500 dark:text-[#A1A1AA] uppercase tracking-wide mb-1.5">Product
                                        Image <span class="text-[#E2601F]">*</span></label>
                                    <input wire:model="imageUpload" type="file" accept="image/*"
                                        class="w-full text-sm text-slate-500 dark:text-[#A1A1AA] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#0E6B4C]/10 dark:file:bg-[#F2B705]/10 file:text-[#0E6B4C] dark:file:text-[#F2B705] hover:file:bg-[#0E6B4C]/20 transition">
                                    <p class="text-[10px] text-slate-400 mt-1">Clear photo of the front label.</p>
                                    <div wire:loading wire:target="imageUpload"
                                        class="mt-2 text-xs font-medium text-[#0E6B4C]">Uploading...</div>
                                    @if ($imageUpload)
                                        <img src="{{ $imageUpload->temporaryUrl() }}"
                                            class="mt-3 h-20 w-20 rounded-lg object-cover border border-slate-200 dark:border-[#2E2E32]">
                                    @endif
                                    @error('imageUpload')
                                        <p class="mt-1.5 text-xs font-semibold text-[#E2601F]">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Barcode Proof Image (Required unless manual/tingi) -->
                                @if (!$manualEntry)
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-slate-500 dark:text-[#A1A1AA] uppercase tracking-wide mb-1.5">Barcode
                                            Image <span class="text-[#E2601F]">*</span></label>
                                        <input wire:model="barcodeImageUpload" type="file" accept="image/*"
                                            class="w-full text-sm text-slate-500 dark:text-[#A1A1AA] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#0E6B4C]/10 dark:file:bg-[#F2B705]/10 file:text-[#0E6B4C] dark:file:text-[#F2B705] hover:file:bg-[#0E6B4C]/20 transition">
                                        <p class="text-[10px] text-slate-400 mt-1">Photo showing the barcode clearly.
                                        </p>
                                        <div wire:loading wire:target="barcodeImageUpload"
                                            class="mt-2 text-xs font-medium text-[#0E6B4C]">Uploading...</div>
                                        @if ($barcodeImageUpload)
                                            <img src="{{ $barcodeImageUpload->temporaryUrl() }}"
                                                class="mt-3 h-20 w-20 rounded-lg object-cover border border-slate-200 dark:border-[#2E2E32]">
                                        @endif
                                        @error('barcodeImageUpload')
                                            <p class="mt-1.5 text-xs font-semibold text-[#E2601F]">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 dark:text-[#A1A1AA] uppercase tracking-wide mb-1.5">Description
                                    <span class="normal-case font-medium ml-1 text-slate-400">(Optional)</span></label>
                                <textarea wire:model="description" rows="2"
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#101012] border border-slate-200 dark:border-[#2E2E32] text-slate-900 dark:text-white rounded-lg text-sm shadow-sm focus:ring-[#0E6B4C] focus:border-transparent transition-colors resize-none"></textarea>
                                @error('description')
                                    <p class="mt-1.5 text-xs font-semibold text-[#E2601F]">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endif

                    <!-- Section 2: Personal Tracking Details (Always required) -->
                    <div
                        class="space-y-5 {{ !$isExistingGlobal ? 'pt-6 border-t border-slate-200 dark:border-[#2E2E32]' : '' }}">
                        <h3 class="text-sm font-bold text-[#0E6B4C] dark:text-[#F2B705] uppercase tracking-wide">
                            {{ $isExistingGlobal ? 'Personal Tracking Details' : '2. Personal Tracking Details' }}
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 dark:text-[#A1A1AA] uppercase tracking-wide mb-1.5">Store
                                    / Source <span
                                        class="normal-case font-medium ml-1 text-slate-400">(Optional)</span></label>
                                <select wire:model="store_id"
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#101012] border border-slate-200 dark:border-[#2E2E32] text-slate-900 dark:text-white rounded-lg text-sm shadow-sm focus:ring-[#0E6B4C] focus:border-transparent transition-colors">
                                    <option value="">-- No specific store --</option>
                                    @foreach ($stores as $store)
                                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                                    @endforeach
                                </select>
                                @error('store_id')
                                    <p class="mt-1.5 text-xs font-semibold text-[#E2601F]">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 dark:text-[#A1A1AA] uppercase tracking-wide mb-1.5">Custom
                                    Display Name <span
                                        class="normal-case font-medium ml-1 text-slate-400">(Optional)</span></label>
                                <input wire:model="custom_name" type="text" placeholder="e.g., Coke Small"
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#101012] border border-slate-200 dark:border-[#2E2E32] text-slate-900 dark:text-white rounded-lg text-sm shadow-sm focus:ring-[#0E6B4C] focus:border-transparent transition-colors">
                                <p class="text-[10px] text-slate-400 mt-1">Leave blank to use the global product name.
                                </p>
                                @error('custom_name')
                                    <p class="mt-1.5 text-xs font-semibold text-[#E2601F]">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-5">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 dark:text-[#A1A1AA] uppercase tracking-wide mb-1.5">Packaging</label>
                                <select wire:model="purchase_unit"
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#101012] border border-slate-200 dark:border-[#2E2E32] text-slate-900 dark:text-white rounded-lg text-sm shadow-sm focus:ring-[#0E6B4C] focus:border-transparent transition-colors">
                                    <option value="piece">Piece</option>
                                    <option value="pack">Pack</option>
                                    <option value="case">Case</option>
                                    <option value="box">Box</option>
                                    <option value="rim">Rim</option>
                                </select>
                                @error('purchase_unit')
                                    <p class="mt-1.5 text-xs font-semibold text-[#E2601F]">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 dark:text-[#A1A1AA] uppercase tracking-wide mb-1.5">Qty
                                    per Pack</label>
                                <input wire:model="pieces_per_bulk" type="number" min="1"
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#101012] border border-slate-200 dark:border-[#2E2E32] text-slate-900 dark:text-white rounded-lg text-sm shadow-sm focus:ring-[#0E6B4C] focus:border-transparent transition-colors">
                                @error('pieces_per_bulk')
                                    <p class="mt-1.5 text-xs font-semibold text-[#E2601F]">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-500 dark:text-[#A1A1AA] uppercase tracking-wide mb-1.5">Total
                                    Price (₱)</label>
                                <input wire:model="price" type="number" step="0.01" min="0"
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#101012] border border-slate-200 dark:border-[#2E2E32] text-slate-900 dark:text-white rounded-lg text-sm shadow-sm focus:ring-[#0E6B4C] focus:border-transparent transition-colors">
                                @error('price')
                                    <p class="mt-1.5 text-xs font-semibold text-[#E2601F]">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-3 pt-4 border-t border-slate-200 dark:border-[#2E2E32]">
                        <button type="submit" wire:loading.attr="disabled"
                            class="flex-1 py-3 px-4 bg-[#0E6B4C] hover:bg-opacity-90 text-white text-sm font-bold rounded-lg shadow-sm transition active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0E6B4C] dark:focus:ring-offset-[#1C1C1F]">
                            <span
                                wire:loading.remove>{{ $isExistingGlobal ? 'Track Product' : 'Submit for Review & Track' }}</span>
                            <span wire:loading>Saving...</span>
                        </button>
                        <button type="button" wire:click="newScan"
                            class="px-5 py-3 border border-slate-200 dark:border-[#2E2E32] text-slate-600 dark:text-slate-300 text-sm font-bold rounded-lg hover:bg-slate-50 dark:hover:bg-[#2E2E32] transition-colors focus:outline-none focus:ring-2 focus:ring-slate-200 dark:focus:ring-[#2E2E32]">
                            Cancel
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

@script
    <script>
        Alpine.data('barcodeScanner', () => ({
            isScanning: false,
            cameraError: null,
            scanner: null,
            libraryReady: null,

            loadScannerLibrary() {
                if (window.Html5Qrcode) return Promise.resolve();
                let existing = document.querySelector('script[data-html5-qrcode]');
                if (existing) {
                    return new Promise((resolve, reject) => {
                        existing.addEventListener('load', () => resolve());
                        existing.addEventListener('error', reject);
                    });
                }
                return new Promise((resolve, reject) => {
                    const script = document.createElement('script');
                    script.src = 'https://unpkg.com/html5-qrcode';
                    script.dataset.html5Qrcode = 'true';
                    script.onload = () => resolve();
                    script.onerror = () => reject(new Error('Failed to load html5-qrcode library'));
                    document.head.appendChild(script);
                });
            },

            initScanner() {
                this.$wire.on('stop-camera', () => this.stopCamera());
                this.libraryReady = this.loadScannerLibrary().then(() => {
                    this.scanner = new Html5Qrcode("reader");
                }).catch(err => {
                    this.cameraError = err.message || String(err);
                });
            },

            stopCamera() {
                if (!this.isScanning || !this.scanner) return;
                this.scanner.stop().then(() => {
                    this.scanner.clear();
                    this.isScanning = false;
                }).catch(err => console.error("Failed to stop scanner", err));
            },

            playBeep() {
                try {
                    const ctx = new(window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    osc.connect(ctx.destination);
                    osc.frequency.value = 800;
                    osc.start();
                    setTimeout(() => osc.stop(), 100);
                } catch (e) {}
            },

            toggleCamera() {
                this.cameraError = null;
                if (this.isScanning) return this.stopCamera();
                if (!this.libraryReady) {
                    this.cameraError = 'Scanner library is not ready yet.';
                    return;
                }
                this.libraryReady.then(() => {
                    if (!this.scanner) return;
                    this.isScanning = true;
                    this.$nextTick(() => {
                        this.scanner.start({
                                facingMode: "environment"
                            }, {
                                fps: 10,
                                qrbox: {
                                    width: 250,
                                    height: 250
                                },
                                formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE,
                                    Html5QrcodeSupportedFormats.EAN_13,
                                    Html5QrcodeSupportedFormats.CODE_128,
                                    Html5QrcodeSupportedFormats.UPC_A
                                ]
                            },
                            (decodedText) => {
                                this.playBeep();
                                this.scanner.pause();
                                $wire.dispatch('barcode-scanned', {
                                    barcode: decodedText
                                });
                                setTimeout(() => {
                                    if (this.isScanning) this.scanner.resume();
                                }, 1500);
                            },
                            () => {}
                        ).catch(err => {
                            this.cameraError = err.message || err;
                            this.isScanning = false;
                        });
                    });
                });
            }
        }));

        Livewire.hook('morph.updated', ({
            el
        }) => {
            const input = el.querySelector ? el.querySelector('#barcode') : null;
            if (input && !input.readOnly && document.activeElement !== input && document.activeElement === document
                .body) {
                input.focus();
            }
        });
    </script>
@endscript
