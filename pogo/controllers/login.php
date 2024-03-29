<?php

/**
 * Description of login
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
 * FILE: login.php
 * DESCRIPTION: DESCRIPTION GOES HERE
 */

namespace Pogo\Controllers;

use Pogo\Util;
use Pogo\Pogo;
use Pogo\Interfaces\Request;
use Pogo\Models\User as UserModel;
use Pogo\Requests\RedirectRequest;

class login extends BaseController
{
    function defaultAction() {
	return "login";
    }
    
    function runLogin(Request $request) {
	$origRequest = Pogo::lock()->getInitiatingRequest();
	$params = array();
	
	if($request != $origRequest) {
	    $params["origin"] = htmlentities(serialize($origRequest));
	}
	Pogo::lock()->template->render("login/showLogin", $request->getOutputFormat(), $params);
    }
    
    function runTakeLogin(Request $request) {
	$args = $request->getParameters();
	
	$user = UserModel::find_by_username_and_password($args["user"],$args["pass"]);
	
	if(!$user) {
	    Util::showError(401, "Invalid username or password");
	    return;
	}
	
	$_SESSION["logged_user"] = $user->id;
	
	if(isset($args["origin"]) && $args["origin"]) {
	    $origRequest = html_entity_decode(unserialize($args["origin"]));
	    $origRequest->execute();
	}else{
	    $redirRequest = new RedirectRequest("index", NULL);
	    Pogo::lock()->dispatchRequest($redirRequest);
	}
	
	
    }
    
    function runTakeLogout(Request $request) {
	$_SESSION["logged_user"] = NULL;
	$redirRequest = new RedirectRequest("index", NULL);
	Pogo::lock()->dispatchRequest($redirRequest);
    }
}
?>
