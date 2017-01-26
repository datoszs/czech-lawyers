<?php

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;


class RouterFactory
{

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new RouteList;
		$router[] = new Route('advocate/detail/<id>[/<slug>]', 'Advocate:detail');
		$router[] = new Route('advocate/search/<query>', 'Advocate:search');

		$router[] = new Route('case/detail/<id>[/<slug>]', 'Case:detail');
		$router[] = new Route('case/search/<query>[/<match>]', 'Case:search');

		$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
	}

}
