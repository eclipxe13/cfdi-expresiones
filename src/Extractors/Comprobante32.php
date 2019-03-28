<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiExpresiones\Extractors;

use DOMDocument;
use PhpCfdi\CfdiExpresiones\Exceptions\UnmatchedDocumentException;
use PhpCfdi\CfdiExpresiones\ExpressionExtractorInterface;
use PhpCfdi\CfdiExpresiones\Internal\DOMHelper;
use PhpCfdi\CfdiExpresiones\Internal\MatchDetector;

class Comprobante32 implements ExpressionExtractorInterface
{
    /** @var MatchDetector */
    private $matchDetector;

    public function __construct()
    {
        $this->matchDetector = new MatchDetector('http://www.sat.gob.mx/cfd/3', 'cfdi:Comprobante', 'version', '3.2');
    }

    public function matches(DOMDocument $document): bool
    {
        return $this->matchDetector->matches($document);
    }

    public function extract(DOMDocument $document): string
    {
        if (! $this->matches($document)) {
            throw new UnmatchedDocumentException('The document is not a CFDI 3.2');
        }
        $helper = new DOMHelper($document);

        $uuid = $helper->getAttribute('cfdi:Comprobante', 'cfdi:Complemento', 'tfd:TimbreFiscalDigital', 'UUID');
        $rfcEmisor = $helper->getAttribute('cfdi:Comprobante', 'cfdi:Emisor', 'rfc');
        $rfcReceptor = $helper->getAttribute('cfdi:Comprobante', 'cfdi:Receptor', 'rfc');
        $totalComprobante = $helper->getAttribute('cfdi:Comprobante', 'total');

        return '?' . implode('&', [
            're=' . $rfcEmisor,
            'rr=' . $rfcReceptor,
            'tt=' . $this->formatTotal($totalComprobante),
            'id=' . $uuid,
        ]);
    }

    public function formatTotal(string $input): string
    {
        return str_pad(number_format(floatval($input), 6, '.', ''), 17, '0', STR_PAD_LEFT);
    }
}