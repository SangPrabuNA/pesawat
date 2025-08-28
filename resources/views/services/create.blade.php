<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Layanan Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900 dark:text-gray-100">
                    <form method="post" action="{{ route('services.store') }}" class="space-y-6">
                        @csrf
                        <div>
                            <x-input-label for="name" :value="__('Nama Layanan (e.g., APP, TWR)')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                        </div>
                        <div>
                            <x-input-label for="rate_idr" :value="__('Harga (IDR)')" />
                            <x-text-input id="rate_idr" name="rate_idr" type="number" class="mt-1 block w-full" :value="old('rate_idr')" required />
                        </div>
                        <div>
                            <x-input-label for="rate_usd" :value="__('Harga (USD)')" />
                            <x-text-input id="rate_usd" name="rate_usd" type="number" step="0.01" class="mt-1 block w-full" :value="old('rate_usd')" required />
                        </div>
                        <div class="flex items-center justify-end gap-4">
                             <a href="{{ route('services.index') }}" class="text-sm text-gray-400 hover:text-white">Batal</a>
                            <x-primary-button>{{ __('Simpan') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
