<?php
// Classe que representa um item de forma de pagamento
class FormaPagamentoItem {
    public $idFormaPagamento;
    public $nome;

    public function __construct($idFormaPagamento, $nome) {
        $this->idFormaPagamento = $idFormaPagamento;
        $this->nome = $nome;
    }
}

class FormaPagamento {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Listar todas as formas de pagamento
    public function listar() {
        $stmt = $this->pdo->query("SELECT * FROM formapagamento");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar uma forma de pagamento pelo id
    public function buscar($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM formapagamento WHERE idFormaPagamento = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Alterar uma forma de pagamento
    public function alterar($id, $nome) {
        $stmt = $this->pdo->prepare("UPDATE formapagamento SET nome = ? WHERE idFormaPagamento = ?");
        return $stmt->execute([$nome, $id]);
    }

    // Deletar uma forma de pagamento
    public function deletar($id) {
        $stmt = $this->pdo->prepare("DELETE FROM formapagamento WHERE idFormaPagamento = ?");
        return $stmt->execute([$id]);
    }

    // Função para pegar todas as formas de pagamento com o idFormaPagamento como índice
    public function getFormasComIds() {
        // Executa a consulta para pegar idFormaPagamento e nome das formas de pagamento
        $stmt = $this->pdo->query("SELECT idFormaPagamento, nome FROM formapagamento");

        // Recupera todos os resultados da consulta
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Cria um array para armazenar os objetos de forma de pagamento
        $formas = [];

        foreach ($result as $row) {
            // Cria um objeto FormaPagamentoItem e adiciona ao array
            $formas[] = new FormaPagamentoItem($row['idFormaPagamento'], $row['nome']);
        }

        return $formas;
    }
}
?>

