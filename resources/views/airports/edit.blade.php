<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Bandara: ') }} {{ $airport->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('airports.update', $airport) }}">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="iata_code" :value="__('Kode IATA (Tidak dapat diubah)')" />
                                <x-text-input id="iata_code" class="block mt-1 w-full bg-gray-200 dark:bg-gray-700" type="text" name="iata_code" :value="$airport->iata_code" disabled />
                            </div>
                             <div>
                                <x-input-label for="icao_code" :value="__('Kode ICAO (Tidak dapat diubah)')" />
                                <x-text-input id="icao_code" class="block mt-1 w-full bg-gray-200 dark:bg-gray-700" type="text" name="icao_code" :value="$airport->icao_code" disabled />
                            </div>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="name" :value="__('Nama Bandara')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $airport->name)" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <div>
                                <x-input-label for="op_start" :value="__('Jam Buka Operasional')" />
                                <x-text-input id="op_start" class="block mt-1 w-full" type="time" name="op_start" :value="old('op_start', $airport->op_start)" required />
                                <x-input-error :messages="$errors->get('op_start')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="op_end" :value="__('Jam Tutup Operasional')" />
                                <x-text-input id="op_end" class="block mt-1 w-full" type="time" name="op_end" :value="old('op_end', $airport->op_end)" required />
                                <x-input-error :messages="$errors->get('op_end')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('airports.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                Batal
                            </a>
                            <x-primary-button class="ms-4">
                                {{ __('Simpan Perubahan') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
