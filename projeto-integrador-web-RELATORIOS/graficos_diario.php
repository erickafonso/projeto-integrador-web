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

// Função para obter o intervalo de datas da semana (segunda a domingo) a partir de uma data
function getSemana($data = null) {
    $data = $data ?: date('Y-m-d');
    $diaSemana = date('N', strtotime($data)); // 1 (segunda) a 7 (domingo)
    
    $segunda = date('Y-m-d', strtotime($data . ' - ' . ($diaSemana - 1) . ' days'));
    $domingo = date('Y-m-d', strtotime($segunda . ' + 6 days'));
    
    return [
        'inicio' => $segunda,
        'fim' => $domingo,
        'dias' => [
            'Segunda' => $segunda,
            'Terça' => date('Y-m-d', strtotime($segunda . ' + 1 day')),
            'Quarta' => date('Y-m-d', strtotime($segunda . ' + 2 days')),
            'Quinta' => date('Y-m-d', strtotime($segunda . ' + 3 days')),
            'Sexta' => date('Y-m-d', strtotime($segunda . ' + 4 days')),
            'Sábado' => date('Y-m-d', strtotime($segunda . ' + 5 days')),
            'Domingo' => $domingo
        ]
    ];
}

// Função para buscar gastos por dia da semana
function buscarGastosSemanais($pdo, $usuarioId, $dataInicio, $dataFim, $tipo) {
    // Inicializa o array com todos os dias da semana
    $diasSemana = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'];
    $resultado = array_fill_keys($diasSemana, 0);
    
    // Consulta para contas (se necessário)
    if ($tipo == 'todos' || $tipo == 'contas') {
        $sql = "SELECT DATE_FORMAT(dataPagamento, '%W') as dia_semana, SUM(valor) as total 
                FROM conta 
                WHERE idUsuario = :usuarioId AND dataPagamento BETWEEN :inicio AND :fim
                GROUP BY dia_semana";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->bindParam(':inicio', $dataInicio);
        $stmt->bindParam(':fim', $dataFim);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Converte o nome do dia em inglês para português
            $dia = '';
            switch($row['dia_semana']) {
                case 'Monday': $dia = 'Segunda'; break;
                case 'Tuesday': $dia = 'Terça'; break;
                case 'Wednesday': $dia = 'Quarta'; break;
                case 'Thursday': $dia = 'Quinta'; break;
                case 'Friday': $dia = 'Sexta'; break;
                case 'Saturday': $dia = 'Sábado'; break;
                case 'Sunday': $dia = 'Domingo'; break;
            }
            if ($dia) {
                $resultado[$dia] += $row['total'];
            }
        }
    }
    
    // Consulta para despesas (se necessário)
    if ($tipo == 'todos' || $tipo == 'despesas') {
        $sql = "SELECT DATE_FORMAT(dataPagamento, '%W') as dia_semana, SUM(valor) as total 
                FROM despesa 
                WHERE idUsuario = :usuarioId AND dataPagamento BETWEEN :inicio AND :fim
                GROUP BY dia_semana";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->bindParam(':inicio', $dataInicio);
        $stmt->bindParam(':fim', $dataFim);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Converte o nome do dia em inglês para português
            $dia = '';
            switch($row['dia_semana']) {
                case 'Monday': $dia = 'Segunda'; break;
                case 'Tuesday': $dia = 'Terça'; break;
                case 'Wednesday': $dia = 'Quarta'; break;
                case 'Thursday': $dia = 'Quinta'; break;
                case 'Friday': $dia = 'Sexta'; break;
                case 'Saturday': $dia = 'Sábado'; break;
                case 'Sunday': $dia = 'Domingo'; break;
            }
            if ($dia) {
                $resultado[$dia] += $row['total'];
            }
        }
    }
    
    // Reorganiza o array para manter a ordem correta dos dias
    $resultadoOrdenado = [];
    foreach ($diasSemana as $dia) {
        $resultadoOrdenado[$dia] = $resultado[$dia];
    }
    
    return $resultadoOrdenado;
}

// Obtém a semana selecionada (ou a atual se não houver seleção)
$dataSelecionada = isset($_POST['data_segunda']) ? $_POST['data_segunda'] : date('Y-m-d');
$semanaAtual = getSemana($dataSelecionada);
$tipoGasto = isset($_POST['tipoGasto']) ? $_POST['tipoGasto'] : 'todos';

