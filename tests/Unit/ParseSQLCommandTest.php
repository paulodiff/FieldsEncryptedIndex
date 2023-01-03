<?php

// Unit che esegue gli artisan command
// Esegue FieldsEncryptedIndex:test p1 p2 p3 con i vari parametri
// 


namespace Paulodiff\FieldsEncryptedIndex\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;


use Illuminate\Support\Facades\Cache;

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
        
		Cache::store('file')->flush();

/*
		
		$cmd = 'FieldsEncryptedIndex:test encryption 1';
		Log::channel('stderr')->info('ParseSQLCommandTest:the_db_seed_command:artisan command exec:', [$cmd] );
        Artisan::call($cmd);

*/		


/*		
		$cmd = 'FieldsEncryptedIndex:test createTable 0';
		Log::channel('stderr')->info('ParseSQLCommandTest:the_db_seed_command:artisan command exec:', [$cmd] );
		Artisan::call($cmd);
		
*/

/*
		
		$cmd = 'FieldsEncryptedIndex:test insertDocs 100';
		Log::channel('stderr')->info('ParseSQLCommandTest:the_db_seed_command:artisan command exec:', [$cmd] );
		Artisan::call($cmd);
*/	

/*
		$cmd = 'FieldsEncryptedIndex:test updateDocs 1';
		Log::channel('stderr')->info('ParseSQLCommandTest:the_db_seed_command:artisan command exec:', [$cmd] );
		Artisan::call($cmd);
*/


		$cmd = 'FieldsEncryptedIndex:test selectDocs 1';
		Log::channel('stderr')->info('ParseSQLCommandTest:the_db_seed_command:artisan command exec:', [$cmd] );
		Artisan::call($cmd);




		/* UPDATE ... DELETE ... SELECT */
		
/*		
		$cmd = 'FieldsEncryptedIndex:test insertMigrations 500';
		Log::channel('stderr')->info('ParseSQLCommandTest:the_db_seed_command:artisan command exec:', [$cmd] );
		Artisan::call($cmd);
*/		
		

		/*
		$cmd = 'FieldsEncryptedIndex:test reindexMigrations 0';
		Log::channel('stderr')->info('ParseSQLCommandTest:the_db_seed_command:artisan command exec:', [$cmd] );
		Artisan::call($cmd);
		*/

		
			
/*		
		$cmd = 'FieldsEncryptedIndex:test updateMigrationsEncrypted 500';
		Log::channel('stderr')->info('ParseSQLCommandTest:the_db_seed_command:artisan command exec:', [$cmd] );
        Artisan::call($cmd);
*/		

		
/*
		$cmd = 'FieldsEncryptedIndex:test selectMigrationsEncrypted 5 description';
		Log::channel('stderr')->info('ParseSQLCommandTest:the_db_seed_command:artisan command exec:', [$cmd] );
        Artisan::call($cmd);
*/		

/*
		$cmd = 'FieldsEncryptedIndex:test selectMigrationsEncryptedIndex 100 name';
		Log::channel('stderr')->info('ParseSQLCommandTest:the_db_seed_command:artisan command exec:', [$cmd] );
        Artisan::call($cmd);


		$cmd = 'FieldsEncryptedIndex:test selectMigrationsEncryptedIndex 100 surname';
		Log::channel('stderr')->info('ParseSQLCommandTest:the_db_seed_command:artisan command exec:', [$cmd] );
        Artisan::call($cmd);
*/
			


		/*
		$cmd = 'FieldsEncryptedIndex:test updateMigrationsEncryptedIndex 50 surname';
		Log::channel('stderr')->info('ParseSQLCommandTest:the_db_seed_command:artisan command exec:', [$cmd] );
        Artisan::call($cmd);
		*/

		
				

		
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