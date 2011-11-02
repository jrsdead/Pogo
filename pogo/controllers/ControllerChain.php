<?php

/**
 * Description of ControllerChain
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
 * FILE: ControllerChain.php
 * DESCRIPTION: DESCRIPTION GOES HERE
 */

namespace Pogo\Controllers;

use Pogo\Interfaces\Controller;
use Pogo\Interfaces\Request;
use Pogo\Exceptions\MissingActionException;
use Pogo\Models\User;
use Pogo\Pogo;
use Pogo\Util;

class ControllerChain extends BaseController
{
    private $defaultAction;
    private $actionMap = array();
    private $controllers = array();
    private $baseController;
    private $controllerName;
    private $cachedOrder;

    public function __sleep() {
	return array("controllers","baseController","controllerName");
    }

    public function __wakeup() {
	$this->actionMap = array();
	$this->defaultAction = NULL;
    }

    public function __construct($controllerName, $baseController = NULL) {
	$this->controllerName = $controllerName;
	$this->baseController = $baseController;
    }

    private function getControllerOrder() {
	// Return a cached order if we have one
	if($this->cachedOrder) {
	    return $this->cachedOrder;
	}
	// Otherwise, build one
	$this->cachedOrder = array();
	// Then each extension controller
	foreach($this->controllers as $controller) {
	    $this->cachedOrder[] = $controller;
	}
	// And finally, the base controller if we have one
	if($this->baseController) {
	    $this->cachedOrder[] = $this->baseController;
	}
	return $this->cachedOrder;
    }

    public function defaultAction() {
	if($this->defaultAction) {
	    return $this->defaultAction;
	}
	// If none was cached, walk them until we find a non-index one
	foreach($this->getControllerOrder() as $controller) {
	    $action = $controller->defaultAction();
	    if($action != "index") {
		$this->defaultAction = $action;
		return $action;
	    }
	}
	$this->defaultAction = "index";
	return "index";
    }
    
    public function hasPermission(Request $request, User $user =null) {
	// Check each controller in turn
	foreach($this->getControllerOrder() as $controller) {
	    if(!$controller->hasPermission($request, $user)) {
		return false;
	    }
	}
	return true;
    }
    
    public function runAction(Request $request) {
	// Get the action name to use
	$action = $request->getAction();
	if(!$action) {
	    $action = $this->defaultAction();
	}
	// If we have a cached action for this, use it
	if(isset($this->actionMap[$action])) {
	    $this->actionMap[$action]->runAction($request);
	} else {
	    // Otherwise, look through all of the controllers for it
	    foreach($this->getControllerOrder() as $ctrlCheck) {
		// Use the controller's internal mapping function to check for the action
		if($ctrlCheck->lookupActionFunc("run" . $action) != NULL) {
		    // If it has it, cache it, run it, and return
		    $this->actionMap[$action] = $ctrlCheck;
		    $ctrlCheck->runAction($request);
		    return;
		}
	    }
	    // If we reach here, that means that the action isn't implemented.
	    // Throw an exception.
	    var_dump(array($request,$this));
	    throw new MissingActionException($action);
	}
    }
    public function addController(Controller $controller) {
	// Tack the new controller on to the beginning of the list
	array_unshift($this->controllers, $controller);
	// And destroy the cache
	$this->defaultAction = NULL;
	$this->actionMap = array();
	$this->cachedOrder = NULL;
    }
}
