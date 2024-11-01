<?php
class Categoria {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function listar() {
        $stmt = $this->pdo->query("SELECT * FROM categoria");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM categoria WHERE idCategoria = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function alterar($id, $nome) {
        $stmt = $this->pdo->prepare("UPDATE categoria SET nome = ? WHERE idCategoria = ?");
        return $stmt->execute([$nome, $id]);
    }

    public function deletar($id) {
        $stmt = $this->pdo->prepare("DELETE FROM categoria WHERE idCategoria = ?");
        return $stmt->execute([$id]);
    }
}
?>
