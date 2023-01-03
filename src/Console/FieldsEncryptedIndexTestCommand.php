<?php
namespace Paulodiff\FieldsEncryptedIndex\Console;

// Implementa il comando php artisan command
// 'FieldsEncryptedIndex:test {action} {rows} {fieldName?}'

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Faker\Factory as Faker;

use Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexEngine;
use Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexEncrypter;


class FieldsEncryptedIndexTestCommand extends Command
{
    protected $signature = 'FieldsEncryptedIndex:test {action} {rows} {fieldName?}';

    protected $description = 'dbSeed with FieldsEncryptedIndexEngine';
	public $FEI_engine;

    public function handle()
    {
        
		$action = $this->argument('action');
		$rows = $this->argument('rows');
		$fieldName = $this->argument('fieldName');
		
		Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:test:', [$action, $rows, $fieldName] );


// esegue $rows inserimenti nella tabella migrations ...
		if ($action == "insertDocs") 
		{

			for($i = 0; $i<$rows; $i++)
			{

				// create JSON request
				$faker = Faker::create('SeedData');
				$rNumber = $faker->randomNumber(5, true);
				$rMigrationName = $faker->name();
				$rSentence = $faker->sentence();
				// $rSentence = 'A eveniet suscipit molestiae minus sit tenetur.';
				$rName = $faker->words(3, true);
				$rSurname = $faker->words(3, true);

	

				$jsonRequest = '{
					"action"    : "INSERT",
					"table" : "docs",
					"fields" : [
							{  
								"fieldName": "docs.description",   
								"fieldValue" : "' . $rName . '"
							},
							{  
								"fieldName": "docs.batchNumber",   
								"fieldValue" : ' . $rNumber . '
							},
							{  
								"fieldName": "docs.note",   
								"fieldValue" : "' . $rName . '"
							},
							{  
								"fieldName": "docs.address",   
								"fieldValue" : "' . $rName . '"
							}
					]          
				}';

			


				Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:' . $action, [$i, $jsonRequest] );
				
				$this->FEI_engine = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexEngine();
				$q = $this->FEI_engine->process($jsonRequest);
				Log::channel('stderr')->info('parseSQL:FINAL!:', [$q] );

			}

		} 


