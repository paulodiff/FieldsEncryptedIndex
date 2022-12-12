<?php
namespace Paulodiff\FieldsEncryptedIndex\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

use Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexService;

use Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexEncrypter;
use Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexQueryBuilder;
use Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexConfig;

class FieldsEncryptedIndexParseSQLCommand extends Command
{
    protected $signature = 'FieldsEncryptedIndex:parseSQL';

    protected $description = 'parseSQL';

    public $GLOBAL_TABLE_CONFIG = [];

	public $FEI_config;

	public function handle()
	{

		Log::channel('stderr')->info('parseSQL:handle', [] );
		$this->FEI_config = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexConfig();
		// $this->FEI_config->checkConfig();
		$this->FEI_service = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexService();


		//$r = $this->FEI_service->feiTokenize('MARIO ROSSI', 3);
		//Log::channel('stderr')->info('parseSQL:handle', [$r] );

		/*

		$tf1 = $this->FEI_config->getTableConfig('migrations');
		Log::channel('stderr')->info('parseSQL:handle', [$tf1] );
		$tf2 = $this->FEI_config->getFieldTypeDefinition('migrations.id');
		Log::channel('stderr')->info('parseSQL:handle', [$tf2] );
		$tf3 = $this->FEI_config->getFieldTypeDefinition('ldapuser.surname');
		Log::channel('stderr')->info('parseSQL:handle', [$tf3] );

		*/

		Log::channel('stderr')->info('parseSQL:handle', ['load migrationInsert'] );
		$sqlRequest = $this->FEI_config->loadFakeRequestAndValidate('migrationInsert');

		$this->FEI_sql_query_builder = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexQueryBuilder();
		$q = $this->FEI_sql_query_builder->buildQuery($sqlRequest);
		Log::channel('stderr')->info('parseSQL:FINAL!:', [$q] );

		$this->FEI_sql_query_runner = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexQueryRunner();
		$r = $this->FEI_sql_query_runner->runQuery($q);
		Log::channel('stderr')->info('runSQL:FINAL!:', [$r] );

		




/*		

		Log::channel('stderr')->info('### VERB ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
        $verbClause = $this->buildVerbClause($sqlRequest); 
        Log::channel('stderr')->info('### VERB SQL-> ##', [$verbClause] );


		Log::channel('stderr')->info('### FROM ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
        $fromTableClause = $this->buildFromTableClause($sqlRequest); 
        Log::channel('stderr')->info('### FROM TABLE SQL-> ##', [$fromTableClause] );

        // check fields and table name se esistono e di che tipo sono
        Log::channel('stderr')->info('### FIELDS ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );

		$fieldsClause = $this->buildFieldsClause($sqlRequest); 

		Log::channel('stderr')->info('### FIELDS SQL ####', [$fieldsClause] );
        Log::channel('stderr')->info('### WHERE ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );

        $whereClause = $this->buildWhereClause($sqlRequest['where'][0]);
        Log::channel('stderr')->info('### WHERE COND SQL-> ##', [$whereClause] );

		Log::channel('stderr')->info('### JOIN ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
        $joinClause = $this->buildJoinClause($sqlRequest); 
        Log::channel('stderr')->info('### JOIN SQL-> ##', [$joinClause] );


		Log::channel('stderr')->info('### ORDER ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
        $orderClause = $this->buildOrderClause($sqlRequest); 
        Log::channel('stderr')->info('### ORDER SQL-> ##', [$orderClause] );

		$sqlStatement = $verbClause . " " . $fieldsClause['SQL'] . " " . $fromTableClause . " " . $joinClause . " " . $whereClause . " " . $orderClause;

*/
		Log::channel('stderr')->info('<<<<<<END>>>>>>>', [] );
	}

	
    function getTableConfig($tn)
    {
        Log::channel('stderr')->debug('getTableConfig:', [$tn] );
		return $this->FEI_config->getTableConfig($tn);

		// Log::channel('stderr')->debug('getTableConfig:', [$this->GLOBAL_TABLE_CONFIG] );

		// dd($this->GLOBAL_TABLE_CONFIG['migrations']);

        //return $this->GLOBAL_TABLE_CONFIG[$tn];
    }

	function getFieldTypeDefinition($fn)
	{
		return $this->FEI_config->getFieldTypeDefinition($fn);
	}

