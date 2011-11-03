<?php

/**
 * Description of RedirectRequest
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
 * FILE: RedirectRequest.php
 * DESCRIPTION: DESCRIPTION GOES HERE
 */

namespace Pogo\Requests;

use Pogo\Pogo;
use Pogo\Util;

/**
 * A redirecting request - this is executed by sending an HTTP redirect header
 * 
 * The permission check occurs on the WebRequest which is triggered by the subsequent HTTP request
 * 
 * @author predakanga
 * @package Pogo
 * @subpackage Requests
 * @since 0.1
 */
class RedirectRequest extends BaseRequest
{
    public function __construct($controller, $action = NULL, $parameters = array())
    {
	$this->controller = $controller;
	$this->action = $action;
	$this->parameters = $parameters;
	// Set our output format to be the same as the incoming request
	$this->format = Pogo::lock()->getInitiatingRequest()->getOutputFormat();
    }

    public function execute()
    {
	// Grab the correct URL for the target controller/action
	$targetURL = Util::getURL($this->controller, $this->action, $this->parameters);
	// And output the header
	header("Location: $targetURL");
    }
}

?>
