<?php
session_start();

if (!isset($_SESSION['idUsuario'])) {
    header('Location: usuario/login.php');
    exit;
}

header('Content-Type: text/html; charset=UTF-8');

include_once('usuario/conexao.php');
include('modelo/Conta.php');
include('modelo/Categoria.php');
include('modelo/FormaPagamento.php');

if (!isset($pdo)) {
    die("Erro: A conexão não foi estabelecida.");
}

$contaModel = new Conta($pdo);
$categoriaModel = new Categoria($pdo);
$formaPagamentoModel = new FormaPagamento($pdo);

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

    $sql = "
        SELECT MIN(dataPagamento) AS data_inicio, MAX(dataPagamento) AS data_fim
        FROM despesa
        WHERE idUsuario = :usuarioId
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['data_inicio'] && strtotime($result['data_inicio']) < strtotime($data_inicio)) {
        $data_inicio = $result['data_inicio'];
    }
    if ($result['data_fim'] && strtotime($result['data_fim']) > strtotime($data_fim)) {
        $data_fim = $result['data_fim'];
    }

    return ['data_inicio' => $data_inicio, 'data_fim' => $data_fim];
}

// Configuração de datas
$datasMinMax = obterDatasMinMax($pdo, $_SESSION['idUsuario']);
$dataInicio = isset($_POST['data_inicio']) ? $_POST['data_inicio'] : $datasMinMax['data_inicio'];
$dataFim = isset($_POST['data_fim']) ? $_POST['data_fim'] : $datasMinMax['data_fim'];
$tipoGasto = isset($_POST['tipoGasto']) ? $_POST['tipoGasto'] : 'todos';

