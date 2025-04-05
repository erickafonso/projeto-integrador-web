<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['idUsuario'])) {
    // Caso não esteja logado, redireciona para a página de login
    header('Location: usuario/login.php');
    exit;
}

header('Content-Type: text/html; charset=UTF-8');

include_once('usuario/conexao.php'); // Inclui a conexão com o banco de dados
include('modelo/Conta.php'); // Inclui o modelo Conta
include('modelo/Categoria.php'); // Inclui o modelo da tabela Categoria
include('modelo/FormaPagamento.php'); // Inclui o modelo da tabela FormaPagamento

// Verifica se a conexão com o banco de dados foi realizada
if (!isset($pdo)) {
    die("Erro: A conexão não foi estabelecida.");
}

$contaModel = new Conta($pdo); // Passa a conexão PDO para o modelo Conta
$categoriaModel = new Categoria($pdo);  // Cria uma instância do modelo Categoria
$formaPagamentoModel = new FormaPagamento($pdo); // Cria uma instância do modelo FormaPagamento
$nomesCategorias = $categoriaModel->getNomesComIds();

// Função para obter as datas mais antigas e mais recentes
function obterDatasMinMax($pdo, $usuarioId) {
    $sql = "
        SELECT MIN(dataPagamento) AS data_inicio, MAX(dataPagamento) AS data_fim
        FROM conta
        WHERE idUsuario = :usuarioId
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $data_inicio = $result['data_inicio'];
    $data_fim = $result['data_fim'];

    // Buscando a data mínima e a data máxima das despesas
    $sql = "
        SELECT MIN(dataPagamento) AS data_inicio, MAX(dataPagamento) AS data_fim
        FROM despesa
        WHERE idUsuario = :usuarioId
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Comparando as datas para garantir que estamos pegando o intervalo correto
    if ($result['data_inicio'] && strtotime($result['data_inicio']) < strtotime($data_inicio)) {
        $data_inicio = $result['data_inicio'];
    }
    if ($result['data_fim'] && strtotime($result['data_fim']) > strtotime($data_fim)) {
        $data_fim = $result['data_fim'];
    }

    return ['data_inicio' => $data_inicio, 'data_fim' => $data_fim];
}

// Obter datas mínima e máxima para inicializar os campos
$datasMinMax = obterDatasMinMax($pdo, $_SESSION['idUsuario']);
$data_inicio = $datasMinMax['data_inicio'];
$data_fim = $datasMinMax['data_fim'];

// Processa a alteração ou exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['alterar'])) {
        $contaModel->alterar(
            $_POST['idConta'],
            $_POST['nome'],
            $_POST['valor'],
            $_POST['descricao'],
            $_POST['dataPagamento'],
            $_POST['dataVencimento'],
            $_POST['categoria'],
            $_POST['formaPagamento']
        );
    } elseif (isset($_POST['deletar'])) {
        $contaModel->deletar($_POST['idConta']);
    }

    // Atualiza as variáveis de datas com base no filtro do usuário
    if (isset($_POST['data_inicio']) && isset($_POST['data_fim'])) {
        $data_inicio = $_POST['data_inicio'];
        $data_fim = $_POST['data_fim'];
    }
}

