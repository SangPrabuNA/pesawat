<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Menunggu Penugasan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 text-center">
                    <h3 class="text-lg font-medium">{{ __('Akun Anda Aktif') }}</h3>
                    <p class="mt-2 text-sm text-gray-400">
                        {{ __('Saat ini Anda belum ditugaskan ke bandara manapun. Mohon hubungi Master Admin untuk mendapatkan akses penuh ke sistem.') }}
                    </p>

                    <!-- Tombol Logout -->
                    <div class="mt-6">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-danger-button :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-danger-button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
