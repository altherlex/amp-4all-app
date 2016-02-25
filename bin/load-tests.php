<?php
# php bin/load-tests.php -v=latest
require("src/ExameAmp.php");

$debugMode  = false;
$environment = 'DEV';

$params = Array(
  "simply" => "amp.exame.abril.com.br/mundo/noticias/florida-declara-emergencia-sanitaria-por-novos-casos-de-zika",
  "infograficos"=> "amp.exame.abril.com.br?url=/marketing/noticias/infografico-mostra-como-os-brasileiros-consomem-midia",
  "galeria"=> "int.amp.exame.abril.com.br?url=/marketing/noticias/cvc-dara-10-anos-de-ferias-gratis-para-10-clientes",
  "youtube"=> "int.amp.exame.abril.com.br?url=/tecnologia/noticias/ondas-gravitacionais-previstas-por-einstein-sao-descobertas",
  "youtube_2"=> "amp.exame.abril.com.br/brasil/noticias/5-revelacoes-curiosas-sobre-a-prisao-de-lula-na-ditadura",
  "twitter"=> "int.exame.abril.com.br/negocios/noticias/ceo-do-twitter-doa-parte-de-suas-acoes-aos-seus-funcionarios",
  "instagram"=> "int.exame.abril.com.br/marketing/noticias/estilista-marc-jacobs-procura-por-modelos-no-instagram",
  "facebook"=> "int.amp.exame.abril.com.br/tecnologia/noticias/e-uma-das-maiores-descobertas-da-ciencia-diz-zuckerberg",
  "galeria photos"=> "int.amp.exame.abril.com.br?url=negocios/noticias/por-dentro-da-nova-sede-da-hp-inc-em-alphaville",
  "imagem_na_materia"=> "int.amp.exame.abril.com.br/tecnologia/noticias/voce-pode-quebrar-seu-iphone-simplesmente-trocando-sua-data",
  "duas_imagens_materia"=> "int.amp.exame.abril.com.br/revista-exame/edicoes/1105/noticias/para-a-rumo-a-all-e-trem-chamado-problema",
  "autor_vazio"=> "int.amp.exame.abril.com.br?url=/negocios/noticias/cade-ira-analisar-com-cuidado-compra-do-hsbc-por-bradesco",
  "materia_com_galeria"=> "int.amp.exame.abril.com.br?url=/marketing/noticias/cvc-dara-10-anos-de-ferias-gratis-para-10-clientes",
  "imagens_sem_autor"=> "int.amp.exame.abril.com.br/tecnologia/noticias/samsung-apresenta-galaxy-s7-com-tela-que-fica-sempre-ligada"
);

$tag_version = getopt('v:');
if ( empty($tag_version) && empty($tag_version['v']) )
  $tag_version = system("git describe --tags `git rev-list --tags --max-count=1`");
else
  $tag_version = $tag_version["v"];

while ( ($url = current($params)) !== FALSE ){
  $dir = "./test/tags/";
  $dir .= $tag_version;
  if(!is_dir($dir))
    mkdir($dir);

  ob_start();

    new ExameAmp($url, $debugMode, $environment);
    $out = ob_get_contents();
    file_put_contents( $dir."/".key($params).".html", $out );

  ob_end_clean();

  next($params);
}