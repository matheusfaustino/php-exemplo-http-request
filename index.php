<?php

require 'http.php';

$url  = '<scheme>://<url>';

if($url == '<scheme>://<url>') die('Não esqueça de alterar a URL'.PHP_EOL);

$classHttp = new Http($url,'GET');

// Se adicionar esse header a página ficará em load pois o arquivo do socket não é fechado
// $classHttp->addHeader('Connection','keep-alive');
$classHttp->addHeader('User-Agent','Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0');
$classHttp->addHeader('Accept','text/html,application/xhtml+xml,application/xml;q=0.9');
$classHttp->addHeader('Accept-Charset','utf-8,ISO-8859-1;q=0.7,*;q=0.3');

$classHttp->send();

var_dump($classHttp->getAllHeaders());
var_dump($classHttp->getResponseHeaders());
// var_dump($classHttp->getResponseBody());
