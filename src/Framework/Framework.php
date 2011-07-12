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

use Framework\Request;

/**
 * The main framework class
 */
class Framework implements \ArrayAccess
{

    //======================//
    //  Private properties  //
    //======================//   
    
    private $_routes;
    private $_route_matched;
    private $_container;

    function __construct()
    {
        $this->_routes = array();
        $this->_route_matched = FALSE;
        $this->_container = array(
            'request' => new Request()
        );
    }
    
    //===================//
    //  Private methods  //
    //===================//   
    
    /**
     * Returns the number of parameters the path accepts. 
     * 
     * @return int
     */
    private function _hasParameters($path)
    {
        return count($this->_getParameterNames($path));
    }
    
    /**
     * Return the parameters from the specified paths. 
     * 
     * @param string $path The path to get the parameters from.
     *
     * @return array
     */
    private function _getParameters($path)
    {
        //return array_diff(explode('/', $this['request']->getPathInfo()), explode('/', $path)); 
        return array_diff(preg_split('/[\/.]/', $this['request']->getPathInfo()), preg_split('/[\/.]/', $path));
    }

    /**
     * Get the names of the parameters specified in the path.
     *
     * @return array
     */
    private function _getParameterNames($path)
    {
        $matches = array();
        preg_match_all('/:[a-zA-Z_][a-zA-Z0-9_]*/', $path, $matches);

        return $matches[0];
    }
    
    /**
     * Get the path with optional parameter data. 
     * 
     * @param array $data Array containing data to insert into the path.
     *
     * @return string
     */
    private function _getPath($path, array $data = array())
    {
        if (empty($data)) {
            // If data array is empty replace
            // parameters in path with regular expression
            $path = str_replace('/', '\/', $path);
            $data = array_fill_keys($this->_getParameterNames($path), '[\w]+');
        } else {
            // If data array is not empty replace
            // parameters with data from array.
            
            // Check if the expected number of
            // parameters in the data array is right.
            if ($this->_hasParameters($path) > count($data)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Expecting %s parameter%s.',    // Error message
                        $this->_hasParameters(),         // Number of parameters
                        (($this->_hasParameters() === 1) ? '' : 's') // Plural?
                    )
                );
            }
            
            $data = array_combine($this->_getParameterNames($path), $data);
        }

        foreach ($data as $key => $value) {
            $path = str_replace($key, $value, $path);
        }

        return $path;
    }
    
    private function _getPathByName($name, $data = array()) {
        if (isset($this->_routes[$name])) {
            $route = $this->_routes[$name];
        
            if (empty($data) && (bool)$this->_hasParameters($route['path'])) {
                throw new Exception('No data passed to path.');
            }
            
            return rtrim($this['request']->getScriptName().$this->_getPath($route['path'], $data), '/');
        }
        
        throw new Exception("There's no path with the name {$name}.");
    }
    
    /**
     * Returns the script name of the
     * file that includes the framework. 
     * 
     * @return string
     */
    private function _getScriptName() {
        $script_name = $_SERVER['SCRIPT_NAME'];

        return substr($script_name, strripos($script_name, '/') + 1);
    }

    /**
     * Returns the current url.  
     * 
     * @return string
     */
    private function _getCurrentUrl() {
        $url = $this->_getBaseUrl();
        $url .= substr($_SERVER['REQUEST_URI'], strripos($_SERVER['REQUEST_URI'], '/'));

        return $url;
    }

    /**
     * Return the url of the script
     * file that includes the framework.
     * 
     * @return string
     */
    private function _getScriptUrl() {
        $url = $this->_getBaseUrl();
        $url .= '/'.$this->_getScriptName();

        return $url;
    }

    /**
     * Returns the base url of the script. 
     * 
     * @return string
     */
    private function _getBaseUrl() {
        $url = $this['request']->getScheme();

        $url .= sprintf('://%s', $this['request']->getHost());
        $url .= $_SERVER['SCRIPT_NAME'];

        return str_replace(sprintf('/%s', $this->_getScriptName()), '', $url);
    }

    //==================//
    //  Public methods  //
    //==================//  
    
    /**
     * Dispatches request to correct route. 
     * 
     * @return void
     */
    public function dispatch()
    {
        foreach ($this->_routes as $route) {
            if ($route['method'] === $this['request']->getMethod()) {
                if (preg_match('/^'.$this->_getPath($route['path']).'$/', $this['request']->getPathInfo())) {
                    ob_start();
                    $output = call_user_func_array($route['action'], $this->_getParameters($route['path']));
                    ob_end_clean();
                    
                    echo $output;
                    return TRUE;
                }
            }
        }

        return FALSE;
    }

    /**
     * Add a new route
     * 
     * @param string $method The HTTP request method.
     * @param string $path   The path to respond to.
     * @param string $action The action associate with the path.
     * @param string $name   The name to associate the path with.
     *
     * @return void
     */
    public function addRoute($method, $path, $action, $name = NULL)
    {
        if (is_null($name)) {
            $this->_routes[] = array(
                'method'    => $method,
                'path'      => $path,
                'action'    => $action
            );
        } else {
            $this->_routes[$name] = array(
                'method'    => $method,
                'path'      => $path,
                'action'    => $action
            );
        }
        
        if ($this->_route_matched === FALSE) {
            $this->_route_matched = $this->dispatch();
        }
    }

    
    //==============================//
    //  ArrayAccess implementation  //
    //==============================//
    
    /**
     * Assigns a route to the specified name.
     * 
     * @param mixed $name  The name to assign the route to.
     * @param mixed $route The route to set.
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (!is_null($offset)) {
            $this->_container[$offset] = $value;
        }
    }

    /**
     * Returns the route with the specified name. 
     * 
     * @param mixed $name The name of the route to retrieve.
     *
     * @return void
     */
    public function offsetGet($offset)
    {
        return isset($this->_container[$offset]) ? $this->_container[$offset] : null;
    }
    
    /**
     * Checks whether or not an route with the specified name exists. 
     * 
     * @param mixed $name The name to check for.
     *
     * @return void
     */
    public function offsetExists($offset)
    {
        return isset($this->_container[$offset]);
    }
    
    /**
     * Unsets the route with the specified name. 
     * 
     * @param mixed $name The name of the route to unset.
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->_container[$offset]);
    }
}

$app = new Framework();

//================================//
//  Public Application Interface  //
//================================//

function get($path, $action, $name = NULL) {
    $GLOBALS['app']->addRoute('GET', $path, $action, $name);
}

function post($path, $action, $name = NULL) {
    $GLOBALS['app']->addRoute('GET', $path, $action, $name);
}

//====================//
//  Helper functions  //
//====================//

function link_to($text, $name, $data = array()) {
    $method = new ReflectionMethod('Framework', '_getPathByName');
    $method->setAccessible(true);
    
    $path = $method->invokeArgs($GLOBALS['app'], array($name, $data));
    
    return sprintf('<a href="%s">%s</a>', $path, $text);
}

function is_assoc_array(array $arr) {
    foreach ($arr as $key => $value) {
        if (!is_string($key)) {
            return false;   
        }
    }
    
    return true;
}
