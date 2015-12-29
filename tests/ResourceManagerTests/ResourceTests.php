<?php
namespace ResourceManagerTests;

use imrannaqvi\ResourceManager;
use PHPUnit_Framework_TestCase;

class ResourceTests extends PHPUnit_Framework_TestCase
{
	public $config = array(
		'mapping' => array(
			'modules' => array(
				'module1' => array(
					'controllers' => array(
						'controller1' => array(
							'actions' => array(
								'action1' => array(
									'wrong'
								),
								'action2' => array(
									'correct-js'
								),
							),
						),
					),
				),
			),
		),
		'resources' => array(
			'correct-js' => 'public/js/correct.js'
		),
	);
	
	public function setUp()
	{
		$this->rm = new ResourceManager\ResourceManager($this->config);
	}
	
	/**
	* @expectedException	imrannaqvi\ResourceManager\Exception
	* @expectedExceptionCode	imrannaqvi\ResourceManager\Exception::CONFIG_RESOURCE_NOT_DEFINED
	*/
	public function testConfigWithWrongResource()
	{
		$this->rm->getResolvedResourcesList(array(
			'module' => 'module1',
			'controller' => 'controller1',
			'action' => 'action1',
		));
	}
	
	public function testConfigWithCorrectResource()
	{
		$out = $this->rm->getResolvedResourcesList(array(
			'module' => 'module1',
			'controller' => 'controller1',
			'action' => 'action2',
		));
		$this->assertCount(1, $out);
	}
}