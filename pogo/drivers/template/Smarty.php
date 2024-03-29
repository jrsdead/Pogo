<?php

/**
 * Description of Smarty
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
 * FILE: Smarty.php
 * DESCRIPTION: DESCRIPTION GOES HERE
 */

namespace Pogo\Drivers\Template;

use Pogo\Pogo;
use Pogo\Util;
use Pogo\Interfaces\Drivers\TemplateDriver;

class Smarty implements TemplateDriver
{
    private $pathList;
    private $smarty;
    private $theme;
    private $formats;
    
    public static function usable() {
	// Try to include the Smarty class, and see whether it exists
	if(!Util::fileExistsInPath("smarty/Smarty.class.php")) {
	    die("No smarty file in " . get_include_path());
	    return false;
	}
	require_once("smarty/Smarty.class.php");
	if(class_exists("\Smarty")) {
	    return true;
	}
	return false;
    }
    
    public function __construct() {
	$this->pathList = array();
	$this->formats = array();

	// Create the smarty object
	$this->smarty = new \Smarty();
	$this->smarty->compile_dir = sys_get_temp_dir();
	$this->addTemplateDir(Pogo::lock()->serverRoot . "pogo" . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "default");
	$this->addTemplateDir(Pogo::lock()->serverRoot . "pogo" . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "pogo");
	// Register our template resource
	$this->smarty->register->resource('pogo', array($this,
                                                      'smartyGetTemplate',
                                                      'smartyGetTimestamp',
                                                      'smartyGetSecure',
                                                      'smartyGetTrusted'));
	// Add a function to generate links to controller actions
	$this->smarty->register->templateFunction('action', array($this, 'smartyGetActionURL'));
	// And tack on our default view directory
	//$this->smarty->addTemplateDir(Util::PogoPath().DIRECTORY_SEPARATOR."views/default");
	//$this->smarty->addTemplateDir(Util::PogoPath().DIRECTORY_SEPARATOR."views/pogo","pogo");
	// And set it to use the "pogo" theme by default
	$this->setTheme("pogo");
    }

    public function connect() {
	return true;
    }
    
    public function disconnect() {
	return true;
    }

    public function addTemplateDir($tmplDir) {
	// Tack the new directory onto the start of the list
	array_unshift($this->pathList, $tmplDir);
	$this->smarty->addTemplateDir($tmplDir);
    }
    public function getTemplateDirs() {
	return array_slice(array_reverse($this->pathList), 1);
    }

    public function setTheme($themeName) {
	$this->theme = $themeName;
    }
    
    private function getCurrentFormat() {
	return $this->formats[0];
    }
    
    private function pushFormat($format) {
	array_unshift($this->formats, $format);
    }
    
    private function popFormat() {
	array_shift($this->formats);
    }
    
    public function render($view, $format, $variables = array(), $statusCode=200, $headers = NULL) {
	if($statusCode != 200) {
	    if($statusCode == 500) {
		header("Status: $statusCode Internal Server Error");
	    }
	    else if($statusCode == 404) {
		header("Status: $statusCode File Not Found");
	    }
	    else {
		header("Status: $statusCode Something Went Wrong.");
	    }
	}
	$this->smarty->clearAllAssign();
	foreach($variables as $key => $value) {
	    $this->smarty->assign($key, $value);
	}
	$this->smarty->assign("pogo", Pogo::lock());
	$this->smarty->assign("user", Util::getCurrentUser());
	$this->pushFormat($format);
	$this->smarty->display($format.DIRECTORY_SEPARATOR.$view . ".tpl");
	$this->popFormat();
    }
    
    public function clearCache() {
	$compiledFiles = glob($this->smarty->compile_dir . DIRECTORY_SEPARATOR . "*.tpl.php");
	foreach($compiledFiles as $file) {
	    unlink($file);
	}
    }
    
    private function getFileFromPathList($filename) {
	$format = $this->getCurrentFormat();
	foreach($this->pathList as $tmplDir) {
	    $prefix = $tmplDir . DIRECTORY_SEPARATOR . $format;
	    if(file_exists($prefix . DIRECTORY_SEPARATOR . $filename)) {
		return $prefix . DIRECTORY_SEPARATOR . $filename;
	    }
	}
	return null;
    }
    
    public function smartyGetTemplate($tmplName, &$tmplSource, $smarty) {
	$filename = $this->getFileFromPathList($tmplName);
	if($filename == null) {
	    return false;
	}
	$tmplSource = file_get_contents($filename);
	return true;
    }
    
    public function smartyGetTimestamp($tmplName, &$tmplTimestamp, $smarty) {
	$filename = $this->getFileFromPathList($tmplName);
	if($filename == null) {
	    return false;
	}
	$tmplTimestamp = filemtime($filename);
	return true;
    }
    
    public function smartyGetSecure() {
	return true;
    }
    
    public function smartyGetTrusted() {
	
    }
    
    public function smartyGetActionURL($params, &$smarty) {
	if(!isset($params["controller"])) {
	    $controller = Pogo::lock()->getExecutingRequest()->getTargetController();
	}
	else {
	    $controller = $params["controller"];
	}
	if(!isset($params["action"])) {
	    $action = Util::getController($controller)->defaultAction();
	}
	else{
	    $action = $params["action"];
	}
	$stripParams = array("controller" => true, "action" => true);
	$realParams = array_diff_key($params, $stripParams);
	
	return Util::getURL($controller, $action, $realParams);
    }
}
?>