		elseif ( $action == "updateDocs" ) {

			// UPDATE `laravel`.`migrations` SET `migration`='Tom Sam Jhon q', `batch`='80253' WHERE  `id`=10;

			Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:' . $rows, [$action] );
			Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:' . $fieldName, [$action] );

			for($i = 0; $i<$rows; $i++)
			{
				Log::channel('stderr')->debug('**********************************************************************:' . $i, [$action] );
				Log::channel('stderr')->debug('FieldsEncryptedIndexTestCommand:' . $i, [$action] );
				
				$faker = Faker::create('SeedData');

				// get a random id from all ids

				$this->FEI_config = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexConfig();
				$tableNameHashed = $this->FEI_config->getHashedTableNameConfig('docs');
				$primaryKeyName = $this->FEI_config->getTablePrimaryKeyNameConfig('docs');
		
				
				$Ids = DB::table($tableNameHashed)->select($primaryKeyName)->get();
				$cntIds = count($Ids);
				$idSelected = $faker->numberBetween(1, $cntIds);

				// dd ( $Ids[$idSelected-1] );
				// $val = intval($total_results->getText());
				// dd ( intval($Ids[$idSelected-1]->id)   );
				// $v = DB::table('migrations')->where('id', intval($Ids[$idSelected-1]->id) )->get();
				// dd($v[0]);

				// create JSON request
				$rNumber = $faker->randomNumber(5, true);
				$rMigrationName = $faker->name();
				$rSentence = $faker->sentence();
				$rName = $faker->words(3, true);
				$rSurname = $faker->words(3, true);

				// get di tre caratteri
				// Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:SEARCH:', [$toSearch,$fieldName, $textFromSearch] );

				/*

			"fieldName": "description",
			"fieldName": "batchNumber",
			"fieldName": "note",
			"fieldName": "address",


				*/


				$jsonRequest = '{
					"action" : "UPDATE",
					"table" : "docs",
					"fields" : [
							{  
								"fieldName" : "docs.description",
								"fieldValue" : "' . $rName . '"  
							},
							{  
								"fieldName" : "docs.batchNumber",
								"fieldValue" : ' . $rNumber . '  
							},
							{  
								"fieldName" : "docs.note",
								"fieldValue" : "' . $rSurname . '"  
							},
							{  
								"fieldName" : "docs.address",
								"fieldValue" : "' . $rSurname . '"  
							}
						
					],

					"where" : [
            
						{
							"operator" : "",
							"clauses" : [
								{
									"fieldName" : "docs.id",
									"operator" : "=",
									"fieldValue" : ' . $idSelected . '
								}
							]
						}
					]

							

						}';

				Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:' . $action, [$i, $idSelected, $jsonRequest] );
				// Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:' . $action, [$i, $toSearch, $jsonRequest] );
				
				$this->FEI_engine = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexEngine();
				$q = $this->FEI_engine->process($jsonRequest);
				
				// Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:Conteggio n. rec:', [ count($q), count($test1) ] );
				
	
				Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand[' . $i . '] ' . $action . ' :FINAL!:', [] );
				
				// recupero seconda lista ids
		
			} 



		}

		// esegue select su docs sui vari campi per controllare la correttezza dei dati
		// usa la process per fare le query
		elseif ( $action == "selectDocs" ) {

			Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:' . $action, [] );

			$this->FEI_engine = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexEngine();

			$jsonRequest = '{
				"action" : "SELECT",
				"tables" : 
				    [
						{
							"tableName"  : "docs",
							"tableAlias" : "docs"
						}
					],
				"fields" : 
				    [
						{  "fieldName": "docs.id"   }
					],
				
				"where" : 
					[
		
						{
							"operator" : "",
							"clauses" : 
							[
								{
									"fieldName" : "docs.id",
									"operator" : ">",
									"fieldValue" : "1"
								}
							]
						}
					]

			}';
			
			$q = $this->FEI_engine->process($jsonRequest);


			$Ids = json_decode($q);
			
			// die('selectDocs');

			// per il numero di volte 
			// - get id
			//   get = LIKE ... 
			/*
			{
				"fieldName": "description",
				"fieldType": "STRING",
			},
			{
				"fieldName": "note",
				"fieldType": "ENCRYPTED",
			},
			{
				"fieldName": "address",
				"fieldType": "ENCRYPTED_INDEXED",
			}
			*/

			for($i = 0; $i<$rows; $i++)
			{

				$faker = Faker::create('SeedData');
				$cntIds = count($Ids);
				$idSelected = $faker->numberBetween(1, $cntIds);


				$ID_ = $Ids[$idSelected-1]->docs_id;

				Log::channel('stderr')->debug('FieldsEncryptedIndexTestCommand: SELECT ' , [$ID_] );
				
				$rNumber = $faker->randomNumber(5, true);
				$rMigrationName = $faker->name();
				$rSentence = $faker->sentence();
				$rName = $faker->words(3, true);
				$rSurname = $faker->words(3, true);

				$jsonRequest = '{
					"action" : "SELECT",
					"tables" : 
						[
							{
								"tableName" : "docs",
								"tableAlias" : "docs"
							}
						],
						
					"fields" : 
						[
							{  "fieldName": "docs.id"   },
							{  "fieldName": "docs.description"   },
							{  "fieldName": "docs.note"   },
							{  "fieldName": "docs.address"   }
						],

					"where" : 
						[
			
							{
								"operator" : "",
								"clauses" : 
								[
									{
										"fieldName" : "docs.id",
										"operator" : "=",
										"fieldValue" : "' . $ID_ . '"
									}
								]
							}
						]
						
					}';

				$q = $this->FEI_engine->process($jsonRequest);

				$jq = json_decode($q);

				// dd($jq[0]);

				// VERIFICA 1 description == note == address
				Log::channel('stderr')->notice('[[[[[VERIFICA 1 description == note == address]]]]', [$i] );


				if ( 
				( $jq[0]['docs_description'] <> $jq[0]['docs_note'] ) ||
				( $jq[0]['docs_description'] <> $jq[0]['docs_address'] ) ||
				( $jq[0]['docs_note'] <> $jq[0]['docs_address'] ) ||
				) 
				{
					die('VERIFICA 1 FALLITA!')
				}



				Log::channel('stderr')->notice('[[[[[VERIFICA 2 SELECT SU description SU NOTE SU ADDRESS stesso ID con valore intero]]]]', [$i] );
				Log::channel('stderr')->notice('[[[[[VERIFICA 3 LIKE su description e su address stesso valore]]]]', [$i] );

				
							
			

			} 

		} 












		
		// esegue $rows inserimenti nella tabella migrations ...
		elseif ($action == "insertMigrations") 
		{


			for($i = 0; $i<$rows; $i++)
			{

				// create JSON request
				$faker = Faker::create('SeedData');
				$rNumber = $faker->randomNumber(5, true);
				$rMigrationName = $faker->name();
				$rSentence = $faker->sentence();
				// $rSentence = 'A eveniet suscipit molestiae minus sit tenetur.';
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
					"fields" : [
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

			


				Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:' . $action, [$i, $jsonRequest] );
				
				$this->FEI_engine = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexEngine();
				$q = $this->FEI_engine->process($jsonRequest);
				Log::channel('stderr')->info('parseSQL:FINAL!:', [$q] );

			}

		} 
		// esegue select su description prendendone una esistente e verificando il risultato
		elseif ( $action == "selectMigrationsEncrypted" ) {

			for($i = 0; $i<$rows; $i++)
			{

				$faker = Faker::create('SeedData');

				// get a real description from migration

				$Ids = DB::table('migrations')
                    ->select('id')
                    // ->where('rt_tag', $tag)
                    //->where('rt_key', $key)
                    ->get();

				$cntIds = count($Ids);

				$idSelected = $faker->numberBetween(1, $cntIds);

				// dd ( $Ids[$idSelected-1] );

				// $val = intval($total_results->getText());
				// dd ( intval($Ids[$idSelected-1]->id)   );

				$v = DB::table('migrations')
				// ->select('id')
				->where('id', intval($Ids[$idSelected-1]->id) )
				//->where('rt_key', $key)
				->get();

				// dd($v[0]);
				// dd($v[0]->description);

				// create JSON request
				
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
							{  "fieldName": "migrations.name"   },
							{  "fieldName": "migrations.name_plain"   },
							{  "fieldName": "migrations.surname"   },
							{  "fieldName": "migrations.surname_plain"   }

							
					],

					"where" : [
            
						{
							"operator" : "AND",
							"clauses" : [
								{
									"fieldName" : "migrations.id",
									"operator" : "<",
									"fieldValue" : 300
								},
								{
									"fieldName" : "migrations.description",
									"operator" : "=",
									"fieldValue" : "' . $v[0]->description_plain . '"
								}

							]
						}
					],

					"order" : [

						{
							"sortOrder" : "DESC",
							"fields" : [
								{  "fieldName": "migrations.id"   }
							]
						}
			
			
					]
					
			

						}';

				Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:' . $action, [$i, $jsonRequest] );
				
				$this->FEI_engine = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexEngine();
				$q = $this->FEI_engine->process($jsonRequest);
				
				Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:' . $action, [ count($q), $q[0]->id , $v[0]->id ] );
				Log::channel('stderr')->notice('------------------------------------------------------------------------------------------------------------------', [] );
				Log::channel('stderr')->notice('[[[[['. $i . ']]]]] <<<<<<<RISULTATO FINALE>>>>>>>', [ $v[0]->id, $q[0]->id ] );

				// TEST TEST se ritorno diverso errore
				if ( $v[0]->id <> $q[0]->id ) 
				{

					Log::channel('stderr')->error('<<<<<<< ID ERROR >>>>>>>', [ $v[0]->id, $q[0]->id ] );
					die();

				}

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

		} 


		// ricerca LIKE su campo EncryptedIndex 
		elseif ( $action == "selectMigrationsEncryptedIndex" ) {

			// prende un valore random e poi esegue due query e verifica i risultati

			Log::channel('stderr')->notice('FEITC:' . $action, [$rows, $fieldName] );

			for($i = 0; $i<$rows; $i++)
			{

				Log::channel('stderr')->notice('FEITC[' . $i . ']', [$action] );

				$faker = Faker::create('SeedData');

				// get a real description from migration

				$Ids = DB::table('migrations')->select('id')->get();

				$cntIds = count($Ids);

				$idSelected = $faker->numberBetween(1, $cntIds);

				// dd ( $Ids[$idSelected-1] );

				// $val = intval($total_results->getText());
				// dd ( intval($Ids[$idSelected-1]->id)   );

				$v = DB::table('migrations')->where('id', intval($Ids[$idSelected-1]->id) )->get();

				// dd($v[0]);
				// dd($v[0]->description);

				$plain_fieldName = $fieldName . "_plain";
				
				$textFromSearch = $v[0]->{$plain_fieldName};

				// create JSON request
				
				$rNumber = $faker->randomNumber(5, true);
				$rMigrationName = $faker->name();
				$rSentence = $faker->sentence();
				$rName = $faker->words(3, true);
				$rSurname = $faker->words(3, true);

				// get di tre caratteri

				for ($j=0; $j<strlen($textFromSearch) - 3 ; $j++)
				{
					$toSearch = substr($textFromSearch, $j ,3);
					$toSearch = trim($toSearch);
					// Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:SEACH TOKEN:', [$j, $toSearch, $textFromSearch] );
					if (strlen($toSearch) == 3) break;
				}


				// Percentuale di errore
				// $toSearch = "QUQUQUQQUQUQUQ";


				// +++++ TO REMOVE ++++
				// $toSearch = 'cum';

				// ricerca originale

				$test1 = DB::table('migrations')->where($fieldName . '_plain', 'LIKE',  '%' . $toSearch . '%')->get();

				Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:SEARCH:', [$toSearch,$fieldName, $textFromSearch] );

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
						{  "fieldName": "migrations.name"   },
						{  "fieldName": "migrations.name_plain"   },
						{  "fieldName": "migrations.surname"   },
						{  "fieldName": "migrations.surname_plain"   }
					],

					"where" : [
            
						{
							"operator" : "AND",
							"clauses" : [
								{
									"fieldName" : "migrations.' . $fieldName . '",
									"operator" : "LIKE",
									"fieldValue" : "' . $toSearch . '"
								}
							]
						}
					],

					"order" : [

						{
							"sortOrder" : "DESC",
							"fields" : [
								{  "fieldName": "migrations.id"   }
							]
						}
			
			
					]
					
			

						}';

				// Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:' . $action, [$i, $toSearch, $jsonRequest] );
				// Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:' . $action, [$i, $toSearch, $jsonRequest] );
				
				$this->FEI_engine = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexEngine();
				$q = $this->FEI_engine->process($jsonRequest);
				
				Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:Conteggio n. rec:', [ count($q), count($test1) ] );
				

				if ( count($q) <> count($test1)  ) 
				{
					Log::channel('stderr')->error('Il numero di righe ritornate non identico', [ count($q), count($test1)  ] );
					die();
				}

				// verifica degli ids devono essere gli stessi

				// recupero prima lista ids
				$id1 = [];
				foreach($q as $item) 
				{
					// Log::channel('stderr')->notice( '#', [$item] );
					Log::channel('stderr')->debug( $item->id );
					$id1[] = $item->id;
				}
				// test1

				$id2 = [];
				foreach($test1 as $item2) 
				{
					// Log::channel('stderr')->notice( '#', [$item] );
					Log::channel('stderr')->debug( $item2->id );
					$id2[] = $item2->id;

				}

				sort($id1);
				sort($id2);

				if( $id1 ==  $id2 )
				{
					Log::channel('stderr')->debug('FieldsEncryptedIndexTestCommand:OK same array ', [ $id1, $id2 ] );
				}
				else
				{
					Log::channel('stderr')->error('FieldsEncryptedIndexTestCommand:ERRORArray are not THE SAME', [ $id1, $id2 ] );
					die();

				}

				Log::channel('stderr')->notice('FEITC[' . $i . '] ' . $action . ' :OK!:', [$fieldName, "LIKE", $toSearch, $id1, $id2 ] );
				
				// recupero seconda lista ids
		
			} 


		}
		elseif ( $action == "reindexMigrations" ) {

			// create JSON request
			$faker = Faker::create('SeedData');
			$rNumber = $faker->randomNumber(5, true);
			$rMigrationName = $faker->name();
			$rSentence = $faker->sentence();
			// $rSentence = 'A eveniet suscipit molestiae minus sit tenetur.';
			$rName = $faker->words(3, true);
			$rSurname = $faker->words(3, true);


			$jsonRequest = '{
				"action"    : "REINDEX",
				"tables" : [
						{
							"tableName" : "migrations",
							"tableAlias" : "migrations"
						}
					],
				"fields" : [
						{  "fieldName": "migrations.surname"   },
						{  "fieldName": "migrations.name"   }
				]
			}';
			

			$i = 9999;
			Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:' . $action, [$i, $jsonRequest] );
			
			$this->FEI_engine = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexEngine();
			$q = $this->FEI_engine->process($jsonRequest);
			Log::channel('stderr')->info('FINAL!:', [$q] );



		}


		elseif ( $action == "updateMigrationsEncrypted" ) {

			// UPDATE `laravel`.`migrations` SET `migration`='Tom Sam Jhon q', `batch`='80253' WHERE  `id`=10;

			Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:' . $rows, [$action] );
			Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:' . $fieldName, [$action] );

			for($i = 0; $i<$rows; $i++)
			{
				Log::channel('stderr')->debug('**********************************************************************:' . $i, [$action] );
				Log::channel('stderr')->debug('FieldsEncryptedIndexTestCommand:' . $i, [$action] );
				
				$faker = Faker::create('SeedData');

				// get a random id from all ids
				$Ids = DB::table('migrations')->select('id')->get();
				$cntIds = count($Ids);
				$idSelected = $faker->numberBetween(1, $cntIds);

				// dd ( $Ids[$idSelected-1] );
				// $val = intval($total_results->getText());
				// dd ( intval($Ids[$idSelected-1]->id)   );
				// $v = DB::table('migrations')->where('id', intval($Ids[$idSelected-1]->id) )->get();
				// dd($v[0]);

				// create JSON request
				$rNumber = $faker->randomNumber(5, true);
				$rMigrationName = $faker->name();
				$rSentence = $faker->sentence();
				$rName = $faker->words(3, true);
				$rSurname = $faker->words(3, true);

				// get di tre caratteri
				// Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:SEARCH:', [$toSearch,$fieldName, $textFromSearch] );

				$jsonRequest = '{
					"action" : "UPDATE",
					"tables" : [
							{
								"tableName" : "migrations",
								"tableAlias" : "migrations"
							}
						  ],
						
					"fields" : [
							{  
								"fieldName" : "migrations.migration",
								"fieldValue" : "' . $rName . '"  
							},
							{  
								"fieldName" : "migrations.batch",
								"fieldValue" : ' . $rNumber . '  
							},
							{  
								"fieldName" : "migrations.description",
								"fieldValue" : "' . $rSurname . '"  
							},
							{  
								"fieldName" : "migrations.description_plain",
								"fieldValue" : "' . $rSurname . '"  
							},
							{  
								"fieldName" : "migrations.name",
								"fieldValue" : "' . $rName . '"  
							},
							{  
								"fieldName" : "migrations.name_plain",
								"fieldValue" : "' . $rName . '"  
							},
							{  
								"fieldName" : "migrations.surname",
								"fieldValue" : "' . $rName . '"  
							},
							{  
								"fieldName" : "migrations.surname_plain",
								"fieldValue" : "' . $rName . '"  
							}
						
					],

					"where" : [
            
						{
							"operator" : "",
							"clauses" : [
								{
									"fieldName" : "migrations.id",
									"operator" : "=",
									"fieldValue" : ' . $idSelected . '
								}
							]
						}
					]

								
			

						}';

				// Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:' . $action, [$i, $toSearch, $jsonRequest] );
				// Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:' . $action, [$i, $toSearch, $jsonRequest] );
				
				$this->FEI_engine = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexEngine();
				$q = $this->FEI_engine->process($jsonRequest);
				
				// Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:Conteggio n. rec:', [ count($q), count($test1) ] );
				
	
				Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand[' . $i . '] ' . $action . ' :FINAL!:', [] );
				
				// recupero seconda lista ids
		
			} 



		}

		elseif ( $action == "createTable" ) {

			// create JSON request
			$faker = Faker::create('SeedData');
			$rNumber = $faker->randomNumber(5, true);
			$rMigrationName = $faker->name();
			$rSentence = $faker->sentence();
			// $rSentence = 'A eveniet suscipit molestiae minus sit tenetur.';
			$rName = $faker->words(3, true);
			$rSurname = $faker->words(3, true);


			// primaryKey is always id

			$jsonRequest = '{
				"action"    : "CREATETABLE",
				"tableName" : "docs",
				"primaryKey" : "id",
				"fields" : [
						{  
							"fieldName" : "description",
							"fieldType" : "STRING"
						},
						{
							"fieldName" : "batchNumber",
							"fieldType" : "LONG"
						},
						{
							"fieldName" : "note",
							"fieldType" : "ENCRYPTED"
						},
						{
							"fieldName" : "address",
							"fieldType" : "ENCRYPTED_INDEXED"
						}
				]

			}';
			

			$i = 9999;
			Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand:' . $action, [$i, $jsonRequest] );
			
			$this->FEI_engine = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexEngine();
			$q = $this->FEI_engine->process($jsonRequest);
			Log::channel('stderr')->info('FINAL!:', [$q] );



		}


		// esegue dei test sulle librerie di cifratura
		elseif ( $action == "encryption" ) {
			
			Log::channel('stderr')->notice('FieldsEncryptedIndexTestCommand: TEST TEST ENCRYPTION DESCRYPTION ---- FieldsEncryptedIndex:' . $action, [] );
            // $v = \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexEncrypter::encrypt("TEST");		
            
			$this->FEI_encrypter = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexEncrypter();
						

			// Generazione chiavi 


			$key = $this->FEI_encrypter->keygen_sodium();
			$key_hash = $this->FEI_encrypter->keygen_short_hash_sodium();
			$nonce = $this->FEI_encrypter->noncegen_sodium();
			


			Log::channel('stderr')->info('KEY', [$key] );
			Log::channel('stderr')->info('KEY_HASH', [$key_hash] );
			Log::channel('stderr')->info('NONCE:', [$nonce] );


			$msg = 'docs.batchnumber';
			$v = $this->FEI_encrypter->short_hash_sodium($msg);
			Log::channel('stderr')->info('HASH sodium :', [$msg, $v] );



			$msg = 'docs.description';
			$v = $this->FEI_encrypter->short_hash_sodium($msg);
			Log::channel('stderr')->info('HASH sodium :', [$msg, $v] );

			$msg = 'docs.note';
			$v = $this->FEI_encrypter->short_hash_sodium($msg);
			Log::channel('stderr')->info('HASH sodium :', [$msg, $v] );

		
			$msg = 'docs.notedfgdfgdfgdfdfg';
			$v = $this->FEI_encrypter->short_hash_sodium($msg);
			Log::channel('stderr')->info('HASH sodium :', [$msg, $v] );


			$v = $this->FEI_encrypter->encrypt_sodium([
				"fieldName" => 'migrations.description',
				"fieldValue" => 	'hic sunt leones'
			]);
			
			Log::channel('stderr')->info('ENCRYPTED-->!:', [$v] );

			// 3ef8430e838966b1d29878e8c5b85edd8eecfd60fb67ebc9e31e0d9a89cb15

			$v = $this->FEI_encrypter->decrypt_sodium([
				"fieldName" => 'migrations.description',
				"fieldValue" => 	'3ef8430e838966b1d29878e8c5b85edd8eecfd60fb67ebc9e31e0d9a89cb15'
			]);

			Log::channel('stderr')->info('DECRYPTED-->!:', [$v] );

		

		} else {
			Log::channel('stderr')->notice('FieldsEncryptedIndex:test: action not found!', [$action] );
		}


		// Log::channel('stderr')->notice('FieldsEncryptedIndex:test!:', ['-------------- END! -------------------------'] );

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


        // Log::channel('stderr')->info('TEST finished!:', []);



        
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