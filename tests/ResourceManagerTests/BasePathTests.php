<?php
namespace ResourceManagerTests;

use imrannaqvi\ResourceManager;
use PHPUnit_Framework_TestCase;

class BasePathTests extends PHPUnit_Framework_TestCase
{
	public $config = array(
		'mapping' => array(
			'modules' => array(
				'module1' => array(
					'controllers' => array(
						'controller1' => array(
							'actions' => array(
								'action1' => array(
									'resource1',
									'resource2',
								),
							),
						),
					),
				),
			),
		),
		'resources' => array(
			'resource1' => '/js/resource1.js',
			'resource2' => array(
				'url' => '/js/resource2.js',
				'basePath' => true
			),
		),
	);
	
	public function setUp()
	{
		$this->rm = new ResourceManager\ResourceManager($this->config);
	}
	
	public function testBasePath()
	{
		$basePath = '/base/path';
		$resource1 = '/js/resource1.js';
		$resource2 = '/js/resource2.js';
		$this->rm->setBasePath($basePath);
		$out = $this->rm->getResolvedResourcesList(array(
			'module' => 'module1',
			'controller' => 'controller1',
			'action' => 'action1',
		));
		$this->assertEquals($resource1, $out[0]['url']);
		$this->assertEquals($basePath . $resource2, $out[1]['url']);
	}
}