{{-- @extends('components.layouts.app')

<div>
    <x-filament::breadcrumbs :breadcrumbs="[
        '/admin/users' => 'Usuários',
        '' => 'Listagem',
    ]" />
    <div class="flex justify-between mt-1">
        <div class="font-bold text-3xl">Usuários</div>
        <div>
            {{ $data }}
        </div>
    </div>
    <div>
        <x-filament::modal>
            <x-slot name="trigger" class="mt-5">
                <x-filament::button>
                    Importar Planilha
                </x-filament::button>
            </x-slot>
            <form wire:submit="save">
                <div class="col">
                    <input type="file" wire:model="file" id="fileInput">
                </div>
                <div class="col text-center mt-5">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full"
                        type="submit">
                        Importar
                    </button>
                </div>
            </form>
        </x-filament::modal>
    </div>
</div>

@section('scripts')
    <script>
        // Get a reference to the file input element
    const inputElement = document.querySelector('input[type="file"]');

    // Create a FilePond instance
    const pond = FilePond.create(inputElement)
    </script>
@endsection --}}
