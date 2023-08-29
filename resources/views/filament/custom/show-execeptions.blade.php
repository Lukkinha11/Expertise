<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ asset('css/filament/filament/custom_exception.css') }}" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.27/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Erros</title>
</head>
<body>
    <div class="container">
        @if (session('import_errors') && session('import_errors_expire') && now()->lt(session('import_errors_expire')))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var messages = {!! json_encode(session('import_errors')) !!};
                    var errorList = '<ul style="text-align: center; max-height: 400px; overflow-y: auto;"><br>';
                    var uniqueErrors = new Set(); // Conjunto para armazenar erros únicos
                    var uniqueRows = new Set(); // Conjunto para armazenar linhas únicas
                    messages.forEach(function(message) {
                        console.log(message.row);
                        message.errors.forEach(function(error) {
                            uniqueErrors.add(error); // Adiciona cada erro ao conjunto
                        });
                        
                        uniqueRows.add(message.row);
                    });
                                    
                    uniqueErrors.forEach(function(error) {
                        uniqueRows.forEach(function(row) {
                            errorList += '<li class="mb-2">' + error + ' ' + 'Linha: ' + row + '</li>'; // Exibe apenas erros únicos
                        });
                    });
                    
                    errorList += '</ul>';

                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...<br> Foram encontradas algumas irregularidades:',
                        html: errorList,
                        width: '850px',
                        allowOutsideClick: false, // Impedir que a modal seja fechada clicando fora dela
                        showConfirmButton: false, // Não exibir o botão de "OK"
                    });
                });
            </script>
        @endif
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>