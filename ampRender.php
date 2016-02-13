<?php
if (!isset($argc))
  {
  //==== Default for web server =============================================================
  $urlToTranslate = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
  }
else
  {
  $devMode    = true;
  $debugMode  = false;
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

  #=== Facebook =========================================================================================================================
  $urlToTranslate = "int.amp.exame.abril.com.br/tecnologia/noticias/e-uma-das-maiores-descobertas-da-ciencia-diz-zuckerberg";

  }


$exameAmp = new ExameAmp($urlToTranslate,$debugMode);

Class ExameAmp
{
  private $contentInfo;
  private $materiaJson;
  private $tipoRecurso;
  private $template;
  private $templatePath="templates/"; #$_SERVER['DOCUMENT_ROOT']."/"."templates/";
  private $url;

  public function __construct($url,$debugMode)
    {
      $this->GetUrlInfo($url);
      $this->SetJsonMateria();
      $this->SetContentType();
      if ($this->tipoRecurso != "404")
        {
          $this->LoadTemplate();
          $this->SetYoutubeVideo();
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
    return $this->contentInfo['resultado']['tipo_recurso'];
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

  private function Debug()
  {
    print_r($this->contentInfo);
    print_r($this->materiaJson);
  }
}

?>
