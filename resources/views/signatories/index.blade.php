<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Manajemen Penandatangan') }}
            </h2>
            <a href="{{ route('signatories.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border rounded-md font-semibold text-xs text-white uppercase hover:bg-indigo-500">
                Tambah Baru
            </a>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Tanda Tangan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach ($signatories as $signatory)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-white">{{ $signatory->name }}</td>
                            <td class="px-6 py-4"><img src="{{ asset('storage/' . $signatory->signature) }}" alt="Tanda Tangan" class="h-10 bg-white p-1 rounded"></td>
                            <td class="px-6 py-4 text-sm">
                                @if($signatory->is_active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-200 text-green-800">Aktif</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-200 text-red-800">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm"><a href="{{ route('signatories.edit', $signatory) }}" class="text-yellow-400 hover:text-yellow-300">Edit</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
