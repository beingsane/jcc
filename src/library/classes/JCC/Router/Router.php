<?php

/**
* Router from Joomla Pythagoras 
 * https://github.com/joomla-projects/joomla-pythagoras/blob/master/libraries/incubator/Router/Router.php
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later;
see LICENSE
 */


/*
ttps://github.com/joomla-projects/joomla-pythagoras/blob/master/docs/router.md
$router = new Router;
$router
    ->addRoute('GET', '/article/:article_id', 'Acme\\Article') // Route to action
	->addRoute('GET', '/component/*', function() {
	return true;
}
) // Route to Closure
	->addRoute('GET', '/user/:id', 'get_user');
// Route to function / callable

function get_user($id) {
	// 	do stuff
}
http://www.htmlist.com/development/extending-php-5-3-closures-with-serialization-and-reflection/
*/

namespace JCC\Router;

use Jeremeamia\SuperClosure\SerializableClosure;


class Router implements \Serializable
{
	
	/**
	* An array of rules, each rule being an associative for routing the request.
		 *
		 * Example: array(
		 *     'regex' => $regex,
		 *     'vars' => $vars,
		 *     'action' => $action
		 * )
		 *
		 * @var    array
		 * @since  __DEPLOY_VERSION__
		 */
		protected $routes = [
			'GET'     => [],
			'PUT'     => [],
			'POST'    => [],
			'DELETE'  => [],
			'HEAD'    => [],
			'OPTIONS' => [],
			'TRACE'   => [],
			'PATCH'   => []
		];
	
	
	/**
	* Constructor.
		 *
		 * @param   array  $maps  An optional array of route maps
		 *
		 * @since   1.0
		 */
		public function __construct(array $maps = [])
		{
		if (! empty($maps))
				{
			$this->addRoutes($maps);
		}
	}
	
	
	/**
	* Add a route of the specified method to the router. If the pattern already exists it will be overwritten.
		 *
		 * @param   string  $method      Request method to match. One of GET, POST, PUT, DELETE, HEAD, OPTIONS, TRACE or PATCH
		 * @param   string  $pattern     The route pattern to use for matching.
		 * @param   mixed   $action  The action to map to the given pattern.
		 * @param   array   $rules       An array of regex rules keyed using the named route variables.
		 *
		 * @return  $this
		 *
		 * @since   __DEPLOY_VERSION__
		 */
		public function addRoute($method, $pattern, $action, array $rules = [])
		{
		list($regex, $vars) = $this->buildRegexAndVarList($pattern, $rules);
		
		$this->routes[strtoupper($method)][] = [
					'regex'      => $regex,
					'vars'       => $vars,
					'action' => $action
				];
		
		return $this;
	}
	
	
	/**
	* Parse the given pattern to extract the named variables and build a proper regular expression for use when parsing the routes.
		 *
		 * @param   string  $pattern  The route pattern to use for matching.
		 * @param   array   $rules    An array of regex rules keyed using the named route variables.
		 *
		 * @return  array
		 *
		 * @since   __DEPLOY_VERSION__
		 */
		protected function buildRegexAndVarList($pattern, array $rules = [])
		{
		// 		Sanitize and explode the pattern.
				$pattern = explode('/', trim(parse_url((string) $pattern, PHP_URL_PATH), ' /'));
		
		// 		Prepare the route variables
				$vars = [];
		
		// 		Initialize regular expression
				$regex = [];
		
		// 		Loop on each segment
				foreach ($pattern as $segment)
				{
			if ($segment == '*')
						{
				// 				Match a splat with no variable.
								$regex[] = '.*';
			}
			elseif (isset($segment[0]) && $segment[0] == '*')
						{
				// 				Match a splat and capture the data to a named variable.
								$vars[] = substr($segment, 1);
				$regex[] = '(.*)';
			}
			elseif (isset($segment[0]) && $segment[0] == '\\' && $segment[1] == '*')
						{
				// 				Match an escaped splat segment.
								$regex[] = '\*' . preg_quote(substr($segment, 2));
			}
			elseif ($segment == ':')
						{
				// 				Match an unnamed variable without capture.
								$regex[] = '([^/]*)';
			}
			elseif (isset($segment[0]) && $segment[0] == ':')
						{
				// 				Match a named variable and capture the data.
								$varName = substr($segment, 1);
				$vars[] = $varName;
				
				// 				Use the regex in the rules array if it has been defined.
								$regex[] = array_key_exists($varName, $rules) ? '(' . $rules[$varName] . ')' : '([^/]*)';
			}
			elseif (isset($segment[0]) && $segment[0] == '\\' && $segment[1] == ':')
						{
				// 				Match a segment with an escaped variable character prefix.
								$regex[] = preg_quote(substr($segment, 1));
			}
			else
						{
				// 				Match the standard segment.
								$regex[] = preg_quote($segment);
			}
		}
		
		return [
					chr(1) . '^' . implode('/', $regex) . '$' . chr(1),
					$vars
				];
	}
	
	
	/**
	* Add an array of route maps to the router.  If the pattern already exists it will be overwritten.
		 *
		 * @param   array  $routes  A list of route maps to add to the router as $pattern => $action.
		 *
		 * @return  $this
		 *
		 * @since   __DEPLOY_VERSION__
		 * @throws  \UnexpectedValueException  If missing the `pattern` or `action` keys from the map.
		 */
		public function addRoutes(array $routes)
		{
		foreach ($routes as $route)
				{
			// 			Ensure a `pattern` key exists
						if (! array_key_exists('pattern', $route))
						{
				throw new \UnexpectedValueException('Route map must contain a pattern variable.');
			}
			
			// 			Ensure a `action` key exists
						if (! array_key_exists('action', $route))
						{
				throw new \UnexpectedValueException('Route map must contain a action variable.');
			}
			
			// 			If rules have been specified, add them as well.
						$rules  = array_key_exists('rules', $route) ? $route['rules'] : [];
			$method = array_key_exists('method', $route) ? $route['method'] : 'GET';
			
			$this->addRoute($method, $route['pattern'], $route['action'], $rules);
		}
		
		return $this;
	}
	
	
	/**
	* Parse the given route and return the name of a action mapped to the given route.
		 *
		 * @param   string  $route   The route string for which to find and execute a action.
		 * @param   string  $method  Request method to match. One of GET, POST, PUT, DELETE, HEAD, OPTIONS, TRACE or PATCH
		 *
		 * @return  array   An array containing the action and the matched variables.
		 *
		 * @since   1.0
		 * @throws  \InvalidArgumentException
		 */
		public function parseRoute($route, $method = 'GET')
		{
		$method = strtoupper($method);
		
		if (! array_key_exists($method, $this->routes))
				{
			throw new \InvalidArgumentException(sprintf('%s is not a valid HTTP method.', $method));
		}
		
		// 		Get the path from the route and remove and leading or trailing slash.
				$route = trim(parse_url($route, PHP_URL_PATH), ' /');
		
		// 		Iterate through all of the known routes looking for a match.
				foreach ($this->routes[$method] as $rule)
				{
			if (preg_match($rule['regex'], $route, $matches))
						{
				// 				If we have gotten this far then we have a positive match.
								$vars = [];
				
				foreach ($rule['vars'] as $i => $var)
								{
					$vars[$var] = $matches[$i + 1];
				}
				
				return [
									'action' => $rule['action'],
									'vars'       => $vars
								];
			}
		}
		
		throw new \InvalidArgumentException(sprintf('Unable to handle request for route `%s`.', $route), 404);
	}
	
	
	/**
	* Add a GET route to the router. If the pattern already exists it will be overwritten.
		 *
		 * @param   string  $pattern     The route pattern to use for matching.
		 * @param   mixed   $action  The action to map to the given pattern.
		 * @param   array   $rules       An array of regex rules keyed using the route variables.
		 *
		 * @return  $this
		 *
		 * @since   __DEPLOY_VERSION__
		 */
		public function get($pattern, $action, array $rules = [])
		{
		return $this->addRoute('GET', $pattern, $action, $rules);
	}
	
	
	/**
	* Add a POST route to the router. If the pattern already exists it will be overwritten.
		 *
		 * @param   string  $pattern     The route pattern to use for matching.
		 * @param   mixed   $action  The action to map to the given pattern.
		 * @param   array   $rules       An array of regex rules keyed using the route variables.
		 *
		 * @return  $this
		 *
		 * @since   __DEPLOY_VERSION__
		 */
		public function post($pattern, $action, array $rules = [])
		{
		return $this->addRoute('POST', $pattern, $action, $rules);
	}
	
	
	/**
	* Add a PUT route to the router. If the pattern already exists it will be overwritten.
		 *
		 * @param   string  $pattern     The route pattern to use for matching.
		 * @param   mixed   $action  The action to map to the given pattern.
		 * @param   array   $rules       An array of regex rules keyed using the route variables.
		 *
		 * @return  $this
		 *
		 * @since   __DEPLOY_VERSION__
		 */
		public function put($pattern, $action, array $rules = [])
		{
		return $this->addRoute('PUT', $pattern, $action, $rules);
	}
	
	
	/**
	* Add a DELETE route to the router. If the pattern already exists it will be overwritten.
		 *
		 * @param   string  $pattern     The route pattern to use for matching.
		 * @param   mixed   $action  The action to map to the given pattern.
		 * @param   array   $rules       An array of regex rules keyed using the route variables.
		 *
		 * @return  $this
		 *
		 * @since   __DEPLOY_VERSION__
		 */
		public function delete($pattern, $action, array $rules = [])
		{
		return $this->addRoute('DELETE', $pattern, $action, $rules);
	}
	
	
	/**
	* Add a HEAD route to the router. If the pattern already exists it will be overwritten.
		 *
		 * @param   string  $pattern     The route pattern to use for matching.
		 * @param   mixed   $action  The action to map to the given pattern.
		 * @param   array   $rules       An array of regex rules keyed using the route variables.
		 *
		 * @return  $this
		 *
		 * @since   __DEPLOY_VERSION__
		 */
		public function head($pattern, $action, array $rules = [])
		{
		return $this->addRoute('HEAD', $pattern, $action, $rules);
	}
	
	
	/**
	* Add a OPTIONS route to the router. If the pattern already exists it will be overwritten.
		 *
		 * @param   string  $pattern     The route pattern to use for matching.
		 * @param   mixed   $action  The action to map to the given pattern.
		 * @param   array   $rules       An array of regex rules keyed using the route variables.
		 *
		 * @return  $this
		 *
		 * @since   __DEPLOY_VERSION__
		 */
		public function options($pattern, $action, array $rules = [])
		{
		return $this->addRoute('OPTIONS', $pattern, $action, $rules);
	}
	
	
	/**
	* Add a TRACE route to the router. If the pattern already exists it will be overwritten.
		 *
		 * @param   string  $pattern     The route pattern to use for matching.
		 * @param   mixed   $action  The action to map to the given pattern.
		 * @param   array   $rules       An array of regex rules keyed using the route variables.
		 *
		 * @return  $this
		 *
		 * @since   __DEPLOY_VERSION__
		 */
		public function trace($pattern, $action, array $rules = [])
		{
		return $this->addRoute('TRACE', $pattern, $action, $rules);
	}
	
	
	/**
	* Add a PATCH route to the router. If the pattern already exists it will be overwritten.
		 *
		 * @param   string  $pattern     The route pattern to use for matching.
		 * @param   mixed   $action  The action to map to the given pattern.
		 * @param   array   $rules       An array of regex rules keyed using the route variables.
		 *
		 * @return  $this
		 *
		 * @since   __DEPLOY_VERSION__
		 */
		public function patch($pattern, $action, array $rules = [])
		{
		return $this->addRoute('PATCH', $pattern, $action, $rules);
	}
	
	
	/**
	* Add a UNIVERSAL (catchall) route to the router. If the pattern already exists it will be overwritten.
		 *
		 * @param   string  $pattern     The route pattern to use for matching.
		 * @param   mixed   $action  The action to map to the given pattern.
		 * @param   array   $rules       An array of regex rules keyed using the route variables.
		 *
		 * @return  $this
		 *
		 * @since   __DEPLOY_VERSION__
		 */
		public function all($pattern, $action, array $rules = [])
		{
		list($regex, $vars) = $this->buildRegexAndVarList($pattern, $rules);
		
		foreach ($this->routes as $method => $routes)
				{
			$this->routes[$method][] = [
							'regex'      => $regex,
							'vars'       => $vars,
							'action' => $action
						];
		}
		
		return $this;
	}
	
	
	/**
	* String representation of the Router object
		 *
		 * @return  string  The string representation of the object or null
		 *
		 * @link    http://p	hp.net/manual/en/serializable.serialize.php
		 * @since   __DEPLOY_VERSION__
		 */
		public function serialize()
		{
		$routesCopy = $this->routes;
		
		foreach ($routesCopy as $httpRequestMethod => $routes)
				{
			foreach ($routes as $i => $route)
						{
				if ($route['action'] instanceof \Closure)
								{
					$routesCopy[$httpRequestMethod][$i]['action'] = new SerializableClosure($route['action']);
				}
			}
		}
		
		return serialize($routesCopy);
	}
	
	
	/**
	* Constructs the object from a serialized string
		 *
		 * @param   string  $serialized  The string representation of the object.
		 *
		 * @return  void
		 *
		 * @link    http://p	hp.net/manual/en/serializable.unserialize.php
		 * @since   __DEPLOY_VERSION__
		 */
		public function unserialize($serialized)
		{
		$this->routes = unserialize($serialized);
	}
}
