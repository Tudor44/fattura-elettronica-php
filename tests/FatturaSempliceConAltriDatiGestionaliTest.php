<?php
/**
 * Copyright (c) Gaetano D'Orsi (noirepa)
 * Classe di test per generare una fattura con gli AltriDatiGestionali
 * 
 */

namespace Deved\FatturaElettronica\Tests;

use Deved\FatturaElettronica\Codifiche\ModalitaPagamento;
use Deved\FatturaElettronica\Codifiche\RegimeFiscale;
use Deved\FatturaElettronica\Codifiche\TipoDocumento;
use Deved\FatturaElettronica\FatturaElettronica;
use Deved\FatturaElettronica\FatturaElettronica\FatturaElettronicaBody\DatiBeniServizi\DettaglioLinee;
use Deved\FatturaElettronica\FatturaElettronica\FatturaElettronicaBody\DatiBeniServizi\AltriDatiGestionali;
use Deved\FatturaElettronica\FatturaElettronica\FatturaElettronicaBody\DatiBeniServizi\Linea;
use Deved\FatturaElettronica\FatturaElettronica\FatturaElettronicaBody\DatiGenerali;
use Deved\FatturaElettronica\FatturaElettronica\FatturaElettronicaBody\DatiPagamento;
use Deved\FatturaElettronica\FatturaElettronica\FatturaElettronicaBody\DatiVeicoli;
use Deved\FatturaElettronica\FatturaElettronica\FatturaElettronicaHeader\Common\DatiAnagrafici;
use Deved\FatturaElettronica\FatturaElettronica\FatturaElettronicaHeader\Common\Sede;
use Deved\FatturaElettronica\FatturaElettronicaFactory;
use Deved\FatturaElettronica\XmlValidator;
use PHPUnit\Framework\TestCase;

class FatturaSempliceTest extends TestCase
{
    /**
     * @return DatiAnagrafici
     */
    public function testCreateAnagraficaCedente()
    {
        $anagraficaCedente = new DatiAnagrafici(
            '12345678901',
            'Acme SpA',
            'IT',
            '12345678901',
            RegimeFiscale::Ordinario
        );
        $this->assertInstanceOf(DatiAnagrafici::class, $anagraficaCedente);
        return $anagraficaCedente;
    }

    /**
     * @return Sede
     */
    public function testCreateSedeCedente()
    {
        $sedeCedente = new Sede('IT', 'Via Roma 10', '33018', 'Tarvisio', 'UD');
        $this->assertInstanceOf(Sede::class, $sedeCedente);
        return $sedeCedente;
    }

    /**
     * @depends testCreateAnagraficaCedente
     * @depends testCreateSedeCedente
     * @param DatiAnagrafici $datiAnagrafici
     * @param Sede $sede
     * @return FatturaElettronicaFactory
     */
    public function testCreateFatturaElettronicaFactory(DatiAnagrafici $datiAnagrafici, Sede $sede)
    {
        $feFactory = new FatturaElettronicaFactory(
            $datiAnagrafici,
            $sede,
            '+39123456789',
            'info@deved.it'
        );
        $this->assertInstanceOf(FatturaElettronicaFactory::class, $feFactory);
        return $feFactory;
    }

    /**
     * @return DatiAnagrafici
     */
    public function testCreateAnagraficaCessionario()
    {
        $anaCessionario = new DatiAnagrafici('XYZYZX77M04H888K', 'Pinco Palla');
        $this->assertInstanceOf(DatiAnagrafici::class, $anaCessionario);
        return $anaCessionario;
    }

    /**
     * @return Sede
     */
    public function testCreateSedeCessionario()
    {
        $sedeCessionario = new Sede('IT', 'Via Diaz 35', '33018', 'Tarvisio', 'UD');
        $this->assertInstanceOf(Sede::class, $sedeCessionario);
        return$sedeCessionario;
    }

    /**
     * @depends testCreateFatturaElettronicaFactory
     * @depends testCreateAnagraficaCessionario
     * @depends testCreateSedeCessionario
     * @param FatturaElettronicaFactory $factory
     * @param DatiAnagrafici $datiAnagrafici
     * @param Sede $sede
     * @return FatturaElettronicaFactory
     */
    public function testSetCessionarioCommittente(
        FatturaElettronicaFactory $factory,
        DatiAnagrafici $datiAnagrafici,
        Sede $sede
    ) {
        $factory->setCessionarioCommittente($datiAnagrafici, $sede, null, 'pippo-pec@pluto.it');
        $this->assertInstanceOf(FatturaElettronicaFactory::class, $factory);
        return $factory;
    }

