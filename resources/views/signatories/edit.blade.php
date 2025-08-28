<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Penandatangan: ') }} {{ $signatory->name }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-100">
                     <form method="post" action="{{ route('signatories.update', $signatory) }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PATCH')
                        <div>
                            <x-input-label for="name" value="Nama Penandatangan" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $signatory->name)" required />
                        </div>
                        <div>
                            <x-input-label for="signature" value="Upload Tanda Tangan Baru (Opsional)" />
                            <input id="signature" name="signature" type="file" class="mt-1 block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-700 file:text-gray-300 hover:file:bg-gray-600" />
                            <p class="text-xs text-gray-400 mt-2">Tanda Tangan Saat Ini:</p>
                            <img src="{{ asset('storage/' . $signatory->signature) }}" alt="Tanda Tangan" class="h-12 mt-1 bg-white p-1 rounded">
                        </div>
                         <div>
                            <x-input-label for="is_active" value="Status" />
                            <select name="is_active" id="is_active" class="mt-1 block w-full border-gray-700 bg-gray-900 rounded-md shadow-sm">
                                <option value="1" @selected(old('is_active', $signatory->is_active))>Aktif</option>
                                <option value="0" @selected(!old('is_active', $signatory->is_active))>Nonaktif</option>
                            </select>
                        </div>
                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('signatories.index') }}" class="text-sm text-gray-400 hover:text-white">Batal</a>
                            <x-primary-button>Simpan Perubahan</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
