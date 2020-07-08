<?php

namespace InterNACHI\Modular\Tests\Commands;

use InterNACHI\Modular\Console\Commands\Make\MakeModule;
use InterNACHI\Modular\Console\Commands\ModuleCache;
use InterNACHI\Modular\Console\Commands\ModuleClear;
use InterNACHI\Modular\Console\Commands\ModuleSync;
use InterNACHI\Modular\Tests\Concerns\WritesToAppFilesystem;
use InterNACHI\Modular\Tests\TestCase;

class ModuleSyncTest extends TestCase
{
	use WritesToAppFilesystem;
	
	public function test_it_updates_phpunit_config() : void
	{
		$config_path = $this->copyStub('phpunit.xml', '/');
		
		$config = simplexml_load_string($this->filesystem->get($config_path));
		$nodes = $config->xpath("//phpunit//testsuites//testsuite//directory[text()='./app-modules/*/tests']");
		
		$this->assertCount(0, $nodes);
		
		$this->artisan(ModuleSync::class);
		
		$config = simplexml_load_string($this->filesystem->get($config_path));
		$nodes = $config->xpath("//phpunit//testsuites//testsuite//directory[text()='./app-modules/*/tests']");
		
		$this->assertCount(1, $nodes);
	}
	
	public function test_it_updates_phpstorm_plugin_config() : void
	{
		$config_path = $this->copyStub('laravel-plugin.xml', '.idea');
		
		$this->makeModule('test-module');
		
		$config = simplexml_load_string($this->filesystem->get($config_path));
		$nodes = $config->xpath('//component[@name="LaravelPluginSettings"]//option[@name="templatePaths"]//list//templatePath');
		
		$this->assertCount(0, $nodes);
		
		$this->artisan(ModuleSync::class);
		
		$config = simplexml_load_string($this->filesystem->get($config_path));
		$nodes = $config->xpath('//component[@name="LaravelPluginSettings"]//option[@name="templatePaths"]//list//templatePath');
		
		$this->assertCount(1, $nodes);
		
		$this->makeModule('test-module-two');
		
		$this->artisan(ModuleSync::class);
		
		$config = simplexml_load_string($this->filesystem->get($config_path));
		$nodes = $config->xpath('//component[@name="LaravelPluginSettings"]//option[@name="templatePaths"]//list//templatePath');
		
		$this->assertCount(2, $nodes);
	}
}