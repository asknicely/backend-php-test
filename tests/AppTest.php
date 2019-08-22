<?php
use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{
    private $_host = 'http://localhost:1337/';

    public function testHomepage()
    {
        $this->assertTrue($this->_webtest(''));     //homepage test: response 200 expected
    }

    public function testLogin()
    {
        $this->assertTrue($this->_webtest('login'));    //login page test: response 200 expected
    }

    public function testTodos()
    {
        $this->assertTrue($this->_webtest('todo', '302'));  //login page test: response 302 expected
    }

    public function testOneRecord()
    {
        $data = $this->_curl($this->_host . 'todo/2/test');

        //Api test, an array like ['id' => 1, 'user_id' => 1, 'description' => 'description'] will return
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('user_id', $data);
        $this->assertArrayHasKey('description', $data);
    }

    private function _webtest($uri, $status = '200')
    {
        $url = $this->_host . $uri;
        $headers = @get_headers($url);
        $requestOK = strpos($headers[0], $status);
        return $requestOK === false ? false : true;
    }

    private function _curl( $url , $params = array() , $isPost = false , $returnJson = true , $isFile = false ,$userCookie = null , $timeout = 6 )
    {
        $ch = curl_init();
        if( $isPost )
        {
            curl_setopt( $ch , CURLOPT_POST, true );
            $params = $isFile ? $params : http_build_query( $params );
            curl_setopt( $ch , CURLOPT_POSTFIELDS , $params );
        }
        else
        {
            if( strpos( $url , "?" ) === false )
            {
                $url .= "?";
            }

            $url .= http_build_query( $params );
        }
        curl_setopt( $ch , CURLOPT_HTTPHEADER , array( "Expect:" ) );
        curl_setopt( $ch , CURLOPT_RETURNTRANSFER , true );
        curl_setopt( $ch , CURLOPT_CONNECTTIMEOUT , $timeout );
        curl_setopt( $ch , CURLOPT_TIMEOUT , $timeout );
        curl_setopt( $ch , CURLOPT_USERAGENT , 'Curl Helper ' . phpversion() );
        curl_setopt( $ch , CURLOPT_URL , $url );
        if( $userCookie )
        {
            $cookie = '';
            foreach ( $_COOKIE  as $k => $v )
            {
                $k = rawurlencode($k);
                $v = rawurlencode($v);
                $cookie .= "{$k}={$v}; ";
            }
            curl_setopt( $ch , CURLOPT_COOKIE , $cookie );
        }

        if( strpos( $url , "https" ) !== false )
        {
            curl_setopt( $ch , CURLOPT_SSL_VERIFYPEER , false );
        }

        $result = curl_exec($ch);
        
        if( curl_errno( $ch ) > 0 )
        {
            $result = curl_error( $ch );
        }
        curl_close($ch);
        if( $returnJson )
            return json_decode( trim($result) , true );

        return $result;
    }
}
