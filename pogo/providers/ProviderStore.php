<?php

/**
 * Description of ProviderStore
 *
 * @author jrsdead
 */
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
 * FILE: ProviderStore.php
 * DESCRIPTION: DESCRIPTION GOES HERE
 */

namespace Pogo\Providers;

use \ArrayAccess;
use Pogo\Interfaces\Providers\Provider;
use Pogo\Exceptions\InvalidProviderException;

class ProviderStore implements ArrayAccess {
    private $providers = array();
    private $defaultProviders = array("routing" => "Pogo\Providers\DefaultRoutingProvider",
					"errors" => "Pogo\Providers\DefaultErrorLogProvider");
    
    public function offsetExists($index) {
	return array_key_exists($index, $this->providers);
    }
    
    public function offsetGet($index) {
	if(array_key_exists($index, $this->providers)) {
	    return $this->providers[$index];
	}
	if(array_key_exists($index, $this->defaultProviders)) {
	    $className = $this->defaultProviders[$index];
	    $this->providers[$index] = new $className;
	    return $this->providers[$index];
	}
	return null;
    }
    
    public function offsetSet($index, $value) {
	if(!array_key_exists($index, $this->providers)) {
	    $this->providers[$index] = $value;
	}
	else {
	    $missingIntfs = array_diff_key(class_implements($this->providers[$index]), class_implements($value));
	    if(count($missingIntfs) > 0) {
		throw new InvalidProviderException($missingIntfs);
	    }
	}
    }
    
    public function offsetUnset($index) {
	return;
    }
}
?>
