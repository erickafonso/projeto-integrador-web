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

// Busca apenas as categorias do usuário logado
$categoriasUsuario = $categoriaModel->getNomesComIds($usuarioId);

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
$categoriaSelecionada = isset($_POST['categoria']) ? (int)$_POST['categoria'] : null;

function buscarGastosPorCategoria($pdo, $usuarioId, $categoriaId, $dataInicio, $dataFim) {
    $sql = "SELECT DATE(dataPagamento) as data, SUM(valor) as total
            FROM conta
            WHERE idUsuario = :usuarioId 
              AND categoria = :categoriaId
              AND dataPagamento BETWEEN :inicio AND :fim
            GROUP BY DATE(dataPagamento)
            ORDER BY dataPagamento ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
    $stmt->bindParam(':categoriaId', $categoriaId, PDO::PARAM_INT);
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
$nomeCategoriaSelecionada = '';
$totalGeral = 0;

if ($categoriaSelecionada) {
    // Verifica se a categoria pertence ao usuário
    $categoriaValida = false;
    foreach ($categoriasUsuario as $categoria) {
        if ($categoria->idCategoria == $categoriaSelecionada) {
            $categoriaValida = true;
            $nomeCategoriaSelecionada = $categoria->nome;
            break;
        }
    }
    
    if ($categoriaValida) {
        $dadosGrafico = buscarGastosPorCategoria($pdo, $usuarioId, $categoriaSelecionada, $dataInicio, $dataFim);
        $totalGeral = calcularTotalGastos($dadosGrafico);
    } else {
        $categoriaSelecionada = null;
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
    <title>Evolução de Gastos por Categoria</title>
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
            <h2>Filtrar por Período</h2>
            <form method="POST" class="filtro-form">
                <div class="filtro-group">
                    <label for="categoria">Categoria:</label>
                    <select id="categoria" name="categoria" required>
                        <option value="">Selecione uma categoria</option>
                        <?php foreach ($categoriasUsuario as $categoria): ?>
                            <option value="<?= $categoria->idCategoria ?>" <?= ($categoriaSelecionada == $categoria->idCategoria) ? 'selected' : '' ?>>
                                <?= $categoria->nome ?>
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
            <h2>Evolução de Gastos por Categoria</h2>
            <div class="info-periodo">
                Período: <?= date('d/m/Y', strtotime($dataInicio)) ?> a <?= date('d/m/Y', strtotime($dataFim)) ?>
            </div>
            
            <?php if ($categoriaSelecionada): ?>
                <div class="grafico-wrapper">
                    <canvas id="graficoLinha"></canvas>
                </div>
                
                <div class="info-total">
                    Você gastou R$ <?= number_format($totalGeral, 2, ',', '.') ?> com <?= $nomeCategoriaSelecionada ?>
                </div>
                
                <script>
                    const datas = <?= json_encode($datas) ?>;
                    const valores = <?= json_encode($valores) ?>;
                    const nomeCategoria = '<?= $nomeCategoriaSelecionada ?>';
                    
                    const ctx = document.getElementById('graficoLinha').getContext('2d');
                    const chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: datas,
                            datasets: [{
                                label: `Meus gastos em ${nomeCategoria}`,
                                data: valores,
                                borderColor: 'rgba(75, 192, 192, 1)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.1
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
                                        text: 'Data'
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return 'Meu gasto: R$ ' + context.raw.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                                        }
                                    }
                                }
                            }
                        }
                    });
                </script>
            <?php else: ?>
                <p style="text-align: center; color: #666;">
                    <?= $categoriaSelecionada === null && isset($_POST['categoria']) ? 
                        'Categoria inválida ou você não tem permissão para acessá-la' : 
                        'Selecione uma categoria e intervalo de datas para visualizar o gráfico' ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>