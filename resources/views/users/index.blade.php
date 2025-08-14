{{-- Tampilan Daftar Pengguna --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manajemen Pengguna') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 dark:bg-green-800 text-green-700 dark:text-green-200 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Username</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Bandara (Admin)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @foreach ($users as $user)
                                <tr>
                                    <td class="px-6 py-4">{{ $user->username }}</td>
                                    <td class="px-6 py-4">{{ $user->email }}</td>
                                    <td class="px-6 py-4 uppercase font-bold">{{ $user->role }}</td>
                                    <td class="px-6 py-4">{{ $user->airport->iata_code ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('users.edit', $user) }}" class="text-indigo-400 hover:text-indigo-300">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
