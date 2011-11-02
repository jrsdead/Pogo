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

class Pogo
{
    private static $instance;
    public $config;
    public $cache;
    private $dbuser;
    private $dbpass;
    private $dbhost;
    private $dbdatabase;
    
    private function __construct() {
	$this->config = null;
	$this->cache = null;
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
	
	ActiveRecord\Config::initialize(function($cfg)
	{
	   $cfg->set_model_directory('models');
	   $cfg->set_connections(array(
	       'development' => 'mysql://'.$this->dbuser.":".$this->dbpass."@".$this->dbhost."/".$this->dbdatabase));
	});
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
}
?>
