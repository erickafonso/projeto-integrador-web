<?php
/**
 * Classe ContasVO
 */
class ContasVO {
    private int $idConta;
    private string $nome;
    private ?float $valor = null;
    private ?DateTime $dataPagamento = null;
    private ?DateTime $dataVencimento = null;
    private int $categoria;
    private int $formaPagamento;
    private ?string $descricao = null;

    public function setCategoria(int $categoria): void {
        $this->categoria = $categoria;
    }

    public function getCategoria(): int {
        return $this->categoria;
    }

    public function setFormaPagamento(int $formaPagamento): void {
        $this->formaPagamento = $formaPagamento;
    }

    public function getFormaPagamento(): int {
        return $this->formaPagamento;
    }

    public function getIdConta(): int {
        return $this->idConta;
    }

    public function setIdConta(int $idConta): void {
        $this->idConta = $idConta;
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

    public function getDataVencimento(): ?DateTime {
        return $this->dataVencimento;
    }

    public function setDataVencimento(?DateTime $dataVencimento): void {
        $this->dataVencimento = $dataVencimento;
    }

    public function getDescricao(): ?string {
        return $this->descricao;
    }

    public function setDescricao(?string $descricao): void {
        $this->descricao = $descricao;
    }
}
