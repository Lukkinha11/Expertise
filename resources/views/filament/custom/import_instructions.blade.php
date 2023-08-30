<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        img.center {
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center">
            <p>Para importar planilhas, é preciso que as mesmas estejam de acordo com a imagem abaixo.</p>
            <p>Lembre-se a <strong>PRIMEIRA LINHA</strong> deve conter o nome da coluna. A planilha deve seguir a seguinte ordem de informações:</p><br>
            <ul>
                <li><strong>Primeira coluna(A): </strong><span style="color: red;">Empresa;</span></li>
                <li><strong>Segunda coluna(B): </strong><span style="color: red;">Filial;</span></li>
                <li><strong>Terceira coluna(C): </strong><span style="color: red;">Nome Empresa;</span></li>
                <li><strong>Quarta coluna(D): </strong><span style="color: red;">CNPJ;</span></li>
                <li><strong>Quinta coluna(E): </strong><span style="color: red;">Resp. {{ $param }};</span></li>
                <li><strong>Sexta coluna(F): </strong><span style="color: red;">Ano/Mês;</span></li>
                @if ($param === "Folha")
                    <li><strong>Sétima coluna(G): </strong><span style="color: red;">Qtde Funcionários;</span></li>
                    <li><strong>Oitava coluna(H): </strong><span style="color: red;">Qtde Sócios;</span></li>
                    <li><strong>Nona coluna(I): </strong><span style="color: red;">Admissões;</span></li>
                    <li><strong>Décima coluna(J): </strong><span style="color: red;">Demissões;</span></li>
                @endif
            </ul>
        </div><br>
        @if ($param === "Contábil")
            <img class="center" src="{{ asset('images/exemplo_planilha_contabil.PNG') }}" alt="">
        @elseif($param === "Fiscal")
            <img class="center" src="{{ asset('images/exemplo_planilha_fiscal.png') }}" alt="">
        @elseif($param === "Folha")
            <img class="center" src="{{ asset('images/exemplo_planilha_dp.png') }}" alt="">
        @endif
    </div>
</body>
</html>