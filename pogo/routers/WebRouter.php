<?php

/**
 * Description of WebRouter
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
 * FILE: WebRouter.php
 * DESCRIPTION: Route web requests
 */

namespace Pogo\Routers;
use Pogo\Interfaces\Router;

class WebRouter implements Router
{
    public function parseWebRequest($requestInfo = NULL) {
	$requestInfo = array("url" => $_SERVER["REQUEST_URI"],
			   "params" => $_REQUEST);
	$retvalue = array("controller" => "index",
			    "action" => NULL,
			    "params" => NULL,
			    "format" => "html");
	if(array_key_exists("controller", $requestInfo["params"])) {
	    $retvalue["controller"] = $requestInfo["params"]["controller"];
	}
	if(array_key_exists("action", $requestInfo["params"])) {
	    $retvalue["action"] = $requestInfo["params"]["action"];
	}
	if(array_key_exists("format", $requestInfo["params"])) {
	    $retvalue["format"] = $requestInfo["params"]["format"];
	}
	$removeFromParams = array("controller" => true, "action" => true, "format" => true);
	$retvalue["params"] = array_diff_key($requestInfo["params"],$removeFromParams);
	return $retvalue;
    }
    
    public function createURL($controller, $action, $params, $format) {
	return true;
    }
}
?>
