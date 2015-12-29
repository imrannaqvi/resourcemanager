<?php
namespace ResourceManagerTests;

use imrannaqvi\ResourceManager;
use PHPUnit_Framework_TestCase;

class BaseUrlTests extends PHPUnit_Framework_TestCase
{
	public $config = array(
		'mapping' => array(
			'modules' => array(
				'module1' => array(
					'controllers' => array(
						'controller1' => array(
							'actions' => array(
								//correct
								'action1' => array(
									'resource2'
								),
								'action2' => array(
									'resource3'
								),
								//circular
								'action3' => array(
									'cd3'
								),
							),
						),
					),
				),
			),
		),
		'resources' => array(
			//correct
			'resource1' => 'public/js/resource1.js',
			'resource2' => array(
				'url' => 'public/js/resource2.js',
				'deps' => array(
					'resource1'
				)
			),
			'resource3' => array(
				'url' => 'public/js/resource3.js',
				'deps' => 'resource2'
			),
			//circular
			'cd1' => array(
				'url' => 'public/js/cd1.js',
				'deps' => 'cd3'
			),
			'cd2' => array(
				'url' => 'public/js/cd1.js',
				'deps' => 'cd1'
			),
			'cd3' => array(
				'url' => 'public/js/cd1.js',
				'deps' => 'cd2'
			),
		),
	);
	
	public function setUp()
	{
		$this->rm = new ResourceManager\ResourceManager($this->config);
	}
	
	/**
	* @expectedException	imrannaqvi\ResourceManager\Exception
	* @expectedExceptionCode	imrannaqvi\ResourceManager\Exception::CIRCULAR_DEPENDENCY
	*/
	public function testCircularDependency()
	{
		$out = $this->rm->getResolvedResourcesList(array(
			'module' => 'module1',
			'controller' => 'controller1',
			'action' => 'action3',
		));
	}
	
	public function testWithOneDependency()
	{
		$out = $this->rm->getResolvedResourcesList(array(
			'module' => 'module1',
			'controller' => 'controller1',
			'action' => 'action1',
		));
		$this->assertCount(2, $out);
		$this->assertEquals('resource1', $out[0]['name']);
		$this->assertEquals('resource2', $out[1]['name']);
	}
	
	public function testWithTwoDependencies()
	{
		$out = $this->rm->getResolvedResourcesList(array(
			'module' => 'module1',
			'controller' => 'controller1',
			'action' => 'action2',
		));
		$this->assertCount(3, $out);
		$this->assertEquals('resource1', $out[0]['name']);
		$this->assertEquals('resource2', $out[1]['name']);
		$this->assertEquals('resource3', $out[2]['name']);
	}
}