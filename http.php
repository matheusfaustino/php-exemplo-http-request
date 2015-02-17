<?php

class Http {

  private $_url;
  private $_port;
  private $_method;
  private $_host;
  private $_path;
  private $_ip;
  private $_scheme;
  private $_headers = array();

  private $_connection = 'close';
  private $_request    = '';

  private $_fp;
  private $_breakLine = "\r\n";

  private $_responseFirstMessage;
  private $_responseStatus;
  private $_responseHeaders = array();
  private $_responseBody = '';

  public function __construct($full_url, $method) {

    $this->_method = $method;
    $this->_url    = $full_url;

    $this->_completeConfigs();

    $this->_defaultHeaders();
  }

  private function _buildHttpRequest() {
    $this->_request = $this->_method . ' ' . $this->_path . ' HTTP/1.1'. $this->_breakLine;

    foreach ($this->_headers as $nome => $valor) {
      $this->_request .= $nome . ': ' . $valor . $this->_breakLine;
    }

    $this->_request .= $this->_breakLine;
  }

  private function _completeConfigs() {
    $url = parse_url($this->_url);

    $this->_host   = $url['host'];
    $this->_scheme = $url['scheme'];
    $this->_port   = $this->_scheme == 'http' ? 80 : 443;
    $this->_path   = !isset($url['path']) ? '/' : $url['path'];
    $this->_ip     = gethostbyname($this->_host);

  }

  private function _defaultHeaders() {
    $this->addHeader('host',$this->_host);
    $this->addHeader('Connection',$this->_connection);
  }

  private function _readLine() {
    $line = '';

    while (!feof($this->_fp)) {
      $line .= fgets($this->_fp, 2048);
      if ( substr($line, -1) == "\n" )
        return trim($line, "\n");
    }

    return $line;
  }

  private function _validateErrorStatus($status) {

    switch ($status) {
      case 400:
        throw new Exception("Bad Request", 1);
        break;

      case 403:
        throw new Exception("Forbidden", 1);
        break;

      case 404:
        throw new Exception("Not found", 1);
        break;

      case 500:
        throw new Exception("Internal Error", 1);
        break;

      case 503:
        throw new Exception("Unavailable", 1);
        break;
    }

  }

  public function send() {

    $this->_fp = fsockopen($this->_ip, $this->_port);

    $this->_buildHttpRequest();

    fwrite($this->_fp, $this->_request);

    $this->_responseFirstMessage = $this->_readLine();

    $line = explode(" ", $this->_responseFirstMessage);

    $this->_responseStatus = (int) $line[1];

    $this->_validateErrorStatus($this->_responseStatus);

    do {

      $line = $this->_readLine();

      if (!empty($line)){

        $header = explode(':', $line);

        if(isset($header[1]))
          $this->_responseHeaders[$header[0]] = trim($header[1]);

      }

    }while (!feof($this->_fp) && !empty($line));

    do {
      $line = $this->_readLine();
      if($line)
        $this->_responseBody .= $line.PHP_EOL;
    } while(!feof($this->_fp));

    fclose($this->_fp);

    return true;
  }

  public function addHeader($nome,$valor) {
    $this->_headers[$nome] = $valor;
  }

  public function getHeader($nome) {
    return $this->_headers[$nome];
  }

  public function getAllHeaders() {
    return $this->_headers;
  }

  public function getResponseBody() {
    return $this->_responseBody;
  }

  public function getResponseHeaders() {
    return $this->_responseHeaders;
  }

}
