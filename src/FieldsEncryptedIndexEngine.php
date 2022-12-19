<?php

/**
 * FieldsEncryptedIndEngine
 * Esegue le richieste SQL richiamanto tutti i servizi
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

class FieldsEncryptedIndexEngine {

    protected $model;
    public $enc_fields;
    protected $rtService;
    public $FEI_config;
    public $FEI_service;
    public $FEI_sql_query_builder;
    public $FEI_sql_query_runner;

	
    /**
     * EncryptableQueryBuilder constructor.
     * @param ConnectionInterface $connection
     * @param Encryptable $model
     */
    public function __construct()
    {
        Log::channel('stderr')->debug('FieldsEncryptedIndexEngine __construct', [] );        
		// $this->checkConfig();
		$this->FEI_config = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexConfig();
		$this->FEI_service = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexService();
		$this->FEI_sql_query_builder = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexQueryBuilder();
		$this->FEI_sql_query_runner = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexQueryRunner();
		// $this->FEI_config->checkConfig();
    }
    /*
    public function __construct(ConnectionInterface $connection, $model)
    {
        parent::__construct($connection, $connection->getQueryGrammar(), $connection->getPostProcessor());
        $this->model = $model;
    }
    */

	// Process processa una richiesta JSON

	public function process(string $q) 
	{

		Log::channel('stderr')->debug('FieldsEncryptedIndexEngine:process', [] );   

		$aR  = $this->FEI_config->retunArrayFromJson($q);

		Log::channel('stderr')->info('FieldsEncryptedIndexEngine:process', [$aR] );
		
		$q = $this->FEI_sql_query_builder->buildQuery($aR);
		Log::channel('stderr')->info('process:parseSQL:FINAL!:', [$q] );
		
		$r = $this->FEI_sql_query_runner->runQuery($q);
		Log::channel('stderr')->info('process:runSQL:FINAL!:', [$r] );

		// "SELECT" recuperare i dati e decodifica ... order by ecc.

		// "UPDATE " ...

		// "INSERT" .. inserire e aggiornare gli indici ...

		// REINDEX tablename;

		return $r;

	}



	
}