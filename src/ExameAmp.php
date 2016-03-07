<?php
require 'vendor/autoload.php';
include('vendor/simple-html-dom/simple-html-dom/simple_html_dom.php');

Class ExameAmp
{
  private $contentInfo;
  private $materiaJson;
  private $tipoRecurso;
  private $template;
  private $templatePath="templates/"; #$_SERVER['DOCUMENT_ROOT']."/"."templates/";
  private $url;

  public function __construct($url,$debugMode, $environment)
    {
      if ($environment=="PROD"){
        $templatePath="/opt/abril/googleamp/templates/";
      }
      $this->GetUrlInfo($url);
      $this->SetJsonMateria();
      $this->SetContentType();
      if ($this->tipoRecurso != "404")
        {
          $this->LoadTemplate();

          $this->SetImage();
          $this->SetYoutubeVideo();
          $this->SetScribd();
          $this->SetTwitter();
          $this->SetInstagram();
          $this->SetFacebook();
          $this->SetGallery();
          $this->SetCleanStyle(); // Should be last one

          $this->InjectBanner();

          $this->ApplyBlackList();


          $this->Render();
          if ($debugMode){$this->Debug();}
        }
      else
        {
        header('HTTP/1.0 404 Not Found');
        echo "<h1>Error 404 Not Found</h1>";
        echo "The page that you have requested could not be found.";
        exit();
        }
    }

  private function GetUrlInfo($url)
  {
    //exit($url);
    $regexUrl   = '(\.br.*?url=)';
    $url        = preg_replace("/$regexUrl/",".br",$url);
    $url        = preg_replace("/(http\:\/\/int\.amp\.|int\.amp\.)/","http://",$url);
    //$url        = preg_replace("/exame\.abril\.com\.br\/ampRender\.php/","exame.abril.com.br",$url);
    $urlInfo    = file_get_contents("http://api.exame.abril.com.br/tipo_de_recurso?url=".$url);
    $this->url  = $url;
    $this->contentInfo = json_decode($urlInfo,true);
  }

  private function GetTipoRecurso()
  {   
    $returnTipoRecurso = $this->contentInfo['resultado']['tipo_recurso'];
    if ($returnTipoRecurso != "Notícia")
    {
      $returnTipoRecurso = "null";
    }
    return $returnTipoRecurso;
  }

  private function GetSlug()
  {
    return $this->contentInfo['resultado']['slug'];
  }

  private function SetJsonMateria()
  {
    $materiaContent = file_get_contents("http://api.exame.abril.com.br/v2/materias/".$this->GetSlug());
    $this->materiaJson = json_decode($materiaContent,true);
  }


  private function Render(){
    $this->AdjustHtmlBody();
    $author = $this->GetAuthor();

    //Completa a autoria
    if (!empty($author)){
      $author.= $this->GetNewsAgency();
      $author_news_article = $this->GetAuthor();
    } else {
      $author = $this->GetNewsAgency(false);
      $author_news_article = $author;
    }
    
    $params = Array(
      "@TITLE" =>             $this->GetHtmlTitle(),
      "@HEADLINE" =>          $this->GetHtmlHeadline(),
      "@SUB_TITLE" =>         $this->GetHtmlSubTitle(),
      "@DATE_PUBLISHED" =>    $this->GetDatePublished(),
      "@DATE_MODIFIED" =>     $this->GetDateModified(),
      "@DATE_MODIFIED-BR"=>  $this->GetDateModifiedBR(),
      "@AUTHOR" =>            $author,
      "@AUTHOR-NEWS-ARTICLE"=>$author_news_article,
      "@BODY" =>              $this->GetHtmlBody(),
      "@URL" =>               $this->GetUrlPage(),
      "@CHANNEL" =>           $this->GetChannel(),
      "@IMAGE_TOP" =>         $this->GetImageTop(),
      "@IMAGE_TOP_CREDIT" =>  $this->GetImageTopCredit(),
      "@IMAGE_TOP_LEGEND" =>  $this->GetImageTopLegend(),

      // 'loader'             => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/templates'),
      // 'partials_loader'    => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/templates/partials'),
    );

    $params = array_merge($this->contentInfo, $this->materiaJson, $params);
    $mustache = new Mustache_Engine;
    print $mustache->render($this->template, $params);
  }

  private function AdjustHtmlBody(){
    $bodyContent = $this->GetHtmlBody();
    $this->SetHtmlBody($bodyContent);
  }

  private function SetContentType()
  {
    if($this->GetTipoRecurso() == "Notícia")            {$this->tipoRecurso = "materias";}
    if($this->GetTipoRecurso() == "Galeria temática")   {$this->tipoRecurso = "galerias-tematicas";}
    if($this->GetTipoRecurso() == "Galeria multimídia") {$this->tipoRecurso = "galerias-multimidia";}
    if($this->GetTipoRecurso() == "Álbum de fotos")     {$this->tipoRecurso = "galerias-de-imagens";}
    if($this->GetTipoRecurso() == "Cobertura ao vivo")  {$this->tipoRecurso = "ao-vivo";}
    if($this->GetTipoRecurso() == "null")               {$this->tipoRecurso = "404";}
  }

  private function LoadTemplate()
  {
    $this->template = file_get_contents($this->templatePath.$this->tipoRecurso.".tmpl");
  }

  private function GetUrlPage()
  {
    return $this->url;
  }

  private function GetChannel()
  {
    return $this->materiaJson['canal']['titulo'];
  }

  private function GetHtmlTitle()
  {
    return $this->materiaJson['titulo'];
  }

  private function GetHtmlHeadline()
  {
    $headLine = $this->materiaJson['titulo'];
    return str_replace('"', "'", $headLine);
  }

  private function GetHtmlSubTitle()
  {
    return $this->materiaJson['subtitulo'];
  }

  private function GetDatePublished()
  {
    return $this->materiaJson['data_de_publicacao'];
  }

  private function GetDateModified()
  {
    return $this->materiaJson['data_de_atualizacao'];
  }

  private function GetDateModifiedBR()
  {
    preg_match_all("/([0-9]{4})\-([0-9]{2})\-([0-9]{2})T([0-9]{2})\:([0-9]{2})/",$this->materiaJson['data_de_atualizacao'],$vectDate);
    return $vectDate[3][0] . "/" . $vectDate[2][0] . "/" . $vectDate[1][0]  . " " . $vectDate[4][0]  . ":" .  $vectDate[5][0];
  }

  private function GetHtmlBody()
  {
    return $this->materiaJson['corpo'];
  }

  private function SetHtmlBody($bodyContent)
  {
    $this->materiaJson['corpo'] = $bodyContent;
  }

  private function GetAuthor()
  {
    return $this->materiaJson['jornalistas'][0]['nome'];
  }

  private function GetNewsAgency($preposition=true)
  {
    if ($preposition){
      return ", ".$this->materiaJson['fonte']['preposicao']." ".$this->materiaJson['fonte']['nome'];
    }
    else{
      return $this->materiaJson['fonte']['nome']; 
    }
  }

  private function GetImageTop()
  {
    return $this->materiaJson['midias'][0]['transformacoes']['810'];
  }

  private function GetImageTopCredit()
  {
    return $this->materiaJson['midias'][0]['creditos'];
  }

  private function GetImageTopLegend()
  {
    return preg_replace("/<(|\/)p>/","",$this->materiaJson['midias'][0]['legenda']);
  }


  private function SetImage(){
    $content = $this->GetHtmlBody();

    //<div class="info-img-articles"> <img align="" alt="Fotos tiradas com Galaxy S7 e iPhone 6s Plus" height="455" src="/assets/images/2016/2/598895/size_810_16_9_fotos-tiradas-com-galaxy-s7-e-iphone-6s-plus.jpg" title="Fotos tiradas com Galaxy S7 e iPhone 6s Plus" width="810" /> <p class="gray" style="width:810px"> Fotos: Samsung comparou foto do S7 com uma tirada com um iPhone 6s Plus</p> </div> 

    $regexImage = '<div class=\"info-img-articles\">.*?<p class=\"author\".*?>(.*?)<\/p>.*?<img.*? src=\"(.*?)\".*?\/>.*?<p.*?>(.*?)<\/p>.*?<\/div>';
    preg_match_all("#$regexImage#s", $content,$imageBody);

    foreach($imageBody[0] as $k=>$v){

      $particleTemplateImagem = file_get_contents('templates/embedded/_imagem_corpo.tmpl');

      if(!preg_match('#size_810#',$imageBody)){
        
        $patternsSize = array('size_380_','size_460_','size_590_','size_960_');
        $imageBody = str_replace($patternsSize,'size_810_',$imageBody);

      }
      
      $particleTemplateImagem = preg_replace("/<@IMAGE_CREDIT>/", $imageBody[1][$k] ,$particleTemplateImagem);
      $particleTemplateImagem = preg_replace("/<@IMAGE_SRC>/", $imageBody[2][$k] ,$particleTemplateImagem);
      $particleTemplateImagem = preg_replace("/<@IMAGE_CAPTION>/", $imageBody[3][$k] ,$particleTemplateImagem);
      $regexToChange = $imageBody[0][$k];
      $content = preg_replace("#$regexToChange#", $particleTemplateImagem,$content);
      
    }


    //Sem o autor
    $regexImage = '<div class=\"info-img-articles\">.*?<img.*? src=\"(.*?)\".*?\/>.*?<p.*?>(.*?)<\/p>.*?<\/div>';
    preg_match_all("#$regexImage#s", $content,$imageBody);

    foreach($imageBody[0] as $k=>$v){

      $particleTemplateImagem = file_get_contents('templates/embedded/_imagem_corpo.tmpl');

      if(!preg_match('#size_810#',$imageBody)){
        
        $patternsSize = array('size_380_','size_460_','size_590_','size_960_');
        $imageBody = str_replace($patternsSize,'size_810_',$imageBody);

      }
      
      $particleTemplateImagem = preg_replace("/<@IMAGE_CREDIT>/", 'EXAME.com' ,$particleTemplateImagem);
      $particleTemplateImagem = preg_replace("/<@IMAGE_SRC>/", $imageBody[1][$k] ,$particleTemplateImagem);
      $particleTemplateImagem = preg_replace("/<@IMAGE_CAPTION>/", $imageBody[2][$k] ,$particleTemplateImagem);
      $regexToChange = $imageBody[0][$k];
      $content = preg_replace("#$regexToChange#", $particleTemplateImagem,$content);
      
    }

    $this->SetHtmlBody($content);
  }

  private function SetYoutubeVideo(){
    $m = new Mustache_Engine;
    $partial_youtube = file_get_contents('templates/embedded/_youtube.mustache');
    $partial_video = file_get_contents('templates/embedded/_video.mustache');

    $body = str_get_html($this->GetHtmlBody());

    foreach($body->find('iframe') as $element){
      if (preg_match('/youtube/', $element->src)){
        $pattern = '/.*?youtube.com\/embed\/(.*?)/s';
        $element->src = preg_replace($pattern, '', $element->src);
        $element->src = preg_replace('/\?.*/', '', $element->src);
        $element->outertext = $m->render($partial_youtube, $element);
      }elseif (preg_match('/videos.abril/', $element->src)) {
        $element->outertext = $m->render($partial_video, $element);
      }
    }

    $this->SetHtmlBody($body);
  }

  private function SetScribd(){
    $m = new Mustache_Engine;
    $partial = file_get_contents('templates/embedded/_scribd.mustache');

    $body = str_get_html($this->GetHtmlBody());

    foreach($body->find('iframe') as $element){
      if (preg_match('/scribd/', $element->src))
        $element->outertext = $m->render($partial, $element);
    }

    $this->SetHtmlBody($body);
  }

  private function SetCleanStyle()
  {
    // Clean remanescent style in body
    $content = $this->GetHtmlBody();
    $regexCleanStyle = 'style=\\"[^\\"]*\\"';
    $content = preg_replace("#$regexCleanStyle#",'',$content);
    $this->SetHtmlBody($content);
  }

  private function ApplyBlackList(){
    $body = str_get_html($this->GetHtmlBody());
    $blackList = file_get_contents(dirname(__FILE__) . "/BlackList.json");
    $blackList = json_decode($blackList,true);

    if (is_null($blackList))
      trigger_error('Json desconfigurado: src/BlackList.json', E_USER_WARNING);

    // PROHIBITED TAGS
    if (!is_null($blackList["tags"]))
      foreach($blackList["tags"] as $tag)
        foreach($body->find($tag) as $element)
          $element->outertext = "";
    
    // STRIPED TAGS
    if (!is_null($blackList["strip_tags"]))
      foreach($blackList["strip_tags"] as $tag)
        foreach($body->find($tag) as $element)
          $element->outertext = $element->plaintext;

    // STRIPED ATTRIBUTE TAGS
    if (!is_null($blackList["strip_tags_attributes"]))
      foreach($blackList["strip_tags_attributes"] as $tag){
        foreach($body->find($tag) as $element){
          preg_match('#\[(.*?)\]#',$tag,$atributo);
          $element->outertext = preg_replace("#".$atributo[1]."=\".*?\"#","",$element->outertext);
        }  
      }
    
    
    $this->SetHtmlBody($body);
  }



  private function SetFacebook(){
    $m = new Mustache_Engine;
    $partial = file_get_contents('templates/embedded/_facebook.mustache');

    $body = str_get_html($this->GetHtmlBody());

    foreach($body->find('div[class=fb-post]') as $element)
      $element->outertext = $m->render($partial, $element);

    $this->SetHtmlBody($body);
  }

  private function SetTwitter()
   {
     $content = $this->GetHtmlBody();
     $content = str_replace('<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>', '', $content);

     $regexTwitter = '<blockquote class=\"twitter.*?status\/(.*?)\">.*?<\/blockquote>';

     preg_match_all("#$regexTwitter#s",$content,$vectTwitter); #-> o # substituiu o / da regex

     $particleTemplateTwitter = '<amp-twitter width=486 height=657 layout="responsive" data-tweetid="<@TWITTER>" data-cards="hidden">';

     for ($x=0; $x<=count($vectTwitter[0])-1; $x++)
     {
       $regexToChange    = $vectTwitter[0][$x];
       $particleToInject = $particleTemplateTwitter;
       $particleToInject = preg_replace("/<@TWITTER>/",$vectTwitter[1][$x],$particleToInject);
       $content          = str_replace("$regexToChange",$particleToInject,$content);
     }
     $this->SetHtmlBody($content);
   }

  private function SetInstagram()
   {
     $content = $this->GetHtmlBody();
     $content = str_replace('<script async defer src="//platform.instagram.com/en_US/embeds.js"></script>', '',$content);
     $regexInstagram = '<blockquote class=\"instagram.*?p/(.*?)\/\".*?<\/blockquote>';
     //'<blockquote class=\"twitter.*?status\/(.*?)\">.*?<\/blockquote>';

     preg_match_all("#$regexInstagram#s",$content,$vectInstagram); #->o # substituiu o / da regex

     $particleTemplateInstagram = '<amp-instagram data-shortcode="<@INSTAGRAM>" width="400" height="400" layout="responsive"></amp-instagram>';

     for ($x=0; $x<=count($vectInstagram[0])-1; $x++)
     {
         $regexToChange    = $vectInstagram[0][$x];
         $particleToInject = $particleTemplateInstagram;
         $particleToInject = preg_replace("/<@INSTAGRAM>/",$vectInstagram[1][$x],$particleToInject);
         $content          = str_replace("$regexToChange",$particleToInject,$content);
     }
     $this->SetHtmlBody($content);
   }

  private function SetGallery(){
    $m = new Mustache_Engine;
    $partial = file_get_contents('templates/embedded/_galeria_multimidia.mustache');

    $body = str_get_html($this->GetHtmlBody());

    foreach($body->find('conteudo') as $element)
      switch ($element->tipo_recurso){
        case 'galeria multimidia':
          $apiGallery = file_get_contents('http://api.exame.abril.com.br/v2/materias/'.$element->slug);
          $apiGallery = json_decode($apiGallery,true);
          $element->outertext = $m->render($partial, $apiGallery);
          break;
        case 'galeria de fotos':
          $apiGallery = file_get_contents('http://api.exame.abril.com.br/v2/album-de-fotos/'.$element->slug);
          $apiGallery = json_decode($apiGallery,true);
          $element->outertext = $m->render($partial, $apiGallery);
          break;
      }

    $this->SetHtmlBody($body);
  }

   private function InjectBanner()
   {
     $content = $this->GetHtmlBody();

     $banner = "
     <!-- Rectangle -->
     <div class=\"ad-container\">
     <amp-ad width=300 height=250
     type=\"doubleclick\"
     data-slot=\"/9287/exame/home\"
     json='{\"targeting\":{\"position\":[\"amp\"]}}' >
     </amp-ad></div>
     ";

     $content = preg_replace("/<p>/"                    ,"<p class='ampBanner'>"  ,$content,4);
     $content = preg_replace("/<p class='ampBanner'>/"  ,"<p>"                    ,$content,3);
     $content = preg_replace("/<p class='ampBanner'>/"  ,"$banner<p>"             ,$content);

     $this->SetHtmlBody($content);
   }

  private function Debug(){
    print_r($this->contentInfo);
    print_r($this->materiaJson['canal']['slug']);
  }
}
