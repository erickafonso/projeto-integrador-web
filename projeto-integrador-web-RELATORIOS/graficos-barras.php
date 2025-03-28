<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['idUsuario'])) {
    // Caso não esteja logado, redireciona para a página de login
    header('Location: usuario/index.php');  // Troque "login.php" pelo arquivo correto de login
    exit;
}

header('Content-Type: text/html; charset=UTF-8');

include_once('usuario/conexao.php'); // Inclui a conexão com o banco de dados
include('modelo/Conta.php'); // Inclui o modelo Conta
include('modelo/Categoria.php'); // Inclui o modelo da tabela Categoria
include('modelo/FormaPagamento.php'); // Inclui o modelo da tabela FormaPagamento
// Verifica se a conexão com o banco de dados foi realizada
if (!isset($pdo)) {
    die("Erro: A conexão não foi estabelecida.");
}

$contaModel = new Conta($pdo); // Passa a conexão PDO para o modelo Conta
$categoriaModel = new Categoria($pdo);  // Cria uma instância do modelo Categoria
$formaPagamentoModel = new FormaPagamento($pdo); // Cria uma instância do modelo FormaPagamento
$nomesCategorias = $categoriaModel->getNomesComIds();
// Processa a alteração ou exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['alterar'])) {
        // Obtém os dados do formulário e chama o método de alteração
        $contaModel->alterar(
            $_POST['idConta'],
            $_POST['nome'],
            $_POST['valor'],
            $_POST['descricao'],
            $_POST['dataPagamento'],
            $_POST['dataVencimento'],
            $_POST['categoria'],
            $_POST['formaPagamento']
        );
    } elseif (isset($_POST['deletar'])) {
        $contaModel->deletar($_POST['idConta']);
    }
}

$categoriaSelecionada = isset($_POST['categoria']) ? (int) $_POST['categoria'] : (isset($conta['categoria']) ? $conta['categoria'] : null);
// Lista todas as contas
$contas = $contaModel->listar();
$categorias = $categoriaModel->listar();

function buscarContasPorIntervalo($pdo, $usuarioId, $data_inicio, $data_fim) {
    // Consulta SQL para buscar as contas dentro do intervalo de tempo
    $sql = "SELECT nome, valor FROM conta WHERE idUsuario = :usuarioId AND dataPagamento BETWEEN :data_inicio AND :data_fim";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
    $stmt->bindParam(':data_inicio', $data_inicio, PDO::PARAM_STR);
    $stmt->bindParam(':data_fim', $data_fim, PDO::PARAM_STR);
    $stmt->execute();
    $contas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $contas;
}


function somarValoresPorCategoria($pdo, $categoriaId)
{
    // Prepara a consulta para somar os valores da categoria especificada
    $sql = "SELECT SUM(valor) AS total FROM conta WHERE categoria = :categoria";

    // Prepara a consulta
    $stmt = $pdo->prepare($sql);

    // Vincula o parâmetro de categoria
    $stmt->bindParam(':categoria', $categoriaId, PDO::PARAM_INT);

    // Executa a consulta
    $stmt->execute();

    // Obtém o resultado
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    // Retorna o total
    return $resultado['total'];
}







// Funções para buscar IDs de categoria e forma de pagamento
function getIdCategoria($pdo, $nome)
{
    $id_categoria = 1;
    $sql = "SELECT idCategoria FROM categoria WHERE nome = :nome";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":nome", $nome, PDO::PARAM_STR);
    $stmt->execute();
    $stmt->bindColumn(1, $id_categoria);
    $stmt->fetch(PDO::FETCH_ASSOC);
    return $id_categoria ?? 1; // Retorna 1 se não encontrar
}

function getIdFormaPagamento($pdo, $nome)
{
    $id_forma_pagamento = 1;
    $sql = "SELECT idFormaPagamento FROM formaPagamento WHERE nome = :nome";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":nome", $nome, PDO::PARAM_STR);
    $stmt->execute();
    $stmt->bindColumn(1, $id_forma_pagamento);
    $stmt->fetch(PDO::FETCH_ASSOC);
    return $id_forma_pagamento ?? 1; // Retorna 1 se não encontrar
}

