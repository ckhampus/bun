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

require_once('autoload.php');

use Framework\Framework;

$app = new Framework();

//================================//
//  Public Application Interface  //
//================================//

function get($path, $action, $name = NULL) {
    $GLOBALS['app']->addRoute('GET', $path, $action, $name);
}

function post($path, $action, $name = NULL) {
    $GLOBALS['app']->addRoute('POST', $path, $action, $name);
}

function run() {
    $GLOBALS['app']->dispatch();
}

function link_to($text, $name, $data = array()) {
    $method = new ReflectionMethod('Framework\Framework', '_getPathByName');
    $method->setAccessible(true);
    
    $path = $method->invokeArgs($GLOBALS['app'], array($name, $data));
    
    return sprintf('<a href="%s">%s</a>', $path, $text);
}

function cache_control($options) {
    if (!is_assoc_array($options)) {
        throw new InvalidArgumentException('Must be an array.');
    }
}

function sass($stylesheet) {

}

function scss($stylesheet) {

}

//====================//
//  Helper functions  //
//====================//

function is_assoc_array(array $arr) {
    foreach ($arr as $key => $value) {
        if (!is_string($key)) {
            return false;   
        }
    }
    
    return true;
}