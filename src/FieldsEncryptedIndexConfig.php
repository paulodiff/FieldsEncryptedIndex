<?php

/**
 * FieldsEncryptedIndexConfig
 * Caricamento e verifica recupero dati di configurazione delle tabelle delle chiamate
 * 
 */

namespace Paulodiff\FieldsEncryptedIndex;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Cache;

use Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexException;

class FieldsEncryptedIndexConfig {

    protected $model;
    public $enc_fields;
    protected $rtService;

	public $tableRules = [
           
		'tableName' => 'string|required',
		'primaryKey' => 'string|required',

		"fields" => 'array|required',
		"fields.*.fieldName" => 'string|required',
		"fields.*.fieldType" => 'string|required',

		"indexes" => 'array'
	];


	public $securityRules = [
           
		'tableName' => 'string|required',
		
		"fieldsKeys" => 'array|required',
		"fieldsKeys.*.fieldName" => 'string|required',
		"fieldsKeys.*.key" => 'string|required',	
		"fieldsKeys.*.nonce" => 'string|required'		

	];
	
    public $sqlRequestRules = [
            'action' => [
                'string',
                'required',
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

    /**
     * EncryptableQueryBuilder constructor.
     * @param ConnectionInterface $connection
     * @param Encryptable $model
     */
    public function __construct()
    {
        Log::channel('stderr')->debug('FieldsEncryptedIndexConfig __construct', [] );        
		$this->checkConfig();
		// $this->checkEncryption();
    }
    /*
    public function __construct(ConnectionInterface $connection, $model)
    {
        parent::__construct($connection, $connection->getQueryGrammar(), $connection->getPostProcessor());
        $this->model = $model;
    }
    */


	// test config
	public function checkConfig()
	{
		Log::channel('stderr')->debug('FieldsEncryptedIndexConfig:checkConfig', [ config('FieldsEncryptedIndex.configFolder') ] );    
	}


	public function checkEncryption()
	{

		Log::channel('stderr')->info('FieldsEncryptedIndexConfig:checkEncryption:', ['Checking Laravel Crypt and Hash function'] );

        try {

            Log::channel('stderr')->info('checkEncryption:Encryption config driver:', [config('hashing.driver')] );
            
  


        } catch (\Exception $e) {
            Log::channel('stderr')->error('CheckConfig:', ['ERROR! Please check hash, encrypt Laravel config! '. $e] );
            die( $e );
            // $this->assertTrue(false);
        }
	}


	public function getConfigFileName($tn)
	{
		return config('FieldsEncryptedIndex.configFolder') . $tn . ".json";
	}

	public function existsConfigFileName($fn)
	{
		Log::channel('stderr')->debug('FieldsEncryptedIndexConfig:existsConfigFileName', [ $fn ] );    
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
				die();
			}
			return $tableArrayConfig;
		}
		else
		{
			Log::channel('stderr')->error('FieldsEncryptedIndexConfig:loadConfig ERROR - Config file does not exists!', [ $cfn] );    
			die('');
		}
	}

