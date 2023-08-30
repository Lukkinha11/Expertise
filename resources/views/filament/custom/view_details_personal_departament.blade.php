<table class="border-collapse table-auto w-full text-sm">
    <thead class="teste">
        <tr>
            <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-center">
                Empresa
            </th>
            <th class="border-b dark:border-slate-600 font-medium p-4 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-center">
                Qtde Funcionários
            </th>
            <th class="border-b dark:border-slate-600 font-medium p-4 pr-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-center">
                Qtde Sócios
            </th>
            <th class="border-b dark:border-slate-600 font-medium p-4 pr-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-center">
                Admissões
            </th>
            <th class="border-b dark:border-slate-600 font-medium p-4 pr-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-center">
                Demissões
            </th>
        </tr>
    </thead>
    <tbody class="teste bg-white dark:bg-slate-800">
        @foreach ($personalDepartament->companies as $item)
            @if ($item->pivot->date === $personalDepartament->date)
                <tr>
                    <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">
                        {{ $item->company_name }}
                    </td>
                    <td class="border-b border-slate-100 dark:border-slate-700 p-4 text-slate-500 dark:text-slate-400 text-center">
                        {{ $item->pivot->number_of_employees }}
                    </td>
                    <td class="border-b border-slate-100 dark:border-slate-700 p-4 pr-8 text-slate-500 dark:text-slate-400 text-center">
                        {{ $item->pivot->number_of_partners }}
                    </td>
                    <td class="border-b border-slate-100 dark:border-slate-700 p-4 pr-8 text-slate-500 dark:text-slate-400 text-center">
                        {{ $item->pivot->admissions }}
                    </td>
                    <td class="border-b border-slate-100 dark:border-slate-700 p-4 pr-8 text-slate-500 dark:text-slate-400 text-center">
                        {{ $item->pivot->layoffs }}
                    </td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>
