<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['idUsuario'])) {
    header('Location: usuario/login.php');
    exit;
}

include_once('conexao/conexao.php');

// Recebe os filtros
$filtro_categoria = $_POST['categoria'] ?? '';
$filtro_forma_pagamento = $_POST['forma_pagamento'] ?? '';
$filtro_data_inicio = $_POST['data_inicio'] ?? '';
$filtro_data_fim = $_POST['data_fim'] ?? '';
$filtro_valor_min = $_POST['valor_min'] ?? '';
$filtro_valor_max = $_POST['valor_max'] ?? '';
$filtro_nome = $_POST['nome'] ?? '';

// Configuração de paginação
$registros_por_pagina = 20;
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $registros_por_pagina;

function buscarDespesas($conn, $categoria, $forma_pagamento, $data_inicio, $data_fim, $valor_min, $valor_max, $nome, $registros_por_pagina = null, $offset = null) {
    $sql_count = "SELECT COUNT(*) as total 
                 FROM despesa d
                 JOIN categoria ca ON d.categoria = ca.idCategoria
                 JOIN formaPagamento f ON d.formaPagamento = f.idFormaPagamento
                 WHERE 1=1";
    
    $sql = "SELECT d.nome, d.valor, d.descricao, d.dataPagamento,  ca.nome AS categoria, f.nome AS formaPagamento 
            FROM despesa d
            JOIN categoria ca ON d.categoria = ca.idCategoria
            JOIN formaPagamento f ON d.formaPagamento = f.idFormaPagamento
            WHERE 1=1";

    $filtros = [];
    if (!empty($categoria)) {
        $filtros[] = "d.categoria = " . (int)$categoria;
        $sql_count .= " AND d.categoria = " . (int)$categoria;
    }
    if (!empty($forma_pagamento)) {
        $filtros[] = "d.formaPagamento = " . (int)$forma_pagamento;
        $sql_count .= " AND d.formaPagamento = " . (int)$forma_pagamento;
    }
    if (!empty($data_inicio) && !empty($data_fim)) {
        $filtros[] = "d.dataPagamento BETWEEN '" . $conn->real_escape_string($data_inicio) . "' AND '" . $conn->real_escape_string($data_fim) . "'";
        $sql_count .= " AND d.dataPagamento BETWEEN '" . $conn->real_escape_string($data_inicio) . "' AND '" . $conn->real_escape_string($data_fim) . "'";
    }
    if (!empty($valor_min)) {
        $filtros[] = "d.valor >= " . (float)$valor_min;
        $sql_count .= " AND d.valor >= " . (float)$valor_min;
    }
    if (!empty($valor_max)) {
        $filtros[] = "d.valor <= " . (float)$valor_max;
        $sql_count .= " AND d.valor <= " . (float)$valor_max;
    }
    if (!empty($nome)) {
        $filtros[] = "d.nome LIKE '%" . $conn->real_escape_string($nome) . "%'";
        $sql_count .= " AND d.nome LIKE '%" . $conn->real_escape_string($nome) . "%'";
    }

    if (!empty($filtros)) {
        $sql .= " AND " . implode(" AND ", $filtros);
    }

    $sql .= " ORDER BY d.dataPagamento DESC";

    if ($registros_por_pagina !== null && $offset !== null) {
        $sql .= " LIMIT " . (int)$offset . ", " . (int)$registros_por_pagina;
    }

    $result_count = $conn->query($sql_count);
    $total_registros = $result_count->fetch_assoc()['total'];

    $result = $conn->query($sql);
    $despesas = [];
    while ($row = $result->fetch_assoc()) {
        $despesas[] = $row;
    }

    return [
        'despesas' => $despesas,
        'total_registros' => $total_registros
    ];
}

// Funções para preencher combobox (as mesmas da página de contas)
function preencherComboBoxFormaPagamento($conn) {
    $sql = "SELECT idFormaPagamento, nome FROM formaPagamento";
    $result = $conn->query($sql);
    $formas_pagamento = [];
    while ($row = $result->fetch_assoc()) {
        $formas_pagamento[] = $row;
    }
    return $formas_pagamento;
}

function preencherComboBoxCategoria($conn) {
    $sql = "SELECT idCategoria, nome FROM categoria";
    $result = $conn->query($sql);
    $categorias = [];
    while ($row = $result->fetch_assoc()) {
        $categorias[] = $row;
    }
    return $categorias;
}

$formas_pagamento = preencherComboBoxFormaPagamento($conn);
$categorias = preencherComboBoxCategoria($conn);

