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

// Processa os filtros
$mesSelecionado = isset($_POST['mes']) ? $_POST['mes'] : date('Y-m');
$tipoGasto = isset($_POST['tipoGasto']) ? $_POST['tipoGasto'] : 'todos';

// Gera lista de meses disponíveis (últimos 12 meses)
$mesesDisponiveis = [];
for ($i = 0; $i < 12; $i++) {
    $mes = date('Y-m', strtotime("-$i months"));
    $mesesDisponiveis[] = $mes;
}

// Função para buscar gastos diários
function buscarGastosDiarios($pdo, $usuarioId, $mes, $tipo) {
    $dataInicio = "$mes-01";
    $ultimoDia = date('t', strtotime($dataInicio));
    $dataFim = "$mes-$ultimoDia";
    
    // Inicializa array com todos os dias do mês
    $diasNoMes = range(1, $ultimoDia);
    $resultado = array_fill_keys($diasNoMes, 0);
    
    if ($tipo == 'todos' || $tipo == 'contas') {
        $sql = "SELECT DAY(dataPagamento) as dia, SUM(valor) as total 
                FROM conta 
                WHERE idUsuario = :usuarioId AND dataPagamento BETWEEN :inicio AND :fim
                GROUP BY dia";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->bindParam(':inicio', $dataInicio);
        $stmt->bindParam(':fim', $dataFim);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $resultado[$row['dia']] += $row['total'];
        }
    }
    
    if ($tipo == 'todos' || $tipo == 'despesas') {
        $sql = "SELECT DAY(dataPagamento) as dia, SUM(valor) as total 
                FROM despesa 
                WHERE idUsuario = :usuarioId AND dataPagamento BETWEEN :inicio AND :fim
                GROUP BY dia";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->bindParam(':inicio', $dataInicio);
        $stmt->bindParam(':fim', $dataFim);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $resultado[$row['dia']] += $row['total'];
        }
    }
    
    return $resultado;
}

// Busca os dados para o gráfico
$gastosDiarios = buscarGastosDiarios($pdo, $_SESSION['idUsuario'], $mesSelecionado, $tipoGasto);

