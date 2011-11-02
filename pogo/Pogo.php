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
 * FILE: Pogo.php
 * DESCRIPTION: The Pogo bootstrap file
 */

namespace Pogo;
use Pogo\Controllers\ControllerStore;
use Pogo\Interfaces\Request;
use Pogo\Requests\InternalRequest;
use Pogo\Routers\WebRouter;

class Pogo
{
    private static $instance;
    public $config;
    public $cache;
    private $dbuser;
    private $dbpass;
    private $dbhost;
    private $dbdatabase;
    public $siteName;
    public $serverRoot;
    private $requestStack;
    public $router;
    public $controllers;
    
    private function __construct() {
	$this->config = null;
	$this->cache = null;
	$this->serverRoot;
	$this->siteName = null;
	$this->requestStack = array();
	$this->router = new WebRouter();
	$this->controllers = new ControllerStore();
    }
    
    public static function lock() {
	if(!isset(self::$instance)) {
	    self::$instance = new Pogo();
	}
	return self::$instance;
    }
    
    public static function bootstrap() {
	$serverRoot = dirname ( __FILE__ );
	set_include_path($serverRoot . "/lib" . PATH_SEPARATOR . get_include_path());
	session_start();
	require_once($serverRoot . '/Autoload.php');
	Autoload::loadPogoAutoLoader($serverRoot);
	Pogo::lock()->startup();
    }
    
    private function startup() {
	date_default_timezone_set("UTC");
	$this->config = new Drivers\ConfigReader();
	$this->siteName = Pogo::lock()->config->getKey("Site", "sitename", "Pogo Site");
	$this->serverRoot = Pogo::lock()->config->getKey("Site", "serverroot","Pogo");
	if(!$this->checkDependencies()) {
	    trigger_error("Requested drivers were not useable", E_USER_ERROR);
	}
	if(!$this->connectDrivers()) {
	    trigger_error("Requested drivers could not connect", E_USER_ERROR);
	}
	
	$this->dbuser = Pogo::lock()->config->getKey("ActiveRecord","user","pogo");
	$this->dbpass = Pogo::lock()->config->getKey("ActiveRecord","password","pogopass");
	$this->dbhost = Pogo::lock()->config->getKey("ActiveRecord","host","localhost");
	$this->dbdatabase = Pogo::lock()->config->getKey("ActiveRecord","database","pogo");
	
	require_once 'php-activerecord/ActiveRecord.php';
	
	$cfg = \ActiveRecord\Config::instance();	
	//\ActiveRecord\Config::initialize(function($cfg)
	//{
	   $cfg->set_model_directory('models');
	   $cfg->set_connections(array(
	       'development' => 'mysql://'.$this->dbuser.":".$this->dbpass."@".$this->dbhost."/".$this->dbdatabase));
	//});
    }
    
    private function checkDependencies() {
	$cacheDriver = __NAMESPACE__ . "\\Drivers\\Cache\\". $this->config->getKey("drivers", "cache", "BitBucket");
	
	$retvalue = true;
	$retvalue = $cacheDriver::usable() ? $retvalue : false;
	
	return $retvalue;
    }

    private function connectDrivers() {
	$cacheDriver = __NAMESPACE__ . "\\Drivers\\Cache\\" . $this->config->getKey("drivers", "cache", "BitBucket");
	
	$this->cache = new $cacheDriver();
	
	$retvalue = true;
	$retvalue = $this->cache->connect() ? $retvalue : false;
	
	return $retvalue;
    }
    
    private function registerErrorHandlers() {
	set_error_handler(array($this, "errorHandler"));
	set_exception_handler(array($this, "exceptionHandler"));
	register_shutdown_function(array($this, "fatalErrorHandler"));
	error_reporting(E_ALL ^ E_NOTICE);
    }

    public function errorHandler($errorNo, $errorStr, $errorFile, $errorLine, $errorContext) {
	$err = new Error($errorNo, $errorStr, $errorFile, $errorLine, $errorContext);
	return $this->handlePogoError($err);
    }

    public function exceptionHandler($ex) {
	$err = new Error($ex->getCode(), $ex->getMessage(), $ex->getFile(), $ex->getLine(), $ex);
	return $this->handlePogoError($err);
    }

    public function fatalErrorHandler() {
	$phpErr = error_get_last();

	if($phpErr) {
	    $PogoStream = dirname($_SERVER["SCRIPT_FILENAME"]);
	    chdir($PogoStream);
	    $err = new Error($phpErr["type"], $phpErr["message"], $phpErr["file"], $phpErr["line"], NULL);
	    $this->handlePogoError($err, true);
	}
    }

    public function handlePogoError(Error $err, $forceShow = false) {
	$this->providers["errors"]->logError($err);
	if($err->getErrNo() & E_ERROR || $forceShow) {
	    $parameters = array("code" => 500, "message" => "An error has occurred. Please try again later.", "internalError" => $err);
	    $errorRequest = new InternalRequest("error", "display", $parameters);
	    Pogo::lock()->dispatchRequest($errorRequest, true);
	    die();
	}
	return false;
    }

    public function getSiteName() {
	return $this->siteName;
    }

    public function dispatchRequest(Request $req, $immediate = false) {
	if($immediate) {
	    $req->execute();
	    return;
	}

	$stackOrdinal = count($this->requestStack);
	$this->requestStack[] = $req;

	$req->execute();
    }

    public function getInitiatingRequest() {
	if($this->requestStack && count($this->requestStack)) {
	    return $this->requestStack[0];
	}
	return NULL;
    }

    public function getExecutingRequest() {
	if($this->requestStack && count($this->requestStack)) {
	    return $this->requestStack[count($this->requestStack)-1];
	}
	return NULL;
    }
    
    public function getServerRoot() {
	return $this->serverRoot;
    }
}

?>