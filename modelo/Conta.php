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
        $stmt = $this->pdo->prepare(
            "UPDATE conta 
            SET nome = :nome, valor = :valor, descricao = :descricao, dataPagamento = :dataPagamento, 
            dataVencimento = :dataVencimento, categoria = :categoria, formaPagamento = :formaPagamento 
            WHERE idConta = :id"
        );
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':valor', $valor);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':dataPagamento', $dataPagamento);
        $stmt->bindParam(':dataVencimento', $dataVencimento);
        $stmt->bindParam(':categoria', $categoria);
        $stmt->bindParam(':formaPagamento', $formaPagamento);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    // Deletar uma conta
    public function deletar($id) {
        $stmt = $this->pdo->prepare("DELETE FROM conta WHERE idConta = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }
}
?>
