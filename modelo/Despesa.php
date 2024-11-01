<?php
class Despesa {
    public $id;
    public $nome;
    public $valor;
    public $descricao;
    public $dataPagamento;
    public $categoria;
    public $formaPagamento;

    public function __construct($id, $nome, $valor, $descricao, $dataPagamento, $categoria, $formaPagamento) {
        $this->id = $id;
        $this->nome = $nome;
        $this->valor = $valor;
        $this->descricao = $descricao;
        $this->dataPagamento = $dataPagamento;
        $this->categoria = $categoria;
        $this->formaPagamento = $formaPagamento;
    }

    public function atualizar($conn) {
        $sql = "UPDATE despesa SET nome=?, valor=?, descricao=?, dataPagamento=?, categoria=?, formaPagamento=? WHERE idDespesa=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdssssi", $this->nome, $this->valor, $this->descricao, $this->dataPagamento, $this->categoria, $this->formaPagamento, $this->id);
        return $stmt->execute();
    }
}
?>