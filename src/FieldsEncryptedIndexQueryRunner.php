<?php

/**
 * FieldsEncryptedIndexQueryRunner
 * Esegue opportunamente la query
 * - tiene conto della connession
 * - degli indici
 * - degli ordinamenti
 * - della cifratura
 * 
 */

namespace Paulodiff\FieldsEncryptedIndex;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexEncrypter;
use Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexService;
use Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexConfig;
use Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexException;

use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;

class FieldsEncryptedIndexQueryRunner {

    protected $model;
    public $enc_fields;
    protected $rtService;
    public $FEI_config;
    public $FEI_service;

	
    /**
     * EncryptableQueryBuilder constructor.
     * @param ConnectionInterface $connection
     * @param Encryptable $model
     */
    public function __construct()
    {
        Log::channel('stderr')->debug('FieldsEncryptedIndexQueryRunner __construct', [] );        
		// $this->checkConfig();
		$this->FEI_config = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexConfig();
		$this->FEI_service = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexService();
		$this->FEI_encrypter = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexEncrypter();
		// $this->FEI_config->checkConfig();
    }
    /*
    public function __construct(ConnectionInterface $connection, $model)
    {
        parent::__construct($connection, $connection->getQueryGrammar(), $connection->getPostProcessor());
        $this->model = $model;
    }
    */

	// Esegue la query ... tenendo conto di tutta la configurazione

	public function runQuery(array $q) 
	{

		Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:runQuery', [$q] );

		$verbClause = $q['verbClause'];

		if ($verbClause === "SELECT")
		{
			// get connection

			Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:runQuery:SELECT', [$q] );

			$sqlStatement = $q['sqlStatement'];

			Log::channel('stderr')->info('@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@', [] );
			Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:runQuery:EXEC', [$sqlStatement] );
			Log::channel('stderr')->info('@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@', [] );

			// $results = DB::select( DB::raw("SELECT * FROM some_table WHERE some_col = :somevariable"), array(  'somevariable' => $someVariable,

			$rs = DB::select( DB::raw($sqlStatement) );
	
			Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:runQuery:DATA', [$rs] );

			/*
			foreach($rs as $item) 
			{
				// Log::channel('stderr')->info('-', [$item] );
				Log::channel('stderr')->info('-', [$item->id, $item->migration] );
			}
			*/

			// se vi sono dati da decifrare
			
			if ( array_key_exists('fiels2decrypt', $q) ) 
			{
				
				$toDecrypt = $q['fiels2decrypt'];

				Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:runQuery:TO_DECRYPT', [$toDecrypt] );

				foreach($rs as $item) 
				{
					// Log::channel('stderr')->info('-', [$item] );
					Log::channel('stderr')->info('-', [$item] );

					foreach( $toDecrypt as $fn)
					{

						// Log::channel('stderr')->info(' ### ', [$fn] );
						// Log::channel('stderr')->info(' ### ', [$fn['fieldName']] );
						// $object->{'$t'};

						// dd($fn);

						$v = $item->{$fn['fieldName']};
						Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:runQuery:@@CHECK@-U-@', [$v] );

						if(is_null($v)) {
							Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:runQuery:@@CHECK@-D1@', [$v] );
						} else {
							
							$s = [
								"fieldName" => $fn['tableName'] . "." . $fn['fieldName'],
								"fieldValue" => $v
							];

							$v2 = $this->FEI_encrypter->decrypt_sodium($s);	
							$item->{$fn['fieldName']} = $v2;
							Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:runQuery:@@CHECK@-D2@', [$v2] );
						}

						

						/*
						if(isNull($v) || isEmpty($v)) {

						} else {
							$v2 = FieldsEncryptedIndexEncrypter::decrypt($v);	
							$item->{$fn['fieldName']} = $v2;
						}
						*/

						// Log::channel('stderr')->info(' ### ', [$v] );
						// $v2 = FieldsEncryptedIndexEncrypter::decrypt($v);
						// Log::channel('stderr')->info(' ### ', [$v2] );

						// $item->{$fn['fieldName']} = $v2;

					}

				}


			}  


			Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:runQuery:TO_ORDER', [$toDecrypt] );
		

			// order values if encrypted


			Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:runQuery:TO_LIMIT', [$toDecrypt] );

			// limit

			return $rs;

		}



		elseif ($verbClause === "INSERT") 
		{
			Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:runQuery:INSERT', [$q] );
			$sqlStatement = $q['sqlStatement'];

			Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:runQuery:EXEC', [$sqlStatement] );

			DB::statement($sqlStatement);

			// Last Inserted Ids ...
			// https://www.larashout.com/laravel-8-get-last-id-of-an-inserted-model

			$lastInsertId = DB::getPdo()->lastInsertId();

			Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:runQuery:ID', [$lastInsertId] );

			// INSERIMENTO DI TUTTE LE CHIAVI SUL DATABASE
			

			// CHECK ID Long and > 0

			$this->checkLastInsertedId($lastInsertId);


			if (array_key_exists('EncrypedIndexedFiels2Update', $q))
			{

				Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:runQuery:UPDATE INDEK KEYS ', [] );
				
				$EncrypedIndexedFiels2Update = $q['EncrypedIndexedFiels2Update'];

				foreach ($EncrypedIndexedFiels2Update  as $item ) 
				{
	
					Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:runQuery:UPDATE INDEX ', [$item] );
					$this->FEI_service->FEI_SET($item['tableName'], $item['fieldName'],  $item['fieldValue'], $lastInsertId);
	
				}
				


			}


			

			Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:runQuery:INSERT', ['---OK---'] );

		}

		else
		{
			Log::channel('stderr')->error('FieldsEncryptedIndexQueryRunner verbClause invalid!', [$verbClause] ); 
			die();

		}

		


		// "SELECT" recuperare i dati e decodifica ... order by ecc.

		// "UPDATE " ...

		// "INSERT" .. inserire e aggiornare gli indici ...




	}


