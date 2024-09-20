<?php
/**
 * Classe FormasPagamentosVO
 */
class FormasPagamentosVO {
    private int $idFormaPagamento;
    private string $nome;

    public function getIdFormaPagamento(): int {
        return $this->idFormaPagamento;
    }

    public function setIdFormaPagamento(int $idFormaPagamento): void {
        $this->idFormaPagamento = $idFormaPagamento;
    }

    public function getNome(): string {
        return $this->nome;
    }

    public function setNome(string $nome): void {
        $this->nome = $nome;
    }
}
