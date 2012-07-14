<?php
/**
 * Generate Package Loader object and register Application with the Environment
 *
 * @package  App
 */

// Forge and return your Application Package object
return $env->forge('Loader')
	->setRoutable(true)
	->setPath(__DIR__)
	->setNamespace('App')
	->setRelativeClassload(true);