	// Costruisce la query per essere eseguita
	public function buildQuery(array $sqlRequest) 
	{


		Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:#############', ['----------------------------------------------------------------'] );
		Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:### VERB ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
        $verbClause = $this->buildVerbClause($sqlRequest); 
        Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:### VERB SQL-> ##', [$verbClause] );

		$Response = [];

		if ($verbClause === "SELECT")
		{



			Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:### 1 FROM ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
			$fromTableClause = $this->buildFromTableClause($sqlRequest, " FROM "); 
			Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:### FROM TABLE SQL-> ##', [$fromTableClause] );

			// check fields and table name se esistono e di che tipo sono
			Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:### FIELDS ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );

			$fieldsClause = $this->buildFieldsClause($sqlRequest); 

			Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:### FIELDS SQL ####', [$fieldsClause] );
			Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:### WHERE ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );

			try
			{
				$whereClause = $this->buildWhereClause($sqlRequest['where'][0]);
				Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:### WHERE COND SQL-> ##', [$whereClause] );

			} 
			catch (FieldsEncryptedIndexException $e) {
				Log::channel('stderr')->error('FieldsEncryptedIndexQueryRunner:Exception:', [$e->getMessage()] );
				die();
			}

	
			Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:### JOIN ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
			$joinClause = $this->buildJoinClause($sqlRequest); 
			Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:### JOIN SQL-> ##', [$joinClause] );


			Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:### ORDER ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
			$orderClause = $this->buildOrderClause($sqlRequest); 
			Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:### ORDER SQL-> ##', [$orderClause] );

			$sqlStatement = $verbClause . " " . $fieldsClause['SQL'] . " " . $fromTableClause . " " . $joinClause . " " . $whereClause . " " . $orderClause;

			Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:FINAL STATEMENT:', [$sqlStatement ] );

		}

		else if ($verbClause === "INSERT")
		{
			Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:### 2 FROM ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
			$fromTableClause = $this->buildFromTableClause($sqlRequest, " INTO "); 
			Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:### FROM TABLE SQL-> ##', [$fromTableClause] );


			Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:### INSERT CLAUSE ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
			$insertClause = $this->buildInsertClause($sqlRequest); 
			Log::channel('stderr')->info('FieldsEncryptedIndexQueryRunner:### INSERT CLAUSE SQL-> ##', [$insertClause] );

			$sqlStatement = $verbClause . " " . " " . $fromTableClause . " " . $insertClause['SQL'] ;

			$Response['EncrypedIndexedFiels2Update'] = $insertClause['EncrypedIndexedFiels2Update'];
		}

		else

		{

			Log::channel('stderr')->error('FieldsEncryptedIndexQueryRunner:### VERB not defined! ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
			die();

		}
		
		$Response['verbClause'] = $verbClause;
		$Response['sqlStatement']= $sqlStatement;
		
		return $Response;
	}

	function checkLastInsertedId($v) 
	{
		if (is_numeric($v) && $v > 0) 
		{
			return true;
		} else {
			throw new FieldsEncryptedIndexException('FieldsEncryptedIndexQueryRunner:LastInsertedId NOT FOUND');
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

	function buildFromTableClause(array $r, $cmd) {

		Log::channel('stderr')->debug('buildFromTableClause:', [is_array($r)] );

		
        if($r['tables'])
        {
			$SQL = "";

            foreach ($r['tables'] as $index => $item) 
            {
                Log::channel('stderr')->debug('buildFromTableClause:tname:', [$item] );
                
				// $tc  = $this->getTableConfig($item['tableName']);
				$tc  = $this->FEI_config->getTableConfig($item['tableName']);
                
				if ( $SQL == "") 
				{
					$SQL = $cmd . $item['tableName'];
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

				// $fiedlType = $this->getFieldTypeDefinition($item['fieldName']);
				$fiedlType = $this->FEI_config->getFieldTypeDefinition($item['fieldName']);

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
				// $tc  = $this->getTableConfig($item['joinTable']);
				$tc  = $this->FEI_config->getTableConfig($item['joinTable']);
				// $ft1 = $this->getFieldTypeDefinition($item['onJoinSource']);
				$ft1 = $this->FEI_config->getFieldTypeDefinition($item['onJoinSource']);
				// $ft2 = $this->getFieldTypeDefinition($item['onJoinDest']);
				$ft2 = $this->FEI_config->getFieldTypeDefinition($item['onJoinDest']);


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
				// $ft = $this->getFieldTypeDefinition($item['fieldName']);
				$ft = $this->FEI_config->getFieldTypeDefinition($item['fieldName']);

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

	function getFieldClause($o)
    {
        
        $ft = $this->FEI_config->getFieldTypeDefinition($o['fieldName']);
        Log::channel('stderr')->debug('getFieldClause:', [$o, $ft] );

        if (in_array($ft, ["LONG", "STRING"])) 
        {
            return  " " . $o['fieldName'] . " " . $o['operator'] . " " . $o['value'] . " ";
        } 
        elseif (in_array($ft, ["ENCRYPTED"]))
        {
			// only = operator is supporte
			if ($o['operator'] !== "=") 
			{
				throw new FieldsEncryptedIndexException('ENCRYPTED support only = operator not ' . $o['operator'] . " fn=" . $o['fieldName']);
			}
			else 
			{
				$value = FieldsEncryptedIndexEncrypter::encrypt($o['value']);

				return  " " . $o['fieldName'] . " " . $o['operator'] . $value . " ";
			}
            
        }
        elseif (in_array($ft, ["ENCRYPTED_INDEXED"]))
        {
			// only LIKE operator is supported
			if ($o['operator'] !== "LIKE") 
			{
				throw new FieldsEncryptedIndexException('ENCRYPTED_INDEXED support only LIKE operator not ' . $o['operator'] . " fn=" . $o['fieldName']);
			}
			else 
			{
				// Search value in FEI_q
				// $tag = $tName . ":" .  $o['fieldName'];

				$r = $this->FEI_service->getRT( $o['fieldName'],  $o['value']);

				Log::channel('stderr')->debug('getFieldClause:FEI_service', [$r] );

				return  " { " . $o['fieldName'] . " !-ENC_INDEX-! IN VALUES (AAAAA,BBBBB) } ";
			}
           
        }
        else
        {
            Log::channel('stderr')->error('fieldType NOT FOUND!', [$ft] );
            die();
        }

    }


	function buildInsertClause(array $r) {

		// INSERT INTO `laravel`.`migrations` (`migration`) VALUES ('qqqqqqqqqq');

		Log::channel('stderr')->info('buildInsertClause:', [is_array($r)] );
		// Log::channel('stderr')->info('buildInsertClause: joinTable:', [array_key_exists('order', $r)] );
		// Log::channel('stderr')->info('buildInsertClause: joinTable:', [array_key_exists('sortOrder', $r['order'][0])] );
		// Log::channel('stderr')->info('buildInsertClause: joinTable:', [array_key_exists('fields', $r['order'][0])] );

		if (    is_array($r) 
				&& array_key_exists('data', $r) 
				)
		{
			$INSERT_CLAUSE_UP = "";
			$INSERT_CLAUSE_DN = "";

			// $sortOrder = $r['order'][0]['sortOrder'];
			Log::channel('stderr')->info('buildInsertClause:', [$r['data']] );

			// if (!in_array($sortOrder, ["ASC", "DESC"]))
			// {
			//	Log::channel('stderr')->error('buildOrderClause: sort not valid!:', [$sortOrder] );
			//	die();
			//}

			foreach ($r['data'] as $index => $item) 
            {
                Log::channel('stderr')->info('buildInsertClause: fieldName:', [$item['fieldName']] );

				// check field's type return value only if exists
				// $ft = $this->getFieldTypeDefinition($item['fieldName']);
				$ft = $this->FEI_config->getFieldTypeDefinition($item['fieldName']);


				Log::channel('stderr')->info('buildInsertClause: fieldType to check:', [$ft] );

				$EncrypedIndexedFiels2Update = [];

				// ($INSERT_CLAUSE_UP === "") ? "pass" : "Fail";

				$INSERT_CLAUSE_UP = ($INSERT_CLAUSE_UP === "") ? $item['fieldName'] : $INSERT_CLAUSE_UP . "," . $item['fieldName'] ;


				if (in_array($ft, ["LONG"])) 
				{
					$INSERT_CLAUSE_DN = ($INSERT_CLAUSE_DN === "") ? $item['fieldValue'] : $INSERT_CLAUSE_DN . "," . $item['fieldValue'] ;
				}
				elseif (in_array($ft, ["STRING"])) 
				{
					$INSERT_CLAUSE_DN = ($INSERT_CLAUSE_DN === "") ? $item['fieldValue'] : $INSERT_CLAUSE_DN . ",'" . $item['fieldValue'] ."'" ;
				} 
				elseif (in_array($ft, ["ENCRYPTED"]))
				{
					$value = FieldsEncryptedIndexEncrypter::encrypt($item['fieldValue']);
					$INSERT_CLAUSE_DN = ($INSERT_CLAUSE_DN === "") ? $item['fieldName'] : $INSERT_CLAUSE_DN . ",'" . $value . "'";
				}
				elseif (in_array($ft, ["ENCRYPTED_INDEXED"]))
				{
					$value = FieldsEncryptedIndexEncrypter::encrypt($item['fieldValue']);
					$INSERT_CLAUSE_DN = ($INSERT_CLAUSE_DN === "") ? $item['fieldName'] : $INSERT_CLAUSE_DN . "," . $value ."'";
					$EncrypedIndexedFiels2Update[] = [
						"fieldName" => $item['fieldName'],
						"fieldValue" => $item['fieldValue'],
					];
				}
				else
				{
					Log::channel('stderr')->error('buildInsertClause: fieldType NOT FOUND!', [$ft] );
					die();
				}

				
			}

			return array(
				"SQL" =>  " ( " . $INSERT_CLAUSE_UP . " ) VALUES ( " . $INSERT_CLAUSE_DN . " ) ",
				"EncrypedIndexedFiels2Update" => $EncrypedIndexedFiels2Update
			);

		}
		else
		{
			Log::channel('stderr')->error('buildInsertClause: return void - failded test!', [] );
			return "";
		}

	}



/*

	// test config
	public function checkConfig()
	{
		Log::channel('stderr')->debug('FieldsEncryptedIndexQueryRunner:checkConfig', [ config('FieldsEncryptedIndex.configFolder') ] );    
	}


	public function getConfigFileName($tn)
	{
		return config('FieldsEncryptedIndex.configFolder') . $tn . ".json";
	}

	public function existsConfigFileName($fn)
	{
		Log::channel('stderr')->debug('FieldsEncryptedIndexQueryRunner:existsConfigFileName', [ $fn ] );    
		return file_exists($fn);
	}

	public function loadConfig($tn)
	{
		Log::channel('stderr')->debug('FieldsEncryptedIndexConfig:loadConfig', [ $tn ] );    
		$cfn = $this->getConfigFileName($tn);

		if ($this->existsConfigFileName($cfn))
		{
			$jsonTableConfig = file_get_contents($cfn);
			$tableArrayConfig = json_decode($jsonTableConfig, true);
			$Validator = Validator::make($tableArrayConfig, $this->tableRules);
			if ($Validator->fails()) {
				Log::channel('stderr')->error('Table config check error!', [$Validator->errors()] );
			}
			return $tableArrayConfig;
		}
		else
		{
			Log::channel('stderr')->error('FieldsEncryptedIndexConfig:loadConfig ERROR - Config file does not exists!', [ $cfn] );    
			die('');
		}
	}


	// load config from file in memory
	public function getTableConfig($tn)
    {
        Log::channel('stderr')->debug('getTableConfig:', [$tn] );

		$gct = $this->loadConfig($tn);
	
		Log::channel('stderr')->debug('getTableConfig:', [$gct] );
	
        return $gct;
    }

    public function getFieldTypeDefinition($fn)
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

    public function getFieldClause($o)
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


	public function loadFakeRequestAndValidate($tn)
	{
		Log::channel('stderr')->debug('FieldsEncryptedIndexConfig:loadFakeRequest', [ $tn ] );    
		
		$cfn = $this->getConfigFileName($tn);

		if ($this->existsConfigFileName($cfn))
		{
			$jsonTableConfig = file_get_contents($cfn);
			$tableArrayConfig = json_decode($jsonTableConfig, true);
			$Validator = Validator::make($tableArrayConfig, $this->sqlRequestRules);
			if ($Validator->fails()) {
				Log::channel('stderr')->error('Fake request config check error!', [$Validator->errors()] );
			}
			return $tableArrayConfig;
		}
		else
		{
			Log::channel('stderr')->error('FieldsEncryptedIndexConfig:loadFakeRequestAndValidate ERROR - Config file does not exists!', [ $cfn] );    
			die('');
		}
	}

	*/













    /**
     * @param array|\Closure|string $column
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return Builder
     * @throws \Exception
     *
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        Log::channel('stderr')->debug('FieldsEncryptedIndexQueryRunner:>>>DATI>>>>', [$column, $operator, $value, $boolean] );
        Log::channel('stderr')->debug('FieldsEncryptedIndexQueryRunner:>>>RAINBOW CONFIG>>>>', [$this->enc_fields] );

        // controllo se il campo è in configurazione e di che tipo

        if(!is_string($column)) 
        {
            Log::channel('stderr')->debug('FieldsEncryptedIndexQueryRunner:>>>>>>>>> NO STRING returm immediatly SIMPLE ----->', [$column, $operator] );
            return parent::where($column, $operator, $value, $boolean);
        }

        // check type

        $tName = $this->enc_fields['table']['tableName']; 
        $primaryKey = $this->enc_fields['table']['primaryKey'];  
        $fName = ""; $fType = "";
        foreach ($this->enc_fields['fields'] as $key => $val) 
        {
            // print_r($key);
            // print_r($val);
            if($val['fName'] == $column)
            {
                $fName = $column; $fType = $val['fType'];
                Log::channel('stderr')->debug('FieldsEncryptedIndexQueryRunner:>>> RAINBOW Field found in enc config!->', [$fName, $fType] );
            }
        }

        // se il campo deve utilizzare una RainbowTable
        if ( is_string($column) && ($operator == 'LIKE') && ($fName !== "") && ($fType == 'ENCRYPTED_FULL_TEXT') )
        {
            Log::channel('stderr')->debug('FieldsEncryptedIndexQueryRunner:>>> RAINBOW Table --GO! ENCRYPTED_FULL_TEXT ->', [$column, $operator, $tName, $primaryKey, $fType] );
            // accesso alla rainbow table per ottenere i valori da mettere nella query tramite ServiceProvider
            $tag = $tName . ":" . $column;
            $r = $this->rtService->getRT($tag, $value);
            Log::channel('stderr')->debug('FieldsEncryptedIndexQueryRunner:>>> RAINBOW Table --DATA!->', [$tag, $r] );
            return self::whereIn( $primaryKey , $r );
            // return self::whereRaw("CONVERT(AES_DECRYPT(FROM_BASE64(`{$filter->field}`), '{$salt}') USING utf8mb4) {$filter->operation} ? ", [$filter->value]);
        }
        elseif ( is_string($column) && ($fName !== "") && ($fType == 'ENCRYPTED') )
        {
            Log::channel('stderr')->debug('FieldsEncryptedIndexQueryRunner:>>>ENCRYPTED ----->', [$column, $operator, $value] );
            $operator = FieldsEncryptedIndexEncrypter::encrypt($operator);
            Log::channel('stderr')->debug('FieldsEncryptedIndexQueryRunner:>>>ENCRYPTED ----->', [$column, $operator, $value] );
            return parent::where($column, $operator, $value, $boolean);
        }
        else
        // il campo può essere cifrato o meno ....
        {
            Log::channel('stderr')->debug('FieldsEncryptedIndexQueryRunner:>>>>>>>>> SIMPLE ----->', [$column, $operator] );
            return parent::where($column, $operator, $value, $boolean);
        }

            
    }

    public function likeEncrypted($param1, $param2, $param3 = null)
    {
      $filter            = new \stdClass();
      $filter->field     = $param1;
      $filter->operation = isset($param3) ? $param2 : '=';
      $filter->value     = isset($param3) ? $param3 : $param2;

      // $salt = substr(hash('sha256', config('laravelDatabaseEncryption.encrypt_key')), 0, 16);

      return self::whereRaw("CONVERT(AES_DECRYPT(FROM_BASE64(`{$filter->field}`), '{$salt}') USING utf8mb4) {$filter->operation} ? ", [$filter->value]);
    }

	*/
}