// Função para buscar os dados de contas
function buscarContas($pdo, $usuarioId, $data_inicio, $data_fim) {
    $sql = "
        SELECT
            c.dataPagamento AS data,
            c.valor
        FROM
            conta c
        WHERE
            c.idUsuario = :usuarioId
            AND c.dataPagamento BETWEEN :data_inicio AND :data_fim
        ORDER BY
            c.dataPagamento ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
    $stmt->bindParam(':data_inicio', $data_inicio);
    $stmt->bindParam(':data_fim', $data_fim);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para buscar os dados de despesas
function buscarDespesas($pdo, $usuarioId, $data_inicio, $data_fim) {
    $sql = "
        SELECT
            d.dataPagamento AS data,
            d.valor
        FROM
            despesa d
        WHERE
            d.idUsuario = :usuarioId
            AND d.dataPagamento BETWEEN :data_inicio AND :data_fim
        ORDER BY
            d.dataPagamento ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
    $stmt->bindParam(':data_inicio', $data_inicio);
    $stmt->bindParam(':data_fim', $data_fim);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para somar os gastos (VERSÃO CORRIGIDA)
function somarGastos($pdo, $usuarioId, $tipoGasto, $data_inicio, $data_fim) {
    $gastosContas = [];
    $gastosDespesas = [];

    if ($tipoGasto == 'contas' || $tipoGasto == 'todos') {
        $contas = buscarContas($pdo, $usuarioId, $data_inicio, $data_fim);
        foreach ($contas as $conta) {
            $gastosContas[] = [
                'data' => $conta['data'],
                'valor' => $conta['valor'],
                'tipo' => 'conta'
            ];
        }
    }

    if ($tipoGasto == 'despesas' || $tipoGasto == 'todos') {
        $despesas = buscarDespesas($pdo, $usuarioId, $data_inicio, $data_fim);
        foreach ($despesas as $despesa) {
            $gastosDespesas[] = [
                'data' => $despesa['data'],
                'valor' => $despesa['valor'],
                'tipo' => 'despesa'
            ];
        }
    }

    // Combina os arrays mantendo a estrutura
    $gastosTotais = array_merge($gastosContas, $gastosDespesas);

    // Ordena mantendo a associação entre data e valor
    usort($gastosTotais, function($a, $b) {
        return strtotime($a['data']) - strtotime($b['data']);
    });

    // Agrupa por data mantendo os tipos separados
    $gastosPorData = [];
    foreach ($gastosTotais as $gasto) {
        $data = $gasto['data'];
        
        if (!isset($gastosPorData[$data])) {
            $gastosPorData[$data] = [
                'total' => 0,
                'contas' => 0,
                'despesas' => 0
            ];
        }
        
        $gastosPorData[$data]['total'] += $gasto['valor'];
        
        if ($gasto['tipo'] === 'conta') {
            $gastosPorData[$data]['contas'] += $gasto['valor'];
        } else {
            $gastosPorData[$data]['despesas'] += $gasto['valor'];
        }
    }

    // Prepara os arrays finais
    $datas = array_keys($gastosPorData);
    $valoresTotais = [];
    $valoresContas = [];
    $valoresDespesas = [];

    foreach ($gastosPorData as $data => $valores) {
        $valoresTotais[] = $valores['total'];
        $valoresContas[] = $valores['contas'];
        $valoresDespesas[] = $valores['despesas'];
    }

    return [
        'datas' => $datas,
        'valoresTotais' => $valoresTotais,
        'valoresContas' => $valoresContas,
        'valoresDespesas' => $valoresDespesas
    ];
}

// Verifica o tipo de gasto selecionado
$tipoGasto = isset($_POST['tipoGasto']) ? $_POST['tipoGasto'] : 'todos';

// Busca os gastos totais
$gastosTotais = somarGastos($pdo, $_SESSION['idUsuario'], $tipoGasto, $data_inicio, $data_fim);

// Prepara os dados para o gráfico
$datas = $gastosTotais['datas'];
$valoresTotais = $gastosTotais['valoresTotais'];
$valoresContas = $gastosTotais['valoresContas'];
$valoresDespesas = $gastosTotais['valoresDespesas'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/alteracoes.css">
    <link rel="stylesheet" href="css/nav.css">
    <link rel="stylesheet" href="css/graficos.css">
    <title>Gastos por Categoria</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <nav id="navMenu">
        <ul>
                <li><a href="index.php">Home</a></li>
                <li><a>|</a></li>
                <li><a href="contas.php">Contas</a></li>
                <li><a>|</a></li>
                <li><a href="despesas.php">Despesas</a></li>
                <li><a>|</a></li>
                <li><a href="formaPagamento.php">Formas de pagamento</a></li>
                <li><a>|</a></li>
                <li><a href="categorias.php">Categorias</a></li>
                <li><a>|</a></li>
                <li><a href="relatoriosv2.php">Relatórios</a></li>
                <li><a>|</a></li>
                <li><a href="graficos.php">Gráficos</a></li>
                <li><a>|</a></li>
                <!-- Botão Sair com class 'logout' -->
                <li><a href="logout.php" class="logout">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div id="conteudo">
        <h2>Total de Gastos por Categoria</h2>

        <form method="POST">
            <label for="tipoGasto">Selecione o tipo de gasto:</label>
            <select id="tipoGasto" name="tipoGasto">
                <option value="todos" <?php if ($tipoGasto == 'todos') echo 'selected'; ?>>Todos</option>
                <option value="contas" <?php if ($tipoGasto == 'contas') echo 'selected'; ?>>Contas</option>
                <option value="despesas" <?php if ($tipoGasto == 'despesas') echo 'selected'; ?>>Despesas</option>
            </select>

            <label for="data_inicio">Data Início:</label>
            <input type="date" name="data_inicio" value="<?php echo $data_inicio; ?>">

            <label for="data_fim">Data Fim:</label>
            <input type="date" name="data_fim" value="<?php echo $data_fim; ?>">

            <button type="submit">Filtrar</button>
        </form>

        <h2>Distribuição de Gastos</h2>
        <canvas id="graficoLinha" width="400" height="400"></canvas>
        <script>
            var datas = <?php echo json_encode($datas); ?>;
            var valoresTotais = <?php echo json_encode($valoresTotais); ?>;
            var valoresContas = <?php echo json_encode($valoresContas); ?>;
            var valoresDespesas = <?php echo json_encode($valoresDespesas); ?>;

            var ctx = document.getElementById('graficoLinha').getContext('2d');
            var graficoLinha = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: datas,
                    datasets: [
                        {
                            label: 'Gastos Totais',
                            data: valoresTotais,
                            borderColor: 'rgba(75, 192, 192, 1)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            fill: true
                        },
                        {
                            label: 'Contas',
                            data: valoresContas,
                            borderColor: 'rgba(54, 162, 235, 1)',
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            fill: true
                        },
                        {
                            label: 'Despesas',
                            data: valoresDespesas,
                            borderColor: 'rgba(255, 99, 132, 1)',
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    return 'R$ ' + tooltipItem.raw.toFixed(2);
                                }
                            }
                        }
                    }
                }
            });
        </script>
    </div>
</body>
</html>