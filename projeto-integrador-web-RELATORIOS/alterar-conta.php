<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['idUsuario'])) {
    header('Location: usuario/login.php');
    exit;
}

include('usuario/conexao.php');
include('modelo/Conta.php');
include('modelo/Categoria.php');
include('modelo/FormaPagamento.php');

if (!isset($pdo)) {
    die("Erro: A conexão não foi estabelecida.");
}

$categoria = new Categoria($pdo);
$formaPagamentoModel = new FormaPagamento($pdo);
$nomesCategorias = $categoria->getNomesComIds();
$formas = $formaPagamentoModel->getFormasComIds($_SESSION['idUsuario']);
$contaModel = new Conta($pdo);

// Variável para mensagens de sucesso
$mensagemSucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['alterar'])) {
        $contaModel->alterar($_POST['idConta'], $_POST['nome'], $_POST['valor'], $_POST['descricao'], $_POST['dataPagamento'], $_POST['dataVencimento'], $_POST['categoria'], $_POST['formaPagamento']);
        $mensagemSucesso = 'alterar';
    } elseif (isset($_POST['deletar'])) {
        $contaModel->deletar($_POST['idConta']);
        $mensagemSucesso = 'deletar';
    }
    
    // Recarrega a página para evitar reenvio do formulário
    header("Location: alterar-conta.php?sucesso=" . $mensagemSucesso);
    exit;
}

$contas = $contaModel->listar();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/alteracoes.css">
    <link rel="stylesheet" href="css/nav.css">
    <title>Manutenção de Contas</title>
    <script type="text/javascript">
        function confirmarAcao(acao) {
            if (acao === 'deletar') {
                return confirm("Você tem certeza que deseja deletar esta conta?");
            } else {
                return confirm("Você tem certeza que deseja alterar esta conta?");
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
                alert('Conta alterada com sucesso!');
            } else if (sucesso === 'deletar') {
                alert('Conta deletada com sucesso!');
            }
        };
    </script>
</head>
<body>
<header class="contas">
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
    <h1>Manutenção de Contas</h1>
    <table border="1">
        <tr>
            <th class="contas">Código</th>
            <th class="contas">Nome</th>
            <th class="contas">Valor</th>
            <th class="contas">Descrição</th>
            <th class="contas">Data Pagamento</th>
            <th class="contas">Data Vencimento</th>
            <th class="contas">Categoria</th>
            <th class="contas">Forma de Pagamento</th>
            <th class="contas">Ações</th>
        </tr>
        <?php foreach ($contas as $conta): ?>
        <tr>
            <td><?php echo $conta['idConta']; ?></td>
            <td><?php echo $conta['nome']; ?></td>
            <td><?php echo $conta['valor']; ?></td>
            <td><?php echo $conta['descricao']; ?></td>
            <td><?php echo date('d/m/Y', strtotime($conta['dataPagamento'])); ?></td>
            <td><?php echo date('d/m/Y', strtotime($conta['dataVencimento'])); ?></td>
            <td>
                <?php
                foreach ($nomesCategorias as $categoriaItem) {
                    if ($categoriaItem->idCategoria == $conta['categoria']) {
                        echo $categoriaItem->nome;
                        break;
                    }
                }
                ?>
            </td>
            <td>
                <?php
                foreach ($formas as $formaPagamentoItem) {
                    if ($formaPagamentoItem->idFormaPagamento == $conta['formaPagamento']) {
                        echo $formaPagamentoItem->nome;
                        break;
                    }
                }
                ?>
            </td>
            <td class="acoes">
                <form method="POST" action="alterar-conta.php">
                    <div class="input-group">
                        <div class="input-container">
                            <input type="text" name="nome" placeholder="Nome:" value="<?php echo $conta['nome']; ?>" required>
                            <input type="number" name="valor" placeholder="Valor:" value="<?php echo $conta['valor']; ?>" step="0.01" required>
                            <input type="text" name="descricao" placeholder="Descrição:" value="<?php echo $conta['descricao']; ?>">
                        </div>
                        <div class="input-container">
                            <input type="date" name="dataPagamento" value="<?php echo $conta['dataPagamento']; ?>" required>
                            <input type="date" name="dataVencimento" value="<?php echo $conta['dataVencimento']; ?>" required>
                        </div>
                        <div class="input-container">
                            <select name="categoria" required>
                                <option value="">Selecione uma categoria</option>
                                <?php foreach ($nomesCategorias as $categoriaItem): ?>
                                    <option value="<?php echo $categoriaItem->idCategoria; ?>" <?php echo ($categoriaItem->idCategoria == $conta['categoria']) ? 'selected' : ''; ?>><?php echo $categoriaItem->nome; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="formaPagamento" required>
                                <option value="">Selecione uma forma de pagamento</option>
                                <?php foreach ($formas as $formaPagamentoItem): ?>
                                    <option value="<?php echo $formaPagamentoItem->idFormaPagamento; ?>" <?php echo ($formaPagamentoItem->idFormaPagamento == $conta['formaPagamento']) ? 'selected' : ''; ?>><?php echo $formaPagamentoItem->nome; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="button-container">
                            <input type="hidden" name="idConta" value="<?php echo $conta['idConta']; ?>">
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