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
$nomesCategorias = $categoriaModel->getNomesComIds();

// Configuração de datas (mês atual como padrão)
$dataInicio = isset($_POST['data_inicio']) ? $_POST['data_inicio'] : date('Y-m-01');
$dataFim = isset($_POST['data_fim']) ? $_POST['data_fim'] : date('Y-m-t');

// Buscar contas no período selecionado
$usuarioId = $_SESSION['idUsuario'];
$contasPeriodo = buscarContasPorIntervalo($pdo, $usuarioId, $dataInicio, $dataFim);

// Preparar dados para o gráfico
$nomesContas = [];
$valoresContas = [];
$totalGeral = 0;

foreach ($contasPeriodo as $conta) {
    $nomesContas[] = $conta['nome'];
    $valoresContas[] = $conta['valor'];
    $totalGeral += $conta['valor'];
}

function buscarContasPorIntervalo($pdo, $usuarioId, $dataInicio, $dataFim) {
    $sql = "SELECT nome, valor FROM conta 
            WHERE idUsuario = :usuarioId 
            AND dataPagamento BETWEEN :dataInicio AND :dataFim
            ORDER BY dataPagamento";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
    $stmt->bindParam(':dataInicio', $dataInicio);
    $stmt->bindParam(':dataFim', $dataFim);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gastos por Período</title>
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
        
        .alternar-grafico {
            display: block;
            margin: 10px auto;
            padding: 8px 16px;
            background-color: #303030;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .alternar-grafico:hover {
            background-color: #202020;
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
            <h2>Distribuição de Gastos por Período</h2>
            <div class="info-periodo">
                Período: <?= date('d/m/Y', strtotime($dataInicio)) ?> a <?= date('d/m/Y', strtotime($dataFim)) ?>
            </div>
            
            <div class="grafico-wrapper">
                <canvas id="graficoContas"></canvas>
            </div>
            
            <button id="alternarGrafico" class="alternar-grafico">Alternar para Gráfico de Pizza</button>
            
            <div class="info-total">
                Total no período: R$ <?= number_format($totalGeral, 2, ',', '.') ?>
            </div>
        </div>
    </div>

    <script>
        // Dados para o gráfico
        const contasLabels = <?= json_encode($nomesContas) ?>;
        const contasValores = <?= json_encode($valoresContas) ?>;
        const totalGeral = <?= $totalGeral ?>;
        
        // Cores para as barras
        const cores = [
            '#FF5733', '#33FF57', '#3357FF', '#FF8333', '#33C4FF', '#FF3399',
            '#8E44AD', '#F39C12', '#16A085', '#C0392B', '#2980B9', '#27AE60',
            '#D35400', '#9B59B6', '#3498DB', '#E74C3C', '#1ABC9C', '#F1C40F'
        ];
        
        // Configuração inicial do gráfico (Barras)
        let tipoGraficoAtual = 'bar';
        const ctx = document.getElementById('graficoContas').getContext('2d');
        let chart = new Chart(ctx, {
            type: tipoGraficoAtual,
            data: {
                labels: contasLabels,
                datasets: [{
                    label: 'Valor (R$)',
                    data: contasValores,
                    backgroundColor: cores,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const percent = ((value / totalGeral) * 100).toFixed(2);
                                return [
                                    `${context.label}: R$ ${value.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`,
                                    `Percentual: ${percent}%`
                                ];
                            }
                        }
                    },
                    legend: {
                        display: false
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
                            text: 'Contas'
                        }
                    }
                }
            }
        });
        
        // Função para alternar entre gráfico de barras e pizza
        document.getElementById('alternarGrafico').addEventListener('click', function() {
            chart.destroy();
            
            tipoGraficoAtual = tipoGraficoAtual === 'bar' ? 'pie' : 'bar';
            
            this.textContent = tipoGraficoAtual === 'bar' 
                ? 'Alternar para Gráfico de Pizza' 
                : 'Alternar para Gráfico de Barras';
            
            chart = new Chart(ctx, {
                type: tipoGraficoAtual,
                data: {
                    labels: contasLabels,
                    datasets: [{
                        label: 'Valor (R$)',
                        data: contasValores,
                        backgroundColor: tipoGraficoAtual === 'bar' ? cores : cores.slice(0, contasLabels.length),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    const percent = ((value / totalGeral) * 100).toFixed(2);
                                    return [
                                        `${context.label}: R$ ${value.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`,
                                        `Percentual: ${percent}%`
                                    ];
                                }
                            }
                        },
                        legend: {
                            display: tipoGraficoAtual === 'pie',
                            position: 'right',
                            labels: {
                                boxWidth: 12
                            }
                        }
                    },
                    scales: tipoGraficoAtual === 'bar' ? {
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
                                text: 'Contas'
                            }
                        }
                    } : {}
                }
            });
        });
    </script>
</body>
</html>