<?php

/**
 * FieldsEncryptedIndexQueryBuilder
 * Costruisce la query data la request
 * Le verifiche della request sono demandate al FieldsEncryptedIndexConfig
 * FieldsEncryptedIndexService ritorna i dati da indice
 * FieldsEncryptedIndexConfig verifica e carica le configurazioni 
 * 
 */

namespace Paulodiff\FieldsEncryptedIndex;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexEncrypter;
use Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexService;
use Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexConfig;
use Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexException;

class FieldsEncryptedIndexQueryBuilder {

    protected $model;
    public $enc_fields;
    protected $rtService;
    public $FEI_config;
    public $FEI_service;
    public $FEI_encrypter;
	public $SHORT_NAME = 'FEIQB:';

	// SHOT
	
    /**
     * EncryptableQueryBuilder constructor.
     * @param ConnectionInterface $connection
     * @param Encryptable $model
     */
    public function __construct()
    {
        Log::channel('stderr')->debug($this->SHORT_NAME . 'FieldsEncryptedIndexQueryBuilder __construct', [] );        
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

	// Costruisce la query per essere eseguita
	public function buildQuery(array $sqlRequest) 
	{


		Log::channel('stderr')->info($this->SHORT_NAME  . '[START]:#####################################################', [] );
		Log::channel('stderr')->info($this->SHORT_NAME  . ':### VERB ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
        $verbClause = $this->buildVerbClause($sqlRequest); 
        Log::channel('stderr')->info($this->SHORT_NAME  . ':### VERB SQL-> ##', [$verbClause] );

		$Response = [];

		if ($verbClause === "SELECT")
		{

			Log::channel('stderr')->info($this->SHORT_NAME  . 'FieldsEncryptedIndexQueryBuilder:### 1 FROM ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
			$fromTableClause = $this->buildFromTableClause($sqlRequest, " FROM "); 
			Log::channel('stderr')->info($this->SHORT_NAME  . 'FieldsEncryptedIndexQueryBuilder:### FROM TABLE SQL-> ##', [$fromTableClause] );

			// check fields and table name se esistono e di che tipo sono
			Log::channel('stderr')->info($this->SHORT_NAME  . 'FieldsEncryptedIndexQueryBuilder:### FIELDS ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );

			$fieldsClause = $this->buildFieldsClause($sqlRequest); 

			$Response['fiels2decrypt'] = $fieldsClause['fiels2decrypt'];

			Log::channel('stderr')->info($this->SHORT_NAME  . 'FieldsEncryptedIndexQueryBuilder:### FIELDS SQL ####', [$fieldsClause] );
			Log::channel('stderr')->info($this->SHORT_NAME  . 'FieldsEncryptedIndexQueryBuilder:### WHERE ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );

			if ( array_key_exists('where', $sqlRequest) ) 
			{
				try
				{
					Log::channel('stderr')->info($this->SHORT_NAME  . 'where clause:', [$sqlRequest] );
					Log::channel('stderr')->info($this->SHORT_NAME  . 'where clause:', [$sqlRequest['where'][0]] );
					$whereClause = " WHERE " . $this->buildWhereClause($sqlRequest['where'][0]);
					Log::channel('stderr')->info($this->SHORT_NAME  . 'FieldsEncryptedIndexQueryBuilder:### WHERE COND SQL-> ##', [$whereClause] );

				} catch (FieldsEncryptedIndexException $e) {
					Log::channel('stderr')->error('FieldsEncryptedIndexQueryBuilder:Exception:', [$e->getMessage()] );
					die();
				}
			} else {
				$whereClause = "";
			}

	
			Log::channel('stderr')->info($this->SHORT_NAME  . ':### JOIN ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
			$joinClause = $this->buildJoinClause($sqlRequest); 
			Log::channel('stderr')->info($this->SHORT_NAME  . ':### JOIN SQL-> ##', [$joinClause] );


			Log::channel('stderr')->info($this->SHORT_NAME  . ':### ORDER ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
			$orderClause = $this->buildOrderClause($sqlRequest); 
			Log::channel('stderr')->info($this->SHORT_NAME  . ':### ORDER SQL-> ##', [$orderClause] );


			// LIMIT TODO TODO TODO 

			$sqlStatement = $verbClause . " " . $fieldsClause['SQL'] . " " . $fromTableClause . " " . $joinClause . " " . $whereClause . " " . $orderClause;

			Log::channel('stderr')->info($this->SHORT_NAME  . ':FINAL STATEMENT:', [$sqlStatement ] );

		}

		elseif ($verbClause === "INSERT")
		{
			Log::channel('stderr')->info($this->SHORT_NAME  . ':### 2 FROM ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
			$fromTableClause = $this->buildFromTableClause($sqlRequest, " INTO "); 
			Log::channel('stderr')->info($this->SHORT_NAME  . ':### FROM TABLE SQL-> ##', [$fromTableClause] );


			Log::channel('stderr')->info($this->SHORT_NAME  . ':### INSERT CLAUSE ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
			$insertClause = $this->buildInsertClause($sqlRequest); 
			Log::channel('stderr')->info($this->SHORT_NAME  . ':### INSERT CLAUSE SQL-> ##', [$insertClause] );

			$sqlStatement = $verbClause . " " . " " . $fromTableClause . " " . $insertClause['SQL'] ;

			$Response['EncrypedIndexedFiels2Update'] = $insertClause['EncrypedIndexedFiels2Update'];
		}

		elseif ($verbClause === "REINDEX")
		{
			Log::channel('stderr')->info($this->SHORT_NAME  . ':### 3 FROM ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
			$fromTableClause = $this->buildFromTableClause($sqlRequest, ""); 
			Log::channel('stderr')->info($this->SHORT_NAME  . ':### FROM TABLE SQL-> ##', [$fromTableClause] );

			$Response['tableNameToReindex'] = $fromTableClause;

			$fieldsClause = $this->buildFieldsClause($sqlRequest); 

			// dd($fieldsClause);
			
			$Response['fiels2decrypt'] = $fieldsClause['fiels2decrypt'];

			// $Response['fiels2decrypt'] = $fieldsClause['fiels2decrypt'];
			Log::channel('stderr')->info($this->SHORT_NAME  . ':### FIELDS SQL ####', [$fieldsClause] );
			Log::channel('stderr')->info($this->SHORT_NAME  . ':### WHERE ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );


			// $sqlStatement = $verbClause . " " . " " . $fromTableClause . " " . $fieldsClause['SQL'] ;

			// dd($Response);

			$sqlStatement = $verbClause . " " . " " . $fromTableClause . " " . $fieldsClause['SQL'] ;

			// $Response['EncrypedIndexedToReindex'] = $insertClause['EncrypedIndexedToReindex'];
		}

		elseif ($verbClause === "UPDATE")
		{

			// // UPDATE `laravel`.`migrations` SET `migration`='Tom Sam Jhon q', `batch`='80253' WHERE  `id`=10;
			// Verificare se la query esegue più di un elemento
			// Cifrare la query
			// Elencare tutti i campi per aggiornare l'eventuale indice

			Log::channel('stderr')->info($this->SHORT_NAME  . ':### 1 UPDATE ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
			$fromTableClause = $this->buildFromTableClause($sqlRequest, " "); 
			Log::channel('stderr')->info($this->SHORT_NAME  . ':### FROM TABLE SQL-> ##', [$fromTableClause] );

			if ( array_key_exists('where', $sqlRequest) ) 
			{
				try
				{
					Log::channel('stderr')->info($this->SHORT_NAME  . ':where clause:', [$sqlRequest] );
					Log::channel('stderr')->info($this->SHORT_NAME  . ':where clause:', [$sqlRequest['where'][0]] );
					$whereClause = " WHERE " . $this->buildWhereClause($sqlRequest['where'][0]);
					Log::channel('stderr')->info($this->SHORT_NAME  . '# WHERE COND SQL #', [$whereClause] );

				} 
				catch (FieldsEncryptedIndexException $e) 
				{
					Log::channel('stderr')->error('FieldsEncryptedIndexQueryBuilder:Exception:', [$e->getMessage()] );
					die();
				}
			} 
			else 
			{
				throw new FieldsEncryptedIndexException("UPDATE where condition do not exists");
			}

			// Chech if query is on multiple rows via CONFIG

			$dataInfo = $this->FEI_config->storageCountRow($fromTableClause , $whereClause);

			if (  $dataInfo['rowCount'] !== 1)
			{
				Log::channel('stderr')->error('FieldsEncryptedIndexQueryBuilder:UPDATE SUPPORT 1 row only!:', [] );
				throw new FieldsEncryptedIndexException("UPDATE SUPPORT 1 row only!");
			} 
			
			// dd($dataInfo);

			$fieldsUpdateClause = $this->buildUpdateClause($sqlRequest); 

			$Response['EncrypedIndexedFiels2Update'] = $fieldsUpdateClause['EncrypedIndexedFiels2Update']; 
			$Response['rowId'] = $dataInfo['rowId'];

			$sqlStatement = $verbClause . " " .  $fromTableClause  . " " . $fieldsUpdateClause['SQL'] . " " .  $whereClause;

			// dd($sqlStatement);

			// dd($fieldsUpdateClause);

		}

		elseif ($verbClause === "CREATETABLE")
		{

	
			Log::channel('stderr')->info($this->SHORT_NAME  . ':### 1 CREATETABLE ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
		

			$tableName = $this->createGetTableName($sqlRequest);
			Log::channel('stderr')->info($this->SHORT_NAME  . ':### TABLE ##', [$tableName] );

		
			$configTable=[];
			$configTable['tableName'] = $sqlRequest['tableName'];
			$configTable['tableNameHashed'] = $this->FEI_encrypter->short_hash_sodium(
				$configTable['tableName'],
				$this->FEI_encrypter->keygen_short_hash_sodium()
			);
			$configTable['primaryKey'] = $sqlRequest['primaryKey'];
			$configTable['fields'] = $sqlRequest['fields'];

			// dd($configTable);

			// TODO Crea il file  .json 
			
			
			// $fks = [];
			foreach ($configTable['fields'] as $key=>$val) 
            {
				// $configTable['fields'][$item];
				

				$configTable['fields'][$key]['fieldNameHashed'] = $this->FEI_encrypter->short_hash_sodium(
					$configTable['fields'][$key]['fieldName'],
					$this->FEI_encrypter->keygen_short_hash_sodium()
				);

				if ( in_array($configTable['fields'][$key]['fieldType'], ["ENCRYPTED", "ENCRYPTED_INDEXED"]) )
				{

					$configTable['fields'][$key]['key'] = $this->FEI_encrypter->keygen_sodium();
					$configTable['fields'][$key]['nonce'] = $this->FEI_encrypter->noncegen_sodium();

				}
			}
		

			// dd($configTable);

			$options = [
				'force' => true
			];
			$this->FEI_config->saveConfig($configTable, $tableName, $options);
			

			// $SECconfigTable=[];
			// $SECconfigTable['tableName'] = $sqlRequest['tableName'];
			//$SECconfigTable['key'] = $this->FEI_encrypter->keygen_short_hash_sodium();
			//$SECconfigTable['fieldsKeys'] = $fks;

			// NON NECESSARIA TUTTI LE CONFIGURAZIONI dati e sicurezza sono nel .json
			// ***************************** $this->FEI_config->saveSecurityConfig($SECconfigTable, $tableName, $options);

			

			$createTableClause = $this->buildCreateTableClause($sqlRequest);

			// dd($createTableClause);
						

			$sqlStatement = $createTableClause;



		}


		else

		{

			Log::channel('stderr')->error('FieldsEncryptedIndexQueryBuilder:### VERB not defined! ####', ['@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@'] );
			die();

		}
		
		$Response['verbClause'] = $verbClause;
		$Response['sqlStatement']= $sqlStatement;
		
		return $Response;
	}

	function buildVerbClause(array $r) {


		Log::channel('stderr')->debug($this->SHORT_NAME  . 'buildVerbClause: :', [is_array($r)] );
		Log::channel('stderr')->debug($this->SHORT_NAME  . 'buildVerbClause: :', [array_key_exists('action', $r)] );

		if (    is_array($r) 
				&& array_key_exists('action', $r) 
				)
		{

			$verb = $r['action'];
			Log::channel('stderr')->debug($this->SHORT_NAME  . 'buildVerbClause: :', [$verb] );

			if (!in_array($verb, ["SELECT", "UPDATE", "INSERT", "REINDEX", "CREATETABLE"]))
			{
				Log::channel('stderr')->error('buildVerbClause: verb not valid!:', [$verb] );
				die();
			}
			
			return $verb;

		}
		else
		{
			Log::channel('stderr')->debug($this->SHORT_NAME  . 'buildVerbClause: return void - failded test!', [] );
			return "";
		}

	}

	function buildFromTableClause(array $r, $cmd) {

		Log::channel('stderr')->debug($this->SHORT_NAME  . 'buildFromTableClause:', [is_array($r)] );

		// se esiste tableName ritona altrimenti	
		

		if ( array_key_exists('tableName', $r) 	)
		{

			$tableNameHashed = $this->FEI_config->getHashedTableNameConfig($r['tableName']);
			$SQL = $cmd . $tableNameHashed;
			return $SQL;

		}


        if($r['tables'])
        {
			$SQL = "";

            foreach ($r['tables'] as $index => $item) 
            {
                Log::channel('stderr')->debug($this->SHORT_NAME  . 'buildFromTableClause:tname:', [$item] );
                
				// $tc  = $this->getTableConfig($item['tableName']);
				// solo per verificare l'esistenza della configurazione della tabella
			    // $tc  = $this->FEI_config->getTableConfig($item['tableName']);
                
				if ( $SQL == "") 
				{
					$tableNameHashed = $this->FEI_encrypter->short_hash_sodium($item['tableName'] . ".###TABLE_NAME###");
					$SQL = $cmd . $tableNameHashed;
				}
				else 
				{
					$tableNameHashed = $this->FEI_encrypter->short_hash_sodium($item['tableName'] . ".###TABLE_NAME###");
					$SQL = $SQL . " , " . $tableNameHashed;
				}
			
            }

        }

		return $SQL;


	}




	function buildFieldsClause(array $r) {

		Log::channel('stderr')->info($this->SHORT_NAME  . 'buildFieldsClause:', [is_array($r)] );

		$encryptedSelectFields = [];
        
        if($r['fields'])
        {
			$SQL = "";

            foreach ($r['fields'] as $index => $item) 
            {
                Log::channel('stderr')->info($this->SHORT_NAME  . 'buildFieldsClause:F_idx:', [$index] );
                Log::channel('stderr')->info($this->SHORT_NAME  . 'buildFieldsClause:F_name:', [$item] );

                // check fieldName in query in table config amd type
                $pieces = explode(".", $item['fieldName']);
                $tname = $pieces[0];
                $fname = $pieces[1];

                Log::channel('stderr')->info($this->SHORT_NAME  . 'buildFieldsClause:Search for 1', [$tname, $item['fieldName'] ] );

				// $fiedlType = $this->getFieldTypeDefinition($item['fieldName']);
				$fiedlType = $this->FEI_config->getFieldTypeDefinition($item['fieldName']);

				Log::channel('stderr')->info($this->SHORT_NAME  . 'buildFieldsClause:Search for 2', [$tname, $fname, $fiedlType ] );

				 
				if (in_array($fiedlType, ["ENCRYPTED", "ENCRYPTED_INDEXED"]))
				{
					$encryptedSelectFields[] = [
						"tableName" => $tname,
						"fieldName" => $fname,
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

		Log::channel('stderr')->debug($this->SHORT_NAME  . 'buildJoinClause: TODO MULTIPLE JOIN:', [$r] );

		if (!array_key_exists('join', $r)) return "";

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
                Log::channel('stderr')->info($this->SHORT_NAME  . 'buildJoinClause: joinTable:', [$item['joinTable']] );
                Log::channel('stderr')->info($this->SHORT_NAME  . 'buildJoinClause: onJoinSource:', [$item['onJoinSource']] );
                Log::channel('stderr')->info($this->SHORT_NAME  . 'buildJoinClause: onJoinDest:', [$item['onJoinDest']] );


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


		if (!array_key_exists('order', $r)) return "";

		Log::channel('stderr')->info($this->SHORT_NAME  . 'buildOrderClause: joinTable:', [is_array($r)] );
		Log::channel('stderr')->info($this->SHORT_NAME  . 'buildOrderClause: joinTable:', [array_key_exists('order', $r)] );
		Log::channel('stderr')->info($this->SHORT_NAME  . 'buildOrderClause: joinTable:', [array_key_exists('sortOrder', $r['order'][0])] );
		Log::channel('stderr')->info($this->SHORT_NAME  . 'buildOrderClause: joinTable:', [array_key_exists('fields', $r['order'][0])] );


		if (    is_array($r) 
				&& array_key_exists('order', $r) 
				&& array_key_exists('sortOrder', $r['order'][0])
				&& array_key_exists('fields', $r['order'][0])
				)
		{
			$ORDER_CLAUSE = "";

			$sortOrder = $r['order'][0]['sortOrder'];
			Log::channel('stderr')->info($this->SHORT_NAME  . 'buildOrderClause: joinTable:', [$sortOrder] );

			if (!in_array($sortOrder, ["ASC", "DESC"]))
			{
				Log::channel('stderr')->error('buildOrderClause: sort not valid!:', [$sortOrder] );
				die();
			}

			foreach ($r['order'][0]['fields'] as $index => $item) 
            {
                Log::channel('stderr')->info($this->SHORT_NAME  . 'buildOrderClause: fieldName:', [$item['fieldName']] );

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
			Log::channel('stderr')->debug($this->SHORT_NAME  . 'buildOrderClause: return void - failded test!', [] );
			return "";
		}

	}


    function buildWhereClause(array $elements) {

        // $branch = array();
    
        // echo "\nSTART buildTree----------------------- \n";
		Log::channel('stderr')->info($this->SHORT_NAME  . 'buildWhereClause:', ['START'] );
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
        Log::channel('stderr')->debug($this->SHORT_NAME  . 'getFieldClause:', [$o, $ft] );

        if (in_array($ft, ["LONG"])) 
        {
            return  " " . $o['fieldName'] . " " . $o['operator'] . " " . $o['fieldValue'] . " ";
        } 
		elseif (in_array($ft, ["STRING"])) 
        {
            return  " " . $o['fieldName'] . " " . $o['operator'] . " '" . $o['fieldValue'] . "' ";
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
				Log::channel('stderr')->debug($this->SHORT_NAME  . 'getFieldClause:', [$o] );
				// Log::channel('stderr')->debug($this->SHORT_NAME  . '[getFieldClause:', [FieldsEncryptedIndexEncrypter::encrypt($o['value'])] );
				// Log::channel('stderr')->debug($this->SHORT_NAME  . '[getFieldClause:', [FieldsEncryptedIndexEncrypter::encrypt($o['value'])] );

				// $this->FEI_encrypter = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexEncrypter();
						
				$value = $this->FEI_encrypter->encrypt_sodium($o);

				// $value = FieldsEncryptedIndexEncrypter::encrypt($o['value']);
				Log::channel('stderr')->debug($this->SHORT_NAME  . '[getFieldClause:', [$value]);
								

				return  " " . $o['fieldName'] . " " . $o['operator'] . " '" . $value . "' ";
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

				// get table name

				$pieces = explode(".", $o['fieldName']);
				$tname = $pieces[0];
				$fname = $pieces[1];
				
				Log::channel('stderr')->debug('VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV', [$o] );

				$r = $this->FEI_service->FEI_get( $tname, $fname, $o['fieldValue']);

				$pkId = $this->FEI_config->getTablePrimaryKey($tname);
				
				$idList = implode("," , $r);

				// dd($idList);

				if($idList === "")
				{
					$idList = "-1";
				}
				
				// Log::channel('stderr')->debug($this->SHORT_NAME  . 'getFieldClause:FEI_service', [$r] );
				

				return  " ( " . $tname ."." . $pkId . "  IN  (" . $idList. ") ) ";
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

		Log::channel('stderr')->info($this->SHORT_NAME  . 'buildInsertClause:', [is_array($r)] );
		// Log::channel('stderr')->info($this->SHORT_NAME  . 'buildInsertClause: joinTable:', [array_key_exists('order', $r)] );
		// Log::channel('stderr')->info($this->SHORT_NAME  . 'buildInsertClause: joinTable:', [array_key_exists('sortOrder', $r['order'][0])] );
		// Log::channel('stderr')->info($this->SHORT_NAME  . 'buildInsertClause: joinTable:', [array_key_exists('fields', $r['order'][0])] );

		if (    is_array($r) 
				&& array_key_exists('fields', $r) 
				)
		{
			$INSERT_CLAUSE_UP = "";
			$INSERT_CLAUSE_DN = "";
			$EncrypedIndexedFiels2Update = [];

			// $sortOrder = $r['order'][0]['sortOrder'];
			Log::channel('stderr')->info($this->SHORT_NAME  . 'buildInsertClause:', [$r['fields']] );

			// if (!in_array($sortOrder, ["ASC", "DESC"]))
			// {
			//	Log::channel('stderr')->error('buildOrderClause: sort not valid!:', [$sortOrder] );
			//	die();
			//}

			// $tableName = $this->buildFromTableClause($r, ''); // get table name from request
			// $tableNameHashed = $tableName;

			Log::channel('stderr')->debug($this->SHORT_NAME  . 'buildInsertClause: tableName:', [$r['tableName']] );

			$tableNameHashed = $this->FEI_config->getHashedTableNameConfig($r['tableName']);

			foreach ($r['fields'] as $index => $item) 
            {
                Log::channel('stderr')->info($this->SHORT_NAME  . 'buildInsertClause: fieldName:', [$item['fieldName']] );

				// check field's type return value only if exists
				// $ft = $this->getFieldTypeDefinition($item['fieldName']);
				$fc = $this->FEI_config->getFieldConfig($item['fieldName']);
				$ft = $fc['fieldType'];

				Log::channel('stderr')->info($this->SHORT_NAME  . 'buildInsertClause: fieldType to check:', [$ft] );

				$fieldNameHashed = $fc['fieldNameHashed'];

				// dd($fieldNameHashed);
				
				// ($INSERT_CLAUSE_UP === "") ? "pass" : "Fail";

				$INSERT_CLAUSE_UP = ($INSERT_CLAUSE_UP === "") ? $fieldNameHashed : $INSERT_CLAUSE_UP . "," . $fieldNameHashed ;


				if (in_array($ft, ["LONG"])) 
				{
					$INSERT_CLAUSE_DN = ($INSERT_CLAUSE_DN === "") ? $item['fieldValue'] : $INSERT_CLAUSE_DN . "," . $item['fieldValue'] ;
				}
				elseif (in_array($ft, ["STRING"])) 
				{
					$INSERT_CLAUSE_DN = ($INSERT_CLAUSE_DN === "") ? "'" . addslashes($item['fieldValue']) . "'" : $INSERT_CLAUSE_DN . ",'" . addslashes($item['fieldValue']) ."'" ;
				} 
				elseif (in_array($ft, ["ENCRYPTED"]))
				{

					$plainValue = $item['fieldValue'];

					Log::channel('stderr')->info($this->SHORT_NAME  . 'buildInsertClause: ENCRYPTED:', [ $plainValue ] );
					
					// $value = FieldsEncryptedIndexEncrypter::encrypt( $plainValue );

					$value = $this->FEI_encrypter->encrypt_sodium($item);

					// $value = FieldsEncryptedIndexEncrypter::encrypt($o['value']);
					Log::channel('stderr')->debug($this->SHORT_NAME  . '[getFieldClause:', [$value]);


					Log::channel('stderr')->info($this->SHORT_NAME  . 'buildInsertClause: ENCRYPTED:', [$value] );
					// Log::channel('stderr')->info($this->SHORT_NAME  . 'buildInsertClause: ENCRYPTED:', [FieldsEncryptedIndexEncrypter::encrypt( $plainValue )] );
					
					$INSERT_CLAUSE_DN = ($INSERT_CLAUSE_DN === "") ? "'" . $value . "'": $INSERT_CLAUSE_DN . ",'" . $value . "'";
				}
				elseif (in_array($ft, ["ENCRYPTED_INDEXED"]))
				{
					// $value = FieldsEncryptedIndexEncrypter::encrypt($item['fieldValue']);

					$value = $this->FEI_encrypter->encrypt_sodium($item);

					$INSERT_CLAUSE_DN = ($INSERT_CLAUSE_DN === "") ? "'" . $value . "'" : $INSERT_CLAUSE_DN . ",'" . $value ."'";
					$EncrypedIndexedFiels2Update[] = [
						"tableName" => $r['tableName'],
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

			$r = [
				"SQL" =>  " ( " . $INSERT_CLAUSE_UP . " ) VALUES ( " . $INSERT_CLAUSE_DN . " ) ",
				"EncrypedIndexedFiels2Update" => $EncrypedIndexedFiels2Update
			];

			Log::channel('stderr')->info($this->SHORT_NAME  . 'buildInsertClause: !RETURN!:', [$r] );


			dd($r);

			return $r;

		}
		else
		{
			Log::channel('stderr')->error('buildInsertClause: return void - failded test!', [] );
			return "";
		}

	}

	function buildUpdateClause(array $r) {

		//  UPDATE `laravel`.`migrations` SET `migration`='Tom Sam Jhon q', `batch`='80253' WHERE  `id`=10;

		Log::channel('stderr')->info($this->SHORT_NAME  . 'buildUpdateClause:', [is_array($r)] );
		// Log::channel('stderr')->info($this->SHORT_NAME  . 'buildInsertClause: joinTable:', [array_key_exists('order', $r)] );
		// Log::channel('stderr')->info($this->SHORT_NAME  . 'buildInsertClause: joinTable:', [array_key_exists('sortOrder', $r['order'][0])] );
		// Log::channel('stderr')->info($this->SHORT_NAME  . 'buildInsertClause: joinTable:', [array_key_exists('fields', $r['order'][0])] );

		if (  is_array($r) && array_key_exists('fields', $r) )
		{
			$UPDATE_CLAUSE = "";
			$EncrypedIndexedFiels2Update = [];

			// $sortOrder = $r['order'][0]['sortOrder'];
			Log::channel('stderr')->info($this->SHORT_NAME  . 'buildUpdateClause:', [$r['fields']] );

			// if (!in_array($sortOrder, ["ASC", "DESC"]))
			// {
			//	Log::channel('stderr')->error('buildOrderClause: sort not valid!:', [$sortOrder] );
			//	die();
			//}

			$tableName = $this->buildFromTableClause($r, ''); // get table name from request


			foreach ($r['fields'] as $index => $item) 
            {
                Log::channel('stderr')->info($this->SHORT_NAME  . 'buildUpdateClause: fieldName:', [$item['fieldName']] );

				// check field's type return value only if exists
				// $ft = $this->getFieldTypeDefinition($item['fieldName']);
				$ft = $this->FEI_config->getFieldTypeDefinition($item['fieldName']);


				Log::channel('stderr')->info($this->SHORT_NAME  . 'buildUpdateClause: fieldType to check:', [$ft] );

				
				// ($INSERT_CLAUSE_UP === "") ? "pass" : "Fail";
				$fn =  $item['fieldName'];

				$UPDATE_CLAUSE = ($UPDATE_CLAUSE === "") ? $fn : $UPDATE_CLAUSE . " , " . $fn ;


				if (in_array($ft, ["LONG"])) 
				{
					$UPDATE_CLAUSE = $UPDATE_CLAUSE . " = " . $item['fieldValue'] ;
				}
				elseif (in_array($ft, ["STRING"])) 
				{
					$UPDATE_CLAUSE = $UPDATE_CLAUSE . " = '" . addslashes($item['fieldValue']) . "'" ;
				} 
				elseif (in_array($ft, ["ENCRYPTED"]))
				{
					$plainValue = $item['fieldValue'];

					Log::channel('stderr')->info($this->SHORT_NAME  . 'buildInsertClause: ENCRYPTED:', [ $plainValue ] );
					
					// $value = FieldsEncryptedIndexEncrypter::encrypt( $plainValue );
					$value = $this->FEI_encrypter->encrypt_sodium($item);
					// $value = FieldsEncryptedIndexEncrypter::encrypt($o['value']);
					Log::channel('stderr')->debug($this->SHORT_NAME  . '[getFieldClause:', [$value]);
					Log::channel('stderr')->info($this->SHORT_NAME  . 'buildInsertClause: ENCRYPTED:', [$value] );
					// Log::channel('stderr')->info($this->SHORT_NAME  . 'buildInsertClause: ENCRYPTED:', [FieldsEncryptedIndexEncrypter::encrypt( $plainValue )] );
					
					$UPDATE_CLAUSE = $UPDATE_CLAUSE . " = '" . $value . "'" ;

				}
				elseif (in_array($ft, ["ENCRYPTED_INDEXED"]))
				{
					// $value = FieldsEncryptedIndexEncrypter::encrypt($item['fieldValue']);

					$value = $this->FEI_encrypter->encrypt_sodium($item);
					$UPDATE_CLAUSE = $UPDATE_CLAUSE . " = '" . $value . "'" ;

					$EncrypedIndexedFiels2Update[] = [
						"tableName" => $tableName,
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

			$r = [
				"SQL" =>  " SET  " .  $UPDATE_CLAUSE,
				"EncrypedIndexedFiels2Update" => $EncrypedIndexedFiels2Update
			];

			Log::channel('stderr')->info($this->SHORT_NAME  . 'buildInsertClause: !RETURN!:', [$r] );

			return $r;

		}
		else
		{
			Log::channel('stderr')->error('buildInsertClause: return void - failded test!', [] );
			return "";
		}

	}

	function buildCreateTableClause(array $r)
	{
		Log::channel('stderr')->debug($this->SHORT_NAME  . 'buildCreateTableClause:', [is_array($r)] );


		$CREATE_CLAUSE = " CREATE TABLE ";

		$tableNameHashed = $this->FEI_config->getHashedTableNameConfig($r['tableName']);
		$primaryKeyName = $this->FEI_config->getTablePrimaryKeyNameConfig($r['tableName']);

		$primaryKeySqlDefs = " INT(11) NOT NULL AUTO_INCREMENT ";

		Log::channel('stderr')->debug($this->SHORT_NAME  . 'buildCreateTableClause:', [$tableNameHashed] );
		Log::channel('stderr')->debug($this->SHORT_NAME  . 'buildCreateTableClause:', [$primaryKeyName] );


		$fieldClauses = [];

		foreach ($r['fields'] as $item) 
        {
            
			$fieldClause = [];
			$sqlFielddefs = "";

			$fieldNameWithTable = $r['tableName'] . "." . $item['fieldName'];
			$fieldNameHashed = $this->FEI_config->getHashedFieldNameConfig($fieldNameWithTable);
			
			Log::channel('stderr')->debug($this->SHORT_NAME  . 'buildCreateTableClause:', [$fieldNameWithTable] );
			Log::channel('stderr')->debug($this->SHORT_NAME  . 'buildCreateTableClause:', [$fieldNameHashed] );
			
			if (in_array($item['fieldType'], ["LONG"])) 
			{
				$sqlFielddefs = 'BIGINT(20) NULL DEFAULT NULL' ;
			}
			elseif (in_array($item['fieldType'], ["STRING", "ENCRYPTED", "ENCRYPTED_INDEXED"])) 
			{
				$sqlFielddefs = 'LONGTEXT NULL DEFAULT NULL' ;
			} 
			else
			{
				Log::channel('stderr')->error('buildCreateTableClause: fieldType NOT FOUND!', [$ft] );
				die();
			}
			
			$fieldClauses[] = $fieldNameHashed  . " " . $sqlFielddefs;

		}

		// dd($fieldClauses);

		$CREATE_CLAUSE = $CREATE_CLAUSE . " " . $tableNameHashed;
		$CREATE_CLAUSE = $CREATE_CLAUSE . " ( " . $primaryKeyName . " " . $primaryKeySqlDefs;

		foreach( $fieldClauses as $sqlField)
		{

			$CREATE_CLAUSE = $CREATE_CLAUSE . " , " . $sqlField;

		}

		$CREATE_CLAUSE = $CREATE_CLAUSE . ", PRIMARY KEY (" . $primaryKeyName . ")  ) ";

		// dd($CREATE_CLAUSE);

		return $CREATE_CLAUSE;

// TODO .keys e crea la tabella nel database

			// SQL CREATE TABLE

			/*
			CREATE TABLE `raccomandate_source` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`raccomandate_ts` DATETIME NULL DEFAULT NULL,
			`raccomandate_data_reg` DATETIME NOT NULL,
			`raccomandate_numero` VARCHAR(255) NOT NULL,
			`raccomandate_mittente` VARCHAR(255) NOT NULL,
			`raccomandate_note` VARCHAR(255) NULL DEFAULT NULL,
			`raccomandate_operatore` VARCHAR(255) NOT NULL,
			`createdAt` DATETIME NOT NULL,
			`updatedAt` DATETIME NOT NULL,
			`deletedAt` DATETIME NULL DEFAULT NULL,
			`raccomandate_destinatario_codice` INT(11) NULL DEFAULT NULL,
			`80f112f9a800cfad18f1f68af66b2fb89fbe2f548547a61cc1fef4afc1d48c1c` INT(11) NULL DEFAULT NULL,
			INDEX `Indice 1` (`raccomandate_mittente`),
			INDEX `id` (`id`),
			INDEX `Indice 3` (`raccomandate_data_reg`)

			CREATE TABLE `migrations` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`migration` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`batch` INT(11) NULL DEFAULT NULL,
	`description` LONGTEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`description_plain` LONGTEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`name` LONGTEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`name_plain` LONGTEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`surname` LONGTEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`surname_plain` LONGTEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`ts` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
)

			*/



		// dd($r);

	}




	
	/*
	
	
		CREATE TABLE
	
	
	*/

	// ritorna il nome della tabella plain and hash
	function createGetTableName(array $r) 
	{
		if (  is_array($r) && array_key_exists('tableName', $r) )
		{
			return  $r['tableName'];
		}
		else 
		{
			Log::channel('stderr')->error('createGetTableName: CREATE tableName not valid!', [] );
			throw new FieldsEncryptedIndexException("CREATE tableName not valid");			
		}
	}




}