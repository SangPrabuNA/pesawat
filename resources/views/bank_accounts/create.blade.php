<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Rekening Bank') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('bank-accounts.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Nama Bank --}}
                        <div>
                            <x-input-label for="bank_name" :value="__('Nama Bank')" />
                            <x-text-input id="bank_name" class="block mt-1 w-full" type="text" name="bank_name" :value="old('bank_name')" required autofocus />
                        </div>

                        {{-- Cabang --}}
                        <div class="mt-4">
                            <x-input-label for="branch_name" :value="__('Cabang')" />
                            <x-text-input id="branch_name" class="block mt-1 w-full" type="text" name="branch_name" :value="old('branch_name')" required />
                        </div>

                        {{-- Nama Pemilik Rekening --}}
                        <div class="mt-4">
                            <x-input-label for="account_holder_name" :value="__('Nama Pemilik Rekening')" />
                            <x-text-input id="account_holder_name" class="block mt-1 w-full" type="text" name="account_holder_name" :value="old('account_holder_name')" required />
                        </div>

                        {{-- Nomor Rekening --}}
                        <div class="mt-4">
                            <x-input-label for="account_number" :value="__('Nomor Rekening')" />
                            <x-text-input id="account_number" class="block mt-1 w-full" type="text" name="account_number" :value="old('account_number')" required />
                        </div>

                        {{-- Logo Bank --}}
                        <div class="mt-4">
                            <x-input-label for="bank_logo" :value="__('Logo Bank (Opsional, format: PNG, JPG)')" />
                            <x-text-input id="bank_logo" class="block mt-1 w-full border-gray-600 p-2" type="file" name="bank_logo" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Simpan') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
