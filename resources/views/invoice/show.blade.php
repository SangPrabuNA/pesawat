<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Detail Invoice #') }}{{ $invoice->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <h3 class="text-lg font-bold mb-4">Data Penerbangan</h3>
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <p><strong>Airline:</strong> {{ $invoice->airline }}</p>
                        <p><strong>No. Penerbangan:</strong> {{ $invoice->flight_number }}</p>
                        <p><strong>Registrasi:</strong> {{ $invoice->registration }}</p>
                        <p><strong>Tipe Pesawat:</strong> {{ $invoice->aircraft_type }}</p>
                        <p><strong>Rute:</strong> {{ $invoice->route }}</p>
                    </div>

                    <hr class="my-6 border-gray-600">

                    <h3 class="text-lg font-bold mb-4">Detail Perhitungan Biaya {{ $invoice->charge_type }}</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <p><strong>Waktu Aktual (ATA/ATD):</strong> {{ \Carbon\Carbon::parse($invoice->actual_time)->format('d M Y, H:i') }}</p>
                        <p><strong>Durasi (Menit):</strong> {{ $invoice->duration_minutes }} menit</p>
                        <p><strong>Jam Ditagihkan:</strong> {{ $invoice->billed_hours }} jam</p>
                        <p><strong>Tarif per Jam:</strong> {{ number_format($invoice->base_rate, 2) }} {{ $invoice->currency }}</p>
                        <p><strong>Biaya Dasar:</strong> {{ number_format($invoice->base_charge, 2) }} {{ $invoice->currency }}</p>
                        <p><strong>PPN (11%):</strong> {{ number_format($invoice->ppn_charge, 2) }} {{ $invoice->currency }}</p>
                        <p class="mt-4 text-xl font-bold"><strong>Total Tagihan:</strong> {{ number_format($invoice->total_charge, 2) }} {{ $invoice->currency }}</p>
                    </div>

                    <div class="mt-8">
                        <a href="{{ route('invoices.download', $invoice->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500">
                            Unduh PDF
                        </a>
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                            Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
