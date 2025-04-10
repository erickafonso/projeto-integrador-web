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

// Configurações comuns para todos os gráficos
$contaModel = new Conta($pdo);
$categoriaModel = new Categoria($pdo);
$formaPagamentoModel = new FormaPagamento($pdo);
$nomesCategorias = $categoriaModel->getNomesComIds();

// Configura os períodos atuais
$anoAtual = date('Y');
$mesAtual = date('Y-m');
$tipoGasto = 'todos'; // Padrão para mostrar todos os gastos

// Funções para o gráfico anual
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

// Funções para o gráfico mensal
function buscarGastosDiarios($pdo, $usuarioId, $mes, $tipo) {
    $dataInicio = "$mes-01";
    $ultimoDia = date('t', strtotime($dataInicio));
    $dataFim = "$mes-$ultimoDia";
    
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

// Funções para o gráfico semanal
function getSemanaAtual($data = null) {
    $data = $data ?: date('Y-m-d');
    $diaSemana = date('N', strtotime($data));
    
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

function buscarGastosSemanais($pdo, $usuarioId, $dataInicio, $dataFim, $tipo) {
    $diasSemana = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'];
    $resultado = array_fill_keys($diasSemana, 0);
    
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
    
    $resultadoOrdenado = [];
    foreach ($diasSemana as $dia) {
        $resultadoOrdenado[$dia] = $resultado[$dia];
    }
    
    return $resultadoOrdenado;
}

// Busca os dados para os gráficos
$gastosMensais = buscarGastosMensais($pdo, $_SESSION['idUsuario'], $anoAtual, $tipoGasto);
$meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
$valoresAnuais = array_values($gastosMensais);
$totalAnual = array_sum($valoresAnuais);

$gastosDiarios = buscarGastosDiarios($pdo, $_SESSION['idUsuario'], $mesAtual, $tipoGasto);
$diasMes = array_keys($gastosDiarios);
$valoresMensais = array_values($gastosDiarios);
$totalMensal = array_sum($valoresMensais);

$semanaAtual = getSemanaAtual();
$gastosSemanais = buscarGastosSemanais($pdo, $_SESSION['idUsuario'], $semanaAtual['inicio'], $semanaAtual['fim'], $tipoGasto);
$diasSemana = array_keys($gastosSemanais);
$valoresSemanais = array_values($gastosSemanais);
$totalSemanal = array_sum($valoresSemanais);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Financeiro</title>
    
    <link rel="stylesheet" href="css/styles.css">
  
    <link rel="stylesheet" href="css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .carrossel {
            position: relative;
            overflow: hidden;
            margin: 20px 0;
        }
        
        .slides {
            display: flex;
            transition: transform 0.5s ease;
        }
        
        .slide {
            min-width: 100%;
            padding: 20px;
            box-sizing: border-box;
        }
        
        .grafico-container {
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
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
        
        .info-periodo {
            margin-bottom: 15px;
            font-style: italic;
            color: #555;
        }
        
        .carrossel-navegacao {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 20px;
        }
        
        .carrossel-btn {
            background-color: #303030;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .carrossel-btn:hover {
            background-color: #202020;
        }
        
        .indicadores {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 10px;
        }
        
        .indicador {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #ccc;
            cursor: pointer;
        }
        
        .indicador.ativo {
            background-color: #4CAF50;
        }
        
        h1, h2 {
            color: #333;
            margin-bottom: 20px;
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
        <h1>Bem vindo(a), <?= isset($_SESSION['nome']) ? $_SESSION['nome'] : 'Usuário' ?></h1>
        
        <div class="carrossel">
            <div class="slides">
                <!-- Slide 1 - Gráfico Anual -->
                <div class="slide">
                    <div class="grafico-container">
                        <h2>Gráfico Anual de Gastos</h2>
                        <div class="info-periodo">Ano: <?= $anoAtual ?></div>
                        
                        <div class="grafico-wrapper">
                            <canvas id="graficoAnual"></canvas>
                        </div>
                        
                        <div class="info-total">
                            Total anual: R$ <?= number_format($totalAnual, 2, ',', '.') ?>
                        </div>
                    </div>
                </div>
                
                <!-- Slide 2 - Gráfico Mensal -->
                <div class="slide">
                    <div class="grafico-container">
                        <h2>Gráfico Mensal de Gastos</h2>
                        <div class="info-periodo">Mês: <?= date('F Y', strtotime($mesAtual . '-01')) ?></div>
                        
                        <div class="grafico-wrapper">
                            <canvas id="graficoMensal"></canvas>
                        </div>
                        
                        <div class="info-total">
                            Total mensal: R$ <?= number_format($totalMensal, 2, ',', '.') ?>
                        </div>
                    </div>
                </div>
                
                <!-- Slide 3 - Gráfico Semanal -->
                <div class="slide">
                    <div class="grafico-container">
                        <h2>Gráfico Semanal de Gastos</h2>
                        <div class="info-periodo">
                            Semana de <?= date('d/m/Y', strtotime($semanaAtual['inicio'])) ?> a <?= date('d/m/Y', strtotime($semanaAtual['fim'])) ?>
                        </div>
                        
                        <div class="grafico-wrapper">
                            <canvas id="graficoSemanal"></canvas>
                        </div>
                        
                        <div class="info-total">
                            Total semanal: R$ <?= number_format($totalSemanal, 2, ',', '.') ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="carrossel-navegacao">
                <button class="carrossel-btn" id="btnAnterior">&#10094;</button>
                <button class="carrossel-btn" id="btnProximo">&#10095;</button>
            </div>
            
            <div class="indicadores">
                <div class="indicador ativo" data-slide="0"></div>
                <div class="indicador" data-slide="1"></div>
                <div class="indicador" data-slide="2"></div>
            </div>
        </div>
    </div>

    <script>
        // Configuração do carrossel
        let slideAtual = 0;
        const slides = document.querySelector('.slides');
        const totalSlides = document.querySelectorAll('.slide').length;
        const indicadores = document.querySelectorAll('.indicador');
        
        function atualizarCarrossel() {
            slides.style.transform = `translateX(-${slideAtual * 100}%)`;
            
            indicadores.forEach((indicador, index) => {
                if (index === slideAtual) {
                    indicador.classList.add('ativo');
                } else {
                    indicador.classList.remove('ativo');
                }
            });
        }
        
        document.getElementById('btnAnterior').addEventListener('click', () => {
            slideAtual = (slideAtual > 0) ? slideAtual - 1 : totalSlides - 1;
            atualizarCarrossel();
        });
        
        document.getElementById('btnProximo').addEventListener('click', () => {
            slideAtual = (slideAtual < totalSlides - 1) ? slideAtual + 1 : 0;
            atualizarCarrossel();
        });
        
        indicadores.forEach(indicador => {
            indicador.addEventListener('click', () => {
                slideAtual = parseInt(indicador.getAttribute('data-slide'));
                atualizarCarrossel();
            });
        });
        
        // Dados para os gráficos
        const meses = <?= json_encode($meses) ?>;
        const valoresAnuais = <?= json_encode($valoresAnuais) ?>;
        const totalAnual = <?= $totalAnual ?>;
        
        const diasMes = <?= json_encode($diasMes) ?>;
        const valoresMensais = <?= json_encode($valoresMensais) ?>;
        const totalMensal = <?= $totalMensal ?>;
        
        const diasSemana = <?= json_encode($diasSemana) ?>;
        const valoresSemanais = <?= json_encode($valoresSemanais) ?>;
        const totalSemanal = <?= $totalSemanal ?>;
        
        // Cores para os gráficos
        const corContas = 'rgba(54, 162, 235, 0.7)';
        const corDespesas = 'rgba(255, 99, 132, 0.7)';
        const corTodos = 'rgba(75, 192, 192, 0.7)';
        
        // Configuração do gráfico anual
        const ctxAnual = document.getElementById('graficoAnual').getContext('2d');
        const chartAnual = new Chart(ctxAnual, {
            type: 'bar',
            data: {
                labels: meses,
                datasets: [{
                    label: 'Gastos Totais',
                    data: valoresAnuais,
                    backgroundColor: corTodos,
                    borderColor: corTodos.replace('0.7', '1'),
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
                            text: 'Meses'
                        }
                    }
                },
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
                    }
                }
            }
        });
        
        // Configuração do gráfico mensal
        const ctxMensal = document.getElementById('graficoMensal').getContext('2d');
        const chartMensal = new Chart(ctxMensal, {
            type: 'bar',
            data: {
                labels: diasMes,
                datasets: [{
                    label: 'Gastos Diários',
                    data: valoresMensais,
                    backgroundColor: corTodos,
                    borderColor: corTodos.replace('0.7', '1'),
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
                            text: 'Dias do Mês'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const percent = ((value / totalMensal) * 100).toFixed(2);
                                return [
                                    `Valor: R$ ${value.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`,
                                    `Percentual: ${percent}%`
                                ];
                            }
                        }
                    }
                }
            }
        });
        
        // Configuração do gráfico semanal
        const ctxSemanal = document.getElementById('graficoSemanal').getContext('2d');
        const chartSemanal = new Chart(ctxSemanal, {
            type: 'bar',
            data: {
                labels: diasSemana,
                datasets: [{
                    label: 'Gastos Semanais',
                    data: valoresSemanais,
                    backgroundColor: corTodos,
                    borderColor: corTodos.replace('0.7', '1'),
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
                                const value = context.raw;
                                const percent = ((value / totalSemanal) * 100).toFixed(2);
                                return [
                                    `Valor: R$ ${value.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`,
                                    `Percentual: ${percent}%`
                                ];
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>