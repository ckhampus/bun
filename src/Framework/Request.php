<?php
/**
 * Copyright (c) 2011 Cristian Hampus
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Framework;

class Request 
{
    //======================//
    //  Private properties  //
    //======================//

    private $_headers;
    private $_htaccess;
    
    //=====================//
    //  Public properties  //
    //=====================//
    
    public $isGet;
    public $isPost;
    public $isPut;
    public $isDelete;
    
    public $isXhrHttpRequest;
    public $isSecure;
    public $isForwarded;

    function __construct()
    {
        $this->_htaccess = file_exists(dirname($_SERVER['SCRIPT_FILENAME']).'/.htaccess');
    
        if (function_exists('apache_request_headers')) {
            $this->_headers = apache_request_headers();
        } else {
            foreach ($_SERVER as $key => $value) {
                if (substr($key, 0, 5) == "HTTP_") {
                    $key = str_replace('_', ' ', substr($key, 5));
                    $key = str_replace(' ', '-', ucwords(strtolower($key)));
          
                    $this->_headers[$key] = $value;
                }
            }
        }
        
        $this->isGet = ($this->getMethod() === 'GET') ? true : false;
        $this->isPost = ($this->getMethod() === 'POST') ? true : false;
        $this->isPut = ($this->getMethod() === 'PUT') ? true : false;
        $this->isDelete = ($this->getMethod() === 'DELETE') ? true : false;
        
        $this->isSecure = ($this->getScheme() === 'https') ? true : false;
    }

    public function getHeader($name)
    {
        if (isset($this->_headers[$name])) {
            return $this->_headers[$name];
        }

        return null;
    }
    
    public function getBody()
    {
        return @file_get_contents('php://input');
    }
    
    public function getContentLength()
    {
        return $this->getHeader('Content-Length');
    }
    
    public function getScheme() 
    {
        return isset($_SERVER['HTTPS']) ? 'https' : 'http';
    }
    
    public function getPort()
    {
        return $_SERVER['SERVER_PORT'];
    }
    
    public function getPath()
    {
        if (!$this->_htaccess) {
            return $_SERVER['PHP_SELF'];
        }

        return $_SERVER['REQUEST_URI'];
    }
    
    public function getScriptName() 
    {   
        if ($this->_htaccess) {
            return rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        }
    
        return rtrim($_SERVER['SCRIPT_NAME'], '/');
    }
    
    public function getUrl()
    {
        return sprintf('%s://%s%s', $this->getScheme(), $this->getHost(), $this->getPath());
    }
    
    public function getPathInfo() 
    {
        $path = substr($this->getPath(), strlen($this->getScriptName()));
        
        if (empty($path)) {
            return '/';
        }

        return $path;
    }
    
    public function getIp()
    {
        return $_SERVER['REMOTE_ADDR'];
    }
    
    public function getUserAgent() 
    {
        return $this->getHeader('User-Agent');
    }

    public function getHost()
    {
        return $this->getHeader('Host');
    }

    public function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    public function getCookies() 
    {
        return $_COOKIE;
    }
}
