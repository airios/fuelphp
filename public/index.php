<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

/**
 * Configure paths
 * (these constants are helpers and are not required by Fuel itself)
 */
define('DOCROOT', __DIR__.'/');
define('FUELPATH', DOCROOT.'../fuelphp/');
define('APPPATH', FUELPATH.'app/');
define('FUEL_INIT_TIME', microtime(true));
define('FUEL_INIT_MEM', memory_get_usage());

/**
 * Setup environment
 */
require FUELPATH.'fuel/kernel/classes/Fuel/Kernel/Environment.php';
$env = new Fuel\Kernel\Environment();
$env->init(array(
	'name'        => isset($_SERVER['FUEL_ENV']) ? $_SERVER['FUEL_ENV'] : 'development',
	'path'        => FUELPATH,
	'namespaces'  => require FUELPATH.'composer/autoload_namespaces.php',
));

/**
 * Initialize Application in package 'app'
 */
$app = $env->loader->loadApplication('app', function() {});

/**
 * Check to make sure the requester's IP is whitelisted or not.  This does not
 * run the check when in production.
 *
 * To enable the whitelist, add the IP addresses in the app's config 'security.ipWhitelist'.
 *
 * Note: You may remove this if you like, however, note that it adds no overhead while
 * in production.
 */
if ($env->name != 'production' and ! $app->security->checkIpWhitelist())
{
	header('HTTP/1.1 403 Forbidden');
	exit('403 Forbidden');
}

/**
 * Run the app and output the response headers
 */
$response = $app->request($env->input->getPathInfo())->execute()->getResponse()->sendHeaders()->getContent();

/**
 * Compile profiling data
 */
$execTime = round($env->getProfiler()->getTimeElapsed(), 5);
$memUsage = round($env->getProfiler()->getMemUsage() / 1000000, 4);
$memPeakUsage = round($env->getProfiler()->getMemUsage(true) / 1000000, 4);
$events = '';
foreach ($env->getProfiler()->getObservedEvents() as $timestamp => $event)
{
	$events .= '<li>'.str_pad($timestamp, 17, '0').' :: '.implode(' :: ', $event).'</li>';
}

/**
 * Output the response body and replace the profiling values
 */
echo str_replace(
	array('{exec_time}', '{mem_usage}', '{mem_peak_usage}', '{events}'),
	array($execTime,     $memUsage,     $memPeakUsage,      '<ul>'.$events.'</ul>'),
	$response
);
