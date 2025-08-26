<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Extend/Advance') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- Menampilkan pesan sukses/error --}}
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 dark:bg-green-800 text-green-700 dark:text-green-200 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif
                     @if (session('error'))
                        <div class="mb-4 p-4 bg-red-100 dark:bg-red-800 text-red-700 dark:text-red-200 rounded-lg">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="flex justify-between items-center">
                        <p>{{ __("Selamat Datang") }}</p>
                        <a href="{{ route('invoices.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                            Buat Invoice Baru
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="pt-6 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Riwayat Invoice</h3>
                    <form method="GET" action="{{ route('dashboard') }}" class="mb-6 flex flex-wrap items-end gap-4">

                        {{-- Filter Bandara (Hanya untuk Master) --}}
                        @if(auth()->user()->role === 'master')
                            <div>
                                <label for="airport_id" class="block text-sm font-medium text-gray-300">Bandara</label>
                                <select name="airport_id" id="airport_id" class="mt-1 block w-full rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600">
                                    <option value="">Semua Bandara</option>
                                    @foreach($airports as $airport)
                                        <option value="{{ $airport->id }}" @selected($selectedAirport == $airport->id)>{{ $airport->iata_code }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Filter Tahun dan Bulan --}}
                        <div>
                            <label for="year" class="block text-sm font-medium text-gray-300">Tahun</label>
                            <select name="year" id="year" class="mt-1 block w-full rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600">
                                <option value="">Semua Tahun</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" @selected($selectedYear == $year)>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="month" class="block text-sm font-medium text-gray-300">Bulan</label>
                            <select name="month" id="month" class="mt-1 block w-full rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600">
                                <option value="">Semua Bulan</option>
                                @for ($m=1; $m<=12; $m++)
                                    <option value="{{ $m }}" @selected($selectedMonth == $m)>{{ date('F', mktime(0,0,0,$m, 1)) }}</option>
                                @endfor
                            </select>
                        </div>

                        {{-- Tombol Aksi --}}
                        <div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border rounded-md font-semibold text-xs text-white uppercase hover:bg-indigo-500">Filter</button>
                            <a href="{{ route('dashboard') }}" class="ml-2 inline-flex items-center px-4 py-2 bg-gray-600 border rounded-md font-semibold text-xs text-white uppercase hover:bg-gray-500">Reset</a>
                            <a href="{{ route('invoices.export.excel', request()->query()) }}" class="ml-2 inline-flex items-center px-4 py-2 bg-green-600 border rounded-md font-semibold text-xs text-white uppercase hover:bg-green-500">Ekspor</a>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                         <table class="min-w-full divide-y divide-gray-700">
                            <thead class="bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Bandara</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Airline</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">No. Penerbangan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Total Tagihan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                @forelse ($invoices as $invoice)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">{{ $invoice->airport->iata_code ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $invoice->airline }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $invoice->flight_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ number_format($invoice->total_charge, 2) }} {{ $invoice->currency }}
                                            @if($invoice->currency == 'USD' && $invoice->usd_exchange_rate > 0)
                                                <span class="text-gray-400 text-xs block">(Rp {{ number_format($invoice->total_charge * $invoice->usd_exchange_rate, 2) }})</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @php
                                                $statusClass = match($invoice->status) {
                                                    'Lunas' => 'bg-green-200 text-green-800',
                                                    'Belum Lunas' => 'bg-red-200 text-red-800',
                                                    'Nonaktif' => 'bg-yellow-200 text-yellow-800',
                                                    default => 'bg-gray-200 text-gray-800',
                                                };
                                            @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                                {{ $invoice->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div class="flex items-center gap-4">
                                                <a href="{{ route('invoices.show', $invoice->id) }}" class="text-indigo-400 hover:text-indigo-300">Detail</a>
                                                <form action="{{ route('invoices.updateStatus', $invoice->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="status" onchange="this.form.submit()" class="text-xs rounded-md border-gray-600 bg-gray-700 shadow-sm focus:border-indigo-300 focus:ring-indigo-200">
                                                        <option value="Belum Lunas" @selected($invoice->status == 'Belum Lunas')>Belum Lunas</option>
                                                        <option value="Lunas" @selected($invoice->status == 'Lunas')>Lunas</option>
                                                        @if(auth()->user()->role === 'master')
                                                            <option value="Nonaktif" @selected($invoice->status == 'Nonaktif')>Nonaktif</option>
                                                        @endif
                                                    </select>
                                                </form>
                                                <!-- --- TOMBOL EDIT BARU --- -->
                                                @if(auth()->user()->role === 'master')
                                                    <a href="{{ route('invoices.edit', $invoice->id) }}" class="text-yellow-400 hover:text-yellow-300">Edit</a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $invoices->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
