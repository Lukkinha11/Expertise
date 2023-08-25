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
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        @if (session('import_errors'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var messages = {!! json_encode(session('messages')) !!};
                    var errorList = '<ul style="text-align: center; max-height: 400px; overflow-y: auto;"><br>';
                    var uniqueErrors = new Set(); // Conjunto para armazenar erros únicos
                    messages.forEach(function(message) {
                        message.errors.forEach(function(error) {
                            uniqueErrors.add(error); // Adiciona cada erro ao conjunto
                        });
                    });
                                    
                    uniqueErrors.forEach(function(error) {
                        errorList += '<li class="mb-2">' + error + '</li>'; // Exibe apenas erros únicos
                    });

                    errorList += '</ul>';

                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...<br> Foram encontradas algumas irregularidades:',
                        html: errorList,
                        width: '850px',
                        allowOutsideClick: false, // Impedir que a modal seja fechada clicando fora dela
                        showConfirmButton: false, // Não exibir o botão de "OK"
                        footer: '<button id="openInstructions">Instruções</button>', // Adicione o botão de Instruções
                    });

                    document.getElementById('openInstructions').addEventListener('click', function() {
                        Swal.fire({
                            title: 'Instruções de Importação',
                            text: 'Aqui estão as instruções sobre como importar os dados corretamente...',
                            confirmButtonText: 'Entendi',
                        });
                    });
                    
                    // Adicione o evento de clique para o botão "Instruções"
                    document.getElementById('openInstructions').addEventListener('click', function() {
                            // Exibir a modal de instruções
                            document.getElementById('instructionsModal').style.display = 'block';
                        });

                        // Evento de clique para o botão "Entendi" na modal de instruções
                        document.getElementById('closeInstructions').addEventListener('click', function() {
                            // Ocultar a modal de instruções
                            document.getElementById('instructionsModal').style.display = 'none';
                        });
                    });
            </script>

            <div id="instructionsModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <h2>Instruções de Importação</h2>
                    <p>Aqui estão as instruções sobre como importar os dados corretamente...</p>
                    <button id="closeInstructions">Entendi</button>
                </div>
            </div>
        @endif
    </div>
</body>
</html>