// Prepara os dados para o JavaScript
$dias = array_keys($gastosDiarios);
$valores = array_values($gastosDiarios);
$totalMensal = array_sum($valores);
$nomeMes = date('F Y', strtotime($mesSelecionado . '-01'));
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráfico Mensal de Gastos</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/nav.css">
    <link rel="stylesheet" href="css/graficos.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .grafico-container {
            margin: 20px 0;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .filtro-container {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filtro-container select, .filtro-container button {
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        
        .filtro-container button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        
        .filtro-container button:hover {
            background-color: #45a049;
        }
        
        .grafico-wrapper {
            position: relative;
            height: 60vh;
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
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a>|</a></li>
                <li><a href="logout.php" class="logout">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div id="conteudo">
        <div class="grafico-container">
            <h2>Gráfico Mensal de Gastos</h2>
            
            <form method="POST" class="filtro-container">
                <label for="mes">Mês:</label>
                <select id="mes" name="mes">
                    <?php foreach ($mesesDisponiveis as $mes): ?>
                        <option value="<?= $mes ?>" <?= ($mes == $mesSelecionado) ? 'selected' : '' ?>>
                            <?= date('F Y', strtotime($mes . '-01')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <label for="tipoGasto">Tipo de Gasto:</label>
                <select id="tipoGasto" name="tipoGasto">
                    <option value="todos" <?= ($tipoGasto == 'todos') ? 'selected' : '' ?>>Total (Contas + Despesas)</option>
                    <option value="contas" <?= ($tipoGasto == 'contas') ? 'selected' : '' ?>>Apenas Contas</option>
                    <option value="despesas" <?= ($tipoGasto == 'despesas') ? 'selected' : '' ?>>Apenas Despesas</option>
                </select>
                
                <button type="submit">Aplicar Filtros</button>
            </form>
            
            <button id="alternarGrafico" class="alternar-grafico">Alternar para Gráfico de Pizza</button>
            
            <div class="grafico-wrapper">
                <canvas id="graficoMensal"></canvas>
            </div>
            
            <div class="info-total">
                Total mensal (<?= date('F Y', strtotime($mesSelecionado . '-01')) ?>): R$ <?= number_format($totalMensal, 2, ',', '.') ?>
            </div>
        </div>
    </div>

    <script>
        // Dados para o gráfico
        const dias = <?= json_encode($dias) ?>;
        const valores = <?= json_encode($valores) ?>;
        const tipoGasto = '<?= $tipoGasto ?>';
        const totalMensal = <?= $totalMensal ?>;
        
        // Cores para os gráficos
        const cores = dias.map((_, i) => {
            const hue = (i * 30) % 360;
            return `hsla(${hue}, 70%, 60%, 0.7)`;
        });
        
        // Configuração inicial do gráfico (barras)
        let tipoGraficoAtual = 'bar';
        const ctx = document.getElementById('graficoMensal').getContext('2d');
        let chart = new Chart(ctx, {
            type: tipoGraficoAtual,
            data: {
                labels: dias,
                datasets: [{
                    label: `Gastos em ${tipoGasto === 'todos' ? 'Total' : tipoGasto === 'contas' ? 'Contas' : 'Despesas'}`,
                    data: valores,
                    backgroundColor: tipoGraficoAtual === 'bar' ? 
                        (tipoGasto === 'contas' ? 'rgba(54, 162, 235, 0.7)' : 
                         tipoGasto === 'despesas' ? 'rgba(255, 99, 132, 0.7)' : 
                         'rgba(75, 192, 192, 0.7)') : 
                        cores,
                    borderColor: tipoGraficoAtual === 'bar' ? 
                        (tipoGasto === 'contas' ? 'rgba(54, 162, 235, 1)' : 
                         tipoGasto === 'despesas' ? 'rgba(255, 99, 132, 1)' : 
                         'rgba(75, 192, 192, 1)') : 
                        cores.map(c => c.replace('0.7', '1')),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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
                            text: 'Dias do Mês'
                        }
                    }
                } : {},
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const percent = totalMensal > 0 ? ((value / totalMensal) * 100).toFixed(2) : 0;
                                return [
                                    `Valor: R$ ${value.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`,
                                    `Percentual: ${percent}%`
                                ];
                            }
                        }
                    },
                    legend: {
                        display: tipoGraficoAtual === 'pie',
                        position: 'right'
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
                    labels: dias,
                    datasets: [{
                        label: `Gastos em ${tipoGasto === 'todos' ? 'Total' : tipoGasto === 'contas' ? 'Contas' : 'Despesas'}`,
                        data: valores,
                        backgroundColor: tipoGraficoAtual === 'bar' ? 
                            (tipoGasto === 'contas' ? 'rgba(54, 162, 235, 0.7)' : 
                             tipoGasto === 'despesas' ? 'rgba(255, 99, 132, 0.7)' : 
                             'rgba(75, 192, 192, 0.7)') : 
                            cores,
                        borderColor: tipoGraficoAtual === 'bar' ? 
                            (tipoGasto === 'contas' ? 'rgba(54, 162, 235, 1)' : 
                             tipoGasto === 'despesas' ? 'rgba(255, 99, 132, 1)' : 
                             'rgba(75, 192, 192, 1)') : 
                            cores.map(c => c.replace('0.7', '1')),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
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
                                text: 'Dias do Mês'
                            }
                        }
                    } : {},
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    const percent = totalMensal > 0 ? ((value / totalMensal) * 100).toFixed(2) : 0;
                                    return [
                                        `Valor: R$ ${value.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`,
                                        `Percentual: ${percent}%`
                                    ];
                                }
                            }
                        },
                        legend: {
                            display: tipoGraficoAtual === 'pie',
                            position: 'right'
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>