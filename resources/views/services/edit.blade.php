<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Layanan: ') }} {{ $service->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900 dark:text-gray-100">
                     <form method="post" action="{{ route('services.update', $service) }}" class="space-y-6">
                        @csrf
                        @method('PATCH')
                        <div>
                            <x-input-label for="name" :value="__('Nama Layanan')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full bg-gray-700" :value="$service->name" disabled />
                        </div>
                        <div>
                            <x-input-label for="rate_idr" :value="__('Harga (IDR)')" />
                            <x-text-input id="rate_idr" name="rate_idr" type="number" class="mt-1 block w-full" :value="old('rate_idr', $service->rate_idr)" required />
                        </div>
                        <div>
                            <x-input-label for="rate_usd" :value="__('Harga (USD)')" />
                            <x-text-input id="rate_usd" name="rate_usd" type="number" step="0.01" class="mt-1 block w-full" :value="old('rate_usd', $service->rate_usd)" required />
                        </div>
                         <div>
                            <x-input-label for="is_active" :value="__('Status')" />
                            <select name="is_active" id="is_active" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm">
                                <option value="1" @selected(old('is_active', $service->is_active))>Aktif</option>
                                <option value="0" @selected(!old('is_active', $service->is_active))>Nonaktif</option>
                            </select>
                        </div>
                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('services.index') }}" class="text-sm text-gray-400 hover:text-white">Batal</a>
                            <x-primary-button>{{ __('Simpan Perubahan') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
