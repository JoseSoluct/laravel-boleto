<?php

namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class Bv extends AbstractBoleto implements BoletoContract
{
    /**
     * Local de pagamento
     *
     * @var string
     */
    protected $localPagamento = 'Pagável em canais eletrônicos, agências ou correspondentes';

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_BV;

    /**
     * Define as carteiras disponíveis para este banco
     * 001 Cobrança Escritural para Operação de Desconto
     * 200 Cobrança Escritural para Carteira Garantia
     * 300 Cobrança Escritural para Carteira Simples
     * 400 Cobrança Direta (Pré-Impressa) para Carteira Garantia
     * 500 Cobrança Direta (Pré-Impressa) para Carteira Simples
     *
     * @var array
     */
    protected $carteiras = [1, 200, 300, 400, 500];

    /**
     * Espécie do documento, coódigo para remessa
     *
     * @var string
     */
    protected $especiesCodigo = [
        'DM' => '01',
        'DS' => '08',
        'FT' => '31',
    ];

    /**
     * Codigo do convenio junto ao banco.
     *
     * @var string
     */
    protected $convenio;

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->setCamposObrigatorios('conta', 'numero', 'convenio', 'carteira');
    }

    /**
     * Gera o Nosso Número.
     *
     * @return string
     */
    protected function gerarNossoNumero()
    {
        return Util::numberFormatGeral($this->getNumero(), 9) . CalculoDV::bvNossoNumero($this->getNumero());
    }

    /**
     * Método que retorna o nosso número usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        return substr_replace($this->getNossoNumero(), '-', -1, 0);
    }

    /**
     * Método para gerar o código da posição de 20 a 44
     *
     * @return string
     */
    protected function getCampoLivre()
    {
        if ($this->campoLivre) {
            return $this->campoLivre;
        }

        $campoLivre = Util::numberFormatGeral($this->getCodigoCliente(), 10);
        $campoLivre .= Util::numberFormatGeral($this->getCarteira(), 3);
        $campoLivre .= Util::numberFormatGeral($this->getNossoNumero(), 10);
        $campoLivre .= '00';

        return $this->campoLivre = $campoLivre;
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
            'codigoCliente'   => null,
            'agenciaDv'       => null,
            'convenio'        => substr($campoLivre, 0, 10),
            'carteira'        => substr($campoLivre, 11, 3),
            'nossoNumero'     => substr($campoLivre, 14, 9),
            'nossoNumeroDv'   => null,
            'nossoNumeroFull' => substr($campoLivre, 14, 10),
            'agencia'         => null,
            'contaCorrente'   => null,
            'contaCorrenteDv' => null,
        ];
    }

    /**
     * Retorna o convenio do cliente.
     *
     * @return mixed
     */
    public function getCodigoCliente()
    {
        return $this->convenio;
    }

    /**
     * Seta o convenio do cliente.
     *
     * @param mixed $convenio
     *
     * @return Bv
     */
    public function setConvenio($convenio)
    {
        $this->convenio = $convenio;

        return $this;
    }

    /**
     * @return string
     */
    public function getAgenciaCodigoBeneficiario()
    {
        return sprintf('%04s/%012s', $this->getAgencia() ?: 1, $this->getCodigoCliente());
    }
}