    /**
     * @return DatiGenerali\DatiDdt
     */
    public function testDatiDdt()
    {
        $datiDdt = new DatiGenerali\DatiDdt('A1', '2018-11-10', ['1', '2']);
        $datiDdt->addDatiDdt(new DatiGenerali\DatiDdt('A2', '2018-12-09', ['3', '4']));
        $this->assertInstanceOf(DatiGenerali\DatiDdt::class, $datiDdt);
        return $datiDdt;
    }

    /**
     * @return DatiGenerali\DatiSal
     */
    public function testDatiSal()
    {
        $datiDdt = new DatiGenerali\DatiSal(1);
        $this->assertInstanceOf(DatiGenerali\DatiSal::class, $datiDdt);
        return $datiDdt;
    }

    /**
     * @depends testDatiDdt
     * @depends testDatiSal
     * @param DatiGenerali\DatiDdt $datiDdt
     * @return DatiGenerali
     */
    public function testCreateDatiGenerali(DatiGenerali\DatiDdt $datiDdt, DatiGenerali\DatiSal $datiSal)
    {
        $datiGenerali = new DatiGenerali(
            TipoDocumento::Fattura,
            '2018-11-22',
            '2018221111',
            122
        );
        $datiGenerali->setDatiDdt($datiDdt);
        $datiGenerali->setDatiSal($datiSal);
        $datiGenerali->Causale = "Fattura di prova";
        $this->assertInstanceOf(DatiGenerali::class, $datiGenerali);
        return $datiGenerali;
    }

    /**
     * @return DatiPagamento
     */
    public function testCreateDatiPagamento()
    {
        $datiPagamento = new DatiPagamento(
            ModalitaPagamento::SEPA_CORE,
            '2018-11-30',
            50
        );
        $this->assertInstanceOf(DatiPagamento::class, $datiPagamento);
        return $datiPagamento;
    }


    /**
     * @return array
     */
    public function testCreateLinee()
    {
        $a1 = new AltriDatiGestionali('INTENTO');
        $a2 = new AltriDatiGestionali('DATIGEST2');
        $linee = [];
        $lineaConAltriDatiGestionali = new Linea('Articolo1', 25, '3286340685115', 2, 'pz', 22.00, 'EAN');
        $lineaConAltriDatiGestionali->setAltriDatiGestionali($a1);
        $lineaConAltriDatiGestionali->setAltriDatiGestionali($a2);
        $linee[] = $lineaConAltriDatiGestionali;

        $this->assertCount(1, $linee);
        return $linee;
    }

    /**
     * @param array $linee
     * @depends testCreateLinee
     * @return DettaglioLinee
     */
    public function testCreateDettaglioLinee($linee)
    {
        $dettaglioLinee = new DettaglioLinee($linee);
        $this->assertInstanceOf(DettaglioLinee::class, $dettaglioLinee);
        return $dettaglioLinee;
    }

    /**
     * @return DatiVeicoli
     */
    public function testCreateDatiVeicoli()
    {
        $datiVeicoli = new DatiVeicoli(date('Y-m-d'), '100 KM');
        $this->assertInstanceOf(DatiVeicoli::class, $datiVeicoli);
        return $datiVeicoli;
    }

    /**
     * @depends testSetCessionarioCommittente
     * @depends testCreateDatiGenerali
     * @depends testCreateDatiPagamento
     * @depends testCreateDettaglioLinee
     * @depends testCreateDatiVeicoli
     * @param FatturaElettronicaFactory $factory
     * @param DatiGenerali $datiGenerali
     * @param DatiPagamento $datiPagamento
     * @param DettaglioLinee $dettaglioLinee
     * @param DatiVeicoli $datiVeicoli
     * @return \Deved\FatturaElettronica\FatturaElettronica
     * @throws \Exception
     */
    public function testCreateFattura(
        FatturaElettronicaFactory $factory,
        DatiGenerali $datiGenerali,
        DatiPagamento $datiPagamento,
        DettaglioLinee $dettaglioLinee,
        DatiVeicoli $datiVeicoli
    ) {
        $fattura = $factory->create(
            $datiGenerali,
            $datiPagamento,
            $dettaglioLinee,
            '12345',
            null,
            null,
            $datiVeicoli
        );

        $this->assertInstanceOf(FatturaElettronica::class, $fattura);
        echo $fattura->toXml() ;
        return $fattura;
    }

    /**
     * @depends testCreateFattura
     * @param FatturaElettronica $fattura
     */
    public function testGetNomeFattura(FatturaElettronica $fattura)
    {
        $name = $fattura->getFileName();
        $this->assertTrue(strlen($name) > 5);
    }

    /**
     * @depends testCreateFattura
     * @param FatturaElettronica $fattura
     * @throws \Exception
     */
    public function testXmlSchemaFattura(FatturaElettronica $fattura)
    {
        $this->assertTrue($fattura->verifica());
    }
}
