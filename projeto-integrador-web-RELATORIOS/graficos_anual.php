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

// Função para obter anos disponíveis
function obterAnosDisponiveis($pdo, $usuarioId) {
    $sql = "SELECT YEAR(MIN(dataPagamento)) as min_year, YEAR(MAX(dataPagamento)) as max_year 
            FROM (
                SELECT dataPagamento FROM conta WHERE idUsuario = :usuarioId
                UNION ALL
                SELECT dataPagamento FROM despesa WHERE idUsuario = :usuarioId
            ) as datas";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $anos = [];
    if ($result['min_year']) {
        for ($i = $result['min_year']; $i <= $result['max_year']; $i++) {
            $anos[] = $i;
        }
    } else {
        $anos[] = date('Y');
    }
    
    return $anos;
}

// Processa o filtro
$anoSelecionado = isset($_POST['ano']) ? $_POST['ano'] : date('Y');
$tipoGasto = isset($_POST['tipoGasto']) ? $_POST['tipoGasto'] : 'todos';
$anosDisponiveis = obterAnosDisponiveis($pdo, $_SESSION['idUsuario']);

// Função para buscar gastos mensais
function buscarGastosMensais($pdo, $usuarioId, $ano, $tipo) {
    $dataInicio = "$ano-01-01";
    $dataFim = "$ano-12-31";
    
    $resultado = array_fill(1, 12, 0);
    
    if ($tipo == 'todos' || $tipo == 'contas') {
        $sql = "SELECT MONTH(dataPagamento) as mes, SUM(valor) as total 
                FROM conta 
                WHERE idUsuario = :usuarioId AND dataPagamento BETWEEN :inicio AND :fim
                GROUP BY MONTH(dataPagamento)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->bindParam(':inicio', $dataInicio);
        $stmt->bindParam(':fim', $dataFim);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $resultado[$row['mes']] += $row['total'];
        }
    }
    
    if ($tipo == 'todos' || $tipo == 'despesas') {
        $sql = "SELECT MONTH(dataPagamento) as mes, SUM(valor) as total 
                FROM despesa 
                WHERE idUsuario = :usuarioId AND dataPagamento BETWEEN :inicio AND :fim
                GROUP BY MONTH(dataPagamento)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->bindParam(':inicio', $dataInicio);
        $stmt->bindParam(':fim', $dataFim);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $resultado[$row['mes']] += $row['total'];
        }
    }
    
    return $resultado;
}

// Busca os dados para o gráfico
$gastosMensais = buscarGastosMensais($pdo, $_SESSION['idUsuario'], $anoSelecionado, $tipoGasto);

// Prepara os dados para o JavaScript
$meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
$valores = array_values($gastosMensais);
$totalAnual = array_sum($valores);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="css/alteracoes.css">
    
    <link rel="stylesheet" href="css/graficos.css">
    <link rel="stylesheet" href="css/styles.css">
    <title>Gráfico Anual de Gastos</title>
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
            height: 70vh;
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
                <!-- Botão Sair com class 'logout' -->
                <li><a href="logout.php" class="logout">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div id="conteudo">
        <div class="grafico-container">
            <h2>Gráfico Anual de Gastos</h2>
            
            <form method="POST" class="filtro-container">
                <label for="ano">Ano:</label>
                <select id="ano" name="ano">
                    <?php foreach ($anosDisponiveis as $ano): ?>
                        <option value="<?= $ano ?>" <?= ($ano == $anoSelecionado) ? 'selected' : '' ?>>
                            <?= $ano ?>
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
                <canvas id="graficoAnual"></canvas>
            </div>
            
            <div class="info-total">
                Total anual (<?= $anoSelecionado ?>): R$ <?= number_format($totalAnual, 2, ',', '.') ?>
            </div>
        </div>
    </div>

    <script>
        // Dados para o gráfico
        const meses = <?= json_encode($meses) ?>;
        const valores = <?= json_encode($valores) ?>;
        const tipoGasto = '<?= $tipoGasto ?>';
        const totalAnual = <?= $totalAnual ?>;
        
        // Cores para os meses (12 cores distintas)
        const cores = [
            'rgba(255, 99, 132, 0.7)',
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 206, 86, 0.7)',
            'rgba(75, 192, 192, 0.7)',
            'rgba(153, 102, 255, 0.7)',
            'rgba(255, 159, 64, 0.7)',
            'rgba(199, 199, 199, 0.7)',
            'rgba(83, 102, 255, 0.7)',
            'rgba(40, 159, 64, 0.7)',
            'rgba(210, 99, 132, 0.7)',
            'rgba(120, 162, 235, 0.7)',
            'rgba(255, 206, 86, 0.7)'
        ];
        
        // Configuração inicial do gráfico (barras)
        let tipoGraficoAtual = 'bar';
        const ctx = document.getElementById('graficoAnual').getContext('2d');
        let chart = new Chart(ctx, {
            type: tipoGraficoAtual,
            data: {
                labels: meses,
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
                            text: 'Meses'
                        }
                    }
                } : {},
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const percent = ((value / totalAnual) * 100).toFixed(2);
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
            // Destrói o gráfico atual
            chart.destroy();
            
            // Alterna o tipo de gráfico
            tipoGraficoAtual = tipoGraficoAtual === 'bar' ? 'pie' : 'bar';
            
            // Atualiza o texto do botão
            this.textContent = tipoGraficoAtual === 'bar' 
                ? 'Alternar para Gráfico de Pizza' 
                : 'Alternar para Gráfico de Barras';
            
            // Cria o novo gráfico
            chart = new Chart(ctx, {
                type: tipoGraficoAtual,
                data: {
                    labels: meses,
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
                                text: 'Meses'
                            }
                        }
                    } : {},
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    const percent = ((value / totalAnual) * 100).toFixed(2);
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