<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Buat Invoice Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Oops!</strong>
                            <span class="block sm:inline">Ada beberapa kesalahan pada input Anda.</span>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('invoices.store') }}" method="POST">
                        @csrf
                        {{-- Data Umum Penerbangan --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Kolom Kiri --}}
                            <div>
                                <div>
                                    <x-input-label for="airport_id" :value="__('Bandara Saat Ini')" />
                                    <select name="airport_id" id="airport_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">-- Pilih Bandara --</option>
                                        @foreach($airports as $airport)
                                            <option value="{{ $airport->id }}" {{ old('airport_id') == $airport->id ? 'selected' : '' }}>{{ $airport->name }} ({{ $airport->iata_code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mt-4">
                                    <x-input-label for="ground_handling" :value="__('Ground Handling (Opsional)')" />
                                    <x-text-input id="ground_handling" class="block mt-1 w-full" type="text" name="ground_handling" :value="old('ground_handling')" />
                                </div>
                                <div class="mt-4">
                                    <x-input-label for="flight_number" :value="__('Nomor Penerbangan / Call Sign')" />
                                    <x-text-input id="flight_number" class="block mt-1 w-full" type="text" name="flight_number" :value="old('flight_number')" required />
                                </div>
                                <div class="mt-4">
                                    <x-input-label for="aircraft_type" :value="__('Tipe Pesawat')" />
                                    <x-text-input id="aircraft_type" class="block mt-1 w-full" type="text" name="aircraft_type" :value="old('aircraft_type')" required />
                                </div>
                            </div>
                            {{-- Kolom Kanan --}}
                            <div>
                                <div>
                                    <x-input-label for="airline" :value="__('Nama Airline')" />
                                    <x-text-input id="airline" class="block mt-1 w-full" type="text" name="airline" :value="old('airline')" required />
                                </div>
                                <div class="mt-4">
                                    <x-input-label for="registration" :value="__('Registrasi A/C')" />
                                    <x-text-input id="registration" class="block mt-1 w-full" type="text" name="registration" :value="old('registration')" required />
                                </div>
                                <div class="mt-4">
                                    <x-input-label for="flight_number_2" :value="__('Nomor Penerbangan 2 (Opsional)')" />
                                    <x-text-input id="flight_number_2" class="block mt-1 w-full" type="text" name="flight_number_2" :value="old('flight_number_2')" />
                                </div>
                                 <div class="mt-4">
                                    <x-input-label for="other_airport" :value="__('Rute (Contoh: WAAA-WADD)')" />
                                    <x-text-input id="other_airport" class="block mt-1 w-full" type="text" name="other_airport" :value="old('other_airport')" placeholder="WAAA-WADD" required />
                                </div>
                            </div>
                        </div>

                        <hr class="my-6 border-gray-200 dark:border-gray-700">

                        {{-- Data Pergerakan & Waktu --}}
                        <div>
                            <x-input-label :value="__('Pilih Pergerakan (Movement)')" class="mb-2 font-semibold"/>
                            <div class="flex items-center space-x-6">
                                <label for="movement_arrival" class="flex items-center">
                                    <input type="checkbox" id="movement_arrival" name="movements[]" value="Arrival" class="rounded dark:bg-gray-900 border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ is_array(old('movements')) && in_array('Arrival', old('movements')) ? 'checked' : '' }}>
                                    <span class="ms-2">Arrival</span>
                                </label>
                                <label for="movement_departure" class="flex items-center">
                                    <input type="checkbox" id="movement_departure" name="movements[]" value="Departure" class="rounded dark:bg-gray-900 border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ is_array(old('movements')) && in_array('Departure', old('movements')) ? 'checked' : '' }}>
                                    <span class="ms-2">Departure</span>
                                </label>
                            </div>
                            <x-input-error :messages="$errors->get('movements')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div id="arrival_time_div" class="mt-4" style="display: none;">
                                <x-input-label for="arrival_time" :value="__('Waktu Arrival Aktual')" />
                                <x-text-input id="arrival_time" class="block mt-1 w-full" type="datetime-local" name="arrival_time" :value="old('arrival_time')" />
                                <x-input-error :messages="$errors->get('arrival_time')" class="mt-2" />
                            </div>
                            <div id="departure_time_div" class="mt-4" style="display: none;">
                                <x-input-label for="departure_time" :value="__('Waktu Departure Aktual')" />
                                <x-text-input id="departure_time" class="block mt-1 w-full" type="datetime-local" name="departure_time" :value="old('departure_time')" />
                                <x-input-error :messages="$errors->get('departure_time')" class="mt-2" />
                            </div>
                        </div>

                        <hr class="my-6 border-gray-200 dark:border-gray-700">

                        {{-- Opsi Biaya --}}
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                             <div>
                                <x-input-label for="flight_type" :value="__('Jenis Penerbangan')" />
                                <select name="flight_type" id="flight_type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                    <option value="Domestik" {{ old('flight_type') == 'Domestik' ? 'selected' : '' }}>Domestik</option>
                                    <option value="Internasional" {{ old('flight_type') == 'Internasional' ? 'selected' : '' }}>Internasional</option>
                                </select>
                            </div>
                            <div id="usd_exchange_rate_div" style="display: none;">
                                <x-input-label for="usd_exchange_rate" :value="__('Kurs Dollar (USD ke IDR)')" />
                                <x-text-input id="usd_exchange_rate" class="block mt-1 w-full" type="number" step="0.01" name="usd_exchange_rate" :value="old('usd_exchange_rate')" placeholder="Contoh: 15000" />
                                <x-input-error :messages="$errors->get('usd_exchange_rate')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="service_type" :value="__('Jenis Layanan')" />
                                <select name="service_type" id="service_type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                    <option value="APP" {{ old('service_type') == 'APP' ? 'selected' : '' }}>APP</option>
                                    <option value="TWR" {{ old('service_type') == 'TWR' ? 'selected' : '' }}>TWR</option>
                                    <option value="AFIS" {{ old('service_type') == 'AFIS' ? 'selected' : '' }}>AFIS</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label for="charge_type" :value="__('Jenis Biaya')" />
                                <select name="charge_type" id="charge_type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                    <option value="Advance" {{ old('charge_type') == 'Advance' ? 'selected' : '' }}>Advance</option>
                                    <option value="Extend" {{ old('charge_type') == 'Extend' ? 'selected' : '' }}>Extend</option>
                                </select>
                            </div>
                        </div>
                        <div class="block mt-4">
                            <label for="apply_pph" class="inline-flex items-center">
                                <input id="apply_pph" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="apply_pph" value="1" {{ old('apply_pph') ? 'checked' : '' }}>
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Terapkan PPh (2%) untuk Domestik') }}</span>
                            </label>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Elemen untuk Kurs USD
            const flightTypeSelect = document.getElementById('flight_type');
            const usdExchangeRateDiv = document.getElementById('usd_exchange_rate_div');
            const usdExchangeRateInput = document.getElementById('usd_exchange_rate');

            // Elemen untuk Waktu Pergerakan
            const arrivalCheckbox = document.getElementById('movement_arrival');
            const departureCheckbox = document.getElementById('movement_departure');
            const arrivalTimeDiv = document.getElementById('arrival_time_div');
            const departureTimeDiv = document.getElementById('departure_time_div');
            const arrivalTimeInput = document.getElementById('arrival_time');
            const departureTimeInput = document.getElementById('departure_time');

            function toggleUsdField() {
                if (flightTypeSelect.value === 'Internasional') {
                    usdExchangeRateDiv.style.display = 'block';
                    usdExchangeRateInput.required = true;
                } else {
                    usdExchangeRateDiv.style.display = 'none';
                    usdExchangeRateInput.required = false;
                }
            }

            function toggleTimeFields() {
                if (arrivalCheckbox.checked) {
                    arrivalTimeDiv.style.display = 'block';
                    arrivalTimeInput.required = true;
                } else {
                    arrivalTimeDiv.style.display = 'none';
                    arrivalTimeInput.required = false;
                }

                if (departureCheckbox.checked) {
                    departureTimeDiv.style.display = 'block';
                    departureTimeInput.required = true;
                } else {
                    departureTimeDiv.style.display = 'none';
                    departureTimeInput.required = false;
                }
            }

            flightTypeSelect.addEventListener('change', toggleUsdField);
            arrivalCheckbox.addEventListener('change', toggleTimeFields);
            departureCheckbox.addEventListener('change', toggleTimeFields);

            // Panggil semua fungsi saat halaman dimuat
            toggleUsdField();
            toggleTimeFields();
        });
    </script>
</x-app-layout>
