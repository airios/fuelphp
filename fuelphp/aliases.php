<?php

/**
 * This is a helperfile for IDE code completion, it's never actually used
 *
 * @ignore
 */

namespace Fuel\Aliases\Application
{
	abstract class Base extends \Fuel\Kernel\Application\Base {}
}

namespace Fuel\Aliases\Controller
{
	class Base extends \Fuel\Kernel\Controller\Base {}

	class Template extends \Fuel\Core\Controller\Template {}
}

namespace Fuel\Aliases\Data
{
	abstract class Base extends \Fuel\Kernel\Data\Base {}
}
namespace Fuel\Aliases\Data\Config
{
	class Base extends \Fuel\Kernel\Data\Config\Base {}
}
namespace Fuel\Aliases\Data\Language
{
	class Base extends \Fuel\Kernel\Data\Language\Base {}
}

namespace Fuel\Aliases\DiC
{
	class Base extends \Fuel\DiC\Base {}
}

namespace Fuel\Aliases\Loader
{
	interface Loadable extends \Fuel\Kernel\Loader\Loadable {}
	class Package extends \Fuel\Kernel\Loader\Package {}
}

namespace Fuel\Aliases\Presenter
{
	abstract class Base extends \Fuel\Kernel\Presenter\Base {}
}

namespace Fuel\Aliases\Request
{
	abstract class Base extends \Fuel\Kernel\Request\Base {}
	class Fuel extends \Fuel\Kernel\Request\Fuel {}

	class Curl extends \Fuel\Core\Request\Curl {}
}

namespace Fuel\Aliases\Request\Exception
{
	class Base extends \Fuel\Kernel\Request\Exception\Base {}
}

namespace Fuel\Aliases\Response
{
	class Base extends \Fuel\Kernel\Response\Base {}
}

namespace Fuel\Aliases\Route
{
	abstract class Base extends \Fuel\Kernel\Route\Base {}
	class Fuel extends \Fuel\Kernel\Route\Fuel {}

	class Task extends \Fuel\Kernel\Route\Task {}
}

namespace Fuel\Aliases\Security\Csrf
{
	abstract class Base extends \Fuel\Kernel\Security\Csrf\Base {}
}

namespace Fuel\Aliases\Security\String
{
	abstract class Base extends \Fuel\Kernel\Security\String\Base {}
}

namespace Fuel\Aliases\Task
{
	class Base extends \Fuel\Kernel\Task\Base {}
}

namespace Fuel\Aliases\View
{
	class Base extends \Fuel\Kernel\View\Base {}
}
