<?php
header('Content-Type: text/html; charset=UTF-8');

include_once('usuario/conexao.php');


if (!isset($pdo)) {
    die("Erro: A conexão não foi estabelecida.");
}


// Processamento do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    // Validações básicas
    if (empty($nome) || empty($email) || empty($senha)) {
        $erro = "Todos os campos são obrigatórios!";
    } elseif ($senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem!";
    } else {
        // Verifica se o email já está cadastrado
        $stmt = $pdo->prepare("SELECT idUsuario FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $erro = "Este email já está cadastrado!";
        } else {
            // Criptografa a senha
            $senha_hash = password_hash($senha, PASSWORD_BCRYPT);
            
            // Insere o novo usuário
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
            if ($stmt->execute([$nome, $email, $senha_hash])) {
                $sucesso = "Cadastro realizado com sucesso!";
                // Limpa os campos do formulário
                $nome = $email = '';
            } else {
                $erro = "Erro ao cadastrar usuário!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%; padding: 8px; box-sizing: border-box;
        }
        button { padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #45a049; }
        .erro { color: red; margin-bottom: 15px; }
        .sucesso { color: green; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h1>Cadastro de Usuário</h1>
    
    <?php if (isset($erro)): ?>
        <div class="erro"><?php echo htmlspecialchars($erro); ?></div>
    <?php endif; ?>
    
    <?php if (isset($sucesso)): ?>
        <div class="sucesso"><?php echo htmlspecialchars($sucesso); ?></div>
    <?php endif; ?>
    
    <form method="post">
        <div class="form-group">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($nome ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>
        </div>
        
        <div class="form-group">
            <label for="confirmar_senha">Confirmar Senha:</label>
            <input type="password" id="confirmar_senha" name="confirmar_senha" required>
        </div>
        
        <button type="submit">Cadastrar</button>
    </form>
</body>
</html>