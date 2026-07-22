<?php
use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

new class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        Auth::login($user);
        return $this->redirect('/dashboard', navigate: true);
    }
};
?>

<div
    class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-[#101012] transition-colors duration-200 p-4 relative">

    <button type="button" @click="darkMode = !darkMode"
        class="absolute top-6 right-6 p-2.5 rounded-full bg-white dark:bg-[#1C1C1F] border border-slate-200 dark:border-[#2E2E32] text-slate-500 dark:text-[#A1A1AA] hover:text-slate-900 dark:hover:text-white transition shadow-sm"
        aria-label="Toggle Dark Mode">
        <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
            x-cloak>
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
        </svg>
        <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
            viewBox="0 0 24 24" x-cloak>
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
        </svg>
    </button>

    <div
        class="w-full max-w-sm bg-white/95 dark:bg-[#1C1C1F]/95 backdrop-blur-md rounded-xl shadow-2xl p-6 sm:p-8 border border-white/30 dark:border-[#2E2E32]">

        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-[#F4F4F5] tracking-tight">Create Account</h1>
            <p class="text-xs text-slate-500 dark:text-[#A1A1AA] mt-1">Join to start tracking your lists</p>
        </div>

        <form wire:submit="register" class="space-y-4">

            <div class="space-y-1">
                <label for="name" class="block text-xs font-semibold text-slate-700 dark:text-[#A1A1AA]">Full
                    Name</label>
                <input wire:model="name" id="name" type="text" required
                    class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-[#101012] border border-slate-200 dark:border-[#2E2E32] rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow text-slate-800 dark:text-[#F4F4F5] placeholder-slate-400 dark:placeholder-[#A1A1AA] shadow-sm"
                    placeholder="Juan Dela Cruz">
                @error('name')
                    <span class="text-red-500 text-[10px] font-medium">{{ $message }}</span>
                @enderror
            </div>

            <div class="space-y-1">
                <label for="email" class="block text-xs font-semibold text-slate-700 dark:text-[#A1A1AA]">Email
                    Address</label>
                <input wire:model="email" id="email" type="email" required
                    class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-[#101012] border border-slate-200 dark:border-[#2E2E32] rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow text-slate-800 dark:text-[#F4F4F5] placeholder-slate-400 dark:placeholder-[#A1A1AA] shadow-sm"
                    placeholder="name@example.com">
                @error('email')
                    <span class="text-red-500 text-[10px] font-medium">{{ $message }}</span>
                @enderror
            </div>

            <div class="space-y-1">
                <label for="password"
                    class="block text-xs font-semibold text-slate-700 dark:text-[#A1A1AA]">Password</label>
                <input wire:model="password" id="password" type="password" required
                    class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-[#101012] border border-slate-200 dark:border-[#2E2E32] rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow text-slate-800 dark:text-[#F4F4F5] placeholder-slate-400 dark:placeholder-[#A1A1AA] shadow-sm"
                    placeholder="Minimum 8 characters">
                @error('password')
                    <span class="text-red-500 text-[10px] font-medium">{{ $message }}</span>
                @enderror
            </div>

            <div class="space-y-1">
                <label for="password_confirmation"
                    class="block text-xs font-semibold text-slate-700 dark:text-[#A1A1AA]">Confirm Password</label>
                <input wire:model="password_confirmation" id="password_confirmation" type="password" required
                    class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-[#101012] border border-slate-200 dark:border-[#2E2E32] rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow text-slate-800 dark:text-[#F4F4F5] placeholder-slate-400 dark:placeholder-[#A1A1AA] shadow-sm"
                    placeholder="Re-type your password">
            </div>

            <div class="pt-3">
                <button type="submit"
                    class="w-full py-2.5 px-4 bg-slate-900 dark:bg-[#F4F4F5] hover:bg-slate-800 dark:hover:bg-white text-white dark:text-slate-900 text-sm font-semibold rounded-md shadow-md transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 dark:focus:ring-offset-[#1C1C1F]">
                    Sign Up
                </button>
            </div>

        </form>

        <div class="mt-6 text-center">
            <p class="text-xs text-slate-600 dark:text-[#A1A1AA]">
                Already have an account?
                <a href="{{ route('login') }}" wire:navigate
                    class="font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors ml-0.5">
                    Sign in
                </a>
            </p>
        </div>

    </div>
</div>