// Busca os dados para o gráfico
$gastosSemanais = buscarGastosSemanais($pdo, $_SESSION['idUsuario'], $semanaAtual['inicio'], $semanaAtual['fim'], $tipoGasto);

// Prepara os dados para o JavaScript
$dias = array_keys($gastosSemanais);
$valores = array_values($gastosSemanais);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráfico Semanal de Gastos</title>
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
                    <label for="data_segunda">Segunda-feira:</label>
                    <input type="date" id="data_segunda" name="data_segunda" 
                           value="<?= $semanaAtual['inicio'] ?>" 
                           min="2020-01-01" 
                           max="<?= date('Y-m-d') ?>" 
                           required
                           onchange="this.form.submit()">
                </div>
                
                <div class="filtro-group">
                    <label for="tipoGasto">Tipo de Gasto:</label>
                    <select id="tipoGasto" name="tipoGasto" onchange="this.form.submit()">
                        <option value="todos" <?= ($tipoGasto == 'todos') ? 'selected' : '' ?>>Total (Contas + Despesas)</option>
                        <option value="contas" <?= ($tipoGasto == 'contas') ? 'selected' : '' ?>>Apenas Contas</option>
                        <option value="despesas" <?= ($tipoGasto == 'despesas') ? 'selected' : '' ?>>Apenas Despesas</option>
                    </select>
                </div>
                
                <div class="info-total">
                    Semana selecionada:<br>
                    <?= date('d/m/Y', strtotime($semanaAtual['inicio'])) ?> a <?= date('d/m/Y', strtotime($semanaAtual['fim'])) ?>
                </div>
            </form>
        </div>
        
        <!-- Container do gráfico à direita -->
        <div class="grafico-container">
            <h2>Gráfico Semanal de Gastos</h2>
            <div class="info-semana">
                Visualizando gastos de <?= date('d/m/Y', strtotime($semanaAtual['inicio'])) ?> (Segunda) 
                a <?= date('d/m/Y', strtotime($semanaAtual['fim'])) ?> (Domingo)
            </div>
            
            <div class="grafico-wrapper">
                <canvas id="graficoBarrasSemanal"></canvas>
            </div>
            
            <div class="info-total">
                Total da semana: R$ <?= number_format(array_sum($valores), 2, ',', '.') ?>
            </div>
        </div>
    </div>

    <script>
        // Dados para o gráfico
        const dias = <?= json_encode($dias) ?>;
        const valores = <?= json_encode($valores) ?>;
        const tipoGasto = '<?= $tipoGasto ?>';
        
        // Cores baseadas no tipo de gasto selecionado
        let backgroundColor;
        if (tipoGasto === 'contas') {
            backgroundColor = 'rgba(54, 162, 235, 0.7)';
        } else if (tipoGasto === 'despesas') {
            backgroundColor = 'rgba(255, 99, 132, 0.7)';
        } else {
            backgroundColor = 'rgba(75, 192, 192, 0.7)';
        }
        
        // Configuração do gráfico
        const ctx = document.getElementById('graficoBarrasSemanal').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: dias,
                datasets: [{
                    label: `Gastos ${tipoGasto === 'todos' ? 'Totais' : tipoGasto === 'contas' ? 'em Contas' : 'em Despesas'}`,
                    data: valores,
                    backgroundColor: backgroundColor,
                    borderColor: backgroundColor.replace('0.7', '1'),
                    borderWidth: 1
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
                            text: 'Dias da Semana',
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
                                return 'Total: R$ ' + context.raw.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                            }
                        }
                    }
                }
            }
        });

        // Ajusta a data selecionada para sempre ser uma segunda-feira
        document.getElementById('data_segunda').addEventListener('change', function(e) {
            const dataSelecionada = new Date(this.value);
            const diaSemana = dataSelecionada.getDay(); // 0 (domingo) a 6 (sábado)
            
            // Se não for segunda-feira (1), ajusta para a segunda anterior
            if (diaSemana !== 1) {
                const diff = diaSemana === 0 ? 6 : diaSemana - 1;
                dataSelecionada.setDate(dataSelecionada.getDate() - diff);
                
                // Formata a data para YYYY-MM-DD
                const novaData = dataSelecionada.toISOString().split('T')[0];
                this.value = novaData;
            }
        });
    </script>
</body>
</html>