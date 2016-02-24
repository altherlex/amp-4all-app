<?php
require 'vendor/autoload.php';

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
          $this->SetTwitter();
          $this->SetInstagram();
          $this->SetFacebook();
          $this->SetGallery();
          $this->SetCleanStyle(); // Should be last one

          $this->InjectBanner();


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


  private function Render()
  {
    $ampPage = $this->template;

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
      "@IMAGE_TOP_LEGEND" =>  $this->GetImageTopLegend()
    );
    $params = array_merge($this->contentInfo, $this->materiaJson, $params);
    $mustache = new Mustache_Engine;
    print $mustache->render($ampPage, $params);
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



 private function SetYoutubeVideo()
  {
    $content = $this->GetHtmlBody();
    $regexYoutube = '<iframe.*?youtube.com/embed/(.*?)\".*?iframe>';
    preg_match_all("#$regexYoutube#", $content,$vectYoutube); #-> o # substituiu o / da regex
    $particleTemplateYoutube = file_get_contents('templates/embedded/_youtube.tmpl');
    for ($x=0; $x<=count($vectYoutube[0])-1; $x++){
      $regexToChange    = $vectYoutube[0][$x];
      $particleToInject = $particleTemplateYoutube;
      $particleToInject = preg_replace("/<@YOUTUBE-ID>/", $vectYoutube[1][$x],$particleToInject);
      $content          = preg_replace("#$regexToChange#", $particleToInject,$content);
    }

    $this->SetHtmlBody($content);
  }


 private function SetCleanStyle()
  {
    // Clean remanescent style in body
    $content = $this->GetHtmlBody();
    $regexCleanStyle = 'style=\\"[^\\"]*\\"';
    $content = preg_replace("#$regexCleanStyle#",'',$content);
    $this->SetHtmlBody($content);
  }



  private function SetFacebook()
  {
    $content = $this->GetHtmlBody();

    #== Clear Facebook JS =====================================================================================================
    $clearFacebookScript = "<script>\(function.*?facebook.*?facebook.*?>";
    $content = preg_replace("/$clearFacebookScript/","",$content);

    #== Get Facebook ID =======================================================================================================
    $regerxFbUrl = '<div class.*?fb\-post.*?href.*?\"(.*?)\".*?>';
    preg_match_all("/$regerxFbUrl/",$content,$vectFacebookUrls);

    #== Parse each facebook that exist in the page with the new call to amp facebook ===========================================
    $facebookTemplate = '<amp-facebook width=486 height=657 layout="responsive" data-href="<@FACEBOOK-URL>"> </amp-facebook>';
    for ($x=0; $x<=count($vectFacebookUrls[0])-1; $x++)
      {
        $regexToChange    = $vectFacebookUrls[0][$x];
        $particleToInject = $facebookTemplate;
        $particleToInject = preg_replace("/<@FACEBOOK-URL>/",$vectFacebookUrls[1][$x],$particleToInject);
        $content          = preg_replace("#$regexToChange#",$particleToInject,$content);
      }

    #== Clear FB blockquote =================================================================================================
    $content = preg_replace("/\r\n+|\r+|\n+|\t+/i","<##QB##>",$content);
    $regexFlatContent = '<div class.*?fb\-xfbml\-parse\-ignore.*?>.*?<blockquote.*?<\/a><\/blockquote>.*?<##QB##><##QB##><\/div><##QB##><\/div>';
    $content = preg_replace("/$regexFlatContent/","",$content);
    $content = preg_replace("/<##QB##>/","\n",$content);

    $this->SetHtmlBody($content);
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


//<p>.*?<conteudo slug="(.*?)\" tipo_recurso=\"(.*?)\".*?\/><\/p>

  private function SetGallery()
   {


     $content = $this->GetHtmlBody();

     $regexGallery = '<conteudo slug="(.*?)\" tipo_recurso=\"(.*?)\".*?\/>';
     //'<blockquote class=\"twitter.*?status\/(.*?)\">.*?<\/blockquote>';

     preg_match_all("#$regexGallery#m",$content,$vectGallery); #->o # substituiu o / da regex

     if ($vectGallery[2][0] === "galeria multimidia"){


        $apiGallery = file_get_contents('http://api.exame.abril.com.br/v2/materias/'.$vectGallery[1][0]);
        $apiGalleryContent = json_decode($apiGallery,true);

        //Gallery
        $particleTemplateGallery = '<div class="gallery"><h2>'.$apiGalleryContent['titulo'].'</h2><amp-carousel width="auto" height="331">';

        foreach($apiGalleryContent['midias'] as $k=>$midia){

            $particleTemplateGallery .= '<amp-img tabindex="'.$k.'" role="button" src="'.$midia['transformacoes']['590'].'" width="590" height="331" on="tap:lightbox'.$k.'"></amp-img>';
        }

        $particleTemplateGallery .= '</amp-carousel>';


        //Lightbox
        foreach($apiGalleryContent['midias'] as $k=>$midia){

          $midia['corpo'] = strip_tags($midia['corpo'], '<p><a>');

          $particleTemplateGallery .= '
          <amp-lightbox id="lightbox'.$k.'" class="lightbox1" layout="nodisplay">
          <div class="lightbox1-content">
            <div class="image-credit">'.$midia['creditos'].'</div>
            <amp-img id="img'.$k.'" tabindex="'.$k.'" src="'.$midia['transformacoes']['590'].'" width="590" height="331" layout="responsive" on="tap:lightbox'.$k.'.close" role="button"></amp-img>
            <div class="image-caption">'.$midia['alt'].'</div>
            '.$midia['corpo'].'
          </div>
        </amp-lightbox>';
        }

        $particleTemplateGallery .= '</div>';


        $content = str_replace($vectGallery[0][0],$particleTemplateGallery,$content);
        $this->SetHtmlBody($content);
     }
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
