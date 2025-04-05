<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['idUsuario'])) {
    header('Location: usuario/login.php');
    exit;
}

include('usuario/conexao.php');
include('modelo/Despesa.php');
include('modelo/Categoria.php');
include('modelo/FormaPagamento.php');

if (!isset($pdo)) {
    die("Erro: A conexão não foi estabelecida.");
}

$despesaModel = new Despesa($pdo);
$categoriaModel = new Categoria($pdo);
$formaPagamentoModel = new FormaPagamento($pdo);

$categorias = $categoriaModel->listar();
$formasPagamento = $formaPagamentoModel->listar($_SESSION['idUsuario']);

// Variável para mensagens de sucesso
$mensagemSucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['alterar'])) {
        $despesaModel->alterar($_POST['idDespesa'], $_POST['nome'], $_POST['valor'], $_POST['descricao'], $_POST['dataPagamento'], $_POST['categoria'], $_POST['formaPagamento']);
        $mensagemSucesso = 'alterar';
    } elseif (isset($_POST['deletar'])) {
        $despesaModel->deletar($_POST['idDespesa']);
        $mensagemSucesso = 'deletar';
    }
    
    // Recarrega a página para evitar reenvio do formulário
    header("Location: alterar-despesa.php?sucesso=" . $mensagemSucesso);
    exit;
}

$despesas = $despesaModel->listar();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/alteracoes.css">
    <link rel="stylesheet" href="css/nav.css">
    <link rel="stylesheet" href="css/styles.css">
    <title>Manutenção de Despesas</title>
    <script type="text/javascript">
        function confirmarAcao(acao) {
            if (acao === 'deletar') {
                return confirm("Você tem certeza que deseja deletar esta despesa?");
            } else {
                return confirm("Você tem certeza que deseja alterar esta despesa?");
            }
        }
        
        function submeterFormulario(btn, acao) {
            if (confirmarAcao(acao)) {
                var form = btn.closest('form');
                
                // Cria um input hidden para a ação específica
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = acao;
                input.value = '1';
                form.appendChild(input);
                
                form.submit();
            }
            return false;
        }
        
        // Mostra mensagem de sucesso quando a página carrega
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const sucesso = urlParams.get('sucesso');
            
            if (sucesso === 'alterar') {
                alert('Despesa alterada com sucesso!');
            } else if (sucesso === 'deletar') {
                alert('Despesa deletada com sucesso!');
            }
        };
    </script>
</head>
<body>
<header class="despesas">
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
                <!-- Botão Sair com class 'logout' -->
                <li><a href="logout.php" class="logout">Sair</a></li>
            </ul>
    </nav>
</header>

<div id="conteudo">
    <h1>Manutenção de Despesas</h1>
    <table border="1">
        <tr>
            <th class="despesas">Código</th>
            <th class="despesas">Nome</th>
            <th class="despesas">Valor</th>
            <th class="despesas">Descrição</th>
            <th class="despesas">Data de Pagamento</th>
            <th class="despesas">Categoria</th>
            <th class="despesas">Forma de Pagamento</th>
            <th class="despesas">Ações</th>
        </tr>
        <?php foreach ($despesas as $despesa): ?>
        <tr>
            <td><?php echo $despesa['idDespesa']; ?></td>
            <td><?php echo $despesa['nome']; ?></td>
            <td><?php echo number_format($despesa['valor'], 2, ',', '.'); ?></td>
            <td><?php echo $despesa['descricao']; ?></td>
            <td><?php echo date('d/m/Y', strtotime($despesa['dataPagamento'])); ?></td>
            <td>
                <?php
                    $categoriaNome = '';
                    foreach ($categorias as $categoria) {
                        if ($categoria['idCategoria'] == $despesa['categoria']) {
                            $categoriaNome = $categoria['nome'];
                            break;
                        }
                    }
                    echo $categoriaNome;
                ?>
            </td>
            <td>
                <?php
                    $formaPagamentoNome = '';
                    foreach ($formasPagamento as $forma) {
                        if ($forma['idFormaPagamento'] == $despesa['formaPagamento']) {
                            $formaPagamentoNome = $forma['nome'];
                            break;
                        }
                    }
                    echo $formaPagamentoNome;
                ?>
            </td>
            <td>
                <form method="POST" action="alterar-despesa.php">
                    <div class="input-group">
                        <div class="input-container">
                            <input type="hidden" name="idDespesa" value="<?php echo $despesa['idDespesa']; ?>">
                            <input type="text" name="nome" value="<?php echo $despesa['nome']; ?>" required>
                            <input type="number" step="0.01" name="valor" value="<?php echo $despesa['valor']; ?>" required>
                            <input type="text" name="descricao" value="<?php echo $despesa['descricao']; ?>">
                        </div>
                        <div class="input-container">
                            <input type="date" name="dataPagamento" value="<?php echo $despesa['dataPagamento']; ?>" required>
                        </div>
                        <div class="input-container">
                            <select name="categoria" required>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo $categoria['idCategoria']; ?>" <?php echo ($categoria['idCategoria'] == $despesa['categoria']) ? 'selected' : ''; ?>>
                                        <?php echo $categoria['nome']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <select name="formaPagamento" required>
                                <?php foreach ($formasPagamento as $forma): ?>
                                    <option value="<?php echo $forma['idFormaPagamento']; ?>" <?php echo ($forma['idFormaPagamento'] == $despesa['formaPagamento']) ? 'selected' : ''; ?>>
                                        <?php echo $forma['nome']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="button-container">
                            <button type="button" class="btn-alterar" onclick="submeterFormulario(this, 'alterar')">Alterar</button>
                            <button type="button" class="btn-deletar" onclick="submeterFormulario(this, 'deletar')">Deletar</button>
                        </div>
                    </div>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>