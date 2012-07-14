<?php

namespace App\Presenter;
use Fuel\Kernel\Environment;
use Fuel\Aliases;

class Welcome extends Aliases\Presenter\Base
{
	public function view()
	{
		$this->presenter  = true;
		$this->version    = Environment::VERSION;
	}
}
