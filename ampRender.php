<?php
include("src/ExameAmp.php");

if (!isset($argc)){
  //==== Default for web server =============================================================
  //==== Default for web server =============================================================
  $urlToTranslate = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
  $environment = 'PROD';
}else{
  $debugMode  = false;
  $environment = 'DEV';

  if ($argv[2] == "debug")
      $debugMode = true;

  //==== Dev mode URL's de teste ========================================================================================================
  $urlToTranslate = "amp.exame.abril.com.br/mundo/noticias/florida-declara-emergencia-sanitaria-por-novos-casos-de-zika";

  #=== Infograficos =====================================================================================================================
  $urlToTranslate = "amp.exame.abril.com.br?url=/marketing/noticias/infografico-mostra-como-os-brasileiros-consomem-midia";

  #=== Galeria ==========================================================================================================================
  $urlToTranslate = "int.amp.exame.abril.com.br?url=/marketing/noticias/cvc-dara-10-anos-de-ferias-gratis-para-10-clientes";

  #=== youtube ==========================================================================================================================
  $urlToTranslate = "int.amp.exame.abril.com.br?url=/tecnologia/noticias/ondas-gravitacionais-previstas-por-einstein-sao-descobertas";


  #=== Twitter ==========================================================================================================================
  $urlToTranslate = "int.exame.abril.com.br/negocios/noticias/ceo-do-twitter-doa-parte-de-suas-acoes-aos-seus-funcionarios";

  #=== Instagram ==========================================================================================================================
  $urlToTranslate = "int.exame.abril.com.br/marketing/noticias/estilista-marc-jacobs-procura-por-modelos-no-instagram";

  #=== Facebook =========================================================================================================================
  $urlToTranslate = "int.amp.exame.abril.com.br/tecnologia/noticias/e-uma-das-maiores-descobertas-da-ciencia-diz-zuckerberg";

 
  #=== Galeria Photos ==========================================================================================================================
  // $urlToTranslate = "int.amp.exame.abril.com.br?url=negocios/noticias/por-dentro-da-nova-sede-da-hp-inc-em-alphaville";

  #=== Com imagem no corpo da materia ==========================================================================================================================
  $urlToTranslate = "int.amp.exame.abril.com.br/tecnologia/noticias/voce-pode-quebrar-seu-iphone-simplesmente-trocando-sua-data";
  
  #=== Com 2 imagens no corpo da materia ==========================================================================================================================
  $urlToTranslate = "int.amp.exame.abril.com.br/revista-exame/edicoes/1105/noticias/para-a-rumo-a-all-e-trem-chamado-problema";

 #=== materia com Galeria ==========================================================================================================================
  $urlToTranslate = "int.amp.exame.abril.com.br?url=/marketing/noticias/cvc-dara-10-anos-de-ferias-gratis-para-10-clientes";

}

new ExameAmp($urlToTranslate, $debugMode, $environment);
?>
