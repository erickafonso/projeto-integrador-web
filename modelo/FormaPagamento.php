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

    // Listar todas as formas de pagamento de um usuário
    public function listar($idUsuario) {
        // Modificar a consulta para filtrar pelo idUsuario
        $stmt = $this->pdo->prepare("SELECT * FROM formapagamento WHERE idUsuario = ?");
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar uma forma de pagamento pelo id e verificar se o idUsuario corresponde ao logado
    public function buscar($id, $idUsuario) {
        $stmt = $this->pdo->prepare("SELECT * FROM formapagamento WHERE idFormaPagamento = ? AND idUsuario = ?");
        $stmt->execute([$id, $idUsuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Alterar uma forma de pagamento - agora verifica se o idUsuario corresponde ao logado
    public function alterar($id, $nome, $idUsuario) {
        // Verifica se a forma de pagamento pertence ao usuário logado
        $stmt = $this->pdo->prepare("UPDATE formapagamento SET nome = ? WHERE idFormaPagamento = ? AND idUsuario = ?");
        return $stmt->execute([$nome, $id, $idUsuario]);
    }

    // Deletar uma forma de pagamento - agora verifica se o idUsuario corresponde ao logado
    public function deletar($id, $idUsuario) {
        // Verifica se a forma de pagamento pertence ao usuário logado
        $stmt = $this->pdo->prepare("DELETE FROM formapagamento WHERE idFormaPagamento = ? AND idUsuario = ?");
        return $stmt->execute([$id, $idUsuario]);
    }

    // Função para pegar todas as formas de pagamento com o idFormaPagamento como índice para um usuário específico
    public function getFormasComIds($idUsuario) {
        // Executa a consulta para pegar idFormaPagamento e nome das formas de pagamento do usuário logado
        $stmt = $this->pdo->prepare("SELECT idFormaPagamento, nome FROM formapagamento WHERE idUsuario = ?");
        $stmt->execute([$idUsuario]);

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
