<?php
/**
* Copyright 2021 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Opoink\Router;
class Router {

	protected $routePattern = '/[^-:\/_{}()a-zA-Z\d]/';

	public function getRegex($pattern){
	    if (preg_match($this->routePattern, $pattern)) {
	        return false;
	    }

	    /*Turn "(/)" into "/?"*/
	    $pattern = preg_replace('#\(/\)#', '/?', $pattern);

	    /*Create capture group for ":parameter"*/
	    $allowedParamChars = '[a-zA-Z0-9\_\-\.]+';
	    $pattern = preg_replace(
	        '/:(' . $allowedParamChars . ')/',   /*Replace ":parameter"*/
	        '(?<$1>' . $allowedParamChars . ')', /*with "(?<parameter>[a-zA-Z0-9\_\-\.]+)"*/
	        $pattern
	    );

	    /*Create capture group for '{parameter}'*/
	    $pattern = preg_replace(
	        '/{('. $allowedParamChars .')}/',    /*Replace "{parameter}"*/
	        '(?<$1>' . $allowedParamChars . ')', /*with "(?<parameter>[a-zA-Z0-9\_\-\.]+)"*/
	        $pattern
	    );

	    /*Add start and end matching*/
	    $patternAsRegex = "@^" . $pattern . "/?$@D";

	    return $patternAsRegex;
	}

	/**
	 * return an array if the route matched
	 * return null instead
	 * @param $pattern is the pattern for the URI that will be matched
	 * @param $callback instance of Closure
	 */
	public function get($pattern, $callback=null){
		$patternAsRegex = $this->getRegex($pattern);
		$testUrl = '';
		if(isset($_SERVER['REQUEST_URI'])){
			$testUrl = $_SERVER['REQUEST_URI'];
		}
		$testUrl =	explode('?', $testUrl);
		$testUrl = $testUrl[0];

		$params = null;
		if($patternAsRegex){
			preg_match($patternAsRegex, $testUrl, $matches);
			if(count($matches)){
				$params = array_intersect_key( $matches, array_flip(array_filter(array_keys($matches), 'is_string')) );
			}
		}

		if($callback instanceof \Closure){
			$callback($params);
		} else {
			return $params;
		}
	}
	
	public function getMatch($route){
		if( !isset($route['method']) ){
			return $this->get($route['pattern']); /** the method is not set means is any method */
		} else {
			if($route['method'] == "*"){ /** the method "*" means is any method */
				return $this->get($route['pattern']);
			} else {
				if($_SERVER['REQUEST_METHOD'] == strtoupper($route['method'])){
					return $this->get($route['pattern']);
				}
			}
		}
	}
}
?>
