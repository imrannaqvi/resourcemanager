<?php
namespace ResourceManagerTests;

use imrannaqvi\ResourceManager;
use PHPUnit_Framework_TestCase;

class WrongParameterWithoutExceptionsTests extends PHPUnit_Framework_TestCase
{
	public $config = array(
		'mapping' => array(
			'modules' => array(
				'module1' => array(
					'controllers' => array(
						'controller1' => array(
							'actions' => array(
								'action1' => array(),
							),
						),
					),
				),
			),
		),
	);
	
	public function setUp()
	{
		$this->rm = new ResourceManager\ResourceManager($this->config);
	}
	
	public function testConfigWithWrongModule()
	{
		$out = $this->rm->getResolvedResourcesList(array(
			'module' => 'module2',
			'controller' => 'controller2',
			'action' => 'action2',
		));
		$this->assertCount(0, $out);
	}	
	
	public function testConfigWithWrongController()
	{
		$out = $this->rm->getResolvedResourcesList(array(
			'module' => 'module1',
			'controller' => 'controller2',
			'action' => 'action2',
		));
		$this->assertCount(0, $out);
	}	

	public function testConfigWithAction()
	{
		$out = $this->rm->getResolvedResourcesList(array(
			'module' => 'module1',
			'controller' => 'controller1',
			'action' => 'action2',
		));
		$this->assertCount(0, $out);
	}
}