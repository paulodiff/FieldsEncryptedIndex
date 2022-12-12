<?php

namespace Paulodiff\FieldsEncryptedIndex\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

use Paulodiff\FieldsEncryptedIndex\Tests\TestCase;

class ParseSQLCommandTest extends TestCase
{
    /** @test */
    function the_db_dynamic_command()
    {
        // make sure we're starting from a clean state
        // if (File::exists(config_path('blogpackage.php'))) {
        //     unlink(config_path('blogpackage.php'));
        // }
        // $this->assertFalse(File::exists(config_path('blogpackage.php')));
        Log::channel('stderr')->info('parseSQL:artisan command', [] );
        Artisan::call('FieldsEncryptedIndex:parseSQL');

        // $this->assertTrue(File::exists(config_path('blogpackage.php')));
        $this->assertTrue(true);
    }
}