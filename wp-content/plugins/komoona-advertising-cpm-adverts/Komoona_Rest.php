<?php
class Kmn_Rest_Exception extends Exception{
    const NOT_MODIFIED = 304; 
    const NOT_AUTHORIZED = 401;
    const BAD_REQUEST = 400; 
    const NOT_FOUND = 404; 
    const NOT_ALOWED = 405; 
    const CONFLICT = 409; 
    const PRECONDITION_FAILED = 412; 
    const INTERNAL_ERROR = 500; 
   
    function __toString()
    {
        switch ($this->code) {
            case Kmn_Rest_Exception::NOT_AUTHORIZED:
                return "Not Authorized";
                break;
            default:
                return parent::__toString();
                break;
        }
    }
} // end of Kmn_Rest_Exception

class Kmn_Rest
{
    private $_host = null;
    private $_port = null;
    private $_user = null;
    private $_pass = null;
    private $_protocol = null;

    const HTTP  = 'http';
    const HTTPS = 'https';
    
    /**
     * Factory of the class. Lazy connect
     *
     * @param string $host
     * @param integer $port
     * @param string $user
     * @param string $pass
     * @return Http
     */
    static public function connect($host, $port = 80, $protocol = self::HTTP) {
        return new self($host, $port, $protocol, false);
    }
   
    private $_silentMode = false;
    /**
     *
     * @param bool $mode
     * @return Http
     */
    public function silentMode($mode=true) {
        $this->_silentMode = $mode;
        return $this;    
    }
    
    protected function __construct($host, $port, $protocol, $connMultiple) {
        $this->_connMultiple = $connMultiple;
        
        $this->_host     = $host;
        $this->_port     = $port;
        $this->_protocol = $protocol;
    }
    
    public function setCredentials($user, $pass) {
        $this->_user = $user;
        $this->_pass = $pass;
    }

    const POST   = 'POST';
    const GET    = 'GET';
    const DELETE = 'DELETE';
  
    /**
     * POST request
     *
     * @param string $url
     * @param array $params
     * @return string
     */
    public function post($url, $params=array()) {
        return $this->_exec(self::POST, $this->_url($url), $params);
    }

    /**
     * GET Request
     *
     * @param string $url
     * @param array $params
     * @return string
     */
    public function get($url, $params=array()) {
        return $this->_exec(self::GET, $this->_url($url), $params);
    }
    
    /**
     * Helper method: post request to server with out using cURL
     * 
     * @param type $url
     * @param array $params
     * @return string 
     */
    static public function http_post($url, $params)
    {
        $res = null;
        
        try {
            $data = http_build_query($params);
            $cparams = array (
                'http' => array (
                    'method' => 'POST',
                    'content' => $data,
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n" . "Content-Length: " . strlen($data) . "\r\n",
                    'timeout' => 60
                )
            );
            $context = stream_context_create($cparams);

            $fp = fopen($url, 'rb', false, $context);
            if($fp) {
                $res = stream_get_contents($fp);
                fclose($fp);
            }
        }
        catch (Exception $ex) {
            throw new Kmn_Rest_Exception($ex->__toString(), $ex->code);
        }

        return $res;
    }
    
    /**
     * Builds absolute url 
     *
     * @param unknown_type $url
     * @return unknown
     */
    private function _url($url=null)
    {
        return "{$this->_protocol}://{$this->_host}:{$this->_port}/{$url}";
    }

    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACEPTED = 202;
    const HTTP_NOT_SUPPORT = 505;

    /**
     * Performing the real request
     *
     * @param string $type
     * @param string $url
     * @param array $params
     * @return string
     */
    private function _exec($type, $url, $params = array())
    {
        $headers = $this->_headers;
        $s = curl_init();
        
        $curl_options = array (
            CURLOPT_USERAGENT      => "Komoona Rest Client API",
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_CONNECTTIMEOUT => 120,      
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_SSL_VERIFYPEER => FALSE
        );
		
        if(!is_null($this->_user)){
           $curl_options[CURLOPT_USERPWD] = $this->_user.':'.$this->_pass;
        }

        curl_setopt($s, CURLOPT_SSL_VERIFYPEER, FALSE);
		
        switch ($type) {
            case self::DELETE:
                $curl_options[CURLOPT_URL] = $url . '?' . http_build_query($params);
                $curl_options[CURLOPT_CUSTOMREQUEST] = self::DELETE;
                break;
            case self::POST:
                $curl_options[CURLOPT_URL] = $url;
                $curl_options[CURLOPT_POST] = TRUE;
                $curl_options[CURLOPT_POSTFIELDS] = $params;
                break;
            case self::GET:
                $curl_options[CURLOPT_URL] = $url . '?' . http_build_query($params);
                break;
        }

        $curl_options[CURLOPT_RETURNTRANSFER] = TRUE;
		
        // set cURL transfer options
        curl_setopt_array($s, $curl_options);
		
        $_out = curl_exec($s);
        $status = curl_getinfo($s, CURLINFO_HTTP_CODE);
        curl_close($s);
        switch ($status) {
            case self::HTTP_OK:
            case self::HTTP_CREATED:
            case self::HTTP_ACEPTED:
            case self::HTTP_NOT_SUPPORT:
                $out = $_out;
                break;
            default:
                if (!$this->_silentMode) {
                    throw new Kmn_Rest_Exception("HTTP Error: {$status}", $status);
                }
        }
        return $out;
    }
} // end of Kmn_Rest

?>