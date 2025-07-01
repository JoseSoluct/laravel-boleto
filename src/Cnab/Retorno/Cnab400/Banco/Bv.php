<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco;

use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab400;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class Bv extends AbstractRetorno implements RetornoCnab400
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_BV;

    /**
     * Array com as ocorrencias do banco;
     *
     * @var array
     */
    private $ocorrencias = [
        '02' => 'Entrada confirmada',
        '03' => 'Entrada rejeitada (nota 11 - tabela 1)',
        '06' => 'Liquidação normal',
        '07' => 'Liquidação parcial',
        '08' => 'Liquidação em cartório',
        '09' => 'Baixa simples',
        '12' => 'Abatimento concedido',
        '14' => 'Vencimento alterado',
        '16' => 'Instruções rejeitadas (nota 11 - tabela 2)',
        '18' => 'Confirmação da instrução de alteração de dias para baixa automatica',
        '19' => 'Confirmação da instrução de protesto',
        '20' => 'Confirmação da instrução de sustação de protesto',
        '21' => 'Confirmação da instrução de não protestar',
        '22' => 'Confirmação da instrução de não baixar automaticamente',
        '23' => 'Protesto enviado a cartório',
        '24' => 'Confirmação de alteração de dias para baixa automatica',
        '25' => 'Confirmação de cancelamento de baixa automatica',
        '26' => 'Confirmação de alteração do valor nominal',
        '27' => 'Confirmação de alteração de valor/percentual minimo',
        '28' => 'Confirmação de alteração de valor/percentual maximo',
        '29' => 'Confirmação de alteração de valor/percentual mínimo e maximo',
        '32' => 'Baixa por ter sido protestado',
        '33' => 'Confirmação de protesto',
        '34' => 'Confirmação de sustação',
        '35' => 'Protesto sustado judicialmente',
        '47' => 'Transferência de carteira',
        '48' => 'Alteração de percentual mínimo/ máximo',
        '49' => 'Alteração de percentual mínimo',
        '50' => 'Alteração de percentual máximo',
        '51' => 'Alteração da quantidade de parcelas',
        '70' => 'Liquidação pix',
    ];

    /**
     * Array com as possiveis rejeicoes do banco.
     *
     * @var array
     */
    private $rejeicoes = [
        '03' => [
            '01' => 'Valor do abatimento inválido',
            '02' => 'Agencia encarregada da cobranca em branco ou inválido',
            '03' => 'Bairro do sacado em branco ou inválido',
            '04' => 'Carteira inválida',
            '05' => 'Cep não numérico ou invalido',
            '06' => 'Cidade do sacado em branco ou inválido',
            '07' => 'Cnpj do cedente inapto',
            '08' => 'Cnpj/cpf do sacado não numérico ou igual a zeros',
            '09' => 'Mensagem de cobrança em branco ou inválida para registro do tipo 7',
            '10' => 'Data de emissão de titulo em branco ou inválido',
            '11' => 'Data de mora em branco ou inválido',
            '12' => 'Data de multa invalida/inferior ao vencimento do título',
            '13' => 'Data de vencto invalida/fora de prazo de operação (mínimo ou máximo)',
            '14' => 'Data limite para concessão de desconto inválido',
            '15' => 'Sigla do estado inválida',
            '16' => 'Cep incompatível com a sigla do estado',
            '17' => 'Identifica a especie do titulo em branco ou inválido',
            '18' => 'Identificação do tipo inscrição do sacado em branco ou inválido',
            '19' => 'Instrução de cobranca inválida',
            '20' => 'Juros de mora maior que o permitido',
            '21' => 'Logradouro não informado ou deslocado',
            '22' => 'Nome do sacado não informado ou deslocado',
            '23' => 'Nosso número já registrado ou inválido',
            '24' => 'Numero da inscrição do sacado em branco ou inválido',
            '25' => 'Numero de inscrição da empresa em branco ou inválido',
            '26' => 'Ocorrência inválida',
            '27' => 'Prazo para protesto em branco ou inválido',
            '28' => 'Nome não informado ou deslocado (bancos correspondentes)',
            '29' => 'Seu numero em branco',
            '30' => 'Valor de multa inválido ou maior que o permitido',
            '31' => 'Valor de mora por dia de atraso em branco ou inválido',
            '32' => 'Valor do abatimento a ser concedido inválido',
            '33' => 'Valor de desconto a ser concedido inválido',
            '34' => 'Valor do abatimento a ser concedido inválido',
            '35' => 'Valor nominal do titulo em branco ou inválido',
            '50' => 'Convênio sem código do boleto personalizado cadastrado ou código informado diferente do cadastrado',
            '53' => 'Arquivo excedeu a quantidade de linhas permitidas',
            '54' => 'Convenio não permite mensagem personalizada',
        ],
        '16' => [
            '36' => 'Valor de abatimento inválido',
            '37' => 'Valor nominal inválido',
            '38' => 'Data de prorrogação inválida',
            '39' => 'Data de vencimento menor que a data atual ou inválida',
            '40' => 'Valor minimo inválido',
            '41' => 'Valor maximo inválido',
            '42' => 'Valor minimo e/o maximo inválido(s)',
            '43' => 'Titulo já baixado, liquidado, com instrução de protesto ou recusada pelo banco',
            '44' => 'Numero de dias para baixa automática inválido',
            '45' => 'Titulo sem instrução de protesto ou baixado/liquidado',
            '46' => 'Sustação de protesto não permitida para o titulo',
            '47' => 'Titulo sem instrução e baixa automática ou já baixado/liquidado',
            '48' => 'Recusado cartório sem custas',
            '49' => 'Recusado cartório com custas',
            '51' => 'Protesto recusado pelo cartório',
            '52' => 'Quantidade de parcelas inválida',
        ],
    ];

    /**
     * Array com as possiveis rejeicoes do banco.
     *
     * @var array
     */
    private $codLiquidacao = [
        '01' => 'Outros bancos - guiche de caixa',
        '02' => 'Outros bancos - terminal de auto atendimento',
        '03' => 'Outros bancos - home/office banking',
        '05' => 'Outros bancos - correspondente',
        '06' => 'Outros bancos - telefone',
        '07' => 'Outros bancos - arquivo eletrônico',
        '08' => 'Banco votorantim - por debito em conta corrente ou dinheiro',
    ];

    /**
     * Roda antes dos metodos de processar
     */
    protected function init()
    {
        $this->totais = [
            'liquidados'  => 0,
            'entradas'    => 0,
            'baixados'    => 0,
            'protestados' => 0,
            'alterados'   => 0,
        ];
    }

    /**
     * @param array $header
     *
     * @return bool
     * @throws ValidationException
     */
    protected function processarHeader(array $header)
    {
        $this->getHeader()
            ->setOperacaoCodigo($this->rem(2, 2, $header))
            ->setOperacao($this->rem(3, 9, $header))
            ->setServicoCodigo($this->rem(10, 11, $header))
            ->setServico($this->rem(12, 26, $header))
            ->setCodigoCliente($this->rem(27, 36, $header))
            ->setConta($this->rem(37, 45, $header))
            ->setContaDv($this->rem(46, 46, $header))
            ->setData($this->rem(100, 105, $header));

        return true;
    }

    /**
     * @param array $detalhe
     *
     * @return bool
     * @throws ValidationException
     */
    protected function processarDetalhe(array $detalhe)
    {
        $d = $this->detalheAtual();
        $d->setCarteira($this->rem(98, 100, $detalhe))
            ->setNossoNumero($this->rem(63, 72, $detalhe))
            ->setNumeroDocumento($this->rem(117, 126, $detalhe))
            ->setNumeroControle($this->rem(38, 62, $detalhe))
            ->setOcorrencia($this->rem(101, 102, $detalhe))
            ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, $d->getOcorrencia(), 'Desconhecida'))
            ->setDataOcorrencia($this->rem(103, 110, $detalhe))
            ->setDataVencimento($this->rem(147, 152, $detalhe))
            ->setValor(Util::nFloat($this->rem(153, 165, $detalhe) / 100, 2, false))
            ->setValorTarifa(Util::nFloat($this->rem(176, 188, $detalhe) / 100, 2, false))
            ->setValorIOF(Util::nFloat($this->rem(215, 227, $detalhe) / 100, 2, false))
            ->setValorDesconto(Util::nFloat($this->rem(241, 253, $detalhe) / 100, 2, false))
            ->setValorRecebido(Util::nFloat($this->rem(254, 266, $detalhe) / 100, 2, false))
            ->setValorMora(Util::nFloat($this->rem(267, 279, $detalhe) / 100, 2, false))
            ->setCodigoLiquidacao(Arr::get($this->codLiquidacao, $this->rem(377, 378, $detalhe), ''));

        if ($d->hasOcorrencia('06', '07', '08', '70')) {
            $this->totais['liquidados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_LIQUIDADA);
        } elseif ($d->hasOcorrencia('02')) {
            $this->totais['entradas']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ENTRADA);
        } elseif ($d->hasOcorrencia('09', '32')) {
            $this->totais['baixados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_BAIXADA);
        } elseif ($d->hasOcorrencia('23', '33')) {
            $this->totais['protestados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_PROTESTADA);
        } elseif ($d->hasOcorrencia('14', '24', '26', '27', '28', '29', '48', '49', '50', '51')) {
            $this->totais['alterados']++;
            $d->setOcorrenciaTipo($d::OCORRENCIA_ALTERACAO);
        } elseif ($d->hasOcorrencia('03', '16')) {
            $msgAdicional = str_split(sprintf('%08s', $this->rem(367, 374, $detalhe)), 2) + array_fill(0, 4, '');
            $this->totais['erros']++;
            $error = Util::appendStrings(Arr::get($this->rejeicoes[sprintf('%02s', $d->getOcorrencia())], $msgAdicional[0], ''), Arr::get($this->rejeicoes[sprintf('%02s', $d->getOcorrencia())], $msgAdicional[1], ''), Arr::get($this->rejeicoes[sprintf('%02s', $d->getOcorrencia())], $msgAdicional[2], ''), Arr::get($this->rejeicoes[sprintf('%02s', $d->getOcorrencia())], $msgAdicional[3], ''));
            $d->setError($error);
        } else {
            $d->setOcorrenciaTipo($d::OCORRENCIA_OUTROS);
        }

        return true;
    }

    /**
     * @param array $trailer
     *
     * @return bool
     * @throws ValidationException
     */
    protected function processarTrailer(array $trailer)
    {
        $this->getTrailer()
            ->setQuantidadeTitulos((int) $this->rem(18, 25, $trailer))
            ->setValorTitulos(Util::nFloat($this->rem(26, 39, $trailer) / 100, 2, false))
            ->setQuantidadeEntradas((int) $this->totais['entradas'])
            ->setQuantidadeLiquidados((int) $this->totais['liquidados'])
            ->setQuantidadeBaixados((int) $this->totais['baixados'])
            ->setQuantidadeAlterados((int) $this->totais['alterados']);

        return true;
    }
}
