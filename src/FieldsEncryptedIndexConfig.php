<?php

/**
 * FieldsEncryptedIndexConfig
 * Caricamento e verifica recupero dati di configurazione delle tabelle delle chiamate
 * 
 */

namespace Paulodiff\FieldsEncryptedIndex;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexEncrypter;
use Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexService;
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
		$this->checkEncryption();
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
        Log::channel('stderr')->debug('FieldsEncryptedIndexQueryBuilder:>>>DATI>>>>', [$column, $operator, $value, $boolean] );
        Log::channel('stderr')->debug('FieldsEncryptedIndexQueryBuilder:>>>RAINBOW CONFIG>>>>', [$this->enc_fields] );

        // controllo se il campo è in configurazione e di che tipo

        if(!is_string($column)) 
        {
            Log::channel('stderr')->debug('FieldsEncryptedIndexQueryBuilder:>>>>>>>>> NO STRING returm immediatly SIMPLE ----->', [$column, $operator] );
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
                Log::channel('stderr')->debug('FieldsEncryptedIndexQueryBuilder:>>> RAINBOW Field found in enc config!->', [$fName, $fType] );
            }
        }

        // se il campo deve utilizzare una RainbowTable
        if ( is_string($column) && ($operator == 'LIKE') && ($fName !== "") && ($fType == 'ENCRYPTED_FULL_TEXT') )
        {
            Log::channel('stderr')->debug('FieldsEncryptedIndexQueryBuilder:>>> RAINBOW Table --GO! ENCRYPTED_FULL_TEXT ->', [$column, $operator, $tName, $primaryKey, $fType] );
            // accesso alla rainbow table per ottenere i valori da mettere nella query tramite ServiceProvider
            $tag = $tName . ":" . $column;
            $r = $this->rtService->getRT($tag, $value);
            Log::channel('stderr')->debug('FieldsEncryptedIndexQueryBuilder:>>> RAINBOW Table --DATA!->', [$tag, $r] );
            return self::whereIn( $primaryKey , $r );
            // return self::whereRaw("CONVERT(AES_DECRYPT(FROM_BASE64(`{$filter->field}`), '{$salt}') USING utf8mb4) {$filter->operation} ? ", [$filter->value]);
        }
        elseif ( is_string($column) && ($fName !== "") && ($fType == 'ENCRYPTED') )
        {
            Log::channel('stderr')->debug('FieldsEncryptedIndexQueryBuilder:>>>ENCRYPTED ----->', [$column, $operator, $value] );
            $operator = FieldsEncryptedIndexEncrypter::encrypt($operator);
            Log::channel('stderr')->debug('FieldsEncryptedIndexQueryBuilder:>>>ENCRYPTED ----->', [$column, $operator, $value] );
            return parent::where($column, $operator, $value, $boolean);
        }
        else
        // il campo può essere cifrato o meno ....
        {
            Log::channel('stderr')->debug('FieldsEncryptedIndexQueryBuilder:>>>>>>>>> SIMPLE ----->', [$column, $operator] );
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