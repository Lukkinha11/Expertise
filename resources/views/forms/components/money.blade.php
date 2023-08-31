<x-dynamic-component
    :component="$getFieldWrapperView()"
    :id="$getId()"
    :label="$getLabel()"
    :label-sr-only="$isLabelHidden()"
    :helper-text="$getHelperText()"
    :hint="$getHint()"
    :hint-action="$getHintAction()"
    :hint-color="$getHintColor()"
    :hint-icon="$getHintIcon()"
    :required="$isRequired()"
    :state-path="$getStatePath()"
>


    {{--https://alpinejs.dev/plugins/mask--}}
    <div  class="filament-forms-text-input-component flex items-center space-x-2 rtl:space-x-reverse group"
          x-data="{ state: $wire.entangle('{{ $getStatePath() }}').defer }">
         <span class="whitespace-nowrap group-focus-within:text-primary-500 text-gray-400">
             @if ($label = $getPrefixLabel())
                 <span>{{ $label }}</span>
             @endif
         </span>
        <div class="flex-1">
            <input {!! $isDisabled() ? 'disabled' : null !!}
                   class="block w-full transition duration-75 border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500 dark:border-gray-600"
                   wire:model.defer="{{ $getStatePath() }}" placeholder="0,00" type="text"
                   x-mask:dynamic="$money($input, ',')" />
        </div>
    </div>
</x-dynamic-component>