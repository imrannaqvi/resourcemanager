<?php
namespace ResourceManagerTests;

use imrannaqvi\ResourceManager;
use PHPUnit_Framework_TestCase;

class MissingParameterTests extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->rm = new ResourceManager\ResourceManager();
	}
	
	/**
	* @expectedException	imrannaqvi\ResourceManager\Exception
	* @expectedExceptionCode	imrannaqvi\ResourceManager\Exception::PARAMETERS_MODULE_NOT_DEFINED
	*/
	public function testParamsWithoutModule()
	{
		$this->rm->getResolvedResourcesList(array());
	}

	/**
	* @expectedException	imrannaqvi\ResourceManager\Exception
	* @expectedExceptionCode imrannaqvi\ResourceManager\Exception::PARAMETERS_CONTROLLER_NOT_DEFINED
	*/
	public function testParamsWithoutController()
	{
		$this->rm->getResolvedResourcesList(array(
			'module' => 'module1'
		));
	}
	
	/**
	* @expectedException	imrannaqvi\ResourceManager\Exception
	* @expectedExceptionCode imrannaqvi\ResourceManager\Exception::PARAMETERS_ACTION_NOT_DEFINED
	*/
	public function testParamsWithoutAction()
	{
		$this->rm->getResolvedResourcesList(array(
			'module' => 'module1',
			'controller' => 'controller1',
		));
	}
}