$resultado = buscarDespesas($conn, $filtro_categoria, $filtro_forma_pagamento, $filtro_data_inicio, $filtro_data_fim, $filtro_valor_min, $filtro_valor_max, $filtro_nome, $registros_por_pagina, $offset);
$despesas = $resultado['despesas'];
$total_registros = $resultado['total_registros'];

$total_paginas = ceil($total_registros / $registros_por_pagina);

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/cadastro.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/alteracoes.css">
    <link rel="stylesheet" href="css/tabelas.css">
    <title>Relatório de Despesas</title>
    <style>
        /* Estilo específico para o cabeçalho da tabela de despesas */
        .table-container table thead th {
            background-color: #303030 !important;
            color: #fff !important;
            border-bottom: 2px solid #444 !important;
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

    <div class="form-container" id="formContainer">
        <h2>Relatório de Despesas</h2>
        <form action="" method="post">
            <div class="form-group">
                <label for="nome">Pesquisar por nome:</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($filtro_nome); ?>" placeholder="Digite o nome da despesa">
            </div>
            
            <div class="form-group">
                <label for="categoria">Categoria:</label>
                <select id="categoria" name="categoria">
                    <option value="">Selecione uma categoria</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['idCategoria']; ?>" <?php echo ($filtro_categoria == $cat['idCategoria']) ? 'selected' : ''; ?>><?php echo $cat['nome']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="forma-pagamento">Forma de Pagamento:</label>
                <select id="forma-pagamento" name="forma_pagamento">
                    <option value="">Selecione uma forma de pagamento</option>
                    <?php foreach ($formas_pagamento as $fp): ?>
                        <option value="<?php echo $fp['idFormaPagamento']; ?>" <?php echo ($filtro_forma_pagamento == $fp['idFormaPagamento']) ? 'selected' : ''; ?>><?php echo $fp['nome']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="data-inicio">Data Início:</label>
                <input type="date" id="data-inicio" name="data_inicio" value="<?php echo $filtro_data_inicio; ?>">
            </div>

            <div class="form-group">
                <label for="data-fim">Data Fim:</label>
                <input type="date" id="data-fim" name="data_fim" value="<?php echo $filtro_data_fim; ?>">
            </div>

            <div class="form-group">
                <label for="valor_min">Valor Mínimo:</label>
                <input type="number" id="valor_min" name="valor_min" step="0.01" min="0" value="<?php echo $filtro_valor_min; ?>" placeholder="Digite o valor mínimo">
            </div>

            <div class="form-group">
                <label for="valor_max">Valor Máximo:</label>
                <input type="number" id="valor_max" name="valor_max" step="0.01" min="0" value="<?php echo $filtro_valor_max; ?>" placeholder="Digite o valor máximo">
            </div>

            <div class="form-group">
                <button type="submit">Filtrar</button>
            </div>
        </form>
    </div>
    
    <div class="table-container">
        <h3>Relatório de Despesas</h3>
        <?php if ($total_registros > 0): ?>
            <div class="info-paginacao">
                Exibindo <?php echo min($registros_por_pagina, count($despesas)); ?> de <?php echo $total_registros; ?> registros
            </div>
        <?php endif; ?>
        
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Valor</th>
                        <th>Descrição</th>
                        <th>Data Pagamento</th>
                        
                        <th>Categoria</th>
                        <th>Forma Pagamento</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($despesas) > 0) : ?>
                        <?php foreach ($despesas as $despesa) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($despesa['nome']); ?></td>
                                <td>R$ <?php echo number_format($despesa['valor'], 2, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($despesa['descricao']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($despesa['dataPagamento'])); ?></td>
                                
                                <td><?php echo htmlspecialchars($despesa['categoria']); ?></td>
                                <td><?php echo htmlspecialchars($despesa['formaPagamento']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7">Nenhuma despesa encontrada com os filtros aplicados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_paginas > 1): ?>
            <div class="paginacao">
                <?php if ($pagina_atual > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => 1])); ?>">Primeira</a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina_atual - 1])); ?>">Anterior</a>
                <?php endif; ?>

                <?php
                $inicio = max(1, $pagina_atual - 2);
                $fim = min($total_paginas, $pagina_atual + 2);
                
                if ($inicio > 1) {
                    echo '<span>...</span>';
                }
                
                for ($i = $inicio; $i <= $fim; $i++): ?>
                    <?php if ($i == $pagina_atual): ?>
                        <span class="ativa"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $i])); ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor;
                
                if ($fim < $total_paginas) {
                    echo '<span>...</span>';
                }
                ?>

                <?php if ($pagina_atual < $total_paginas): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina_atual + 1])); ?>">Próxima</a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $total_paginas])); ?>">Última</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>