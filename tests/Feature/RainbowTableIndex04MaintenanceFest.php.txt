<?php

// php artisan test --testsuite=Feature --filter=RainbowTableIndex04MaintenanceTest --stop-on-failure

namespace Paulodiff\RainbowTableIndex\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Paulodiff\RainbowTableIndex\Tests\TestCase;

use DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Faker\Factory as Faker;

use Paulodiff\RainbowTableIndex\RainbowTableIndexService;
use Paulodiff\RainbowTableIndex\RainbowTableIndexTrait;

use Paulodiff\RainbowTableIndex\Tests\Models\Author;
use Paulodiff\RainbowTableIndex\Tests\Models\Post;



class RainbowTableIndex04MaintenanceTest extends TestCase
{
    use RainbowTableIndexTrait;
    // -------------------- TO CHANGE ---------------------------------------
    public $NUM_OF_SEARCH = 100;
    // -------------------- TO CHANGE ---------------------------------------
    public $faker;



    // test read data with time execution
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_maintenance()
    {

        Log::channel('stderr')->info('Maintenance: start!', [] );
/*
        $numOftests = $this->NUM_OF_SEARCH;
        $this->faker = Faker::create('Maintenance');
*/
        // Author search test
        $a = Author::where('id', 1)->first();
        Log::channel('stderr')->info('Maintenance:config', [$a::$rainbowTableIndexConfig]);

        $r1 = $a->rebuildRainbowIndex();
        Log::channel('stderr')->info('Maintenance:rebuildRainbowIndex:->', [$r1]);

        Log::channel('stderr')->info('Maintenance:rebuildFullRainbowIndex:->', []);
        $r2 = Author::rebuildFullRainbowIndex();

        Log::channel('stderr')->info('Maintenance: end!', [] );

        $this->assertTrue(true);

    }



}
