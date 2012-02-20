<?php

namespace Fuel\Kernel\Application;
use Fuel\Kernel\Loader;
use Fuel\Kernel\Request;

abstract class Base
{
	/**
	 * @var  array  appnames and their classnames
	 */
	protected static $_apps = array();

	/**
	 * Register a new app classname
	 *
	 * @param  string  $appname    Given name for an application
	 * @param  string  $classname  Classname for the application
	 */
	public static function register($appname, $classname)
	{
		static::$_apps[$appname] = $classname;
	}

	/**
	 * Serve the application as configured the name
	 *
	 * @param   string   $appname
	 * @param   Closure  $config
	 * @return  Base
	 * @throws  \OutOfBoundsException
	 */
	public static function load($appname, \Closure $config)
	{
		$loader = _loader()->load_package($appname, Loader::TYPE_APP);
		$loader->set_routable(true);

		if ( ! isset(static::$_apps[$appname]))
		{
			throw new \OutOfBoundsException('Unknown Appname.');
		}

		$class = static::$_apps[$appname];
		return new $class($config, $loader);
	}

	/**
	 * @var  \Fuel\Kernel\Loader\Base  the Application's own loader instance
	 */
	protected $loader;

	/**
	 * @var  array  packages to load
	 */
	protected $packages = array();

	/**
	 * @var  \Fuel\Kernel\Request\Base  contains the app main request object once created
	 */
	protected $request;

	/**
	 * @var  \Fuel\Kernel\Request\Base  current active Request, not necessarily the main request
	 */
	protected $active_request;

	/**
	 * @var  Base  active Application before activation of this one
	 */
	protected $_before_activate;

	/**
	 * @var  \Fuel\Kernel\Response\Base  contains the response object after execution
	 */
	protected $response;

	/**
	 * @var  array  classnames and their 'translation'
	 */
	protected $dic_classes = array();

	/**
	 * @var  array  named instances organized by classname
	 */
	protected $dic_instances = array();

	public function __construct(\Closure $config, Loader\Base $loader)
	{
		$this->loader = $loader;

		foreach ($this->packages as $pkg)
		{
			try
			{
				_loader()->load_package($pkg, Loader::TYPE_PACKAGE);
			}
			// ignore exception thrown for double package load
			catch (\RuntimeException $e) {}
		}
	}

	/**
	 * Create the application main request
	 *
	 * @param   string  $uri
	 * @return  \Fuel\Kernel\Request\Base
	 */
	public function request($uri)
	{
		$this->request = _loader()->forge('Request', $uri);
		return $this;
	}

	/**
	 * Execute the application main request
	 *
	 * @return  Base
	 */
	public function execute()
	{
		$this->activate();
		$this->response = $this->request->execute();
		$this->deactivate();
		return $this;
	}

	/**
	 * Makes this Application the active one
	 *
	 * @return  Base  for method chaining
	 */
	public function activate()
	{
		$this->_before_activate = _env()->active_app();
		_env()->set_active_app($this);
		return $this;
	}

	/**
	 * Deactivates this Application and reactivates the previous active
	 *
	 * @return  Base  for method chaining
	 */
	public function deactivate()
	{
		_env()->set_active_app($this->_before_activate);
		$this->_before_activate = null;
		return $this;
	}

	/**
	 * Return the response object
	 *
	 * @return  \Fuel\Kernel\Response\Base
	 */
	public function response()
	{
		return $this->response->send_headers();
	}

	/**
	 * Attempts to find one or more files in the packages
	 *
	 * @param   string  $location
	 * @param   string  $file
	 * @param   bool    $multiple
	 * @return  array|bool
	 */
	public function find_file($location, $file, $basepath = null, $multiple = false)
	{
		$return = $multiple ? array() : false;

		// First search app
		$path = $this->loader->find_file($location, $file, $basepath);
		if ($path)
		{
			if ( ! $multiple)
			{
				return $path;
			}
			$return[] = $path;
		}

		// If not found or searching for multiple continue with packages
		foreach ($this->packages as $pkg)
		{
			if ($path = _loader()->package($pkg)->find_file($location, $file, $basepath))
			{
				if ( ! $multiple)
				{
					return $path;
				}
				$return[] = $path;
			}
		}

		if ($multiple)
		{
			return $return;
		}

		return false;
	}

	/**
	 * Find multiple files using find_file() method
	 *
	 * @param   $location
	 * @param   $file
	 * @return  array|bool
	 */
	public function find_files($location, $file, $basepath = null)
	{
		return $this->find_file($location, $file, $basepath, true);
	}

	/**
	 * Locate the controller
	 *
	 * @param   string  $controller
	 * @return  bool|string  the controller classname or false on failure
	 */
	public function find_controller($controller)
	{
		// First attempt the package
		if ($found = $this->loader->find_controller($controller))
		{
			return $found;
		}

		// if not found attempt loaded packages
		foreach ($this->packages as $pkg)
		{
			is_array($pkg) and $pkg = reset($pkg);
			if ($found = _loader()->package($pkg)->find_controller($controller))
			{
				return $found;
			}
		}

		// all is lost
		return false;
	}

	/**
	 * Set class that is fetched from the dic classes property
	 *
	 * @param   string  $class
	 * @param   string  $actual
	 * @return  Base    to allow method chaining
	 */
	public function set_dic_class($class, $actual)
	{
		$this->set_dic_classes(array($class => $actual));
		return $this;
	}

	/**
	 * Set classes that are fetched from the dic classes property
	 *
	 * @param   array   $classes
	 * @return  Base    to allow method chaining
	 */
	public function set_dic_classes(array $classes)
	{
		foreach ($classes as $class => $actual)
		{
			$this->dic_classes[$class] = $actual;
		}
		return $this;
	}

	/**
	 * Translates a classname to the one set in the DiC classes property
	 *
	 * @param   string  $class
	 * @return  string
	 */
	public function get_dic_class($class)
	{
		if (isset($this->dic_classes[$class]))
		{
			return $this->dic_classes[$class];
		}

		return _loader()->get_dic_class($class);
	}

	/**
	 * Forges a new object for the given class, supporting DI replacement
	 *
	 * @param   string  $class
	 * @return  object
	 */
	public function forge($class)
	{
		$reflection  = new \ReflectionClass($this->get_dic_class($class));
		$instance    = $reflection->newInstanceArgs(array_slice(func_get_args(), 1));

		// Setter support for the instance to know which app created it
		if (method_exists($instance, '_set_app'))
		{
			$instance->_set_app($this);
		}

		return $instance;
	}

	/**
	 * Register an instance with the DiC
	 *
	 * @param   string  $class
	 * @param   string  $name
	 * @param   object  $instance
	 * @return  Base
	 */
	protected function set_dic_instance($class, $name, $instance)
	{
		$this->dic_instances[$class][$name] = $instance;
		return $this;
	}

	/**
	 * Fetch an instance from the DiC
	 *
	 * @param   string  $class
	 * @param   string  $name
	 * @return  object
	 * @throws  \RuntimeException
	 */
	protected function get_dic_instance($class, $name)
	{
		if ( ! isset($this->dic_instances[$class][$name]))
		{
			return _loader()->get_dic_instance($class, $name);
		}
		return $this->dic_instances[$class][$name];
	}

	/**
	 * Sets the current active request
	 *
	 * @param  \Fuel\Kernel\Request\Base  $request
	 */
	public function set_active_request($request)
	{
		$this->active_request = $request;
	}

	/**
	 * Returns current active Request
	 *
	 * @return  \Fuel\Kernel\Request\Base
	 */
	public function active_request()
	{
		return $this->active_request;
	}
}
