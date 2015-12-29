<?php
namespace ResourceManagerTests;

use imrannaqvi\ResourceManager;
use PHPUnit_Framework_TestCase;

class EmptyConfigTests extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->rm = new ResourceManager\ResourceManager();
	}
	
	public function testParams()
	{
		$out = $this->rm->getResolvedResourcesList(array(
			'module' => 'module1',
			'controller' => 'controller1',
			'action' => 'action'
		));
		$this->assertCount(0, $out);
	}
}