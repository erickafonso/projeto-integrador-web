<?php
session_start();

if (!isset($_SESSION['idUsuario']) || empty($_SESSION['idUsuario'])) {
    header('Location: usuario/login.php');
    exit;
}

$usuarioId = $_SESSION['idUsuario'];

include_once('usuario/conexao.php');
include('modelo/FormaPagamento.php');

if (!isset($pdo)) {
    die("Erro: A conexão com o banco de dados não foi estabelecida.");
}

$formaPagamentoModel = new FormaPagamento($pdo);

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
$ordenarPor = isset($_POST['ordenar_por']) ? $_POST['ordenar_por'] : 'forma_pagamento';
$ordem = isset($_POST['ordem']) ? $_POST['ordem'] : 'asc';

function gerarRelatorioFormaPagamento($pdo, $usuarioId, $dataInicio, $dataFim, $ordenarPor, $ordem) {
    $sql = "SELECT 
                fp.idFormaPagamento,
                fp.nome as forma_pagamento,
                COUNT(*) as quantidade,
                SUM(CASE WHEN con.idConta IS NOT NULL THEN con.valor ELSE 0 END) + 
                SUM(CASE WHEN d.idDespesa IS NOT NULL THEN d.valor ELSE 0 END) as total,
                MAX(GREATEST(IFNULL(con.dataPagamento, '0000-00-00'), IFNULL(d.dataPagamento, '0000-00-00'))) as ultima_data
            FROM formaPagamento fp
            LEFT JOIN conta con ON con.formaPagamento = fp.idFormaPagamento AND con.idUsuario = :usuarioId 
                AND con.dataPagamento BETWEEN :inicio AND :fim
            LEFT JOIN despesa d ON d.formaPagamento = fp.idFormaPagamento AND d.idUsuario = :usuarioId 
                AND d.dataPagamento BETWEEN :inicio AND :fim
            WHERE fp.idUsuario = :usuarioId
            GROUP BY fp.idFormaPagamento, fp.nome";

    $ordenacaoValida = [
        'forma_pagamento' => 'fp.nome',
        'valor' => 'total',
        'quantidade' => 'quantidade',
        'data' => 'ultima_data'
    ];
    
    if (array_key_exists($ordenarPor, $ordenacaoValida)) {
        $sql .= " ORDER BY " . $ordenacaoValida[$ordenarPor] . " " . ($ordem === 'desc' ? 'DESC' : 'ASC');
    }

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
    $stmt->bindParam(':inicio', $dataInicio);
    $stmt->bindParam(':fim', $dataFim);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$relatorio = gerarRelatorioFormaPagamento($pdo, $usuarioId, $dataInicio, $dataFim, $ordenarPor, $ordem);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório por Forma de Pagamento</title>
    <link rel="stylesheet" href="css/cadastro.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/alteracoes.css">
    <link rel="stylesheet" href="css/tabelas.css">
    <style>
        body {
            padding-top: 80px;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }
        
        .dashboard-container {
            display: flex;
            max-width: 1200px;
            margin: 20px auto;
            gap: 20px;
            padding: 0 20px;
        }
        
        .filtro-container {
            flex: 1;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            height: fit-content;
        }
        
        .relatorio-container {
            flex: 3;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
        }
        
        .filtro-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .filtro-group {
            margin-bottom: 15px;
        }
        
        .filtro-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .filtro-group select,
        .filtro-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .filtro-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        
        .filtro-btn:hover {
            background-color: #45a049;
        }
        
        .info-periodo {
            margin-bottom: 15px;
            font-style: italic;
            color: #555;
            text-align: center;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #303030;
            color: #fff;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #f2f2f2;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }
            
            .filtro-container,
            .relatorio-container {
                width: 100%;
            }
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
        <div class="filtro-container">
            <h2>Filtros</h2>
            <form method="POST" class="filtro-form">
                <div class="filtro-group">
                    <label for="data_inicio">Data Início:</label>
                    <input type="date" id="data_inicio" name="data_inicio" value="<?= $dataInicio ?>" 
                           min="<?= $datasLimite['min_date'] ?>" max="<?= $datasLimite['max_date'] ?>">
                </div>
                
                <div class="filtro-group">
                    <label for="data_fim">Data Fim:</label>
                    <input type="date" id="data_fim" name="data_fim" value="<?= $dataFim ?>" 
                           min="<?= $datasLimite['min_date'] ?>" max="<?= $datasLimite['max_date'] ?>">
                </div>
                
                <div class="filtro-group">
                    <label for="ordenar_por">Ordenar por:</label>
                    <select id="ordenar_por" name="ordenar_por">
                        <option value="forma_pagamento" <?= $ordenarPor == 'forma_pagamento' ? 'selected' : '' ?>>Forma de Pagamento</option>
                        <option value="valor" <?= $ordenarPor == 'valor' ? 'selected' : '' ?>>Valor Gasto</option>
                        <option value="quantidade" <?= $ordenarPor == 'quantidade' ? 'selected' : '' ?>>Quantidade</option>
                        <option value="data" <?= $ordenarPor == 'data' ? 'selected' : '' ?>>Data</option>
                    </select>
                </div>
                
                <div class="filtro-group">
                    <label for="ordem">Ordem:</label>
                    <select name="ordem">
                        <option value="asc" <?= $ordem == 'asc' ? 'selected' : '' ?>>Crescente</option>
                        <option value="desc" <?= $ordem == 'desc' ? 'selected' : '' ?>>Decrescente</option>
                    </select>
                </div>
                
                <button type="submit" class="filtro-btn">Gerar Relatório</button>
            </form>
        </div>
        
        <div class="relatorio-container">
            <h2>Relatório por Forma de Pagamento</h2>
            <div class="info-periodo">
                Período: <?= date('d/m/Y', strtotime($dataInicio)) ?> a <?= date('d/m/Y', strtotime($dataFim)) ?>
            </div>
            
            <?php if (count($relatorio) > 0): ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Forma de Pagamento</th>
                                <th>Valor Gasto (R$)</th>
                                <th>Quantidade</th>
                                <th>Última Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalGeral = 0;
                            $quantidadeGeral = 0;
                            
                            foreach ($relatorio as $linha): 
                                $totalGeral += $linha['total'];
                                $quantidadeGeral += $linha['quantidade'];
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($linha['forma_pagamento']) ?></td>
                                    <td>R$ <?= number_format($linha['total'], 2, ',', '.') ?></td>
                                    <td><?= $linha['quantidade'] ?></td>
                                    <td><?= $linha['ultima_data'] != '0000-00-00' ? date('d/m/Y', strtotime($linha['ultima_data'])) : '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <tr class="total-row">
                                <td><strong>TOTAL GERAL</strong></td>
                                <td><strong>R$ <?= number_format($totalGeral, 2, ',', '.') ?></strong></td>
                                <td><strong><?= $quantidadeGeral ?></strong></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>Nenhum registro encontrado para o período selecionado.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>