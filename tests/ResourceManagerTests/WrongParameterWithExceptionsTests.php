<?php
namespace ResourceManagerTests;

use imrannaqvi\ResourceManager;
use PHPUnit_Framework_TestCase;

class WrongParameterWithExceptionsTests extends PHPUnit_Framework_TestCase
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
		$this->rm->raiseMappingExceptions(true);
	}
	
	/**
	* @expectedException	imrannaqvi\ResourceManager\Exception
	* @expectedExceptionCode	imrannaqvi\ResourceManager\Exception::CONFIG_MODULE_NOT_DEFINED
	*/
	public function testConfigWithWrongModule()
	{
		$this->rm->getResolvedResourcesList(array(
			'module' => 'module2',
			'controller' => 'controller2',
			'action' => 'action2',
		));
	}	
	/**
	* @expectedException	imrannaqvi\ResourceManager\Exception
	* @expectedExceptionCode	imrannaqvi\ResourceManager\Exception::CONFIG_CONTROLLER_NOT_DEFINED
	*/
	public function testConfigWithWrongController()
	{
		$this->rm->getResolvedResourcesList(array(
			'module' => 'module1',
			'controller' => 'controller2',
			'action' => 'action2',
		));
	}	
	/**
	* @expectedException	imrannaqvi\ResourceManager\Exception
	* @expectedExceptionCode	imrannaqvi\ResourceManager\Exception::CONFIG_ACTION_NOT_DEFINED
	*/
	public function testConfigWithAction()
	{
		$this->rm->getResolvedResourcesList(array(
			'module' => 'module1',
			'controller' => 'controller1',
			'action' => 'action2',
		));
	}
}