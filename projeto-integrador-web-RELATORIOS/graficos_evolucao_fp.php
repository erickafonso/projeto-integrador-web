<?php
session_start();

if (!isset($_SESSION['idUsuario']) || empty($_SESSION['idUsuario'])) {
    header('Location: usuario/login.php');
    exit();
}

$usuarioId = $_SESSION['idUsuario'];

include_once('usuario/conexao.php');

if (!isset($pdo)) {
    die("Erro: A conexão com o banco de dados não foi estabelecida.");
}

include('modelo/Conta.php');
include('modelo/Categoria.php');
include('modelo/FormaPagamento.php');

$contaModel = new Conta($pdo);
$categoriaModel = new Categoria($pdo);
$formaPagamentoModel = new FormaPagamento($pdo);

// Busca apenas as formas de pagamento do usuário logado
$formasPagamentoUsuario = $formaPagamentoModel->getFormasComIds($usuarioId);

function obterDatasMinMax($pdo, $usuarioId) {
    $sql = "SELECT MIN(dataPagamento) as min_date, MAX(dataPagamento) as max_date 
            FROM (
                SELECT dataPagamento FROM conta WHERE idUsuario = :usuarioId
                UNION ALL
                SELECT dataPagamento FROM despesa WHERE idUsuario = :usuarioId
            ) as datas";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$datasLimite = obterDatasMinMax($pdo, $usuarioId);

$dataInicio = isset($_POST['data_inicio']) ? $_POST['data_inicio'] : $datasLimite['min_date'];
$dataFim = isset($_POST['data_fim']) ? $_POST['data_fim'] : $datasLimite['max_date'];
$formaPagamentoSelecionada = isset($_POST['forma_pagamento']) ? (int)$_POST['forma_pagamento'] : null;

function buscarGastosPorFormaPagamento($pdo, $usuarioId, $formaPagamentoId, $dataInicio, $dataFim) {
    $sql = "SELECT DATE(dataPagamento) as data, SUM(valor) as total
            FROM conta
            WHERE idUsuario = :usuarioId 
              AND formaPagamento = :formaPagamentoId
              AND dataPagamento BETWEEN :inicio AND :fim
            GROUP BY DATE(dataPagamento)
            ORDER BY dataPagamento ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
    $stmt->bindParam(':formaPagamentoId', $formaPagamentoId, PDO::PARAM_INT);
    $stmt->bindParam(':inicio', $dataInicio);
    $stmt->bindParam(':fim', $dataFim);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function calcularTotalGastos($dadosGrafico) {
    $total = 0;
    foreach ($dadosGrafico as $gasto) {
        $total += $gasto['total'];
    }
    return $total;
}

$dadosGrafico = [];
$nomeFormaPagamentoSelecionada = '';
$totalGeral = 0;

if ($formaPagamentoSelecionada) {
    // Verifica se a forma de pagamento pertence ao usuário
    $formaPagamentoValida = false;
    foreach ($formasPagamentoUsuario as $formaPagamento) {
        if ($formaPagamento->idFormaPagamento == $formaPagamentoSelecionada) {
            $formaPagamentoValida = true;
            $nomeFormaPagamentoSelecionada = $formaPagamento->nome;
            break;
        }
    }
    
    if ($formaPagamentoValida) {
        $dadosGrafico = buscarGastosPorFormaPagamento($pdo, $usuarioId, $formaPagamentoSelecionada, $dataInicio, $dataFim);
        $totalGeral = calcularTotalGastos($dadosGrafico);
    } else {
        $formaPagamentoSelecionada = null;
    }
}

$datas = [];
$valores = [];

foreach ($dadosGrafico as $gasto) {
    $datas[] = date('d/m/Y', strtotime($gasto['data']));
    $valores[] = (float)$gasto['total'];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evolução de Gastos por Forma de Pagamento</title>
    <link rel="stylesheet" href="css/styles.css">
   
    <link rel="stylesheet" href="css/graficos.css">
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
        
        .filtro-group select,
        .filtro-group input {
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
                <li><a href="dashboard.php">Inicio</a></li>
                <li><a>|</a></li>
                <li><a href="contas.php">Contas</a></li>
                <li><a>|</a></li>
                <li><a href="despesas.php">Despesas</a></li>
                <li><a>|</a></li>
                <li><a href="formaPagamento.php">Formas de pagamento</a></li>
                <li><a>|</a></li>
                <li><a href="categorias.php">Categorias</a></li>
                <li><a>|</a></li>
                <li><a href="tabelas.php">Tabelas</a></li>
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
            <h2>Filtrar por Período</h2>
            <form method="POST" class="filtro-form">
                <div class="filtro-group">
                    <label for="forma_pagamento">Forma de Pagamento:</label>
                    <select id="forma_pagamento" name="forma_pagamento" required>
                        <option value="">Selecione uma forma</option>
                        <?php foreach ($formasPagamentoUsuario as $formaPagamento): ?>
                            <option value="<?= $formaPagamento->idFormaPagamento ?>" <?= ($formaPagamentoSelecionada == $formaPagamento->idFormaPagamento) ? 'selected' : '' ?>>
                                <?= $formaPagamento->nome ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filtro-group">
                    <label for="data_inicio">Data Início:</label>
                    <input type="date" id="data_inicio" name="data_inicio" value="<?= $dataInicio ?>" min="<?= $datasLimite['min_date'] ?>" max="<?= $datasLimite['max_date'] ?>" required>
                </div>
                
                <div class="filtro-group">
                    <label for="data_fim">Data Fim:</label>
                    <input type="date" id="data_fim" name="data_fim" value="<?= $dataFim ?>" min="<?= $datasLimite['min_date'] ?>" max="<?= $datasLimite['max_date'] ?>" required>
                </div>
                
                <button type="submit" class="filtro-btn">Aplicar Filtros</button>
            </form>
        </div>
        
        <!-- Container do gráfico à direita -->
        <div class="grafico-container">
            <h2>Evolução de Gastos por Forma de Pagamento</h2>
            <div class="info-periodo">
                Período: <?= date('d/m/Y', strtotime($dataInicio)) ?> a <?= date('d/m/Y', strtotime($dataFim)) ?>
            </div>
            
            <?php if ($formaPagamentoSelecionada && !empty($dadosGrafico)): ?>
                <div class="grafico-wrapper">
                    <canvas id="graficoLinha"></canvas>
                </div>
                
                <div class="info-total">
                    Você gastou R$ <?= number_format($totalGeral, 2, ',', '.') ?> usando <?= $nomeFormaPagamentoSelecionada ?>
                </div>
                
                <script>
                    const datas = <?= json_encode($datas) ?>;
                    const valores = <?= json_encode($valores) ?>;
                    const nomeFormaPagamento = '<?= $nomeFormaPagamentoSelecionada ?>';
                    
                    const ctx = document.getElementById('graficoLinha').getContext('2d');
                    const chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: datas,
                            datasets: [{
                                label: `Meus gastos com ${nomeFormaPagamento}`,
                                data: valores,
                                borderColor: '#3498db',
                                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.1,
                                pointBackgroundColor: '#3498db',
                                pointBorderColor: '#fff',
                                pointHoverRadius: 5,
                                pointHoverBackgroundColor: '#3498db',
                                pointHoverBorderColor: '#fff',
                                pointHitRadius: 10,
                                pointBorderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Valor (R$)',
                                        color: '#2c3e50',
                                        font: {
                                            weight: 'bold'
                                        }
                                    },
                                    ticks: {
                                        color: '#7f8c8d',
                                        callback: function(value) {
                                            return 'R$ ' + value.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.05)'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Data',
                                        color: '#2c3e50',
                                        font: {
                                            weight: 'bold'
                                        }
                                    },
                                    ticks: {
                                        color: '#7f8c8d'
                                    },
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.05)'
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    labels: {
                                        color: '#2c3e50',
                                        font: {
                                            weight: 'bold'
                                        }
                                    }
                                },
                                tooltip: {
                                    backgroundColor: '#2c3e50',
                                    titleColor: '#fff',
                                    bodyColor: '#fff',
                                    borderColor: 'rgba(0, 0, 0, 0.1)',
                                    borderWidth: 1,
                                    callbacks: {
                                        label: function(context) {
                                            return 'Gasto: R$ ' + context.raw.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                                        }
                                    }
                                }
                            }
                        }
                    });
                </script>
            <?php elseif ($formaPagamentoSelecionada && empty($dadosGrafico)): ?>
                <div class="info-total" style="background-color: #fff8e1; border-left-color: #ffc107;">
                    Nenhum gasto encontrado para <?= $nomeFormaPagamentoSelecionada ?> no período selecionado
                </div>
            <?php else: ?>
                <div class="info-total" style="background-color: #f8f9fa; border-left-color: #6c757d;">
                    <?= $formaPagamentoSelecionada === null && isset($_POST['forma_pagamento']) ? 
                        'Forma de pagamento inválida ou você não tem permissão para acessá-la' : 
                        'Selecione uma forma de pagamento e intervalo de datas para visualizar o gráfico' ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>