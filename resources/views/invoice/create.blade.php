<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Buat Invoice Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900 dark:text-gray-100">
                    @if ($errors->any())
                        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Oops! Terjadi kesalahan.</strong>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('invoices.store') }}" method="POST">
                        @csrf

                        <!-- ... SEKSI 1: INFORMASI UMUM (Tidak ada perubahan) ... -->
                        <h3 class="text-lg font-semibold border-b border-gray-700 pb-2 mb-4">Informasi Umum</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="airline" :value="__('Nama Airline')" />
                                <x-text-input id="airline" class="block mt-1 w-full" type="text" name="airline" :value="old('airline')" required />
                            </div>
                            <div>
                                <x-input-label for="paid_by" :value="__('Dibayar Oleh (Opsional)')" />
                                <x-text-input id="paid_by" class="block mt-1 w-full" type="text" name="paid_by" :value="old('paid_by')" placeholder="Kosongkan jika sama dengan Airline" />
                            </div>
                            <div>
                                <x-input-label for="ground_handling" :value="__('Ground Handling (Opsional)')" />
                                <x-text-input id="ground_handling" class="block mt-1 w-full" type="text" name="ground_handling" :value="old('ground_handling')" />
                            </div>
                            <div>
                                <x-input-label for="invoice_date" :value="__('Tanggal Invoice')" />
                                <x-text-input id="invoice_date" class="block mt-1 w-full" type="date" name="invoice_date" :value="old('invoice_date', now()->format('Y-m-d'))" required />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                             <div>
                                <x-input-label for="flight_number" :value="__('Call Sign 1')" />
                                <x-text-input id="flight_number" class="block mt-1 w-full" type="text" name="flight_number" :value="old('flight_number')" required />
                            </div>
                             <div>
                                <x-input-label for="flight_number_2" :value="__('Call Sign 2 (Opsional)')" />
                                <x-text-input id="flight_number_2" class="block mt-1 w-full" type="text" name="flight_number_2" :value="old('flight_number_2')" />
                            </div>
                            <div>
                                <x-input-label for="registration" :value="__('Registrasi A/C')" />
                                <x-text-input id="registration" class="block mt-1 w-full" type="text" name="registration" :value="old('registration')" required />
                            </div>
                        </div>
                        <div class="mt-4">
                            <x-input-label for="aircraft_type" :value="__('Tipe Pesawat')" />
                            <x-text-input id="aircraft_type" class="block mt-1 w-full" type="text" name="aircraft_type" :value="old('aircraft_type')" required />
                        </div>


                        <!-- SEKSI 2: RUTE & WAKTU -->
                        <h3 class="text-lg font-semibold border-b border-gray-700 pb-2 mb-4 mt-8">Rute & Waktu</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="airport_id" :value="__('Bandara Saat Ini')" />
                                <select name="airport_id" id="airport_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm" required {{ $airports->count() === 1 ? 'disabled' : '' }}>
                                    @if($airports->count() > 1) <option value="">-- Pilih Bandara --</option> @endif
                                    @foreach($airports as $airport)
                                        <option value="{{ $airport->id }}" @selected(old('airport_id') == $airport->id || ($airports->count() === 1 && is_null(old('airport_id'))))>
                                            {{ $airport->name }} ({{ $airport->iata_code }})
                                        </option>
                                    @endforeach
                                </select>
                                @if($airports->count() === 1)
                                    <input type="hidden" name="airport_id" value="{{ $airports->first()->id }}">
                                @endif
                            </div>
                            <div>
                                <x-input-label for="origin_airport" id="origin_airport_label" :value="__('Bandara Asal / Tujuan')" />
                                <x-text-input id="origin_airport" class="block mt-1 w-full" type="text" name="origin_airport" :value="old('origin_airport')" placeholder="Contoh: WADD" required />
                            </div>
                            <!-- --- INPUT BARU DI SINI --- -->
                            <div id="destination_airport_div" style="display: none;">
                                <x-input-label for="destination_airport" :value="__('Bandara Tujuan Keberangkatan')" />
                                <x-text-input id="destination_airport" class="block mt-1 w-full" type="text" name="destination_airport" :value="old('destination_airport')" placeholder="Contoh: WSSS" />
                            </div>
                        </div>
                        <div class="mt-4">
                            <x-input-label :value="__('Pilih Pergerakan (Movement)')" class="mb-2"/>
                            <div class="flex items-center space-x-6">
                                <label class="flex items-center"><input type="checkbox" id="movement_arrival" name="movements[]" value="Arrival" class="movement-checkbox rounded dark:bg-gray-900 border-gray-300 text-indigo-600 shadow-sm" @checked(is_array(old('movements')) && in_array('Arrival', old('movements')))> <span class="ms-2">Arrival</span></label>
                                <label class="flex items-center"><input type="checkbox" id="movement_departure" name="movements[]" value="Departure" class="movement-checkbox rounded dark:bg-gray-900 border-gray-300 text-indigo-600 shadow-sm" @checked(is_array(old('movements')) && in_array('Departure', old('movements')))> <span class="ms-2">Departure</span></label>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div id="arrival_time_div" class="mt-4" style="display: none;">
                                <x-input-label for="arrival_time" :value="__('Waktu Arrival Aktual')" />
                                <x-text-input id="arrival_time" class="block mt-1 w-full" type="datetime-local" name="arrival_time" :value="old('arrival_time')" />
                            </div>
                            <div id="departure_time_div" class="mt-4" style="display: none;">
                                <x-input-label for="departure_time" :value="__('Waktu Departure Aktual')" />
                                <x-text-input id="departure_time" class="block mt-1 w-full" type="datetime-local" name="departure_time" :value="old('departure_time')" />
                            </div>
                        </div>

                        <!-- ... SEKSI 3: OPSI BIAYA (Tidak ada perubahan) ... -->
                        <h3 class="text-lg font-semibold border-b border-gray-700 pb-2 mb-4 mt-8">Opsi Biaya</h3>
                        <div class="block mb-4">
                            <label for="is_free_charge" class="inline-flex items-center">
                                <input id="is_free_charge" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 text-indigo-600 shadow-sm" name="is_free_charge" value="1" {{ old('is_free_charge') ? 'checked' : '' }}>
                                <span class="ms-2 text-sm font-semibold">{{ __('Tandai sebagai Free Charge') }}</span>
                            </label>
                        </div>
                        <div id="cost_options_div">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                                <div>
                                    <x-input-label for="flight_type" :value="__('Jenis Penerbangan')" />
                                    <select name="flight_type" id="flight_type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm">
                                        <option value="Domestik">Domestik</option>
                                        <option value="Internasional">Internasional</option>
                                    </select>
                                </div>
                                <div id="usd_exchange_rate_div" style="display: none;">
                                    <x-input-label for="usd_exchange_rate" :value="__('Kurs Dollar (USD ke IDR)')" />
                                    <x-text-input id="usd_exchange_rate" class="block mt-1 w-full" type="number" step="1" name="usd_exchange_rate" :value="old('usd_exchange_rate')" placeholder="Contoh: 16000" />
                                </div>
                                <div>
                                    <x-input-label for="service_type" :value="__('Jenis Layanan')" />
                                    <select name="service_type" id="service_type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm">
                                        @foreach($services as $service)
                                            <option value="{{ $service->name }}" @selected(old('service_type') == $service->name)>
                                                {{ $service->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="block mt-4">
                                <label for="apply_pph" class="inline-flex items-center">
                                    <input id="apply_pph" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 text-indigo-600 shadow-sm" name="apply_pph" value="1" {{ old('apply_pph') ? 'checked' : '' }}>
                                    <span class="ms-2 text-sm">{{ __('Terapkan PPh (2%) untuk Domestik') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8">
                            <x-primary-button>
                                {{ __('Simpan Invoice') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const arrivalCheckbox = document.getElementById('movement_arrival');
            const departureCheckbox = document.getElementById('movement_departure');
            const arrivalTimeDiv = document.getElementById('arrival_time_div');
            const departureTimeDiv = document.getElementById('departure_time_div');
            const arrivalTimeInput = document.getElementById('arrival_time');
            const departureTimeInput = document.getElementById('departure_time');
            const freeChargeCheckbox = document.getElementById('is_free_charge');
            const costOptionsDiv = document.getElementById('cost_options_div');
            const flightTypeSelect = document.getElementById('flight_type');
            const usdExchangeRateDiv = document.getElementById('usd_exchange_rate_div');
            const usdExchangeRateInput = document.getElementById('usd_exchange_rate');

            // --- ELEMEN BARU ---
            const originAirportLabel = document.getElementById('origin_airport_label');
            const destinationAirportDiv = document.getElementById('destination_airport_div');
            const destinationAirportInput = document.getElementById('destination_airport');

            function toggleTimeFields() {
                // Tampilkan/sembunyikan input waktu
                arrivalTimeDiv.style.display = arrivalCheckbox.checked ? 'block' : 'none';
                arrivalTimeInput.required = arrivalCheckbox.checked;
                departureTimeDiv.style.display = departureCheckbox.checked ? 'block' : 'none';
                departureTimeInput.required = departureCheckbox.checked;

                // --- LOGIKA BARU UNTUK RUTE ---
                const bothChecked = arrivalCheckbox.checked && departureCheckbox.checked;
                destinationAirportDiv.style.display = bothChecked ? 'block' : 'none';
                destinationAirportInput.required = bothChecked;
                originAirportLabel.textContent = bothChecked ? 'Bandara Asal Kedatangan' : 'Bandara Asal / Tujuan';
            }

            function toggleCostOptions() {
                costOptionsDiv.style.display = freeChargeCheckbox.checked ? 'none' : 'block';
                flightTypeSelect.required = !freeChargeCheckbox.checked;
                document.getElementById('service_type').required = !freeChargeCheckbox.checked;
                toggleUsdField();
            }

            function toggleUsdField() {
                const isInternational = flightTypeSelect.value === 'Internasional' && !freeChargeCheckbox.checked;
                usdExchangeRateDiv.style.display = isInternational ? 'block' : 'none';
                usdExchangeRateInput.required = isInternational;
            }

            arrivalCheckbox.addEventListener('change', toggleTimeFields);
            departureCheckbox.addEventListener('change', toggleTimeFields);
            freeChargeCheckbox.addEventListener('change', toggleCostOptions);
            flightTypeSelect.addEventListener('change', toggleUsdField);

            // Jalankan fungsi saat halaman dimuat
            toggleTimeFields();
            toggleCostOptions();
        });
    </script>
</x-app-layout>