    function getFieldTypeDefinition_old($fn)
    {
        // Log::channel('stderr')->info('getFieldTypeDefinition:', [$fn] );
        // check fieldName in query in table config amd type
        $pieces = explode(".", $fn);
        $tname = $pieces[0];
        $fname = $pieces[1];
        
        Log::channel('stderr')->debug('getFieldTypeDefinition:', [$fn, $tname, $fname] );

        $gc = $this->getTableConfig($tname);

		Log::channel('stderr')->debug('getFieldTypeDefinition:', [$gc] );

        if ( array_search($fname, array_column($gc['fields'], 'fieldName') ) === false ) 
        {
            Log::channel('stderr')->debug('getFieldTypeDefinition:NOT FOUND!', [$tname, $fname, array_search($fname, array_column($gc['fields'], 'fieldName') )] );
            die();
        } 
        else 
        {
            $key = array_search($fname, array_column($gc['fields'], 'fieldName') );
            // Log::channel('stderr')->info('FOUND!', [$tname, $fname, array_search($fname, array_column($GLOBAL_TABLE_CONFIG[$tname]['fields'], 'fieldName') )] );

			// Log::channel('stderr')->info('getFieldTypeDefinition:return ', [$key] );

            $fiedlType = $gc['fields'][$key]['fieldType'];
            // Log::channel('stderr')->info('FOUND!', [$tname, $fname, $GLOBAL_TABLE_CONFIG[$tname]['fields'][$key]['fieldType']] );

			// Log::channel('stderr')->info('getFieldTypeDefinition:return ', [$fiedlType] );

            return $fiedlType;
        }
    }
	

    function getFieldClause($o)
    {
        
        $ft = $this->getFieldTypeDefinition($o['fieldName']);
        Log::channel('stderr')->debug('getFieldClause:', [$ft] );

        if (in_array($ft, ["LONG", "STRING"])) 
        {
            return  " " . $o['fieldName'] . " " . $o['operator'] . " " . $o['value'] . " ";
        } 
        elseif (in_array($ft, ["ENCRYPTED"]))
        {
            return  " " . $o['fieldName'] . " " . $o['operator'] . " !ENC! " . $o['value'] . " ";
        }
        elseif (in_array($ft, ["ENCRYPTED_INDEXED"]))
        {
            return  " { " . $o['fieldName'] . " !ENC_INDEX! IN VALUES (AAAAA,BBBBB) } ";
        }
        else
        {
            Log::channel('stderr')->error('fieldType NOT FOUND!', [$ft] );
            die();
        }

    }

	function buildVerbClause(array $r) {


		Log::channel('stderr')->debug('buildVerbClause: :', [is_array($r)] );
		Log::channel('stderr')->debug('buildVerbClause: :', [array_key_exists('action', $r)] );

		if (    is_array($r) 
				&& array_key_exists('action', $r) 
				)
		{

			$verb = $r['action'];
			Log::channel('stderr')->debug('buildVerbClause: :', [$verb] );

			if (!in_array($verb, ["SELECT", "UPDATE", "INSERT"]))
			{
				Log::channel('stderr')->error('buildVerbClause: verb not valid!:', [$verb] );
				die();
			}
			
			return $verb;

		}
		else
		{
			Log::channel('stderr')->debug('buildVerbClause: return void - failded test!', [] );
			return "";
		}

	}

	function buildFromTableClause(array $r) {

		Log::channel('stderr')->debug('buildFromTableClause:', [is_array($r)] );

		
        if($r['tables'])
        {
			$SQL = "";

            foreach ($r['tables'] as $index => $item) 
            {
                Log::channel('stderr')->debug('buildFromTableClause:tname:', [$item] );
                
				$tc  = $this->getTableConfig($item['tableName']);
                
				if ( $SQL == "") 
				{
					$SQL = "FROM " . $item['tableName'];
				}
				else 
				{
					$SQL = $SQL . " , " . $item['tableName'];
				}
			

            }

        }

		return $SQL;


	}




	function buildFieldsClause(array $r) {

		Log::channel('stderr')->info('buildFieldsClause:', [is_array($r)] );

		$encryptedSelectFields = [];
        
        if($r['fields'])
        {
			$SQL = "";

            foreach ($r['fields'] as $index => $item) 
            {
                Log::channel('stderr')->info('buildFieldsClause:F_idx:', [$index] );
                Log::channel('stderr')->info('buildFieldsClause:F_name:', [$item] );

                // check fieldName in query in table config amd type
                $pieces = explode(".", $item['fieldName']);
                $tname = $pieces[0];
                $fname = $pieces[1];

                Log::channel('stderr')->info('buildFieldsClause:Search for 1', [$tname, $item['fieldName'] ] );

				$fiedlType = $this->getFieldTypeDefinition($item['fieldName']);

				Log::channel('stderr')->info('buildFieldsClause:Search for 2', [$tname, $fname, $fiedlType ] );

				 
				if (in_array($fiedlType, ["ENCRYPTED", "ENCRYPTED_INDEXED"]))
				{
					$encryptedSelectFields[] = [
						"tableName" => $tname,
						"fiedlName" => $fname,
						"fieldType" => $fiedlType
					];
				}
				
				if ( $SQL == "") 
				{
					$SQL = $item['fieldName'];
				}
				else 
				{
					$SQL = $SQL . "," . $item['fieldName'];
				}
			

            }

			return array(
				"SQL" => $SQL ,
				"fiels2decrypt" => $encryptedSelectFields
			);

        }



	}

