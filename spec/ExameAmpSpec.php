<?php

namespace spec;

// include("../src/ExameAmp.php");
use ExameAmp;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
  * @param ExameAmp $url,$debugMode, $environment
  */
class ExameAmpSpec extends ObjectBehavior{
  // function it_is_initializable(){
  //   $self = new ExameAmp("int.amp.exame.abril.com.br/revista-exame/edicoes/1105/noticias/para-a-rumo-a-all-e-trem-chamado-problema", false, "DEV");
  //   $self->shouldHaveType('ExameAmp');
  // }
  function it_is_run(){
    $result = shell_exec("php ampRender.php dev");
    $result = new ExameAmp("int.amp.exame.abril.com.br/revista-exame/edicoes/1105/noticias/para-a-rumo-a-all-e-trem-chamado-problema", false, "DEV");
    // var_dump($result);
    $this->shouldHaveType( $result );
  }

}
