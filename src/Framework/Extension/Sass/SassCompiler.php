<?php

namespace Framework\Extension\Sass;

use Symfony\Component\Process\Process;

class SassCompiler {
    private $_process;
    
    function __construct($stylesheet, $output_dir) 
    {
        $this->_process = new Process('sass');
    }
}