	function buildJoinClause(array $r) {

		Log::channel('stderr')->debug('buildJoinClause: TODO MULTIPLE JOIN:', [$r] );

		if (count($r['join']) > 1 )
		{
			Log::channel('stderr')->error('buildJoinClause: TODO MULTIPLE JOIN!:', [$r] );
			die();
		}

		if (is_array($r) && array_key_exists('join', $r))
		{
			$JOIN_CLAUSE = "";

			foreach ($r['join'] as $index => $item) 
            {
                Log::channel('stderr')->info('buildJoinClause: joinTable:', [$item['joinTable']] );
                Log::channel('stderr')->info('buildJoinClause: onJoinSource:', [$item['onJoinSource']] );
                Log::channel('stderr')->info('buildJoinClause: onJoinDest:', [$item['onJoinDest']] );


				// check field's type return value only if exists
				$tc  = $this->getTableConfig($item['joinTable']);
				$ft1 = $this->getFieldTypeDefinition($item['onJoinSource']);
				$ft2 = $this->getFieldTypeDefinition($item['onJoinDest']);


				if (in_array($ft1, ["ENCRYPTED", "ENCRYPTED_INDEXED"]))
				{
					Log::channel('stderr')->error('buildJoinClause: JOIN on encrypted not supported:', [] );
					die();
				}

				if (in_array($ft2, ["ENCRYPTED", "ENCRYPTED_INDEXED"]))
				{
					Log::channel('stderr')->error('buildJoinClause: JOIN on encrypted not supported:', [] );
					die();
				}

				// INNER JOIN ldapuser ON migrations.id = ldapuser.id

				$JOIN_CLAUSE = " INNER JOIN " . $item['joinTable'] . " ON " . $item['onJoinSource'] . " = " . $item['onJoinDest'];

			}

			return $JOIN_CLAUSE;

		}
		else
		{
			return "";
		}

	}


	function buildOrderClause(array $r) {


		Log::channel('stderr')->info('buildOrderClause: joinTable:', [is_array($r)] );
		Log::channel('stderr')->info('buildOrderClause: joinTable:', [array_key_exists('order', $r)] );
		Log::channel('stderr')->info('buildOrderClause: joinTable:', [array_key_exists('sortOrder', $r['order'][0])] );
		Log::channel('stderr')->info('buildOrderClause: joinTable:', [array_key_exists('fields', $r['order'][0])] );

		if (    is_array($r) 
				&& array_key_exists('order', $r) 
				&& array_key_exists('sortOrder', $r['order'][0])
				&& array_key_exists('fields', $r['order'][0])
				)
		{
			$ORDER_CLAUSE = "";

			$sortOrder = $r['order'][0]['sortOrder'];
			Log::channel('stderr')->info('buildOrderClause: joinTable:', [$sortOrder] );

			if (!in_array($sortOrder, ["ASC", "DESC"]))
			{
				Log::channel('stderr')->error('buildOrderClause: sort not valid!:', [$sortOrder] );
				die();
			}

			foreach ($r['order'][0]['fields'] as $index => $item) 
            {
                Log::channel('stderr')->info('buildOrderClause: fieldName:', [$item['fieldName']] );

				// check field's type return value only if exists
				$ft = $this->getFieldTypeDefinition($item['fieldName']);

				if (in_array($ft, ["ENCRYPTED", "ENCRYPTED_INDEXED"]))
				{
					Log::channel('stderr')->error('buildOrderClause: ORDER on encrypted not supported:', [] );
					die();
				}

				
				// INNER JOIN ldapuser ON migrations.id = ldapuser.id

				// ORDER BY column1, column2, ... ASC|DESC;

				if ( $ORDER_CLAUSE == "") 
				{ 
					$ORDER_CLAUSE =  $item['fieldName'];
				}
				else 
				{
					$ORDER_CLAUSE =  $ORDER_CLAUSE . " , " . $item['fieldName'];
				}

				
			}

			return " ORDER BY " . $ORDER_CLAUSE . "  " . $sortOrder . " ";

		}
		else
		{
			Log::channel('stderr')->debug('buildOrderClause: return void - failded test!', [] );
			return "";
		}

	}


