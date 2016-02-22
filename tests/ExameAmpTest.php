<?php
class ExameAmpTest extends PHPUnit_Framework_TestCase{
  public function testCanBeNegated(){
    $result = new ExameAmp("int.amp.exame.abril.com.br/revista-exame/edicoes/1105/noticias/para-a-rumo-a-all-e-trem-chamado-problema", false, "DEV");

    // $this->assertEquals(-1, result);
  }
}