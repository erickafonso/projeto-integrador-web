<?php
class FormaPagamento {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Listar todas as formas de pagamento
    public function listar() {
        $stmt = $this->pdo->prepare("SELECT * FROM formapagamento");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Alterar uma forma de pagamento
    public function alterar($id, $nome) {
        $stmt = $this->pdo->prepare("UPDATE formapagamento SET nome = :nome WHERE idFormaPagamento = :id");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    // Deletar uma forma de pagamento
    public function deletar($id) {
        $stmt = $this->pdo->prepare("DELETE FROM formapagamento WHERE idFormaPagamento = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }
}
?>
