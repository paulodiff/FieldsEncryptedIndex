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

			if (!in_array($verb, ["SELECT", "UPDATE", "INSERT", "REINDEX"]))
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

		
        if($r['tables'])
        {
			$SQL = "";

            foreach ($r['tables'] as $index => $item) 
            {
                Log::channel('stderr')->debug($this->SHORT_NAME  . 'buildFromTableClause:tname:', [$item] );
                
				// $tc  = $this->getTableConfig($item['tableName']);
				// solo per verificare l'esistenza della configurazione della tabella
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

			$tableName = $this->buildFromTableClause($r, ''); // get table name from request


			foreach ($r['fields'] as $index => $item) 
            {
                Log::channel('stderr')->info($this->SHORT_NAME  . 'buildInsertClause: fieldName:', [$item['fieldName']] );

				// check field's type return value only if exists
				// $ft = $this->getFieldTypeDefinition($item['fieldName']);
				$ft = $this->FEI_config->getFieldTypeDefinition($item['fieldName']);


				Log::channel('stderr')->info($this->SHORT_NAME  . 'buildInsertClause: fieldType to check:', [$ft] );

				
				// ($INSERT_CLAUSE_UP === "") ? "pass" : "Fail";

				$INSERT_CLAUSE_UP = ($INSERT_CLAUSE_UP === "") ? $item['fieldName'] : $INSERT_CLAUSE_UP . "," . $item['fieldName'] ;


				if (in_array($ft, ["LONG"])) 
				{
					$INSERT_CLAUSE_DN = ($INSERT_CLAUSE_DN === "") ? $item['fieldValue'] : $INSERT_CLAUSE_DN . "," . $item['fieldValue'] ;
				}
				elseif (in_array($ft, ["STRING"])) 
				{
					$INSERT_CLAUSE_DN = ($INSERT_CLAUSE_DN === "") ? "'" . $item['fieldValue'] . "'" : $INSERT_CLAUSE_DN . ",'" . addslashes($item['fieldValue']) ."'" ;
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
				"SQL" =>  " ( " . $INSERT_CLAUSE_UP . " ) VALUES ( " . $INSERT_CLAUSE_DN . " ) ",
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

/*

	// test config
	public function checkConfig()
	{
		Log::channel('stderr')->debug($this->SHORT_NAME  . 'FieldsEncryptedIndexQueryBuilder:checkConfig', [ config('FieldsEncryptedIndex.configFolder') ] );    
	}


	public function getConfigFileName($tn)
	{
		return config('FieldsEncryptedIndex.configFolder') . $tn . ".json";
	}

	public function existsConfigFileName($fn)
	{
		Log::channel('stderr')->debug($this->SHORT_NAME  . 'FieldsEncryptedIndexQueryBuilder:existsConfigFileName', [ $fn ] );    
		return file_exists($fn);
	}

	public function loadConfig($tn)
	{
		Log::channel('stderr')->debug($this->SHORT_NAME  . 'FieldsEncryptedIndexConfig:loadConfig', [ $tn ] );    
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
        Log::channel('stderr')->debug($this->SHORT_NAME  . 'getTableConfig:', [$tn] );

		$gct = $this->loadConfig($tn);
	
		Log::channel('stderr')->debug($this->SHORT_NAME  . 'getTableConfig:', [$gct] );
	
        return $gct;
    }

    public function getFieldTypeDefinition($fn)
    {
        // Log::channel('stderr')->info($this->SHORT_NAME  . 'getFieldTypeDefinition:', [$fn] );
        // check fieldName in query in table config amd type
        $pieces = explode(".", $fn);
        $tname = $pieces[0];
        $fname = $pieces[1];
        
        Log::channel('stderr')->debug($this->SHORT_NAME  . 'getFieldTypeDefinition:', [$fn, $tname, $fname] );

        $gc = $this->getTableConfig($tname);

		Log::channel('stderr')->debug($this->SHORT_NAME  . 'getFieldTypeDefinition:', [$gc] );

        if ( array_search($fname, array_column($gc['fields'], 'fieldName') ) === false ) 
        {
            Log::channel('stderr')->debug($this->SHORT_NAME  . 'getFieldTypeDefinition:NOT FOUND!', [$tname, $fname, array_search($fname, array_column($gc['fields'], 'fieldName') )] );
            die();
        } 
        else 
        {
            $key = array_search($fname, array_column($gc['fields'], 'fieldName') );
            // Log::channel('stderr')->info($this->SHORT_NAME  . 'FOUND!', [$tname, $fname, array_search($fname, array_column($GLOBAL_TABLE_CONFIG[$tname]['fields'], 'fieldName') )] );

			// Log::channel('stderr')->info($this->SHORT_NAME  . 'getFieldTypeDefinition:return ', [$key] );

            $fiedlType = $gc['fields'][$key]['fieldType'];
            // Log::channel('stderr')->info($this->SHORT_NAME  . 'FOUND!', [$tname, $fname, $GLOBAL_TABLE_CONFIG[$tname]['fields'][$key]['fieldType']] );

			// Log::channel('stderr')->info($this->SHORT_NAME  . 'getFieldTypeDefinition:return ', [$fiedlType] );

            return $fiedlType;
        }
    }

    public function getFieldClause($o)
    {
        
        $ft = $this->getFieldTypeDefinition($o['fieldName']);
        Log::channel('stderr')->debug($this->SHORT_NAME  . 'getFieldClause:', [$ft] );

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
		Log::channel('stderr')->debug($this->SHORT_NAME  . 'FieldsEncryptedIndexConfig:loadFakeRequest', [ $tn ] );    
		
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
        Log::channel('stderr')->debug($this->SHORT_NAME  . 'FieldsEncryptedIndexQueryBuilder:>>>DATI>>>>', [$column, $operator, $value, $boolean] );
        Log::channel('stderr')->debug($this->SHORT_NAME  . 'FieldsEncryptedIndexQueryBuilder:>>>RAINBOW CONFIG>>>>', [$this->enc_fields] );

        // controllo se il campo è in configurazione e di che tipo

        if(!is_string($column)) 
        {
            Log::channel('stderr')->debug($this->SHORT_NAME  . 'FieldsEncryptedIndexQueryBuilder:>>>>>>>>> NO STRING returm immediatly SIMPLE ----->', [$column, $operator] );
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
                Log::channel('stderr')->debug($this->SHORT_NAME  . 'FieldsEncryptedIndexQueryBuilder:>>> RAINBOW Field found in enc config!->', [$fName, $fType] );
            }
        }

        // se il campo deve utilizzare una RainbowTable
        if ( is_string($column) && ($operator == 'LIKE') && ($fName !== "") && ($fType == 'ENCRYPTED_FULL_TEXT') )
        {
            Log::channel('stderr')->debug($this->SHORT_NAME  . 'FieldsEncryptedIndexQueryBuilder:>>> RAINBOW Table --GO! ENCRYPTED_FULL_TEXT ->', [$column, $operator, $tName, $primaryKey, $fType] );
            // accesso alla rainbow table per ottenere i valori da mettere nella query tramite ServiceProvider
            $tag = $tName . ":" . $column;
            $r = $this->rtService->getRT($tag, $value);
            Log::channel('stderr')->debug($this->SHORT_NAME  . 'FieldsEncryptedIndexQueryBuilder:>>> RAINBOW Table --DATA!->', [$tag, $r] );
            return self::whereIn( $primaryKey , $r );
            // return self::whereRaw("CONVERT(AES_DECRYPT(FROM_BASE64(`{$filter->field}`), '{$salt}') USING utf8mb4) {$filter->operation} ? ", [$filter->value]);
        }
        elseif ( is_string($column) && ($fName !== "") && ($fType == 'ENCRYPTED') )
        {
            Log::channel('stderr')->debug($this->SHORT_NAME  . 'FieldsEncryptedIndexQueryBuilder:>>>ENCRYPTED ----->', [$column, $operator, $value] );
            $operator = FieldsEncryptedIndexEncrypter::encrypt($operator);
            Log::channel('stderr')->debug($this->SHORT_NAME  . 'FieldsEncryptedIndexQueryBuilder:>>>ENCRYPTED ----->', [$column, $operator, $value] );
            return parent::where($column, $operator, $value, $boolean);
        }
        else
        // il campo può essere cifrato o meno ....
        {
            Log::channel('stderr')->debug($this->SHORT_NAME  . 'FieldsEncryptedIndexQueryBuilder:>>>>>>>>> SIMPLE ----->', [$column, $operator] );
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