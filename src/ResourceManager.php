<?php
namespace imrannaqvi\ResourceManager;

class ResourceManager
{
	protected $config;
	protected $basePath = '';
	protected $dependencyCache = array();

	public function __construct($config = array())
	{
		$this->config = $config;
	}
	
	public function handle($controller, $params, $options = array())
	{
		$resources = $this->getResolvedResourcesList($params);
		for($i=0; $i<count($resources); $i++) {
			switch($resources[$i]['type']) {
				case 'css':
					$stylesheet = $resources[$i]['stylesheet'];
					$controller->getServiceLocator()->get('viewhelpermanager')->get('headLink')->appendStylesheet(
						$resources[$i]['url'],
						$stylesheet['media'],
						$stylesheet['conditionalStylesheet'],
						$stylesheet['extras']
					);
				break;
				case 'js':
					$controller->getServiceLocator()->get('viewhelpermanager')->get('inlineScript')->appendFile(
						$resources[$i]['url'],
						'text/javascript',
						$resources[$i]['attrs']
					);
				break;
			}
		}
	}
	
	public function getResolvedResourcesList($params)
	{
		//check parameters
		if(! array_key_exists('module', $params)) {
			throw new Exception('"module" not defined in parameters.', Exception::PARAMETERS_MODULE_NOT_DEFINED);
		}
		if(! array_key_exists('controller', $params)) {
			throw new Exception('"controller" not defined in parameters.', Exception::PARAMETERS_CONTROLLER_NOT_DEFINED);
		}
		if(! array_key_exists('action', $params)) {
			throw new Exception('"action" not defined in parameters.', Exception::PARAMETERS_ACTION_NOT_DEFINED);
		}
		## get required list
		$list = array();
		if(
			array_key_exists('mapping', $this->config) //&&
			//array_key_exists($params['module'], $this->config['mapping']['modules'])
		) {
			//check module, required
			if(
				! array_key_exists('mapping', $this->config) ||
				! array_key_exists('modules', $this->config['mapping']) ||
				! array_key_exists($params['module'], $this->config['mapping']['modules'])
			) {
				throw new Exception('Module "' . $params['module'] . '" not defined in configuration.', Exception::CONFIG_MODULE_NOT_DEFINED);
			}
			//module
			$module = $this->config['mapping']['modules'][$params['module']];
			//common in module, optional
			if( array_key_exists('commons', $module)) {
				$list = array_merge($list, $module['commons']);
			}
			//check controller, required
			if( 
				! array_key_exists('controllers', $module) ||
				! array_key_exists($params['controller'], $module['controllers']) 
			) {
				throw new Exception('Controller "' . $params['controller'] . '" not defined in configuration.', Exception::CONFIG_CONTROLLER_NOT_DEFINED);
			}
			//controller
			$controller = $module['controllers'][$params['controller']];
			//controller commons, optional
			if(array_key_exists('commons', $controller)) {
				$list = array_merge($list, $controller['commons']);
			}
			//action
			if(
				! array_key_exists('actions', $controller) ||
				! array_key_exists($params['action'], $controller['actions'])
			) {
				throw new Exception('Action "' . $params['action'] . '" not defined in configuration.', Exception::CONFIG_ACTION_NOT_DEFINED);
			}
			$action = $controller['actions'][$params['action']];
			$list = array_merge($list, $action);
		}
		//add dependencies
		$out = array();
		for( $i=0; $i<count($list); $i++) {
			if(
				! array_key_exists('resources', $this->config) ||
				! array_key_exists($list[$i], $this->config['resources'])
			) {
				throw new Exception('Specified Resource "' . $list[$i] . '" Not Found.', Exception::CONFIG_RESOURCE_NOT_DEFINED);
			}
			$this->dependencyCache = array();
			$out = array_merge($out, $this->getItemDependencyRecursive($list[$i]));
		}
		//remove duplicates and fix numeric indexes
		$out = array_values(array_unique($out));
		//prepare final list
		$resources = array();
		$index = 1;
		for($i=0; $i<count($out); $i++) {
			//throw exception if 
			if(! array_key_exists($out[$i], $this->config['resources'])) {
				throw new Exception('Resources with name "'.$out[$i].'" not found.');
			}
			$name = $out[$i];
			$item = $this->config['resources'][$out[$i]];
			$type = null;
			$url = null;
			$basePath = false;
			$attrs = array();
			$priority = false;
			if( is_array($item)) {
				//check if resource is not empty
				if(
					! array_key_exists('url', $item) &&
					! array_key_exists('deps', $item)
				) {
					throw new \Exception('Resources "'.$out[$i].'" is Empty.');
				}
				if( array_key_exists('type', $item)) {
					$type = $item['type'];
				}
				if( array_key_exists('basePath',  $item)) {
					$basePath = true;
				}
				if( array_key_exists('url', $item)) {
					$url = $item['url'];
					if($basePath) {
						$url = $this->getBasePath() . $url;
					}
				}
				//if cdn, disable base path and overwrite url
				if( array_key_exists('cdn',  $item)) {
					$basePath = false;
					$url = $item['cdn'];
				}
				//attributes
				if( array_key_exists('attrs', $item)) {
					$attrs = $item['attrs'];
				}
				//stylesheet
				if( array_key_exists('stylesheet', $item)) {
					$stylesheet = $item['stylesheet'];
				} else {
					$stylesheet =  array();
				}
				if(! array_key_exists('media', $stylesheet)) {
					$stylesheet['media'] = 'screen';
				}
				if(! array_key_exists('conditionalStylesheet', $stylesheet)) {
					$stylesheet['conditionalStylesheet'] = '';
				}
				if(! array_key_exists('extras', $stylesheet)) {
					$stylesheet['extras'] = array();
				}
				//priority
				if( array_key_exists('priority', $item)) {
					$priority = (int) $item['priority'];
				}
			} else {
				$url = $item;
				$stylesheet = array(
					'media' => 'screen',
					'conditionalStylesheet' => '',
					'extras' => array(),
				);
			}
			//check if type not set
			if(! $type && is_string($url)) {
				$parts = explode('.', $url);
				$parts = trim(end($parts));
				if(in_array($parts, array('js', 'css'))) {
					$type = $parts;
				}
			}
			// add in final list
			if($type && $url) {
				if(! $priority) {
					$priority = $index++;
				}
				$resources[] = array(
					'name' => $name,
					'type' => $type,
					'url' => $url,
					'attrs' => $attrs,
					'priority' => $priority,
					'stylesheet' => $stylesheet,
				);
			}
		}
		//return
		return $this->reorderPriorities($resources);
	}
	
