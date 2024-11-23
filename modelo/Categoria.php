<?php
class CategoriaItem {
    public $idCategoria;
    public $nome;

    public function __construct($idCategoria, $nome) {
        $this->idCategoria = $idCategoria;
        $this->nome = $nome;
    }
}

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

    // Função para pegar todos os nomes das categorias com o idCategoria como índice
    public function getNomesComIds() {
        // Executa a consulta para pegar o idCategoria e nome das categorias
        $stmt = $this->pdo->query("SELECT idCategoria, nome FROM categoria");

        // Recupera todos os resultados da consulta
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Cria um array para armazenar os objetos de categoria
        $categorias = [];

        foreach ($result as $row) {
            // Cria um objeto CategoriaItem e adiciona ao array
            $categorias[] = new CategoriaItem($row['idCategoria'], $row['nome']);
        }

        return $categorias;
    }
}
?>