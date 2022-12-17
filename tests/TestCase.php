<?php
namespace Paulodiff\FieldsEncryptedIndex\Tests;

use Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
  public function setUp(): void
  {
    parent::setUp();
    // additional setup
  }

  protected function getPackageProviders($app)
  {
    return [
      FieldsEncryptedIndexServiceProvider::class,
    ];
  }

  protected function getEnvironmentSetUp($app)
  {
    // perform environment setup
    $app['config']->set('FieldsEncryptedIndex.key','DjDLn1H7V1zWDQA7oJ+LMqJ+LQguZgGMO8v/wNei5zs=');
    $app['config']->set('FieldsEncryptedIndex.nonce','cxM2LpMbuIRwn4pP8IQbym7pyM25lQSw');
    $app['config']->set('FieldsEncryptedIndex.encrypt',false); // !!! ONLY TEST !!!!
    $app['config']->set('FieldsEncryptedIndex.prefix','FEI');


	$app['config']->set('FieldsEncryptedIndex.configFolder','d:/PROGETTI/LARAVEL/FieldsEncryptedIndex/tests/JsonSQL/');
    $app['config']->set('database-encryption.enabled', true);
	
/*
	Log::emergency($message);
	Log::alert($message);
	Log::critical($message);
	Log::error($message);
	Log::warning($message);
	Log::notice($message);
	Log::info($message);
	Log::debug($message);
*/

    $app['config']->set('logging.channels.stderr.level', 'notice');
    $app['config']->set('app.key', 'base64:QPE84az7moL6H5xHyV9PYYvlse2C/W9IU3WrvCHKe7U=');
  
    $app['config']->set('database.default', 'mysql');
    $app['config']->set('database.connections.mysql.username', 'root');
    $app['config']->set('database.connections.mysql.database', 'laravel');




  }
}