// Funções para buscar dados
function buscarContas($pdo, $usuarioId, $dataInicio, $dataFim) {
    $sql = "SELECT dataPagamento AS data, valor FROM conta 
            WHERE idUsuario = :usuarioId AND dataPagamento BETWEEN :dataInicio AND :dataFim
            ORDER BY dataPagamento ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
    $stmt->bindParam(':dataInicio', $dataInicio);
    $stmt->bindParam(':dataFim', $dataFim);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function buscarDespesas($pdo, $usuarioId, $dataInicio, $dataFim) {
    $sql = "SELECT dataPagamento AS data, valor FROM despesa 
            WHERE idUsuario = :usuarioId AND dataPagamento BETWEEN :dataInicio AND :dataFim
            ORDER BY dataPagamento ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
    $stmt->bindParam(':dataInicio', $dataInicio);
    $stmt->bindParam(':dataFim', $dataFim);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function processarGastos($contas, $despesas, $tipoGasto) {
    $gastos = [];
    
    if ($tipoGasto == 'contas' || $tipoGasto == 'todos') {
        foreach ($contas as $conta) {
            $dataFormatada = date('d/m/Y', strtotime($conta['data']));
            $gastos[] = ['data' => $dataFormatada, 'valor' => $conta['valor'], 'tipo' => 'conta', 'dataOriginal' => $conta['data']];
        }
    }
    
    if ($tipoGasto == 'despesas' || $tipoGasto == 'todos') {
        foreach ($despesas as $despesa) {
            $dataFormatada = date('d/m/Y', strtotime($despesa['data']));
            $gastos[] = ['data' => $dataFormatada, 'valor' => $despesa['valor'], 'tipo' => 'despesa', 'dataOriginal' => $despesa['data']];
        }
    }
    
    usort($gastos, function($a, $b) {
        return strtotime($a['dataOriginal']) - strtotime($b['dataOriginal']);
    });
    
    $gastosPorData = [];
    foreach ($gastos as $gasto) {
        $data = $gasto['data']; // Já formatada como d/m/Y
        if (!isset($gastosPorData[$data])) {
            $gastosPorData[$data] = ['total' => 0, 'contas' => 0, 'despesas' => 0];
        }
        $gastosPorData[$data]['total'] += $gasto['valor'];
        if ($gasto['tipo'] === 'conta') {
            $gastosPorData[$data]['contas'] += $gasto['valor'];
        } else {
            $gastosPorData[$data]['despesas'] += $gasto['valor'];
        }
    }
    
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
        'valoresDespesas' => $valoresDespesas,
        'totalGeral' => array_sum($valoresTotais)
    ];
}

// Buscar e processar dados
$contas = buscarContas($pdo, $_SESSION['idUsuario'], $dataInicio, $dataFim);
$despesas = buscarDespesas($pdo, $_SESSION['idUsuario'], $dataInicio, $dataFim);
$gastos = processarGastos($contas, $despesas, $tipoGasto);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evolução de Gastos</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        
        .content-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .dashboard-container {
            max-width: 1200px;
            width: 100%;
            display: flex;
            gap: 20px;
            margin: auto;
        }
        
        .filtro-container {
            flex: 1;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            height: fit-content;
            align-self: center;
        }
        
        .grafico-container {
            flex: 3;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            align-self: center;
        }
        
        .grafico-wrapper {
            position: relative;
            height: 60vh;
            margin-bottom: 20px;
        }
        
        canvas {
            max-width: 100%;
            height: 100% !important;
        }
        
        .info-total {
            margin-top: 15px;
            font-size: 1.1em;
            font-weight: bold;
            text-align: center;
        }
        
        .filtro-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .filtro-group {
            display: flex;
            flex-direction: column;
        }
        
        .filtro-group label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .filtro-group input, .filtro-group select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .filtro-btn {
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .filtro-btn:hover {
            background-color: #45a049;
        }
        
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        
        .info-periodo {
            margin-bottom: 15px;
            font-style: italic;
            color: #555;
        }
    </style>
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
                <li><a href="logout.php" class="logout">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div class="dashboard-container">
        <!-- Container de filtros à esquerda -->
        <div class="filtro-container">
            <h2>Filtrar Dados</h2>
            <form method="POST" class="filtro-form">
                <div class="filtro-group">
                    <label for="tipoGasto">Tipo de Gasto:</label>
                    <select id="tipoGasto" name="tipoGasto">
                        <option value="todos" <?= $tipoGasto == 'todos' ? 'selected' : '' ?>>Todos</option>
                        <option value="contas" <?= $tipoGasto == 'contas' ? 'selected' : '' ?>>Contas</option>
                        <option value="despesas" <?= $tipoGasto == 'despesas' ? 'selected' : '' ?>>Despesas</option>
                    </select>
                </div>
                
                <div class="filtro-group">
                    <label for="data_inicio">Data Início:</label>
                    <input type="date" id="data_inicio" name="data_inicio" value="<?= $dataInicio ?>" required>
                </div>
                
                <div class="filtro-group">
                    <label for="data_fim">Data Fim:</label>
                    <input type="date" id="data_fim" name="data_fim" value="<?= $dataFim ?>" required>
                </div>
                
                <button type="submit" class="filtro-btn">Aplicar Filtros</button>
            </form>
        </div>
        
        <!-- Container do gráfico à direita -->
        <div class="grafico-container">
            <h2>Evolução de Gastos ao Longo do Tempo</h2>
            <div class="info-periodo">
                Período: <?= date('d/m/Y', strtotime($dataInicio)) ?> a <?= date('d/m/Y', strtotime($dataFim)) ?>
                <?php if ($tipoGasto != 'todos'): ?>
                    | Mostrando apenas: <?= $tipoGasto == 'contas' ? 'Contas' : 'Despesas' ?>
                <?php endif; ?>
            </div>
            
            <div class="grafico-wrapper">
                <canvas id="graficoLinha"></canvas>
            </div>
            
            <div class="info-total">
                Total no período: R$ <?= number_format($gastos['totalGeral'], 2, ',', '.') ?>
            </div>
        </div>
    </div>

    <script>
        // Dados para o gráfico
        const datas = <?= json_encode($gastos['datas']) ?>;
        const valoresTotais = <?= json_encode($gastos['valoresTotais']) ?>;
        const valoresContas = <?= json_encode($gastos['valoresContas']) ?>;
        const valoresDespesas = <?= json_encode($gastos['valoresDespesas']) ?>;
        const tipoGasto = '<?= $tipoGasto ?>';
        
        // Configuração do gráfico
        const ctx = document.getElementById('graficoLinha').getContext('2d');
        const datasets = [];
        
        if (tipoGasto === 'todos' || tipoGasto === 'contas') {
            datasets.push({
                label: 'Contas',
                data: valoresContas,
                borderColor: '#3366FF',
                backgroundColor: 'rgba(51, 102, 255, 0.1)',
                fill: true,
                tension: 0.4
            });
        }
        
        if (tipoGasto === 'todos' || tipoGasto === 'despesas') {
            datasets.push({
                label: 'Despesas',
                data: valoresDespesas,
                borderColor: '#FF5733',
                backgroundColor: 'rgba(255, 87, 51, 0.1)',
                fill: true,
                tension: 0.4
            });
        }
        
        if (tipoGasto === 'todos') {
            datasets.push({
                label: 'Total',
                data: valoresTotais,
                borderColor: '#4CAF50',
                backgroundColor: 'rgba(76, 175, 80, 0.1)',
                fill: true,
                tension: 0.4
            });
        }
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: datas,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: R$ ${context.raw.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Valor (R$)'
                        },
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Data'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>