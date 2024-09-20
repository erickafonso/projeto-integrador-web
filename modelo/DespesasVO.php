<?php
/**
 * Classe DespesasVO
 */
class DespesasVO {
    private int $idDespesa;
    private string $nome;
    private ?float $valor = null;
    private ?DateTime $dataPagamento = null;
    private ?string $descricao = null;
    private int $categoria;
    private int $formaPagamento;

    public function getIdDespesa(): int {
        return $this->idDespesa;
    }

    public function setIdDespesa(int $idDespesa): void {
        $this->idDespesa = $idDespesa;
    }

    public function getNome(): string {
        return $this->nome;
    }

    public function setNome(string $nome): void {
        $this->nome = $nome;
    }

    public function getValor(): ?float {
        return $this->valor;
    }

    public function setValor(?float $valor): void {
        $this->valor = $valor;
    }

    public function getDataPagamento(): ?DateTime {
        return $this->dataPagamento;
    }

    public function setDataPagamento(?DateTime $dataPagamento): void {
        $this->dataPagamento = $dataPagamento;
    }

    public function getDescricao(): ?string {
        return $this->descricao;
    }

    public function setDescricao(?string $descricao): void {
        $this->descricao = $descricao;
    }

    public function getCategoria(): int {
        return $this->categoria;
    }

    public function setCategoria(int $categoria): void {
        $this->categoria = $categoria;
    }

    public function getFormaPagamento(): int {
        return $this->formaPagamento;
    }

    public function setFormaPagamento(int $formaPagamento): void {
        $this->formaPagamento = $formaPagamento;
    }
}
