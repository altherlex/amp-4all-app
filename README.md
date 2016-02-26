# Exame AMP (Frontend)

App responde http://amp.exame.com/:noticias


## Links Uteis

[CI](http://jenkins.googleamp.abrdigital.com.br/view/GoogleAmp/)

[Documentacao Book](https://confluence.abril.com.br/pages/viewpage.action?title=BOOK+-+GoogleAMP&spaceKey=operacoes)

[eg. API](http://api.exame.abril.com.br/v2/materias/voce-pode-quebrar-seu-iphone-simplesmente-trocando-sua-data)

### Mustache (logic-less template engine)

[How use mustache](https://mustache.github.io/mustache.5.html)

[Mustache in PHP](https://github.com/bobthecow/mustache.php/wiki)

### Parser HTML

[SimpleHTMLdom](http://simplehtmldom.sourceforge.net/manual.htm)
[SimpleHTMLdom for composer](https://github.com/miclf/simple-html-dom)

```
TODO

Remove tag from blacklist with SimpleHTMLdom
```

## Environments

### Stage
ssh t40255@172.16.24.50 -p 5022

```
# add /etc/hosts
172.16.24.50    stage.amp.exame.abril.com.br
```

[http://stage.amp.exame.abril.com.br/](http://stage.amp.exame.abril.com.br/)


### Prod.
ssh t40255@172.16.19.64 -p 5022

## Validations AMP Google

[#Simply](http://amp.exame.abril.com.br/mundo/noticias/florida-declara-emergencia-sanitaria-por-novos-casos-de-zika#development=1)

[Infograficos ](http://amp.exame.abril.com.br/marketing/noticias/infografico-mostra-como-os-brasileiros-consomem-midia#development=1)

[Galeria ](http://amp.exame.abril.com.br/marketing/noticias/cvc-dara-10-anos-de-ferias-gratis-para-10-clientes#development=1)

[youtube ](http://amp.exame.abril.com.br/tecnologia/noticias/ondas-gravitacionais-previstas-por-einstein-sao-descobertas#development=1)

[Twitter ](http://amp.exame.abril.com.br/negocios/noticias/ceo-do-twitter-doa-parte-de-suas-acoes-aos-seus-funcionarios#development=1)

[Instagram ](http://amp.exame.abril.com.br/marketing/noticias/estilista-marc-jacobs-procura-por-modelos-no-instagram#development=1)

[Facebook ](http://amp.exame.abril.com.br/tecnologia/noticias/e-uma-das-maiores-descobertas-da-ciencia-diz-zuckerberg#development=1)

[materia com Galeria ](http://amp.exame.abril.com.br/marketing/noticias/cvc-dara-10-anos-de-ferias-gratis-para-10-clientes#development=1)

[Galeria Photos: expeted 404 error](http://amp.exame.abril.com.br/negocios/noticias/por-dentro-da-nova-sede-da-hp-inc-em-alphaville#development=1)

[Com imagem no corpo da materia ](http://amp.exame.abril.com.br/tecnologia/noticias/voce-pode-quebrar-seu-iphone-simplesmente-trocando-sua-data#development=1)
  
[Com 2 imagens no corpo da materia ](http://amp.exame.abril.com.br/revista-exame/edicoes/1105/noticias/para-a-rumo-a-all-e-trem-chamado-problema#development=1)


## Composer

*Appying dependency management*

```bash
php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
php -r "if (hash('SHA384', file_get_contents('composer-setup.php')) === 'fd26ce67e3b237fffd5e5544b45b0d92c41a4afe3e3f778e942e43ce6be197b9cdc7c251dcde6e2a52297ea269370680') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); }"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

*Installing packages*

```bash
$ php composer.phar install

//Insert yours plugins into composer.json

$ php composer.phar update
```

## App server

[PHP-FPM (FastCGI Process Manager)](http://php-fpm.org/)