	public function getItemDependencyRecursive($name)
	{
		if(in_array($name, $this->dependencyCache)) {
			throw new Exception('Circular Dependency detected for ' .  implode(', ', $this->dependencyCache) . ', ' . $name . '.', Exception::CIRCULAR_DEPENDENCY);
		}
		$this->dependencyCache[] = $name;
		//if any dependencies found
		if(
			array_key_exists($name, $this->config['resources']) && 
			is_array($this->config['resources'][$name]) &&
			array_key_exists('deps', $this->config['resources'][$name])
		) {
			$deps = $this->config['resources'][$name]['deps'];
			//if dependencies is an array
			if( is_array($deps)) {
				//if array have multiple items
				$out = array();
				for($i=0; $i<count($deps); $i++) {
					$out = array_merge($out, $this->getItemDependencyRecursive($deps[$i]));
				}
				//add original one at the end
				$out[] = $name;
				return $out;
			}
			//not array
			return array_merge(
				$this->getItemDependencyRecursive($deps),
				array($name)
			);
		}
		//no dependencies found
		return array(
			$name
		);
	}
	
	public function getBasePath()
	{
		return $this->basePath;
	}
	
	public function setBasePath($basePath)
	{
		$this->basePath = $basePath;
	}
	
	public function reorderPriorities($resources)
	{
		//ordering logic
		usort($resources, function($a, $b) {
			return $a['priority'] > $b['priority'];
		});
		return $resources;
	}
}