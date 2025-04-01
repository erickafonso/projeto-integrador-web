<?php
session_start();

if (!isset($_SESSION['idUsuario'])) {
    header('Location: usuario/index.php');
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

// Função para obter o intervalo de datas da semana atual (segunda a domingo)
function getSemanaAtual($data = null) {
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

// Obtém a semana atual
$semanaAtual = getSemanaAtual();
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
    <link rel="stylesheet" href="css/alteracoes.css">
    <link rel="stylesheet" href="css/nav.css">
    <link rel="stylesheet" href="css/graficos.css">
    <title>Gráfico Semanal de Gastos</title>
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
        canvas {
            max-width: 100%;
            height: auto !important;
        }
        .info-semana {
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
                <li><a href="index.html">Home</a></li>
                <li><a>|</a></li>
                <li><a href="contas.html">Contas</a></li>
                <li><a>|</a></li>
                <li><a href="despesas.html">Despesas</a></li>
                <li><a>|</a></li>
                <li><a href="formaPagamento.html">Formas de pagamento</a></li>
                <li><a>|</a></li>
                <li><a href="categorias.html">Categorias</a></li>
            </ul>
        </nav>
    </header>

    <div id="conteudo">
        <div class="grafico-container">
            <h2>Gráfico Semanal de Gastos</h2>
            
            <div class="info-semana">
                Semana de <?= date('d/m/Y', strtotime($semanaAtual['inicio'])) ?> a <?= date('d/m/Y', strtotime($semanaAtual['fim'])) ?>
            </div>
            
            <form method="POST" class="filtro-container">
                <label for="tipoGasto">Tipo de Gasto:</label>
                <select id="tipoGasto" name="tipoGasto">
                    <option value="todos" <?= ($tipoGasto == 'todos') ? 'selected' : '' ?>>Total (Contas + Despesas)</option>
                    <option value="contas" <?= ($tipoGasto == 'contas') ? 'selected' : '' ?>>Apenas Contas</option>
                    <option value="despesas" <?= ($tipoGasto == 'despesas') ? 'selected' : '' ?>>Apenas Despesas</option>
                </select>
                
                <button type="submit">Atualizar</button>
            </form>
            
            <div style="height: 60vh;">
                <canvas id="graficoBarrasSemanal"></canvas>
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
                            text: 'Dias da Semana'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Total: R$ ' + context.raw.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>