<?php
class Conta {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Listar todas as contas
    public function listar() {
        $stmt = $this->pdo->prepare("SELECT * FROM conta");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Alterar uma conta
    public function alterar($id, $nome, $valor, $descricao, $dataPagamento, $dataVencimento, $categoria, $formaPagamento) {
        try {
            // Prepara a query de atualização
            $stmt = $this->pdo->prepare(
                "UPDATE conta 
                SET nome = :nome, valor = :valor, descricao = :descricao, dataPagamento = :dataPagamento, 
                dataVencimento = :dataVencimento, categoria = :categoria, formaPagamento = :formaPagamento 
                WHERE idConta = :id"
            );
    
            // Vincula os parâmetros à query
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':valor', $valor);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindParam(':dataPagamento', $dataPagamento);
            $stmt->bindParam(':dataVencimento', $dataVencimento);
            $stmt->bindParam(':categoria', $categoria);
            $stmt->bindParam(':formaPagamento', $formaPagamento);
            $stmt->bindParam(':id', $id);
    
            // Executa a query
            $stmt->execute();
    
            // Verifica se algum registro foi alterado
            if ($stmt->rowCount() > 0) {
                echo "Conta atualizada com sucesso!";
            } else {
                echo "Nenhuma conta foi atualizada. Verifique se os dados são válidos.";
            }
    
        } catch (PDOException $e) {
            // Caso ocorra algum erro no banco de dados
            echo "Erro ao atualizar conta: " . $e->getMessage();  // Exibe o erro se ocorrer
        }
    }
    public function deletar($id) {
        try {
            // Prepara a query de exclusão
            $stmt = $this->pdo->prepare("DELETE FROM conta WHERE idConta = :id");
    
            // Vincula o parâmetro à query
            $stmt->bindParam(':id', $id);
    
            // Executa a query
            $stmt->execute();
    
            // Verifica se algum registro foi excluído
            if ($stmt->rowCount() > 0) {
                echo "Conta deletada com sucesso!";
            } else {
                echo "Nenhuma conta foi deletada. Verifique se o ID está correto.";
            }
    
        } catch (PDOException $e) {
            // Caso ocorra algum erro no banco de dados
            echo "Erro ao deletar conta: " . $e->getMessage();  // Exibe o erro se ocorrer
        }
    }
}
?>