    function buildWhereClause(array $elements) {

        // $branch = array();
    
        // echo "\nSTART buildTree----------------------- \n";
		Log::channel('stderr')->info('buildWhereClause: joinTable:', ['START'] );
        // print_r($elements);
        // print_r($elements['operator']);
        // print_r($elements['clauses']);

        $curOPERATOR = "";

        if (is_array($elements) && 
                    array_key_exists('operator', $elements) &&
                    array_key_exists('clauses', $elements) && 
                    is_array($elements['clauses']) ) 
        {
            // echo 'operatore trovato e clauses ...' . $elements['operator'] . "\n";
            
            $curOPERATOR = $elements['operator'];

            // echo 'ciclo sulle clauses n: ' . count($elements['clauses']) . "\n";
            
            $toRet = "(";

            foreach ($elements['clauses'] as $index => $clause) 
            {
                // echo $index . "\n";
                // print_r($clause);
                // echo "call ...\n";
                $q = $this->buildWhereClause($clause);
                // echo "returned... " . $q . "\n";

                if($toRet !== "(")
                {
                    $toRet = $toRet . $curOPERATOR . $q;
                }
                else
                {
                    $toRet = $toRet . $q;
                }
            }

            return $toRet . ")";
        } 
        else 
        {

            // echo "nodo finale \n";

            $out = $this->getFieldClause($elements);

            return $out;

        }


        // return $curOPERATOR;
    }





