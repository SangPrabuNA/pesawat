<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Detail Invoice #') }}{{ $invoice->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900 dark:text-gray-100">

                    {{-- Bagian Informasi Utama --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 border-b border-gray-700 pb-6">
                        <div>
                            <h3 class="text-lg font-bold text-gray-400 uppercase tracking-wider">Ditagihkan Kepada</h3>
                            <p class="text-xl font-semibold">{{ $invoice->airline }}</p>
                            @if($invoice->ground_handling)
                                <p class="text-sm text-gray-400">c/o {{ $invoice->ground_handling }}</p>
                            @endif
                        </div>
                        <div class="text-left md:text-right">
                            <h3 class="text-lg font-bold text-gray-400 uppercase tracking-wider">Invoice #{{ $invoice->id }}</h3>
                            <p><strong>Status:</strong>
                                <span class="font-semibold {{ $invoice->status == 'Lunas' ? 'text-green-400' : 'text-red-400' }}">
                                    {{ $invoice->status }}
                                </span>
                            </p>
                        </div>
                    </div>

                    {{-- Bagian Detail Penerbangan --}}
                    <h3 class="text-lg font-semibold mb-4">Data Penerbangan</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 mb-6">
                        <p><strong>Bandara:</strong> {{ $invoice->airport->name ?? 'N/A' }}</p>
                        <p><strong>Registrasi:</strong> {{ $invoice->registration }}</p>
                        <p><strong>Tipe Pesawat:</strong> {{ $invoice->aircraft_type }}</p>
                        <p><strong>Call Sign 1:</strong> {{ $invoice->flight_number }}</p>
                        <p><strong>Call Sign 2:</strong> {{ $invoice->flight_number_2 ?? '-' }}</p>
                        <p><strong>Jenis Penerbangan:</strong> {{ $invoice->flight_type }}</p>
                        <p><strong>Rute:</strong> {{ $invoice->departure_airport }} - {{ $invoice->arrival_airport }}</p>
                    </div>

                    <hr class="my-6 border-gray-700">

                    {{-- Bagian Detail Biaya --}}
                    <h3 class="text-lg font-semibold mb-4">Detail Perhitungan Biaya {{ $invoice->charge_type }}</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4">
                        <p><strong>Jenis Layanan:</strong> {{ $invoice->service_type }}</p>
                        <p><strong>Pergerakan:</strong> {{ $invoice->movement_type }}</p>
                        <p><strong>Waktu Aktual:</strong> {{ \Carbon\Carbon::parse($invoice->actual_time)->format('d M Y, H:i') }}</p>
                        <p><strong>Jam Operasional:</strong> {{ \Carbon\Carbon::parse($invoice->operational_hour_start)->format('H:i') }} - {{ \Carbon\Carbon::parse($invoice->operational_hour_end)->format('H:i') }}</p>
                        <p><strong>Durasi Terhitung:</strong> {{ floor($invoice->duration_minutes / 60) }} jam {{ $invoice->duration_minutes % 60 }} menit</p>
                        <p><strong>Jam Ditagihkan:</strong> {{ $invoice->billed_hours }} jam</p>
                    </div>

                    {{-- Bagian Rincian Finansial --}}
                    <div class="mt-6 pt-6 border-t border-gray-700">
                        <div class="grid grid-cols-2 gap-4">
                            <p><strong>Tarif per Jam:</strong></p>
                            <p class="text-right">{{ number_format($invoice->base_rate, 2) }} {{ $invoice->currency }}</p>

                            <p><strong>Biaya Dasar:</strong></p>
                            <p class="text-right">{{ number_format($invoice->base_charge, 2) }} {{ $invoice->currency }}</p>

                            @if($invoice->ppn_charge > 0)
                            <p><strong>PPN (11%):</strong></p>
                            <p class="text-right">{{ number_format($invoice->ppn_charge, 2) }} {{ $invoice->currency }}</p>
                            @endif

                            @if($invoice->pph_charge > 0)
                            <p><strong>Potongan PPh (2%):</strong></p>
                            <p class="text-right">- {{ number_format($invoice->pph_charge, 2) }} {{ $invoice->currency }}</p>
                            @endif

                            <p class="mt-4 text-xl font-bold"><strong>Total Tagihan:</strong></p>
                            <p class="mt-4 text-xl font-bold text-right">{{ number_format($invoice->total_charge, 2) }} {{ $invoice->currency }}</p>
                        </div>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="mt-8 flex justify-end gap-4">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500">
                            Kembali
                        </a>
                        <a href="{{ route('invoices.download', $invoice->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500">
                            Unduh PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
