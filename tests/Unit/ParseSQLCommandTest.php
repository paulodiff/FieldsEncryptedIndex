<?php

namespace Paulodiff\FieldsEncryptedIndex\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

use Paulodiff\FieldsEncryptedIndex\Tests\TestCase;

class ParseSQLCommandTest extends TestCase
{
    /** @test */
    function the_db_seed_command_test()
    {
        // make sure we're starting from a clean state
        // if (File::exists(config_path('blogpackage.php'))) {
        //     unlink(config_path('blogpackage.php'));
        // }
        // $this->assertFalse(File::exists(config_path('blogpackage.php')));
        
		/*	
		$cmd = 'FieldsEncryptedIndex:test encryption 1';
		Log::channel('stderr')->info('ParseSQLCommandTest:the_db_seed_command:artisan command exec:', [$cmd] );
        Artisan::call($cmd);
		*/

		/*
		$cmd = 'FieldsEncryptedIndex:test insertMigrations 5';
		Log::channel('stderr')->info('ParseSQLCommandTest:the_db_seed_command:artisan command exec:', [$cmd] );
		Artisan::call($cmd);
		*/
				
		
		
		$cmd = 'FieldsEncryptedIndex:test selectMigrations 5';
		Log::channel('stderr')->info('ParseSQLCommandTest:the_db_seed_command:artisan command exec:', [$cmd] );
        Artisan::call($cmd);
		
				

		
        $this->assertTrue(true);
    }


	function the_parseSQL_command_test()
    {
        // make sure we're starting from a clean state
        // if (File::exists(config_path('blogpackage.php'))) {
        //     unlink(config_path('blogpackage.php'));
        // }
        // $this->assertFalse(File::exists(config_path('blogpackage.php')));
        Log::channel('stderr')->info('ParseSQLCommandTest:the_parseSQL_command:artisan command', [] );
        
		// Artisan::call('FieldsEncryptedIndex:dbSeed migrations 100');

		// Artisan::call('FieldsEncryptedIndex:parseSQL');

        // $this->assertTrue(File::exists(config_path('blogpackage.php')));
        $this->assertTrue(true);
    }
}