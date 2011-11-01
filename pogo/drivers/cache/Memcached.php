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
 * FILE: Memcached.php
 * DESCRIPTION: Memcached driver
 */

namespace Pogo\Drivers\Cache;
use \Memcached as PHPMemcached;
use Pogo\Pogo;
use Pogo\Interfaces\Drivers\CacheDriver;

class Memcached implements CacheDriver
{
    // Driver implementation
    public static function usable() {
	// Check that the Memcached extension is loaded
	if(class_exists("\Memcached")) {
	    return true;
	}
	return false;
    }
	
    public function __construct() {
	$this->mc = new PHPMemcached();
    }
	
    public function connect() {
	if(!count($this->mc->getServerList())) {
	    $serverAddr = Pogo::lock()->config->getKey("Memcached", "host", "127.0.0.1");
	    $serverPort = Pogo::lock()->config->getKey("Memcached", "port", "11211");
	    return $this->mc->addServer($serverAddr, $serverPort);
	}
	    return true;
    }
	
    public function disconnect() {
	return true;
    }
	
    public function getKey($key, $default = null) {
        $storedValue = $this->mc->get($key);
        if($storedValue === FALSE && $this->mc->getResultCode() == PHPMemcached::RES_NOTFOUND) {
		return $default;
	}
	return $storedValue;
    }
	
    public function setKey($key, $value, $expiry = 0) {
        $this->mc->set($key, $value, $expiry);
    }
	
    public function deleteKey($key) {
        $this->mc->delete($key);
    }
	
    public function keyExists($key) {
        $ignoredVal = $this->mc->get($key);
        if($ignoredVal === FALSE && $this->mc->getResultCode() == PHPMemcached::RES_NOTFOUND) {
	    return false;
	}
	return true;
    }
}
?>
