<?php
session_start();

// Ativar exibição de erros
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Verifica se o usuário está logado
if (!isset($_SESSION['idUsuario'])) {
    header('Location: usuario/index.php');
    exit;
}

include_once('conexao/conexao.php'); // Inclui a conexão com o banco de dados

// Recebe os filtros
$filtro_categoria = $_POST['categoria'] ?? '';
$filtro_forma_pagamento = $_POST['forma_pagamento'] ?? '';
$filtro_data_inicio = $_POST['data_inicio'] ?? '';
$filtro_data_fim = $_POST['data_fim'] ?? '';
$filtro_valor_min = $_POST['valor_min'] ?? '';
$filtro_valor_max = $_POST['valor_max'] ?? '';

// Atualize a função de busca para considerar os filtros de valor mínimo e máximo
function buscarContas($conn, $categoria, $forma_pagamento, $data_inicio, $data_fim, $valor_min, $valor_max) {
    $sql = "SELECT c.nome, c.valor, c.descricao, c.dataPagamento, c.dataVencimento, ca.nome AS categoria, f.nome AS formaPagamento 
            FROM conta c
            JOIN categoria ca ON c.categoria = ca.idCategoria
            JOIN formaPagamento f ON c.formaPagamento = f.idFormaPagamento
            WHERE 1=1";

    // Adiciona filtros dinâmicos
    if (!empty($categoria)) {
        $sql .= " AND c.categoria = ?";
    }
    if (!empty($forma_pagamento)) {
        $sql .= " AND c.formaPagamento = ?";
    }
    if (!empty($data_inicio) && !empty($data_fim)) {
        $sql .= " AND c.dataPagamento BETWEEN ? AND ?";
    }
    if (!empty($valor_min)) {
        $sql .= " AND c.valor >= ?";
    }
    if (!empty($valor_max)) {
        $sql .= " AND c.valor <= ?";
    }

    $stmt = $conn->prepare($sql);

    // Inicia as variáveis para tipos e parâmetros
    $paramTypes = '';
    $params = [];

    // Se existir filtro de categoria
    if (!empty($categoria)) {
        $paramTypes .= 'i';  // Tipo inteiro para categoria
        $params[] = $categoria;
    }
    // Se existir filtro de forma de pagamento
    if (!empty($forma_pagamento)) {
        $paramTypes .= 'i';  // Tipo inteiro para forma de pagamento
        $params[] = $forma_pagamento;
    }
    // Se existir filtro de intervalo de datas
    if (!empty($data_inicio) && !empty($data_fim)) {
        $paramTypes .= 'ss'; // Tipo string para ambas as datas (data_inicio e data_fim)
        $params[] = $data_inicio;
        $params[] = $data_fim;
    }
    // Se existir filtro de valor mínimo
    if (!empty($valor_min)) {
        $paramTypes .= 'd'; // Tipo decimal para valor mínimo
        $params[] = $valor_min;
    }
    // Se existir filtro de valor máximo
    if (!empty($valor_max)) {
        $paramTypes .= 'd'; // Tipo decimal para valor máximo
        $params[] = $valor_max;
    }

    // Verifica se há parâmetros para vincular
    if ($paramTypes) {
        // Vincula os parâmetros
        $stmt->bind_param($paramTypes, ...$params);
    }

    $stmt->execute();

    // Retorna os resultados
    $result = $stmt->get_result();
    $contas = [];
    while ($row = $result->fetch_assoc()) {
        $contas[] = $row;
    }
    $stmt->close();
    return $contas;
}




// Preencher os combo boxes de forma de pagamento e categoria
function preencherComboBoxFormaPagamento($conn)
{
    $sql = "SELECT idFormaPagamento, nome FROM formaPagamento";
    $result = $conn->query($sql);
    $formas_pagamento = [];
    while ($row = $result->fetch_assoc()) {
        $formas_pagamento[] = $row;
    }
    return $formas_pagamento;
}

function preencherComboBoxCategoria($conn)
{
    $sql = "SELECT idCategoria, nome FROM categoria";
    $result = $conn->query($sql);
    $categorias = [];
    while ($row = $result->fetch_assoc()) {
        $categorias[] = $row;
    }
    return $categorias;
}

// Obter os dados para os filtros
$formas_pagamento = preencherComboBoxFormaPagamento($conn);
$categorias = preencherComboBoxCategoria($conn);

// Buscar as contas com base nos filtros
$contas = buscarContas($conn, $filtro_categoria, $filtro_forma_pagamento, $filtro_data_inicio, $filtro_data_fim, $filtro_valor_min, $filtro_valor_max);

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
    <title>Relatório de Contas</title>
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
                <li><a>|</a></li>
            </ul>
        </nav>
    </header>

    <div class="form-container" id="formContainer">
        <h2>Relatório de Contas</h2>
        <form action="" method="post">
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
    <div id="conteudo">
        <h3>Relatório de Contas</h3>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Valor</th>
                    <th>Descrição</th>
                    <th>Data de Pagamento</th>
                    <th>Data de Vencimento</th>
                    <th>Categoria</th>
                    <th>Forma de Pagamento</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($contas) > 0) : ?>
                    <?php foreach ($contas as $conta) : ?>
                        <tr>
                            <td><?php echo $conta['nome']; ?></td>
                            <td><?php echo number_format($conta['valor'], 2, ',', '.'); ?></td>
                            <td><?php echo $conta['descricao']; ?></td>
                            <td><?php echo $conta['dataPagamento']; ?></td>
                            <td><?php echo $conta['dataVencimento']; ?></td>
                            <td><?php echo $conta['categoria']; ?></td>
                            <td><?php echo $conta['formaPagamento']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7">Nenhuma conta encontrada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>

</html>