    public function handle_1()
    {
        $this->info('FieldsEncryptedIndex parseSQL ...');
        
        Log::channel('stderr')->info('parseSQL:', [] );

        $jsonTableMigrations = '
        {
            "tableName"  : "migrations",
            "primaryKey" : "id",
            "fields" : [
                {
                    "fieldName" : "id",
                    "fieldType" : "LONG"
                },
                {
                    "fieldName" : "migration",
                    "fieldType" : "STRING"
                },
                {
                    "fieldName" : "description",
                    "fieldType" : "ENCRYPTED"
                }
            ],
            "indexes" : []
        }
        ';

        $jsonTableLdapuser = '
        {
            "tableName"  : "ldapuser",
            "primaryKey" : "id",
            "fields" : [
                {
                    "fieldName" : "id",
                    "fieldType" : "LONG"
                },
                {
                    "fieldName" : "givenname",
                    "fieldType" : "ENCRYPTED_INDEXED"
                },
                {
                    "fieldName" : "surname",
                    "fieldType" : "ENCRYPTED"
                },
                {
                    "fieldName" : "uuid",
                    "fieldType" : "STRING"
                }
            ],
            "indexes" : []
        }
        ';


        $jsonTableDefinition = '
        {
            "tableName"  : "t1",
            "primaryKey" : "id",
            "fields" : [
                {
                    "fieldName" : "id",
                    "fieldType" : "LONG"
                },
                {
                    "fieldName" : "fkId",
                    "fieldType" : "LONG"
                },
                {
                    "fieldName" : "name",
                    "fieldType" : "STRING"
                },
                {
                    "fieldName" : "description",
                    "fieldType" : "ENCRYPTED"
                },
                {
                    "fieldName" : "range",
                    "fieldType" : "ENCRYPTED_INDEXED"
                }
            ],
            "indexes" : []
        }
        ';

        $tableRules = [
           
            'tableName' => 'string|required',
            'primaryKey' => 'string|required',

            "fields" => 'array|required',
            "fields.*.fieldName" => 'string|required',
            "fields.*.fieldType" => 'string|required',

            "indexes" => 'array'
        ];

        //  SELECT, INSERT, UPDATE or DELETE.
        $sqlRequestRules = [
            'action' => [
                'string',
                'required',
                Rule::in(['SELECT', 'INSERT']),
            ],

            'tables' => 'array|required',
            'tables.*.tableName' => 'string|required',
            'tables.*.tableAlisa' => 'string|required',

            "fieldsToGet" => 'array|required',
            "fieldsToGet.*.fieldName" => 'string|required',

            "fields" => 'array|required',
            "fields.*.fieldName" => 'string|required',

            "where" => 'array',

            "order" => 'array',

            "limit" => 'array'
        ];

        /*
        SELECT migrations.migration, ldapuser.id, ldapuser.givenname, ldapuser.uuid
        
        FROM migrations
        
        INNER JOIN ldapuser ON migrations.id = ldapuser.id

        WHERE 
        migrations.migration LIKE '%0000%' 
        AND
        ldapuser.uuid LIKE '%8%'

		ORDER BY column1, column2, ... ASC|DESC;
        */


        $jsonSqlRequest = '
        {
         
          "action" : "SELECT",
        
          "tables" : [

            {
                "tableName" : "migrations",
                "tableAlias" : "migrations"
            },

            {
                "tableName" : "ldapuser",
                "tableAlias" : "ldapuser"
            }

          ],
        
          "fields" : [
            {  "fieldName": "migrations.id"   },
            {  "fieldName": "migrations.migration"   },
            {  "fieldName": "ldapuser.id"   },
            {  "fieldName": "ldapuser.givenname"   },
            {  "fieldName": "ldapuser.surname"   },
            {  "fieldName": "ldapuser.uuid"   }
          ],

          "join" : [
            {
                "joinTable" : "ldapuser",
                "onJoinSource": "migrations.id",
                "onJoinDest": "ldapuser.id"
            }

          ],
        
          "where" : [
            
            {
                "operator" : "OR",
                "clauses" : [
                    {
                        "fieldName" : "migrations.migration",
                        "operator" : "=",
                        "value" : "MARIO1"
                    },

                    {
                        "operator" : "AND",
                        "clauses" : [
                            {
                                "fieldName" : "ldapuser.givenname",
                                "operator" : "=",
                                "value" : "MARIO2"
                            },

                            {
                                "fieldName" : "ldapuser.surname",
                                "operator" : "LIKE",
                                "value" : "%MA%"
                            }
                        ]

                    },

                    {
                        "fieldName" : "ldapuser.uuid",
                        "operator" : ">",
                        "value" : "9304909"
                    }

                ]
            }

          ],
        
          "order" : [

			{
                "sortOrder" : "DESC",
				"fields" : [
					{  "fieldName": "migrations.id"   },
					{  "fieldName": "ldapuser.uuid"   }
				]
            }


		  ],
        
          "limit" : []
        }
        
        ';

		/**
		 * Validazione configurazione tabelle
		 * 
		 * 
		 * 
		 */

		$tableMigrations = json_decode($jsonTableMigrations, true);

        if (is_null($tableMigrations)) 
        {
            Log::channel('stderr')->info('parseSQL:', ['ERROR PARSE table!'] );
            die();
        }

        $Validator = Validator::make($tableMigrations, $tableRules);
        if ($Validator->fails()) {
            Log::channel('stderr')->error('MIGRATIONS ERROR ', [$Validator->errors()] );
        }

        $tableLdapuser = json_decode($jsonTableLdapuser, true);
        $Validator = Validator::make($tableLdapuser, $tableRules);
        if ($Validator->fails()) {
            Log::channel('stderr')->error('LDAPUSER Table ERROR', [$Validator->errors()] );
        }

        // dd($data2validate);

     
        $this->GLOBAL_TABLE_CONFIG['migrations'] = $tableMigrations;
        $this->GLOBAL_TABLE_CONFIG['ldapuser'] = $tableLdapuser;

        // var_dump($this->GLOBAL_TABLE_CONFIG);
        

		/**
         * Validazione request
         * 
         */

        $sqlRequest = json_decode($jsonSqlRequest, true);


        if (is_null($sqlRequest)) 
        {
            Log::channel('stderr')->info('parseSQL:', ['ERROR PARSE request!'] );
            die();
        }

        Log::channel('stderr')->debug('Controllo richiesta', [$sqlRequest] );

        // dd($sqlRequest);

        $sqlRequestValidator = Validator::make($sqlRequest, $sqlRequestRules);

       

        Log::channel('stderr')->debug('FieldsEncryptedIndexTrait!config checkConfig!', [$sqlRequestValidator] );
  
        if ($sqlRequestValidator->fails()) {
          Log::channel('stderr')->error('FieldsEncryptedIndexTrait!config error!', [$sqlRequestValidator->fails()] );
          Log::channel('stderr')->error('FieldsEncryptedIndexTrait!this error --->', [$sqlRequestValidator->errors()] );
          // Log::channel('stderr')->error('FieldsEncryptedIndexTrait! data array ', [self::$FieldsEncryptedIndexConfig] );
          // Log::channel('stderr')->error('FieldsEncryptedIndexTrait:Use this template:', [self::$configFormat] );
        } else {
            Log::channel('stderr')->info('parseSQL:', ['ALL sql request are valid!'] );
        }
   

        

        Log::channel('stderr')->info('Recupero informazioni da configurazione ', ['##################################'] );



        /**
         * Validazione ACTION
         * 
         * 
         * 
         */



		/**
		 * VERB - check and create Verb Action
		 * 
		 * 
		 */

		Log::channel('stderr')->info('### VERB ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
        $verbClause = $this->buildVerbClause($sqlRequest); 
        Log::channel('stderr')->info('### VERB SQL-> ##', [$verbClause] );

        /**
         * FROM controllo elenco tabelle
         * 
         * 
         */

		Log::channel('stderr')->info('### FROM ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
        $fromTableClause = $this->buildFromTableClause($sqlRequest); 
        Log::channel('stderr')->info('### FROM TABLE SQL-> ##', [$fromTableClause] );

        /**
         * Controllo dei campi fields di SELEZIONE
         * 
         * 1- controllo che il campo esiste in configurazione
         * 2- controllo il tipo di campo e lo aggiungo nelle liste corrispondenti
         * 
         * 
         */



        // check fields and table name se esistono e di che tipo sono
        Log::channel('stderr')->info('### FIELDS ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );

		$fieldsClause = $this->buildFieldsClause($sqlRequest); 

		Log::channel('stderr')->info('### FIELDS SQL ####', [$fieldsClause] );
        Log::channel('stderr')->info('### WHERE ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );

        /**
         *  # WHERE # Verifica delle condizioni where 
         * 
         *  1- controlla che ci siano campi cifrati e con i corretti operatori
         *  2 - se esiste un campo ENCRYPTED_INDEXED lo mette encryptedIndexedConditions con i relativi INDEX
         *  3 - se esite un campo ENCRYPTED lo sostituisce
         * 
         */

        $whereClause = $this->buildWhereClause($sqlRequest['where'][0]);
        Log::channel('stderr')->info('### WHERE COND SQL-> ##', [$whereClause] );

		/**
         *  # JOIN validazione della join
         * 
         *  1 - controlla che esita la tabella di join
         *  2 - controlla che esistano i campi di join
         *  3 - ritorna la join clause
         * 
         */

		Log::channel('stderr')->info('### JOIN ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
        $joinClause = $this->buildJoinClause($sqlRequest); 
        Log::channel('stderr')->info('### JOIN SQL-> ##', [$joinClause] );


		/**
         *  # ORDER creazione/validazione 
         * 
         * 
         */

		Log::channel('stderr')->info('### ORDER ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
        $orderClause = $this->buildOrderClause($sqlRequest); 
        Log::channel('stderr')->info('### ORDER SQL-> ##', [$orderClause] );


        // CREATE SQL------------

        // WHERE check

        // GET DATA

        // IF ENCRYPTED DECODED

        // check if fields are encrypted

        // if encryption  : ENCRYPTED only decode

        // if encryption  : ENCRYPTED_INDEX only decode

		$sqlStatement = $verbClause . " " . $fieldsClause['SQL'] . " " . $fromTableClause . " " . $joinClause . " " . $whereClause . " " . $orderClause;

        Log::channel('stderr')->info('FINAL STATEMENT:', [$sqlStatement ] );

        Log::channel('stderr')->info('Recupero dati  ', ['------'] );

        Log::channel('stderr')->info('Decodifica campi cifrati  ', ['------'] );

        // Log::channel('stderr')->info('FIELDS_ENCRYPTED_INDEX_KEY=', [config('FieldsEncryptedIndex.key')] );
        // Log::channel('stderr')->info('FIELDS_ENCRYPTED_INDEX_NONCE=', [config('FieldsEncryptedIndex.nonce')] );
        // Log::channel('stderr')->info('FIELDS_ENCRYPTED_INDEX_ENCRYPT=', [config('FieldsEncryptedIndex.encrypt')] );
        // Log::channel('stderr')->info('FIELDS_ENCRYPTED_INDEX_PREFIX=', [config('FieldsEncryptedIndex.prefix')] );


        /*
        if ( config('FieldsEncryptedIndex.key') !==  null) {
            Log::channel('stderr')->info('FIELDS_ENCRYPTED_INDEX_KEY=', [config('FieldsEncryptedIndex.key')] );
        } else {
            Log::channel('stderr')->error('FIELDS_ENCRYPTED_INDEX_KEY=', ['ERROR NOT FOUND set in /config/FieldsEncryptedIndex.pho'] );
            die();
        }

        if ( config('FieldsEncryptedIndex.nonce') !==  null) {
            Log::channel('stderr')->info('FIELDS_ENCRYPTED_INDEX_NONCE=', [config('FieldsEncryptedIndex.nonce')] );
        } else {
            Log::channel('stderr')->error('FIELDS_ENCRYPTED_INDEX_NONCE=', ['ERROR NOT FOUND set in /config/FieldsEncryptedIndex.pho'] );
            die();
        }

        if ( config('FieldsEncryptedIndex.encrypt') !==  null) {
            Log::channel('stderr')->info('FIELDS_ENCRYPTED_INDEX_ENCRYPT=', [config('FieldsEncryptedIndex.encrypt')] );
        } else {
            Log::channel('stderr')->error('FIELDS_ENCRYPTED_INDEX_ENCRYPT=', ['ERROR NOT FOUND set in /config/FieldsEncryptedIndex.pho'] );
            die();
        }


        if ( config('FieldsEncryptedIndex.prefix') !==  null) {
            Log::channel('stderr')->info('FIELDS_ENCRYPTED_INDEX_PREFIX=', [config('FieldsEncryptedIndex.prefix')] );
        } else {
            Log::channel('stderr')->error('FIELDS_ENCRYPTED_INDEX_PREFIX=', ['ERROR NOT FOUND set in /config/FieldsEncryptedIndex.pho'] );
            die();
        }

/*
        if ( config('FieldsEncryptedIndex.stop') !==  null) {
            Log::channel('stderr')->info('FIELDS_ENCRYPTED_INDEX_PREFIX=', [config('FieldsEncryptedIndex.stop')] );
        } else {
            Log::channel('stderr')->error('FIELDS_ENCRYPTED_INDEX_PREFIX=STOP', ['ERROR NOT FOUND set in /config/FieldsEncryptedIndex.pho'] );
            die();
        }
*/

/*

        Log::channel('stderr')->info('CheckConfig:', ['Checking Laravel Crypt and Hash function'] );

        try {

            Log::channel('stderr')->info('CheckConfig:Encryption config driver:', [config('hashing.driver')] );
            
            $s = 'test';

            $h1 = FieldsEncryptedIndexEncrypter::encrypt($s);
            $h2 = FieldsEncryptedIndexEncrypter::encrypt($s);

            $cr1 = FieldsEncryptedIndexEncrypter::decrypt($h1);
            $cr2 = FieldsEncryptedIndexEncrypter::decrypt($h2);

            Log::channel('stderr')->info('CheckConfig:Encrypted:', [$h1] );
            Log::channel('stderr')->info('CheckConfig:Encrypted:', [$h2] );
            Log::channel('stderr')->info('CheckConfig:Decrypted:', [$cr1] );
            Log::channel('stderr')->info('CheckConfig:Decrypted:', [$cr2] );
            $hs1 = FieldsEncryptedIndexEncrypter::hash($s);
            $hs2 = FieldsEncryptedIndexEncrypter::hash($s);
            Log::channel('stderr')->info('CheckConfig:Hash:', [$hs1] );
            Log::channel('stderr')->info('CheckConfig:Hash:', [$hs2] );

            if ($h1 === $h2)
            {
                Log::channel('stderr')->info('WARNING SECURITY ALERT Encryption data is the SAME!!!!:', [] );
            }

            if ($hs1 <> $hs2)
            {
                Log::channel('stderr')->info('WARNING SECURITY ALERT HASH data is different!!!', [] );
            }


        } catch (\Exception $e) {
            Log::channel('stderr')->error('CheckConfig:', ['ERROR! Please check hash, encrypt Laravel config! '. $e] );
            die( $e );
            // $this->assertTrue(false);
        }

        /*
        Log::channel('stderr')->info('CheckConfig:', ['Checking .env Rainbow parameter']);
        if (    
            ( config('FieldsEncryptedIndex.key')     !==  null) && 
            ( config('FieldsEncryptedIndex.nonce')   !==  null) &&
            ( config('FieldsEncryptedIndex.encrypt') !==  null ) 
            ) {
                 // $h1 = FieldsEncryptedIndexEncrypter::hash($test);
            Log::channel('stderr')->info('CheckConfig:', [
                'Environment vars OK!',
                config('FieldsEncryptedIndex.key'),
                config('FieldsEncryptedIndex.nonce'),
                config('FieldsEncryptedIndex.encrypt')
            ] );
            // $h2 = FieldsEncryptedIndexEncrypter::short_hash($test);
            // Log::channel('stderr')->info('Short_Hash("test"):', [$h2] );
        } else {
            Log::channel('stderr')->info('CheckConfig:', ['!ERROR! .env parameters NOT FOUND, check config/FieldsEncryptedIndex.php configuration, add this following values to .env and run '] );
            $key = sodium_crypto_secretbox_keygen();
            $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            // return  sodium_base642bin(config('rainbowtable.key') , SODIUM_BASE64_VARIANT_ORIGINAL);
            Log::channel('stderr')->info('RAINBOW_TABLE_INDEX_KEY=' . sodium_bin2base64( $key , SODIUM_BASE64_VARIANT_ORIGINAL ), [] );
            Log::channel('stderr')->info('RAINBOW_TABLE_INDEX_NONCE=' . sodium_bin2base64( $nonce , SODIUM_BASE64_VARIANT_ORIGINAL ), [] );
            Log::channel('stderr')->info('RAINBOW_TABLE_INDEX_ENCRYPT=true', ['false only for debugging purpose'] );
            // $this->assertTrue(false);
        }
        */
/*
        Log::channel('stderr')->info('CheckConfig:', ['Checking Sodium library ... ']);

        try {

            $key = sodium_crypto_secretbox_keygen();
            $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

            Log::channel('stderr')->info('KEY=' . sodium_bin2base64( $key , SODIUM_BASE64_VARIANT_ORIGINAL ), [] );
            Log::channel('stderr')->info('NONCE=' . sodium_bin2base64( $nonce , SODIUM_BASE64_VARIANT_ORIGINAL ), [] );
            Log::channel('stderr')->info('ENCRYPT=true', ['false only for debugging purpose'] );
        } catch (\Exception $e) {
            Log::channel('stderr')->error('CheckConfig:', ['ERROR! Sodium! '. $e] );
            die( $e );
            // $this->assertTrue(false);
        } 
*/


/*
        Log::channel('stderr')->info('CheckConfig:', ['Checking PHP SODIUM'] );
        try {
            $out = sodium_crypto_generichash('CHECK SODIUM');
            Log::channel('stderr')->info('CheckConfig:', ['SODIUM OK'] );
        } catch (\Exception $e) {
            Log::channel('stderr')->info('CheckConfig:', ['Could not use PHP SODIUM.  Please check your PHP.INI for SODIUM configuration'] );
            // die("Could not use PHP SODIUM.  Please check your PHP.INI for SODIUM configuration" . $e );
            // $this->assertTrue(false);
        }
*/

        // Test database connection
        // Log::channel('stderr')->info('CheckConfig:', ['Checking db connection ...'] );
/*
        try {
            $p = DB::connection()->getPdo();
            Log::channel('stderr')->info('CheckConfig:', ['DB connection OK', $p] );
        } catch (\Exception $e) {
            Log::channel('stderr')->error('CheckConfig:', ['DB connection ERROR!', $e] );
            // $this->assertTrue(false);
            die($e);
        }
*/
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
*/        
/*
        Log::channel('stderr')->info('CheckConfig:', ['Creating table posts ...'] );
        if ( !Schema::hasTable('posts'))
        {
               Schema::create('posts', function (Blueprint $table) {
                $table->increments('id');
                $table->text('title');
                $table->text('title_enc'); // for test only
                $table->integer('author_id');
                $table->timestamps();
            });
            Log::channel('stderr')->info('CheckConfig:', ['table posts created'] );
        }
        else
        {
            Log::channel('stderr')->info('CheckConfig:', ['table posts already exits'] );
        }
*/

        // Log::channel('stderr')->info('CheckConfig:', ['Checking RainbowTableService'] );
/*
        try {
            $rtService = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexService();
            $value = random_int ( 1, 1000 );

            Log::channel('stderr')->info('CheckConfig:', ['Index create item (TEST,DEMO,' . $value .')'] );
            $o = $rtService->setRT('TEST','DEMO', $value);

            Log::channel('stderr')->info('CheckConfig:', [$o] );
            Log::channel('stderr')->info('CheckConfig:', ['Check database for table ....'] );

            Log::channel('stderr')->info('CheckConfig:', ['Read item (TEST,DEMO) ... '] );
            $v = $rtService->getRT('TEST','DEMO');
            Log::channel('stderr')->info('CheckConfig: values ...  ', [$v] );

        } catch (\Exception $e) {
            Log::channel('stderr')->error('CheckConfig:', ['ERROR', $e] );
            // $this->assertTrue(false);
            // die("ERRORE RainbowTableService re check previuos step!" . $e );
        }
*/
        // $this->assertTrue(true);


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