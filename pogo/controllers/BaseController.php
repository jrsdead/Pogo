<?php

/**
 * Description of BaseController
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
 * FILE: BaseController.php
 * DESCRIPTION: DESCRIPTION GOES HERE
 */

namespace Pogo\Controllers;

use Pogo\Interfaces\Controller;
use Pogo\Interfaces\Request;

abstract class BaseController implements Controller
{
    public function defaultAction() {
	return "index";
    }
    
    public function hasPermission(Request $request) {
	return true;
    }

    public function runAction(Request $request) {
	$action = $request->getAction();
	if(!$action) {
	    $action = $this->defaultAction();
	}
	$functionName = "run" . $action;
	$actionFunction = $this->lookupActionFunc($functionName);
	
	$this->$actionFunction($request);
    }
    
    public function lookupActionFunc($actionName) {
	$actionFunctions = get_class_methods(get_class($this));
	foreach($actionFunctions as $func) {
	    if(strcasecmp($func, $actionName) == 0) {
		return $func;
	    }
	}
	return NULL;
    }
}

