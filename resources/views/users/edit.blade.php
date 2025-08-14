{{-- Form Edit Pengguna --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Pengguna: {{ $user->username }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('users.update', $user) }}">
                        @csrf
                        @method('PATCH')
                        <div>
                            <x-input-label for="username" value="Username" />
                            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username', $user->username)" required />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="email" value="Email" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="role" value="Role" />
                            <select name="role" id="role" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm">
                                <option value="master" @selected(old('role', $user->role) == 'master')>Master</option>
                                <option value="admin" @selected(old('role', $user->role) == 'admin')>Admin</option>
                                <option value="user" @selected(old('role', $user->role) == 'user')>User</option>
                            </select>
                        </div>
                        <div class="mt-4" id="airport_select_div">
                            <x-input-label for="airport_id" value="Bandara (Khusus Admin)" />
                            <select name="airport_id" id="airport_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm">
                                <option value="">-- Tidak Terikat Bandara --</option>
                                @foreach($airports as $airport)
                                    <option value="{{ $airport->id }}" @selected(old('airport_id', $user->airport_id) == $airport->id)>{{ $airport->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('users.index') }}" class="text-sm text-gray-400 hover:text-gray-100">Batal</a>
                            <x-primary-button class="ms-4">Simpan</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const airportDiv = document.getElementById('airport_select_div');
            function toggleAirportSelect() {
                airportDiv.style.display = roleSelect.value === 'admin' ? 'block' : 'none';
            }
            roleSelect.addEventListener('change', toggleAirportSelect);
            toggleAirportSelect();
        });
    </script>
</x-app-layout>
