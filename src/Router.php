<?php
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

	public function get($pattern, $callback){
		$patternAsRegex = $this->getRegex($pattern);
		$testUrl = '';
		if(isset($_SERVER['REQUEST_URI'])){
			$testUrl = $_SERVER['REQUEST_URI'];
		}

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
}
?>
