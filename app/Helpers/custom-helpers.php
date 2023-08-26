<?php

namespace App\Helpers;

if (!function_exists('format_document')) {
    function format_document(string $cpfOrCnpj): string
    {
        $CPF_LENGTH = 11;
        $cnpjCpf = preg_replace("/\D/", '', $cpfOrCnpj);
        
        if (strlen($cnpjCpf) === $CPF_LENGTH) {
            return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $cnpjCpf);
        } 
        
        return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpjCpf);
    }
}