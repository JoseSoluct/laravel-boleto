<?php

namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Exception\ValidationException;
use Eduardokum\LaravelBoleto\Contracts\Boleto\BoletoAPI as BoletoAPIContract;

class Sicredi extends AbstractBoleto implements BoletoAPIContract
{
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addCampoObrigatorio('byte', 'posto', 'tipoimpressao');
    }

    /**
     * Local de pagamento
     *
     * @var string
     */
    protected $localPagamento = 'Pagável preferencialmente nas cooperativas de crédito do sicredi';

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_SICREDI;

    /**
     * Define as carteiras disponíveis para este banco
     *
     * @var array
     */
    protected $carteiras = ['A', '1', '2', '3'];

    /**
     * Espécie do documento, coódigo para remessa
     *
     * @var string
     */
    protected $especiesCodigo240 = [
        'DMI' => '03', // Duplicata Mercantil por Indicação
        'DM'  => '05', // Duplicata Mercantil por Indicação
        'DR'  => '06', // Duplicata Rural
        'NP'  => '12', // Nota Promissória
        'NR'  => '13', // Nota Promissória Rural
        'NS'  => '16', // Nota de Seguros
        'RC'  => '17', // Recibo
        'LC'  => '07', // Letra de Câmbio
        'ND'  => '19', // Nota de Débito
        'DSI' => '99', // Duplicata de Serviço por Indicação
        'OS'  => '99', // Outros
    ];

    /**
     * Espécie do documento, coódigo para remessa
     *
     * @var string
     */
    protected $especiesCodigo400 = [
        'DMI' => 'A', // Duplicata Mercantil por Indicação
        'DM'  => 'A', // Duplicata Mercantil por Indicação
        'DR'  => 'B', // Duplicata Rural
        'NP'  => 'C', // Nota Promissória
        'NR'  => 'D', // Nota Promissória Rural
        'NS'  => 'E', // Nota de Seguros
        'RC'  => 'G', // Recibo
        'LC'  => 'H', // Letra de Câmbio
        'ND'  => 'I', // Nota de Débito
        'DSI' => 'J', // Duplicata de Serviço por Indicação
        'OS'  => 'K', // Outros
    ];

    /**
     * Se possui registro o boleto (tipo = 1 com registro e 3 sem registro)
     *
     * @var bool
     */
    protected $registro = true;

    /**
     * Tipo de impressao do Boleto. A - Normal, B - Carne
     *
     * @var bool
     */
    protected $tipoimpressao;

    /**
     * Código do posto do cliente no banco.
     *
     * @var int
     */
    protected $posto;

    /**
     * Byte que compoe o nosso número.
     *
     * @var int
     */
    protected $byte = 2;

    /**
     * Código do cliente (é código do cedente, também chamado de código do beneficiário) é o código do emissor junto ao banco, geralmente é o próprio número da conta sem o dígito verificador.
     * O código do cliente/cedente/beneficiário será diferente desse padrão em casos como quando um cliente bancário faz a migração da sua conta entre agências.
     *
     * @var string
     */
    protected $codigoCliente;

    /**
     * Define se possui ou não registro
     *
     * @param bool $registro
     * @return Sicredi
     */
    public function setComRegistro($registro)
    {
        $this->registro = $registro;

        return $this;
    }

    /**
     * Retorna se é com registro.
     *
     * @return bool
     */
    public function isComRegistro()
    {
        return $this->registro;
    }

    /**
     * Define o posto do cliente
     *
     * @param int $posto
     * @return Sicredi
     */
    public function setPosto($posto)
    {
        $this->posto = $posto;

        return $this;
    }

    /**
     * Retorna o posto do cliente
     *
     * @return int
     */
    public function getPosto()
    {
        return $this->posto;
    }

    /**
     * Define o byte
     *
     * @param int $byte
     *
     * @return Sicredi
     * @throws ValidationException
     */
    public function setByte($byte)
    {
        if ($byte > 9) {
            throw new ValidationException('O byte deve ser compreendido entre 1 e 9');
        }
        $this->byte = $byte;

        return $this;
    }

    /**
     * Retorna o byte
     *
     * @return int
     */
    public function getByte()
    {
        return $this->byte;
    }

    /**
     * Seta o código do cliente.
     *
     * @param mixed $codigoCliente
     *
     * @return Sicredi
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = $codigoCliente;

        return $this;
    }

    /**
     * Retorna o codigo do cliente.
     *
     * @return string
     */
    public function getCodigoCliente()
    {
        return $this->codigoCliente;
    }

    /**
     * @return bool
     */
    public function isTipoimpressao()
    {
        return $this->tipoimpressao;
    }

    /**
     * @param bool $tipoimpressao
     */
    public function setTipoimpressao($tipoimpressao)
    {
        $this->tipoimpressao = $tipoimpressao;
    }



    /**
     * Retorna o campo Agência/Beneficiário do boleto
     *
     * @return string
     */
    public function getAgenciaCodigoBeneficiario()
    {
        return sprintf('%04s.%02s.%05s', $this->getAgencia(), $this->getPosto(), $this->getCodigoCliente());
    }

    /**
     * Retorna o código da carteira (Com ou sem registro)
     *
     * @return string
     */
    public function getCarteira()
    {
        return $this->carteira == 'A' ? 1 : $this->carteira;
    }

    /**
     * Gera o Nosso Número.
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        $ano = $this->getDataDocumento()->format('y');
        $byte = $this->getByte();
        $numero_boleto = Util::numberFormatGeral($this->getNumero(), 5);

        return $ano . $byte . $numero_boleto
            . CalculoDV::sicrediNossoNumero($this->getAgencia(), $this->getPosto(), $this->getCodigoCliente(), $ano, $byte, $numero_boleto);
    }

    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        return Util::maskString($this->getNossoNumero(), '##/######-#');
    }

    /**
     * Método para gerar o código da posição de 20 a 44
     *
     * @return string
     * @throws ValidationException
     */
    protected function getCampoLivre()
    {
        if ($this->campoLivre) {
            return $this->campoLivre;
        }

        $campoLivre = $this->isComRegistro() ? '1' : '3';
        $campoLivre .= Util::numberFormatGeral($this->getCarteira(), 1);
        $campoLivre .= $this->getNossoNumero();
        $campoLivre .= Util::numberFormatGeral($this->getAgencia(), 4);
        $campoLivre .= Util::numberFormatGeral($this->getPosto(), 2);
        $campoLivre .= Util::numberFormatGeral($this->getCodigoCliente(), 5);
        $campoLivre .= '10';
        $campoLivre .= Util::modulo11($campoLivre);

        return $this->campoLivre .= $campoLivre;
    }

    /**
     * Método onde qualquer boleto deve extender para gerar o código da posição de 20 a 44
     *
     * @param $campoLivre
     *
     * @return array
     */
    public static function parseCampoLivre($campoLivre)
    {
        return [
            'convenio'        => null,
            'agenciaDv'       => null,
            'contaCorrenteDv' => null,
            'codigoCliente'   => substr($campoLivre, 17, 5),
            'carteira'        => substr($campoLivre, 1, 1),
            'nossoNumero'     => substr($campoLivre, 2, 8),
            'nossoNumeroDv'   => substr($campoLivre, 10, 1),
            'nossoNumeroFull' => substr($campoLivre, 2, 9),
            'agencia'         => substr($campoLivre, 11, 4),
            //'contaCorrente' => substr($campoLivre, 17, 5),
        ];
    }

    /**
     * Tipo de cobrança para API
     *
     * @var string
     */
    protected $tipoCobranca = 'HIBRIDO';

    /**
     * Espécie do documento, código para API
     *
     * @var array
     */
    protected $especiesCodigoAPI = [
        'DMI' => 'DUPLICATA_MERCANTIL_INDICACAO',
        'DM'  => 'DUPLICATA_MERCANTIL_INDICACAO',
        'DR'  => 'DUPLICATA_RURAL',
        'NP'  => 'NOTA_PROMISSORIA',
        'NR'  => 'NOTA_PROMISSORIA_RURAL',
        'NS'  => 'NOTA_SEGUROS',
        'RC'  => 'RECIBO',
        'LC'  => 'LETRA_CAMBIO',
        'ND'  => 'NOTA_DEBITO',
        'DSI' => 'DUPLICATA_SERVICO_INDICACAO',
        'OS'  => 'OUTROS',
        'BP'  => 'BOLETO_PROPOSTA',
        'CC'  => 'CARTAO_CREDITO',
        'BD'  => 'BOLETO_DEPOSITO',
    ];

    /**
     * Define o tipo de cobrança
     *
     * @param string $tipoCobranca
     * @return Sicredi
     */
    public function setTipoCobranca($tipoCobranca)
    {
        $this->tipoCobranca = $tipoCobranca;

        return $this;
    }

    /**
     * Retorna o tipo de cobrança
     *
     * @return string
     */
    public function getTipoCobranca()
    {
        return $this->tipoCobranca;
    }

    /**
     * Return Boleto Array for API.
     *
     * @return array
     */
    public function toAPI()
    {
        $especieDocumento = Arr::get($this->especiesCodigoAPI, $this->getEspecieDoc(), 'OUTROS');

        $pagador = [
            'tipoPessoa' => strlen(Util::onlyNumbers($this->getPagador()->getDocumento())) == 14 ? 'PESSOA_JURIDICA' : 'PESSOA_FISICA',
            'documento'  => Util::onlyNumbers($this->getPagador()->getDocumento()),
            'nome'       => $this->getPagador()->getNome(),
            'endereco'   => $this->getPagador()->getEndereco(),
            'cidade'     => $this->getPagador()->getCidade(),
            'uf'         => $this->getPagador()->getUf(),
            'cep'        => Util::onlyNumbers($this->getPagador()->getCep()),
        ];

        $beneficiarioFinal = null;
        if ($this->getSacadorAvalista()) {
            $beneficiarioFinal = [
                'tipoPessoa'     => strlen(Util::onlyNumbers($this->getSacadorAvalista()->getDocumento())) == 14 ? 'PESSOA_JURIDICA' : 'PESSOA_FISICA',
                'documento'      => Util::onlyNumbers($this->getSacadorAvalista()->getDocumento()),
                'nome'           => $this->getSacadorAvalista()->getNome(),
                'logradouro'     => $this->getSacadorAvalista()->getEndereco(),
                'cidade'         => $this->getSacadorAvalista()->getCidade(),
                'uf'             => $this->getSacadorAvalista()->getUf(),
                'cep'            => (int) Util::onlyNumbers($this->getSacadorAvalista()->getCep()),
            ];
        }

        $informativos = array_values(array_filter($this->getDescricaoDemonstrativo()));
        $mensagens = array_values(array_filter($this->getInstrucoes()));

        return array_filter([
            'codigoBeneficiario' => $this->getCodigoCliente(),
            'seuNumero'          => $this->getNumero(),
            'valor'              => Util::nFloat($this->getValor(), 2, false),
            'dataVencimento'     => $this->getDataVencimento()->format('Y-m-d'),
            'especieDocumento'   => $especieDocumento,
            'tipoCobranca'       => $this->getTipoCobranca(),
            'pagador'            => $pagador,
            'beneficiarioFinal'  => $beneficiarioFinal,
            'informativos'       => ! empty($informativos) ? $informativos : null,
            'mensagens'          => ! empty($mensagens) ? $mensagens : null,
        ]);
    }

    /**
     * @param $boleto
     * @param $appends
     *
     * @return Sicredi
     * @throws ValidationException
     */
    public static function fromAPI($boleto, $appends)
    {
        if (! array_key_exists('beneficiario', $appends)) {
            throw new ValidationException('Informe o beneficiario');
        }
        if (! array_key_exists('conta', $appends)) {
            throw new ValidationException('Informe a conta');
        }

        $aSituacao = [
            'LIQUIDADO'   => AbstractBoleto::SITUACAO_PAGO,
            'BAIXADO'     => AbstractBoleto::SITUACAO_BAIXADO,
            'EM_ABERTO'   => AbstractBoleto::SITUACAO_ABERTO,
            'VENCIDO'     => AbstractBoleto::SITUACAO_ABERTO,
            'PROTESTADO'  => AbstractBoleto::SITUACAO_PROTESTADO,
        ];

        $boleto = Arr::dot($boleto);

        return new self(array_merge(array_filter([
            'situacao'        => Arr::get($aSituacao, Arr::get($boleto, 'situacao'), Arr::get($boleto, 'situacao')),
            'nossoNumero'     => Arr::get($boleto, 'nossoNumero'),
            'valor'           => Arr::get($boleto, 'valor'),
            'numero'          => Arr::get($boleto, 'seuNumero'),
            'numeroDocumento' => Arr::get($boleto, 'seuNumero'),
            'dataVencimento'  => Arr::get($boleto, 'dataVencimento')
                ? Carbon::createFromFormat('Y-m-d', Arr::get($boleto, 'dataVencimento'))
                : null,
            'pagador' => array_filter([
                'nome'      => Arr::get($boleto, 'pagador.nome'),
                'documento' => Arr::get($boleto, 'pagador.documento'),
                'endereco'  => Arr::get($boleto, 'pagador.endereco'),
                'cidade'    => Arr::get($boleto, 'pagador.cidade'),
                'uf'        => Arr::get($boleto, 'pagador.uf'),
                'cep'       => Arr::get($boleto, 'pagador.cep'),
            ]),
        ]), $appends));
    }
}
