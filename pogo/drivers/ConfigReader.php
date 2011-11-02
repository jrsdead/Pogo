<?php

/*
 * Copyright (c) 2011, jrsdead
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the <organization> nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * PROJECT: pogo
 * FILE: ConfigReader.php
 * DESCRIPTION: Read a configuration ini file and its various parts
 */

namespace Pogo\Drivers;
use Pogo\Interfaces\Drivers\Driver;

class ConfigReader implements Driver
{
    /*
    * Constructor
    * @param string $filename location of config file
    */
    public function __construct($filename='config/pogoconfig.ini') {
	$this->filename = null;
	$this->mtime = 0;
	$this->config = null;
	$this->ConfigRead($filename);
    }

    /*
    * Read and store the config file
    * @param string $filename location of config file
    */
    private function ConfigRead($filename) {
	if(file_exists($filename)) {
	    $this->filename = $filename;
	    $this->mtime = filemtime($filename);
	    $this->config = parse_ini_file($filename, true);
	}
    }

    /*
    * Get the filename of the config file
    * @return string
    */
    public function GetFilename() {
	return $this->filename;
    }

    /*
    * Get when the config file was modified
    * @return int
    */
    public function GetModTime() {
	return $this->mtime;
    }

    /*
    * Get item from the config
    * @param string $section Section name
    * @param string $key Key name
    * @param string $default Default value
    * @return string The value of the requested key, the default, or null
    */
    public function GetKey($section, $key, $default = null) {
	if(array_key_exists($section, $this->config) && array_key_exists($key, $this->config[$section])) {
	    return $this->config[$section][$key];
	}
	else {
	    return $default;
	}
    }

    /*
    * Get a full section from the config
    * @param string $section Section Name
    * @return array Associative array of all keys in the section
    */
    public function getSection($section) {
	if(array_key_exists($section, $this->config)) {
	    return $this->config[$section];
	}
	else {
	    return null;
	}
    }

    // Interface implementation
    public static function usable() {
	    return true;
    }

    // Connectionless driver - just return true
    public function connect() {
	    return true;
    }

    // Connectionless driver - just return true
    public function disconnect() {
	    return true;
    }
}
?>
