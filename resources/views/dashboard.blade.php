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
                    {{-- Menampilkan pesan sukses setelah update status --}}
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 dark:bg-green-800 text-green-700 dark:text-green-200 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="flex justify-between items-center">
                        <p>{{ __("Selamat Datang") }}</p>
                        <a href="{{ route('invoices.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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

                    {{-- Tabel Invoice (Tidak ada perubahan di sini) --}}
                    <div class="overflow-x-auto">
                        {{-- ... Tabel Anda ... --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
