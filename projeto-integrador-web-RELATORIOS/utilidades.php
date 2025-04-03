<?php
class Utilidades {
    // Formata data para exibição (DD/MM/AAAA)
    public static function formatarDataExibicao($dataBanco) {
        if (empty($dataBanco)) return '';
        return date('d/m/Y', strtotime($dataBanco));
    }

    // Formata data para banco (AAAA-MM-DD)
    public static function formatarDataBanco($dataInput) {
        if (empty($dataInput)) return null;
        
        $data = DateTime::createFromFormat('d/m/Y', $dataInput);
        if ($data === false) {
            $data = DateTime::createFromFormat('Y-m-d', $dataInput);
            if ($data === false) return null;
        }
        
        return $data->format('Y-m-d');
    }

    // Valida se string contém apenas letras e espaços
    public static function validarNome($nome) {
        return preg_match("/^[a-zA-ZÀ-ú\s]+$/", $nome);
    }

    // Valida se string contém apenas números
    public static function validarNumerico($valor) {
        return preg_match("/^[0-9]+$/", $valor);
    }

    // Valida força da senha (mínimo 6 caracteres)
    public static function validarSenha($senha) {
        return strlen($senha) >= 6;
    }

    // Remove caracteres não numéricos
    public static function apenasNumeros($valor) {
        return preg_replace("/[^0-9]/", "", $valor);
    }

    // Formata número para exibição (ex: 1000 → 1.000)
    public static function formatarNumero($numero) {
        return number_format($numero, 0, ',', '.');
    }

    // Formata CPF (ex: 12345678901 → 123.456.789-01)
    public static function formatarCPF($cpf) {
        $cpf = self::apenasNumeros($cpf);
        if (strlen($cpf) != 11) return $cpf;
        
        return substr($cpf, 0, 3) . '.' . 
               substr($cpf, 3, 3) . '.' . 
               substr($cpf, 6, 3) . '-' . 
               substr($cpf, 9, 2);
    }

    // Valida formato de data (DD/MM/AAAA)
    public static function validarData($data) {
        $data = trim($data);
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $data)) {
            list($dia, $mes, $ano) = explode('/', $data);
            return checkdate($mes, $dia, $ano);
        }
        return false;
    }
}
?>