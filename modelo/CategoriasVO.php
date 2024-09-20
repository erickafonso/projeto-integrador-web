<?php
/**
 * Classe CategoriasVO
 */
class CategoriasVO {
    private int $idCategoria;
    private string $nome;

    public function getIdCategoria(): int {
        return $this->idCategoria;
    }

    public function setIdCategoria(int $idCategoria): void {
        $this->idCategoria = $idCategoria;
    }

    public function getNome(): string {
        return $this->nome;
    }

    public function setNome(string $nome): void {
        $this->nome = $nome;
    }
}
