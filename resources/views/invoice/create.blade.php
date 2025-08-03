<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Buat Invoice Advance/Extend Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('invoices.store') }}">
                        @csrf

                        <!-- Baris 1: Airport & Airline -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label for="airport_id" :value="__('Bandara Saat Ini')" />
                                <select name="airport_id" id="airport_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">-- Pilih Bandara --</option>
                                    @foreach($airports as $airport)
                                        <option value="{{ $airport->id }}" data-code="{{ $airport->iata_code }}" {{ old('airport_id') == $airport->id ? 'selected' : '' }}>{{ $airport->name }} ({{ $airport->iata_code }})</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('airport_id')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="airline" :value="__('Nama Airline')" />
                                <x-text-input id="airline" class="block mt-1 w-full" type="text" name="airline" :value="old('airline')" required />
                                <x-input-error :messages="$errors->get('airline')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Baris Tambahan: Rute -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label :value="__('Pergerakan (Movement)')" />
                                <div class="flex items-center space-x-4 mt-2">
                                    <label><input type="radio" name="movement_type" value="Departure" class="dark:bg-gray-900" {{ old('movement_type', 'Departure') == 'Departure' ? 'checked' : '' }}> Departure</label>
                                    <label><input type="radio" name="movement_type" value="Arrival" class="dark:bg-gray-900" {{ old('movement_type') == 'Arrival' ? 'checked' : '' }}> Arrival</label>
                                </div>
                            </div>
                            <div>
                                <x-input-label for="other_airport" id="other_airport_label" :value="__('Bandara Tujuan (Arrival)')" />
                                <x-text-input id="other_airport" class="block mt-1 w-full" type="text" name="other_airport" :value="old('other_airport')" placeholder="Contoh: DPS" required />
                                <x-input-error :messages="$errors->get('other_airport')" class="mt-2" />
                            </div>
                        </div>


                        <!-- Baris 2: Ground Handling & Registrasi -->
                         <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label for="ground_handling" :value="__('Ground Handling (Opsional)')" />
                                <x-text-input id="ground_handling" class="block mt-1 w-full" type="text" name="ground_handling" :value="old('ground_handling')" />
                            </div>
                            <div>
                                <x-input-label for="registration" :value="__('Registrasi A/C')" />
                                <x-text-input id="registration" class="block mt-1 w-full" type="text" name="registration" :value="old('registration')" required />
                            </div>
                        </div>

                        <!-- Baris 3: Nomor Penerbangan -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label for="flight_number" :value="__('Nomor Penerbangan / Call Sign')" />
                                <x-text-input id="flight_number" class="block mt-1 w-full" type="text" name="flight_number" :value="old('flight_number')" required />
                            </div>
                            <div>
                                <x-input-label for="flight_number_2" :value="__('Nomor Penerbangan 2 (Opsional)')" />
                                <x-text-input id="flight_number_2" class="block mt-1 w-full" type="text" name="flight_number_2" :value="old('flight_number_2')" />
                            </div>
                        </div>

                        <!-- Baris 4: Tipe Pesawat & Waktu Aktual -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label for="aircraft_type" :value="__('Tipe Pesawat')" />
                                <x-text-input id="aircraft_type" class="block mt-1 w-full" type="text" name="aircraft_type" :value="old('aircraft_type')" required />
                            </div>
                            <div>
                                <x-input-label for="actual_time" id="actual_time_label" :value="__('Waktu Departure Aktual')" />
                                <x-text-input id="actual_time" class="block mt-1 w-full" type="datetime-local" name="actual_time" :value="old('actual_time')" required />
                                <x-input-error :messages="$errors->get('actual_time')" class="mt-2" />
                            </div>
                        </div>

                        <hr class="my-8 border-gray-600">

                        <!-- Baris 6: Detail Biaya -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                             <div>
                                <x-input-label for="flight_type" :value="__('Jenis Penerbangan')" />
                                <select name="flight_type" id="flight_type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                    <option value="Domestik" {{ old('flight_type') == 'Domestik' ? 'selected' : '' }}>Domestik</option>
                                    <option value="Internasional" {{ old('flight_type') == 'Internasional' ? 'selected' : '' }}>Internasional</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label for="service_type" :value="__('Jenis Layanan')" />
                                <select name="service_type" id="service_type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                    <option value="APP" {{ old('service_type') == 'APP' ? 'selected' : '' }}>APP</option>
                                    <option value="TWR" {{ old('service_type') == 'TWR' ? 'selected' : '' }}>TWR</option>
                                    <option value="AFIS" {{ old('service_type') == 'AFIS' ? 'selected' : '' }}>AFIS</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label for="charge_type" :value="__('Jenis Biaya')" />
                                <select name="charge_type" id="charge_type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                    <option value="Advance" {{ old('charge_type') == 'Advance' ? 'selected' : '' }}>Advance</option>
                                    <option value="Extend" {{ old('charge_type') == 'Extend' ? 'selected' : '' }}>Extend</option>
                                </select>
                            </div>
                        </div>

                        <!-- Baris 7: PPh -->
                        <div class="block mt-4">
                            <label for="apply_pph" class="inline-flex items-center">
                                <input id="apply_pph" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500" name="apply_pph" value="1" {{ old('apply_pph') ? 'checked' : '' }}>
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Terapkan PPh (2%) untuk Domestik') }}</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end mt-8">
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
            const movementRadios = document.querySelectorAll('input[name="movement_type"]');
            const otherAirportLabel = document.getElementById('other_airport_label');
            const actualTimeLabel = document.getElementById('actual_time_label');

            function toggleLabels() {
                if (document.querySelector('input[name="movement_type"]:checked').value === 'Departure') {
                    otherAirportLabel.textContent = 'Bandara Tujuan (Arrival)';
                    actualTimeLabel.textContent = 'Waktu Departure Aktual';
                } else {
                    otherAirportLabel.textContent = 'Bandara Asal (Departure)';
                    actualTimeLabel.textContent = 'Waktu Arrival Aktual';
                }
            }

            movementRadios.forEach(radio => radio.addEventListener('change', toggleLabels));
            toggleLabels(); // Panggil saat load
        });
    </script>
</x-app-layout>
