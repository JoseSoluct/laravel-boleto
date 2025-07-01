<?php

require 'autoload.php';
$beneficiario = new Eduardokum\LaravelBoleto\Pessoa([
    'nome'      => 'ACME',
    'endereco'  => 'Rua um, 123',
    'bairro'    => 'Bairro',
    'cep'       => '99999-999',
    'uf'        => 'UF',
    'cidade'    => 'CIDADE',
    'documento' => '99.999.999/9999-99',
]);

$pagador = new Eduardokum\LaravelBoleto\Pessoa([
    'nome'      => 'Cliente',
    'endereco'  => 'Rua um, 123',
    'bairro'    => 'Bairro',
    'cep'       => '99999-999',
    'uf'        => 'UF',
    'cidade'    => 'CIDADE',
    'documento' => '999.999.999-99',
]);

$boleto = new Eduardokum\LaravelBoleto\Boleto\Banco\Bv([
    'logo'                   => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '655.png',
    'dataVencimento'         => new Carbon\Carbon(),
    'valor'                  => 62.45,
    'multa'                  => false,
    'juros'                  => false,
    'numero'                 => 123456789,
    'numeroDocumento'        => 123456789,
    'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
    'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
    'aceite'                 => 'S',
    'especieDoc'             => 'DM',
    'pagador'                => $pagador,
    'beneficiario'           => $beneficiario,
    'carteira'               => 500,
    'convenio'               => 1234567890,
    'conta'                  => 12345678,
]);

$remessa = new Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco\Bv([
    'agencia'      => '0001',
    'carteira'     => 500,
    'conta'        => 12345678,
    'convenio'     => 1234567890,
    'beneficiario' => $beneficiario,
]);
$remessa->addBoleto($boleto);

echo $remessa->save(__DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'bv.txt');
