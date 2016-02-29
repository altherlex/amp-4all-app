<?php
include("src/ExameAmp.php");

if (!isset($argc)){
  error_reporting(0);
  //==== Default for web server =============================================================
  $urlToTranslate = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
  $environment = 'PROD';
}else{
  $devMode    = true;
  $debugMode  = false;
  $environment = 'DEV';

  if ($argv[2] == "debug")
      $debugMode = true;

  //==== Dev mode URL's de teste ========================================================================================================
  // $urlToTranslate = "amp.exame.abril.com.br/mundo/noticias/florida-declara-emergencia-sanitaria-por-novos-casos-de-zika";

  // #=== Infograficos =====================================================================================================================
  // $urlToTranslate = "amp.exame.abril.com.br?url=/marketing/noticias/infografico-mostra-como-os-brasileiros-consomem-midia";

  // #=== Galeria ==========================================================================================================================
  // $urlToTranslate = "int.amp.exame.abril.com.br?url=/marketing/noticias/cvc-dara-10-anos-de-ferias-gratis-para-10-clientes";

  // #=== youtube ==========================================================================================================================
  // $urlToTranslate = "int.amp.exame.abril.com.br?url=/tecnologia/noticias/ondas-gravitacionais-previstas-por-einstein-sao-descobertas";

  // #=== youtube-2 ==========================================================================================================================
  // $urlToTranslate = "amp.exame.abril.com.br/brasil/noticias/5-revelacoes-curiosas-sobre-a-prisao-de-lula-na-ditadura";

  // #=== youtube-3 ==========================================================================================================================
  // $urlToTranslate = "amp.exame.abril.com.br/seu-dinheiro/noticias/calculadora-mostra-retorno-de-investimentos-atrelados-ao-cdi";

  // #=== No img top  ==========================================================================================================================
  // $urlToTranslate = "amp.exame.abril.com.br/revista-exame/edicoes/793/noticias/marcacao-cerrada-m0050482";

  // #=== Twitter ==========================================================================================================================
  // $urlToTranslate = "int.exame.abril.com.br/negocios/noticias/ceo-do-twitter-doa-parte-de-suas-acoes-aos-seus-funcionarios";

  // #=== Instagram ==========================================================================================================================
  // $urlToTranslate = "int.exame.abril.com.br/marketing/noticias/estilista-marc-jacobs-procura-por-modelos-no-instagram";

  #=== Facebook =========================================================================================================================
  // $urlToTranslate = "int.amp.exame.abril.com.br/tecnologia/noticias/e-uma-das-maiores-descobertas-da-ciencia-diz-zuckerberg";

  // #=== Galeria Photos ==========================================================================================================================
  // // $urlToTranslate = "int.amp.exame.abril.com.br?url=negocios/noticias/por-dentro-da-nova-sede-da-hp-inc-em-alphaville";

  // #=== Com imagem no corpo da materia ==========================================================================================================================
  // $urlToTranslate = "int.amp.exame.abril.com.br/tecnologia/noticias/voce-pode-quebrar-seu-iphone-simplesmente-trocando-sua-data";
  
  // #=== Com 2 imagens no corpo da materia ==========================================================================================================================
  // $urlToTranslate = "int.amp.exame.abril.com.br/revista-exame/edicoes/1105/noticias/para-a-rumo-a-all-e-trem-chamado-problema";

  // #=== Scribd ==========================================================================================================================
  $urlToTranslate = "amp.exame.abril.com.br/carreira/noticias/teste-conhecimentos-de-atualidades-com-o-simulado-da-lfg";

  // #=== Autor vazio ====================================================================================================================
  // $urlToTranslate = "int.amp.exame.abril.com.br?url=/negocios/noticias/cade-ira-analisar-com-cuidado-compra-do-hsbc-por-bradesco";

  // #=== materia com Galeria ==========================================================================================================================
  // $urlToTranslate = "int.amp.exame.abril.com.br?url=/marketing/noticias/cvc-dara-10-anos-de-ferias-gratis-para-10-clientes";

  // #=== materia com tipo_recurso == galeria de fotos ==========================================================================================================================
  // $urlToTranslate = "int.amp.exame.abril.com.br?url=/brasil/noticias/alckmin-anuncia-medidas-para-enfrentar-crise-hidrica-em-sao-paulo-2";

  // #=== Imagens sem autor ==========================================================================================================================
  // $urlToTranslate = "int.amp.exame.abril.com.br/tecnologia/noticias/samsung-apresenta-galaxy-s7-com-tela-que-fica-sempre-ligada";
}

$exameAmp = new ExameAmp($urlToTranslate,$debugMode, $environment);
