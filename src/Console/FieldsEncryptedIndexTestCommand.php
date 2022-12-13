<?php
namespace Paulodiff\FieldsEncryptedIndex\Console;

// Test FieldsEncryptedIndex ...

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Faker\Factory as Faker;

use Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexEngine;


class FieldsEncryptedIndexTestCommand extends Command
{
    protected $signature = 'FieldsEncryptedIndex:test {action} {rows}';

    protected $description = 'dbSeed with FieldsEncryptedIndexEngine';
	public $FEI_engine;

    public function handle()
    {
        

		$action = $this->argument('action');
		$rows = $this->argument('rows');
		

		Log::channel('stderr')->notice('FieldsEncryptedIndex:test:', [$action, $rows] );
		

		if ($action == "insertMigrations") 
		{


			for($i = 0; $i<$rows; $i++)
			{


				// create JSON request
				$faker = Faker::create('SeedData');
				$rNumber = $faker->randomNumber(5, true);
				$rMigrationName = $faker->name();
				$rSentence = $faker->sentence();
				$rName = $faker->words(3, true);
				$rSurname = $faker->words(3, true);


				$jsonRequest = '{
					"action"    : "INSERT",
					"tables" : [
							{
								"tableName" : "migrations",
								"tableAlias" : "migrations"
							}
						],
					"data" : [
							{  
								"fieldName": "migrations.migration",   
								"fieldValue" : "' . $rMigrationName . '"
							},
							{  
								"fieldName": "migrations.batch",   
								"fieldValue" : ' . $rNumber . '
							},
							{  
								"fieldName": "migrations.description",   
								"fieldValue" : "' . $rSentence . '"
							},
							{  
								"fieldName": "migrations.description_plain",   
								"fieldValue" : "' . $rSentence . '"
							},
							{  
								"fieldName": "migrations.name",   
								"fieldValue" : "' . $rName . '"
							},
							{  
								"fieldName": "migrations.name_plain",   
								"fieldValue" : "' . $rName . '"
							},
							{  
								"fieldName": "migrations.surname",   
								"fieldValue" : "' . $rSurname . '"
							},
							{  
								"fieldName": "migrations.surname_plain",   
								"fieldValue" : "' . $rSurname . '"
							}

					]          
				}';


				Log::channel('stderr')->notice('FieldsEncryptedIndex:' . $action, [$i, $jsonRequest] );
				
				$this->FEI_engine = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexEngine();
				$q = $this->FEI_engine->process($jsonRequest);
				Log::channel('stderr')->info('parseSQL:FINAL!:', [$q] );

			}

		} elseif ( $action == "selectMigrations" ) {

			for($i = 0; $i<$rows; $i++)
			{


				// create JSON request
				$faker = Faker::create('SeedData');
				$rNumber = $faker->randomNumber(5, true);
				$rMigrationName = $faker->name();
				$rSentence = $faker->sentence();
				$rName = $faker->words(3, true);
				$rSurname = $faker->words(3, true);


				$jsonRequest = '{
					"action" : "SELECT",
					"tables" : [
							{
								"tableName" : "migrations",
								"tableAlias" : "migrations"
							}
						  ],
						
					"fields" : [
							{  "fieldName": "migrations.id"   },
							{  "fieldName": "migrations.migration"   },
							{  "fieldName": "migrations.description"   },
							{  "fieldName": "migrations.description_plain"   },
							{  "fieldName": "migrations.name"   }
							
					],

					"where" : [
            
						{
							"operator" : "",
							"clauses" : [
								{
									"fieldName" : "migrations.id",
									"operator" : "<",
									"value" : 3
								}
							]
						}
					]
					
			

						}';

				Log::channel('stderr')->notice('FieldsEncryptedIndex:' . $action, [$i, $jsonRequest] );
				
				$this->FEI_engine = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexEngine();
				$q = $this->FEI_engine->process($jsonRequest);
				Log::channel('stderr')->notice('FieldsEncryptedIndex:' . $action, [$q] );
				Log::channel('stderr')->notice('------------------------------------------------------------------------------------------------------------------', [] );

				foreach($q as $item) 
				{
					// Log::channel('stderr')->notice( '#', [$item] );
					$displayRow = "";
					foreach ($item as $key => $value) {
						// echo "$key => $value\n";
						$displayRow = $displayRow . "|" . $value;
					}
					Log::channel('stderr')->notice( $displayRow );

				}


		
			} 
		} else {
			Log::channel('stderr')->notice('FieldsEncryptedIndex:test: action not found!', [$action] );
		}


		Log::channel('stderr')->notice('FieldsEncryptedIndex:test!:', ['-------------- END! -------------------------'] );

		// send JSON request


		/*

        Log::channel('stderr')->info('CheckConfig:', ['Creating table authors ...'] );
        if ( !Schema::hasTable('authors'))
        {
               Schema::create('authors', function (Blueprint $table) {
                $table->increments('id');
                $table->text('name');
                $table->text('name_enc'); // for test only
                $table->text('card_number');
                $table->text('card_number_enc'); // for test only
                $table->text('address');
                $table->text('address_enc'); // for test only
                $table->text('role');
                $table->text('role_enc'); // for test only
                $table->timestamps();
            });
            Log::channel('stderr')->info('CheckConfig:', ['table authors created'] );
        }
        else
        {
            Log::channel('stderr')->info('CheckConfig:', ['table comments already exits'] );
        }
     

        $this->info('FieldsEncryptedIndex DbSeed - Seeding! ');

        $numOfrows = $this->argument('numOfrows');
        
        Log::channel('stderr')->info('DbSeed:rows:', [$numOfrows] );

        $numOfPosts = 2;
        $numOfAuthors = $numOfrows;

        Log::channel('stderr')->info('SeedData:', [
            'start seeding ....',
            'Posts : ' .  $numOfPosts,
            'Authors : ' . $numOfAuthors
        ]);
        $faker = Faker::create('SeedData');

        if ( class_exists('\Paulodiff\FieldsEncryptedIndex\Tests\Models\Author') )
        {
            $a = new \Paulodiff\FieldsEncryptedIndex\Tests\Models\Author();
        }
        else
        {
            $a = new \App\Models\Author();
        }


        Log::channel('stderr')->info('SeedData:', ['destroy authors rainbox index... ']);
        $a::destroyRainbowIndex();

        Log::channel('stderr')->info('SeedData:', ['destroy authors table... ']);
        try
        {
            $a::truncate();
        } 
        catch (\Exception $e) 
        {
            Log::channel('stderr')->error('SeedData:', ['ERROR deleting Authors table', $e] );
            // die("ERRORE RainbowTableService re check previuos step!" . $e );
        }

        Log::channel('stderr')->info('SeedData:', ['start insert! ... ']);

        for($i=0;$i<$numOfAuthors;$i++)
        {
            // $p = new Author();

            if ( class_exists('\Paulodiff\FieldsEncryptedIndex\Tests\Models\Author') )
            {
                $p = new \Paulodiff\FieldsEncryptedIndex\Tests\Models\Author();
            }
            else
            {
                $p = new \App\Models\Author();
            }
    


            $p->name = strtoupper($faker->name());
            $p->name_enc = $p->name;

            $p->card_number = $faker->creditCardNumber('Visa');
            $p->card_number_enc = $p->card_number;

            $p->address = $faker->streetAddress();
            $p->address_enc = $p->address;

            $p->role =  $faker->randomElement(['author', 'reader', 'admin', 'user', 'publisher']);
            $p->role_enc =  $p->role;

            $p->save();

            Log::channel('stderr')->info('SeedData:' . $i . '#' . $numOfAuthors .']Author Added!:', [$p->toArray()]);
            


        }

		*/


        Log::channel('stderr')->info('TEST finished!:', []);



        
    }

    private function configExists($fileName)
    {
        return File::exists(config_path($fileName));
    }

    private function shouldOverwriteConfig()
    {
        return $this->confirm(
            'Config file already exists. Do you want to overwrite it?',
            false
        );
    }

    private function publishConfiguration($forcePublish = false)
    {
        $params = [
            '--provider' => "JohnDoe\BlogPackage\BlogPackageServiceProvider",
            '--tag' => "config"
        ];

        if ($forcePublish === true) {
            $params['--force'] = true;
        }

       $this->call('vendor:publish', $params);
    }
}