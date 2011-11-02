<?php

/**
 * Description of Util
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
 * FILE: Util.php
 * DESCRIPTION: DESCRIPTION GOES HERE
 */

namespace Pogo;
use Pogo\Requests\InternalRequest;
use Pogo\Models\User;

class Util
{
    static function fileExistsInPath($fileName) {
	$searchPaths = explode(PATH_SEPARATOR, get_include_path());
	foreach($searchPaths as $searchDir) {
	    if(file_exists($searchDir . DIRECTORY_SEPARATOR . $fileName)) {
		return true;
	    }
	}
	return false;
    }
    
    static function PogoPath($fileList) {
	if(is_array($fileList)) {
	    array_unshift($fileList, Pogo::lock()->getServerRoot());
	}
	else {
	    $fileList = array(Pogo::lock()->getServerRoot(), $fileList);
	}
	return implode(DIRECTORY_SEPARATOR, $fileList);
    }
    
    static function getURL($controller, $action, $parameters = array()) {
	return Pogo::lock()->providers["routing"]->createWebURL($controller, $action, $parameters);
    }
    
    static function getController($name) {
	return Pogo::lock()->controllers[$name];
    }
    
    static function getCurrentUser() {
	// Check whether we have a logged in user
	if(isset($_SESSION["logged_user"]) && $_SESSION["logged_user"]) {
	    return User::find($_SESSION["logged_user"]);
	}
	return NULL;
    }
    
    static function showError($code, $message) {
	$errorParams = array('code' => $code, 'message' => $message);
	$errorRequest = new InternalRequest("error", "display", $errorParams);
	Pogo::lock()->dispatchRequest($errorRequest, true);
    }
    
    static function tryNew($className) {
	if(in_array($className, get_declared_classes())) {
	    return new $className;
	}
	spl_autoload_call($className);
	if(in_array($className, get_declared_classes())) {
	    return new $className;
	}
	return NULL;
    }
}