// Preencher os combo boxes
function preencherComboBoxFormaPagamento($pdo)
{
    $sql = "SELECT idFormaPagamento, nome FROM formaPagamento";
    $stmt = $pdo->query($sql);
    $formas_pagamento = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $formas_pagamento;
}

function preencherComboBoxCategoria($pdo)
{
    $sql = "SELECT idCategoria, nome FROM categoria";
    $stmt = $pdo->query($sql);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $categorias;
}
// Recupera as listas de categorias e formas de pagamento
$categorias = $categoriaModel->listar();


// $formas_pagamento = preencherComboBoxFormaPagamento($pdo);
// $categorias = preencherComboBoxCategoria($pdo);

// Definir o ID do usuário e o intervalo de datas
$usuarioId = 1; // Ajuste conforme necessário
$data_inicio = '0001-01-01'; // Ajuste conforme necessário
$data_fim = '2025-05-10'; // Ajuste conforme necessário

// Chamar a função para buscar as contas dentro do intervalo
$contas = buscarContasPorIntervalo($pdo, $usuarioId, $data_inicio, $data_fim);

// Inicializar os arrays para os nomes e valores
$nomesContas = [];
$valoresContas = [];

foreach ($contas as $conta) {
    $nomesContas[] = $conta['nome'];
    $valoresContas[] = $conta['valor'];
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/alteracoes.css">
    <link rel="stylesheet" href="css/nav.css">
    <title>Gastos por Categoria</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    <h2>Total de Gastos por Categoria</h2>

<?php
// Exibe o total de gastos por categoria, se houver
if ($gastosPorCategoria) {
    echo "<ul>";
    foreach ($gastosPorCategoria as $gasto) {
        echo "<li><strong>{$gasto['categoria']}:</strong> R$ " . number_format($gasto['totalGastos'], 2, ',', '.') . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Não há gastos registrados para este usuário.</p>";
}
?>

</div>
    <h2>Total de Gastos por Categoria</h2> 
<!-- Formulário com Combo Box Categoria -->
<div class="input-container">
    <form method="POST">
        <label for="categoria">Selecione uma categoria:</label>
        <select id="categoria" name="categoria" required>
            <option value="">Selecione uma categoria</option>
            <?php
            // Preencher as opções do select com categorias
            foreach ($nomesCategorias as $categoriaItem) {
                // Marca a categoria selecionada
                $selected = (isset($_POST['categoria']) && $_POST['categoria'] == $categoriaItem->idCategoria) ? 'selected' : '';
                echo '<option value="' . $categoriaItem->idCategoria . '" ' . $selected . '>' . $categoriaItem->nome . '</option>';
            }
            ?>
        </select>
        <button type="submit">Enviar</button>
    </form>

    <?php
    // Verifica se a categoria foi selecionada e processa o valor
    if (isset($_POST['categoria']) && !empty($_POST['categoria'])) {
        // Obtém o id da categoria selecionada
        $categoriaId = $_POST['categoria'];

        // Chama a função para somar os valores da categoria
        $total = somarValoresPorCategoria($pdo, $categoriaId);

        // Exibe o resultado formatado
        echo "<p>O total dos valores para a categoria {$categoriaId} é: R$ " . number_format($total, 2, ',', '.') . "</p>";
    }
    ?>
</div>

            <h2>Distribuição de Gastos por tempo</h2>
           <!-- Gráfico de Barras -->
<canvas id="graficoBarrasContas" width="400" height="400"></canvas>
<script>
    var ctx = document.getElementById('graficoBarrasContas').getContext('2d');
    var graficoBarrasContas = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($nomesContas); ?>, // Nomes das contas
            datasets: [{
                label: 'Valor das Contas',
                data: <?php echo json_encode($valoresContas); ?>, // Valores das contas
                backgroundColor: '#FF5733',
                borderColor: '#C70039',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.label + ': R$ ' + tooltipItem.raw.toFixed(2);
                        }
                    }
                }
            }
        }
    });
</script>


        </div>

</body>

</html>