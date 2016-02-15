<?php
if (!isset($argc))
  {
  //==== Default for web server =============================================================
  $urlToTranslate = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
  $environment = 'PROD';
  }
else
  {
  $devMode    = true;
  $debugMode  = false;
  $environment = 'DEV';
  if ($argv[2] == "debug")
    {
      $debugMode = true;
    }

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

 #=== materia com Galeria ==========================================================================================================================
  $urlToTranslate = "int.amp.exame.abril.com.br?url=/marketing/noticias/cvc-dara-10-anos-de-ferias-gratis-para-10-clientes";

 #=== Galeria Photos ==========================================================================================================================
  // $urlToTranslate = "int.amp.exame.abril.com.br?url=negocios/noticias/por-dentro-da-nova-sede-da-hp-inc-em-alphaville";

  }


$exameAmp = new ExameAmp($urlToTranslate,$debugMode, $environment);

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
      if ($environment=="PROD")
        {
        $templatePath="/opt/abril/googleamp/templates/";
        }
      $this->GetUrlInfo($url);
      $this->SetJsonMateria();
      $this->SetContentType();
      if ($this->tipoRecurso != "404")
        {
          $this->LoadTemplate();
          $this->SetYoutubeVideo();
          $this->SetTwitter();
          $this->SetInstagram();
          $this->SetFacebook();
          $this->SetGallery();

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
    $url        = preg_replace("/(http\:\/\/int\.amp\.|int\.amp\.)/","http://exame.",$url);
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

    $ampPage = preg_replace("/<@TITLE>/"              ,$this->GetHtmlTitle()      ,$ampPage);
    $ampPage = preg_replace("/<@SUB_TITLE>/"          ,$this->GetHtmlSubTitle()   ,$ampPage);
    $ampPage = preg_replace("/<@DATE_PUBLISHED>/"     ,$this->GetDatePublished()  ,$ampPage);
    $ampPage = preg_replace("/<@DATE_MODIFIED>/"      ,$this->GetDateModified()   ,$ampPage);
    $ampPage = preg_replace("/<@DATE_MODIFIED-BR>/"   ,$this->GetDateModifiedBR() ,$ampPage);
    $author = $this->GetAuthor();
    if (isset($author)){$author.=", de ";}
    $ampPage = preg_replace("/<@AUTHOR>/"             ,$author                    ,$ampPage);
    if (!isset($author)){$author.="Exame";}
    $ampPage = preg_replace("/<@AUTHOR-NEWS-ARTICLE>/",$this->GetAuthor()         ,$ampPage);
    $ampPage = preg_replace("/<@BODY>/"               ,$this->GetHtmlBody()       ,$ampPage);
    $ampPage = preg_replace("/<@URL>/"                ,$this->GetUrlPage()        ,$ampPage);
    $ampPage = preg_replace("/<@CHANNEL>/"            ,$this->GetChannel()        ,$ampPage);
    $ampPage = preg_replace("/<@IMAGE_TOP>/"          ,$this->GetImageTop()       ,$ampPage);
    $ampPage = preg_replace("/<@IMAGE_TOP_CREDIT>/"   ,$this->GetImageTopCredit() ,$ampPage);
    $ampPage = preg_replace("/<@IMAGE_TOP_LEGEND>/"   ,$this->GetImageTopLegend() ,$ampPage);

    //$ampPage = preg_replace("/<@AUTHOR>/"     ,$this->GetUrlPage()      ,$ampPage);

    print($ampPage);
  }


  private function AdjustHtmlBody()
    {
      $bodyContent = $this->GetHtmlBody();
      //=== Insere dominio da exame nas chamadas das imagens que estão no corpo da matéria =====
      $bodyContent = preg_replace("/src=\"\/assets\/images\//","src=\"http://exame.abril.com.br/assets/images/",$bodyContent);

      //=== Ajusta a nomenclatura das tags de image para a do image amp ========================
      //<div class=article__body>
      //<@BODY>
      ///</div>
      ///<div class=gallery>

      //$strBody = preg_replace("/\r\n+|\r+|\n+|\t+/i" ,"" ,$bodyContent);
      //exit($strBody);

/*
      //<img .*?src=\"(.*?)".*?title=\"(.*?)\".*?\/>
      $regexImage = '<img .*?src=\"(.*?)\".*?title=\"(.*?)\".*?\/>';
      preg_match_all("/$regexImage/",$bodyContent,$vectBodyImages);

      for ($x=0; $x<=count($vectBodyImages[0])-1; $x++)
        {
          $regexAmpImage = $vectBodyImages[0][$x];
          $regexAmpImage = str_replace(".", "\.", $regexAmpImage);
          $regexAmpImage = str_replace("/", "\/", $regexAmpImage);
          $regexAmpImage = str_replace('"', '\"', $regexAmpImage);
          //print($regexAmpImage);
          //print("\n\n");
          $newImgTag = '<amp-img src="<@IMAGE_AMP>" width="20%" height="90%" layout=responsive class=image></amp-img>';
          $newImgTag = preg_replace("/<@IMAGE_AMP>/",$vectBodyImages[1][$x],$newImgTag);
          //print($newImgTag);
          //print("\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n");
          $bodyContent = preg_replace("/$regexAmpImage/",$newImgTag,$bodyContent);
          //print($bodyContent);
          //exit();
        }
      //exit();
      //<amp-img src="" layout=responsive class=image></amp-img>
*/
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

  private function GetImageTop()
  {
    return $this->materiaJson['midias'][0]['transformacoes']['590'];
  }

  private function GetImageTopCredit()
  {
    return $this->materiaJson['midias'][0]['creditos'];
  }

  private function GetImageTopLegend()
  {
    return preg_replace("/<(|\/)p>/","",$this->materiaJson['midias'][0]['legenda']);
  }

  private function SetYoutubeVideo()
  {
    $content = $this->GetHtmlBody();
    $regexYoutube = '<iframe.*?youtube.com/embed/(.*?)\".*?iframe>';
    preg_match_all("#$regexYoutube#",$content,$vectYoutube); #-> o # substituiu o / da regex
    $particleTemplateYoutube = '<amp-youtube data-videoid="<@YOUTUBE-ID>" layout="responsive" width="480" height="270"></amp-youtube>';

    for ($x=0; $x<=count($vectYoutube[0])-1; $x++)
      {
        $regexToChange    = $vectYoutube[0][$x];
        $particleToInject = $particleTemplateYoutube;
        $particleToInject = preg_replace("/<@YOUTUBE-ID>/",$vectYoutube[1][$x],$particleToInject);
        $content          = preg_replace("#$regexToChange#",$particleToInject,$content);
      }

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

            $particleTemplateGallery .= '<amp-img src="'.$midia['transformacoes']['590'].'" width="590" height="331" on="tap:lightbox'.$k.'"></amp-img>';
        }

        $particleTemplateGallery .= '</amp-carousel>';


        //Lightbox
        foreach($apiGalleryContent['midias'] as $k=>$midia){

          $midia['corpo'] = strip_tags($midia['corpo'], '<p><a>');

          $particleTemplateGallery .= '
          <amp-lightbox id="lightbox'.$k.'" class="lightbox1" layout="nodisplay">
          <div class="lightbox1-content">
            <div class="image-credit">'.$midia['creditos'].'</div>
            <amp-img id="img'.$k.'" src="'.$midia['transformacoes']['590'].'" width="590" height="331" layout="responsive" on="tap:lightbox'.$k.'.close"></amp-img>
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
     <amp-ad width=300 height=250
     type=\"doubleclick\"
     data-slot=\"/9287/exame/home\"
     json='{\"targeting\":{\"position\":[\"amp\"]}}' >
     </amp-ad>
     ";

     $content = preg_replace("/<p>/"                    ,"<p class='ampBanner'>"  ,$content,4);
     $content = preg_replace("/<p class='ampBanner'>/"  ,"<p>"                    ,$content,3);
     $content = preg_replace("/<p class='ampBanner'>/"  ,"$banner<p>"             ,$content);

     $this->SetHtmlBody($content);
   }

  private function Debug()
  {
    // print_r($this->contentInfo);
    // print_r($this->materiaJson['canal']['slug']);
  }
}

?>
