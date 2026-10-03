<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-100 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between gap-4 p-6 bg-slate-900 border-2 border-indigo-800 rounded-2xl shadow-xl">
                <div class="min-w-0">
                    <h1 class="text-xl font-bold text-white tracking-tight">Settings</h1>
                </div>
                <a href="{{ route('dashboard') }}" class="h-10 px-4 shrink-0 rounded-xl bg-indigo-600 hover:bg-indigo-500 border-2 border-indigo-800 text-white text-sm font-semibold transition flex items-center justify-center shadow-md active:scale-95 ml-auto" aria-label="Back to dashboard">
                    Back
                </a>
            </div>

            <div class="p-4 sm:p-8 bg-[#0f172a] border border-slate-700 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-[#0f172a] border border-slate-700 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-[#0f172a] border border-slate-700 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>