	public function saveConfig($arrayConfig, $tableName, $op)
	{

		Log::channel('stderr')->debug('FieldsEncryptedIndexConfig:saveConfig', [ $tableName ] );    
		$cfn = $this->getConfigFileName($tableName);

		if (!$this->existsConfigFileName($cfn) || array_key_exists('force', $op) )
		{
			$jsonData = json_encode($arrayConfig);
			file_put_contents($cfn, $jsonData);
			return 'saved!';
		}
		else
		{
			Log::channel('stderr')->error('FieldsEncryptedIndexConfig:saveConfig ERROR - Config file already exists!', [ $cfn] );    
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


	// load primary key name from table
	public function getTablePrimaryKey($tn)
    {
        Log::channel('stderr')->debug('getTablePrimaryKey:', [$tn] );
		
        $gc = $this->getTableConfig($tn);

		if ( array_key_exists('primaryKey', $gc) )
		{
			return $gc['primaryKey'];
		} 
		else 
		{
			Log::channel('stderr')->debug('getTablePrimaryKey:NOT FOUND!', [$tn] );
            die();
		}
			
        // return $gct;
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
			// is a PrimaryKey ?

			if ( $fname === $gc['primaryKey'] ) 
			{
				Log::channel('stderr')->debug('getFieldTypeDefinition:IS A PRIMARY KEY!', [$tname, $fname, array_search($fname, array_column($gc['fields'], 'fieldName') )] );
				return 'PRIMARYKEY';
			}
			
            Log::channel('stderr')->debug('getFieldTypeDefinition:NOT FOUND!', [$tname, $fname, array_search($fname, array_column($gc['fields'], 'fieldName') )] );
            die();
        } 
        else 
        {
            $key = array_search($fname, array_column($gc['fields'], 'fieldName') );
            // Log::channel('stderr')->info('FOUND!', [$tname, $fname, array_search($fname, array_column($GLOBAL_TABLE_CONFIG[$tname]['fields'], 'fieldName') )] );

			// Log::channel('stderr')->info('getFieldTypeDefinition:return ', [$key] );

            $fieldType = $gc['fields'][$key]['fieldType'];
            // Log::channel('stderr')->info('FOUND!', [$tname, $fname, $GLOBAL_TABLE_CONFIG[$tname]['fields'][$key]['fieldType']] );

			// Log::channel('stderr')->info('getFieldTypeDefinition:return ', [$fiedlType] );

            return $fieldType;
        }
    }


	public function getFieldConfig($fn)
	{
		Log::channel('stderr')->debug('FieldsEncryptedIndexConfig:getFieldConfig', [ $fn ] ); 

		$pieces = explode(".", $fn);
        $tname = $pieces[0];
        $fname = $pieces[1];

		$sc = $this->loadConfig($tname);

		foreach( $sc['fields'] as $item )
		{
			if ( $item['fieldName'] === $fname ) 
			{
				$item['tableName'] = $sc['tableName'];
				$item['tableNameHashed'] = $sc['tableNameHashed'];
				return $item;
			}
		}
		
		if ( $fname === $sc['primaryKey'] ) 
		{
			Log::channel('stderr')->debug('getFieldConfig:IS A PRIMARY KEY!', [] );
			return [
				"tableName" => $sc['tableName'],
				"tableNameHashed" => $sc['tableNameHashed'],
				"fieldName" => $fname,
				"fieldType"=> "PRIMARYKEY"
			];
		}


		die('getFieldConfig Error getFieldConfig not found in : ' . $fn);

	}


    public function getFieldClause($o)
    {
		die('NOT USED!');
        
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

			if( is_null($tableArrayConfig)) 
			{
				Log::channel('stderr')->error('FieldsEncryptedIndexConfig:loadFakeRequestAndValidate ERROR - Config in config file !', [ $cfn] );    
			}

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

	public function retunArrayFromJson($j)
	{
		
		$tableArrayConfig = json_decode($j, true);

		if( is_null($tableArrayConfig)) 
		{
			Log::channel('stderr')->error('FieldsEncryptedIndexConfig:returnArrayFromJson ERROR - !', [ $j] );  
			print_r($j);  
			throw new FieldsEncryptedIndexException('FieldsEncryptedIndexConfig:retunArrayFromJson JSON parse error');
		}

		return $tableArrayConfig;

	}

//------------------------------------ SECURITY ---------------------------------------------------
	//------------------------------------ SECURITY ---------------------------------------------------
	//------------------------------------ SECURITY ---------------------------------------------------
	//------------------------------------ SECURITY ---------------------------------------------------
	//------------------------------------ SECURITY ---------------------------------------------------
	// SECUTITY CONFIG UTILS 
	// LOAD table.keys


	public function getHashedTableNameConfig($tn)
	{
		Log::channel('stderr')->debug('FieldsEncryptedIndexConfig:getHashedTableNameConfig', [ $tn ] ); 
		
		
		$gct = $this->loadConfig($tn);
	
		Log::channel('stderr')->debug('getHashedTableNameConfig:', [$gct] );
		
		if ( array_key_exists('tableNameHashed', $gct) )
		{
			return $gct['tableNameHashed'];
		}

	
		die('Security Error Table key not found in : ' . $tn);
	        
	}


	public function getTablePrimaryKeyNameConfig($tn)
	{
		Log::channel('stderr')->debug('FieldsEncryptedIndexConfig:getTablePrimaryKeyNameConfig', [ $tn ] ); 
		
		
		$gct = $this->loadConfig($tn);
	
		Log::channel('stderr')->debug('getTablePrimaryKeyNameConfig:', [$gct] );
		
		if ( array_key_exists('primaryKey', $gct) )
		{
			return $gct['primaryKey'];
		}
	
		die('Error primaryKey not found in : ' . $tn);
        
	}

	public function getHashedFieldNameConfig($fn)
	{
		Log::channel('stderr')->debug('FieldsEncryptedIndexConfig:getHashedFieldNameConfig', [ $fn ] ); 

		$pieces = explode(".", $fn);
        $tname = $pieces[0];
        $fname = $pieces[1];

		$sc = $this->loadConfig($tname);

		foreach( $sc['fields'] as $item )
		{
			if ( $item['fieldName'] === $fname ) 
			{
				return $item['fieldNameHashed'];
			}
		}
		
		die('getHashedFieldNameConfig Error fieldNameHashed not found in : ' . $fn);

	}




	public function getFieldSecurityConfig($fn)
	{
		Log::channel('stderr')->debug('FieldsEncryptedIndexConfig:getFieldSecurityConfig', [ $fn ] ); 
		$pieces = explode(".", $fn);
        $tname = $pieces[0];
        $fname = $pieces[1];
	
		$sc = $this->loadConfig($tname);

		foreach( $sc['fields'] as $item )
		{
			if ( $item['fieldName'] === $fname ) 
			{
				return [
					'key' => $item['key'],
					'nonce' => $item['nonce']
				];
			}
		}
		
		die('Security Error key/nonce not found in : ' . $fn);

	}

	public function getTableSecurityConfig($fn)
	{
		Log::channel('stderr')->debug('FieldsEncryptedIndexConfig:getTableSecurityConfig', [ $fn ] ); 
		$pieces = explode(".", $fn);
        $tname = $pieces[0];
        $fname = $pieces[1];
	
		$tc = $this->loadSecurityConfig($tname);

		/*
		"tableName" 
		"key" : 
		"nonce" : 
		*/

		if ( array_key_exists('key', $tc) )
		{
			return $tc['key'];
		}

		
		die('Security Error Table key not found in : ' . $fn);

	}



	public function getSecurityConfigFileName($tn)
	{
		return config('FieldsEncryptedIndex.configFolder') . $tn . ".keys";
	}

	public function loadSecurityConfig($tn)
	{
		$cfn = $this->getSecurityConfigFileName($tn);

		Log::channel('stderr')->debug('FieldsEncryptedIndexConfig:loadSecurityConfig', [ $tn, $cfn ] );    

		if ($this->existsConfigFileName($cfn))
		{
			$jsonTableConfig = file_get_contents($cfn);
			$tableArrayConfig = json_decode($jsonTableConfig, true);
			$Validator = Validator::make($tableArrayConfig, $this->securityRules);
			if ($Validator->fails()) {
				Log::channel('stderr')->error('Security config check error!', [$Validator->errors()] );
				die();
			}
			return $tableArrayConfig;
		}
		else
		{
			Log::channel('stderr')->error('FieldsEncryptedIndexConfig:loadSecurityConfig ERROR - Config file does not exists!', [ $cfn] );    
			die('');
		}
	}

	public function saveSecurityConfig($arrayConfig, $tableName, $op)
	{

		Log::channel('stderr')->debug('FieldsEncryptedIndexConfig:saveSecurityConfig', [ $tableName ] );    
		$cfn = $this->getSecurityConfigFileName($tableName);

		if (!$this->existsConfigFileName($cfn) || array_key_exists('force', $op) )
		{
			$jsonData = json_encode($arrayConfig);
			file_put_contents($cfn, $jsonData);
			return 'saved!';
		}
		else
		{
			Log::channel('stderr')->error('FieldsEncryptedIndexConfig:saveSecurityConfig ERROR - Config file already exists!', [ $cfn] );    
			die('');
		}

	}



	// STORAGE UTILITIES access to DATABASE

	public function storageCountRow($tableName , $whereClause)
	{
		Log::channel('stderr')->debug('FieldsEncryptedIndexConfig:storageCountRow', [ $tableName, $whereClause ] );

		$tableName = trim($tableName);

		// get primaryKey

		$pkName = $this->getTablePrimaryKey($tableName);


		$tableNameHashed = $this->getHashedTableNameConfig($tableName);


		$sqlStatement = "SELECT " .  $pkName . " FROM " . $tableNameHashed . "  " . $whereClause;

		Log::channel('stderr')->debug('FieldsEncryptedIndexConfig:storageCountRow', [ $sqlStatement ] );

		$rs = DB::select( DB::raw($sqlStatement) );


		Log::channel('stderr')->debug('FieldsEncryptedIndexConfig:storageCountRow', [ count($rs) ] );

		/*
			$Ids = DB::table('migrations')->select('id')->get();
			$cntIds = count($Ids);
			$idSelected = $faker->numberBetween(1, $cntIds);
		*/

		return [
			'rowId' => $rs[0]->{$pkName},
			'rowCount' => count($rs)
		];

	}
	

}