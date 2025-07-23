<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Buat Invoice Advance/Extend Baru') }}
            </h2>

            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('invoices.store') }}">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="mt-4">
                                    <x-input-label for="airline" :value="__('Airline')" />
                                    <x-text-input id="airline" class="block mt-1 w-full" type="text" name="airline" :value="old('airline')" required />
                                    <x-input-error :messages="$errors->get('airline')" class="mt-2" />
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="flight_number" :value="__('Nomor Penerbangan')" />
                                    <x-text-input id="flight_number" class="block mt-1 w-full" type="text" name="flight_number" :value="old('flight_number')" required />
                                    <x-input-error :messages="$errors->get('flight_number')" class="mt-2" />
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="registration" :value="__('Registrasi Pesawat')" />
                                    <x-text-input id="registration" class="block mt-1 w-full" type="text" name="registration" :value="old('registration')" required />
                                    <x-input-error :messages="$errors->get('registration')" class="mt-2" />
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="aircraft_type" :value="__('Tipe Pesawat')" />
                                    <x-text-input id="aircraft_type" class="block mt-1 w-full" type="text" name="aircraft_type" :value="old('aircraft_type')" required />
                                    <x-input-error :messages="$errors->get('aircraft_type')" class="mt-2" />
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="route" :value="__('Rute')" />
                                    <select name="route" id="route" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                        <option value="LBJ">LBJ</option>
                                        <option value="WGP">WGP</option>
                                        <option value="TMC">TMC</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('route')" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <div class="mt-4">
                                    <x-input-label for="service_type" :value="__('Jenis Layanan Tertinggi')" />
                                    <select name="service_type" id="service_type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="APP">APP</option>
                                        <option value="TWR">TWR</option>
                                        <option value="AFIS">AFIS</option>
                                    </select>
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="flight_type" :value="__('Jenis Penerbangan')" />
                                    <select name="flight_type" id="flight_type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="Domestik">Domestik</option>
                                        <option value="Internasional">Internasional</option>
                                    </select>
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="charge_type" :value="__('Jenis Biaya')" />
                                    <select name="charge_type" id="charge_type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="Advance">Advance</option>
                                        <option value="Extend">Extend</option>
                                    </select>
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="actual_time" :value="__('Waktu Aktual (ATA/ATD)')" />
                                    <x-text-input id="actual_time" class="block mt-1 w-full" type="datetime-local" name="actual_time" required />
                                    <x-input-error :messages="$errors->get('actual_time')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Hitung & Simpan Invoice') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
