<?php
class Despesa {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Função para listar todas as despesas
    public function listar() {
        $stmt = $this->pdo->query("SELECT * FROM despesa");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Função para alterar uma despesa
    public function alterar($id, $nome, $valor, $descricao, $dataPagamento, $categoria, $formaPagamento) {
        $sql = "UPDATE despesa SET nome = ?, valor = ?, descricao = ?, dataPagamento = ?, categoria = ?, formaPagamento = ? WHERE idDespesa = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nome, $valor, $descricao, $dataPagamento, $categoria, $formaPagamento, $id]);
    }

    // Função para deletar uma despesa
    public function deletar($id) {
        $sql = "DELETE FROM despesa WHERE idDespesa = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
    }
}
