<?php

/**
 * FieldsEncryptedIndexService
 * Gestore di Encrypted Index
 * 
 */




namespace Paulodiff\FieldsEncryptedIndex;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;


use Illuminate\Support\Facades\Cache;

use Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexEncrypter;

// class RainbowTableService implements RainbowTableServiceInterface
class FieldsEncryptedIndexService
{

    public $RAINBOW_TABLE = array();
    protected $MIN_TOKEN_SIZE;

    public $debug = false;
    private $db = null;

    public function __construct() {
        // echo "RainbowTable build mwl:" . $mwl . " sp:" . $sp .  "\n";
        Log::channel('stderr')->debug('FEIS!__construct', [] );
        $this->MIN_TOKEN_SIZE = 3;
        $this->STRING_SEPARATOR = ";";
        Log::channel('stderr')->debug('FEIS!MIN_TOKEN_SIZE', [$this->MIN_TOKEN_SIZE] );
        Log::channel('stderr')->debug('FEIS!STRING_SEPARATOR', [$this->STRING_SEPARATOR] );
    }


    /*
      clean string from characteres in $SAFE_CHARS ...
    */

    public function sanitize_string($s)
    {
      // string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
      //$s = filter_var($s, 	FILTER_SANITIZE_STRING,     FILTER_FLAG_STRIP_HIGH);
      // $s = str_replace(['?', '!', "%"], ' ', $s);
      //$s = strtoupper($s);

      $SAFE_CHARS=" àèéìòùqwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM.";
      //echo "SAFE:$SAFE_CHARS\n";
      $ret = "";
      $sl = mb_strlen($s);

      for($i=0;$i<$sl;$i++)
      {
        $needle = mb_substr($s, $i, 1);
        // echo $needle . " - " . mb_strrpos( $SAFE_CHARS, $needle) . "\n";
        if ( mb_strrpos( $SAFE_CHARS, $needle) !== false  ) // mb_strstr
        {
          $ret = $ret . $needle;
        }
      }
      return $ret;

    }

    public function slugify($text, string $divider = '_')
    {
      // replace non letter or digits by divider
      $text = preg_replace('~[^\pL\d]+~u', $divider, $text);
      // transliterate
      $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
      // remove unwanted characters
      $text = preg_replace('~[^-\w]+~', '', $text);
      // trim
      $text = trim($text, $divider);
      // remove duplicate divider
      $text = preg_replace('~-+~', $divider, $text);
      // lowercase
      $text = strtolower($text);

      if (empty($text)) {
        return 'n-a';
      }

      return $text;
    }




    /*

    Indicizza una chiave nella rainbow table

    La tabella RT viene gestita diversamente

    TAG - KEY - ID

    SOGGETTO INI 1
    SOGGETTO INI 2
    SOGGETTO INI 3
    SOGGETTO INI 4


    $tag tabella:nome_campo
    $s stringa che genera l'indice. può essere composta di più elementi separati da spazio (VALORE)
    %index id della riga della tabella dove si trova la stringa $s

    N.B. TENTA SEMPRE L'INSERIMENTO per evitare duplicati del tipo TAG,KEY,VALUE è stato aggiunto un INDICE unico sulla tabella

    */

    /*
    public function setRT__OLD($tag, $s, $index)
    {
      // sanitize string
      // $this->db->log("RT_add_2 start : " . $index . " " . $s, []);
      Log::channel('stderr')->debug('FEIS!setRT!:', [$tag, $s, $index] );

      // sanitizza la stringa rimuovendo i caratteri speciali
      $s = $this->sanitize_string($s);

      // divide la stringa in un array di token li sanitizza e scarta quelli minori di $this->MIN_TOKEN_SIZE
      $pieces = explode(" ", $s);
      $pieces = array_filter($pieces, function($it) { return strlen($it) >= $this->MIN_TOKEN_SIZE; });

      // print_r($pieces);
      // exit(0);

      foreach ($pieces as $key=>$value)
      {
        // echo "## analyze; ", $key , " - (" , $value, ")\n";
        // $str_len = strlen($value);
        // $token_len = 3;
        $tokens = $this->tokenize_string($value, $this->MIN_TOKEN_SIZE);
        foreach($tokens as $t)
        {
          // DA MIGLIORARE CON BATCH INSERT ....
          $this->setToStorage($tag, $t, $index);
        }
      }
    }
    */

	/*
    public function setRT($tag, $s, $index)
    {
      Log::channel('stderr')->debug('FEIS!setRT*!:', [$tag, $s, $index] );
      return $this->setToStorage($tag, $s, $index);
    }
	*/

    // Ritorna l'array degli id relativi ad un determinato tag
    /*
    public function getRT_OLD($tag, $s)
    {
        Log::channel('stderr')->debug('RainbowTableService!getRT!:', [$tag, $s] );
        $multiple_token_string = $this->sanitize_string($s);
        $pieces = array_filter(explode(" ", $multiple_token_string));
        Log::channel('stderr')->debug('RainbowTableService!getRT!sanitized!:', [$pieces] );
        // print_r($pieces);

        $p_results = [];
        foreach ($pieces as $key=>$t)
        {
            //$t_hash = $this->rt_hash($t);

            $r = $this->getFromStorage($tag, $t);
            if($r)
            {
                Log::channel('stderr')->debug("RainbowTableService!getRT! for: " . $t . " " . json_encode($r), []);
                $p_results = array_merge($p_results, $r);
            }

        }

        $u = array_unique($p_results, SORT_STRING);
        Log::channel('stderr')->debug("RainbowTableService!getRT!:" . $multiple_token_string . " " . json_encode($u), []);
        // echo "RainbowTable search result for :" . $multiple_token_string . "\n";
        // print_r($u);
        return $u;
        // return  [991, 992, 993];
    }
    */

	/* DA REIMPLEMENTARE
    public function getRT($tag, $s)
    {
        $s2 = str_replace("%", "", $s);
        Log::channel('stderr')->debug('FEIS!getRT*!:', [$tag, $s, $s2] );
        $r = $this->getFromStorage($tag, $s2);
        // print_r($pieces);
        $u = array_unique($r, SORT_STRING);
        Log::channel('stderr')->debug("FEIS!getRT!:" . $s2 . " " . json_encode($u), []);
        // echo "RainbowTable search result for :" . $multiple_token_string . "\n";
        // print_r($u);
        return $u;
    }
	*/

	/* DA REIMPLEMENTARE
    // Elimina tutte le entry/righe dell'indice relative ad una determinata coppia TAG/ID
    public function delRT($tag, $index)
    {
        Log::channel('stderr')->debug('RainbowTableService!delRT!:', [$tag, $index] );
        $this->deleteFromStorage($tag, $index);
        return true;
    }
	*/

	/* DA REIMPLEMENTARE
    // Reset index from TAG - DESTROY INDEX!
    public function resetRT($tag)
    {
        Log::channel('stderr')->debug('RainbowTableService!resetRT!:', [$tag] );
        $this->resetIndexFromStorage($tag);
        return true;
    }
	*/



    /**
     * rimuove alcuni caratteri dalla stringa di input
     * lascia il punto . ' e trattini
     * '?', '!', '.', '-', "'", "%"
     */


    // da una stringa genera tutti i token possibile a partire da una data lunghezza
    public function tokenize_string($s, $token_size)
    {
      $tokens = [];
      $str_len = strlen($s);
      if( $str_len > $token_size )
      {
        for($token_len = $token_size; $token_len <= $str_len; $token_len++)
        {
          for($start = 0; $start <= ($str_len - $token_len); $start++)
            {
              // echo "p tl:$token_len strlen:$str_len start:$start\n";
              $t = mb_substr($s, $start, $token_len);
              $tokens[] = $t;
            }
          }
      }
      else
      {
        $tokens[] = $s;
      }
      return $tokens;
    }




    // ritorna un array di valori o [] se non esiste nulla
    function getFromStorage($tag, $key)
    {
        
        $tname = $this->setupStorage($tag);

        if (config('FieldsEncryptedIndex.encrypt'))
        {
          $key = FieldsEncryptedIndexEncrypter::hash($key);
        }
        Log::channel('stderr')->debug('FEIS!getFromStorage!', [$tname, $tag, $key] );

        $r = DB::table($tname)
                    ->select('rt_value')
                    // ->where('rt_tag', $tag)
                    ->where('rt_key', $key)
                    ->get();

        $results = [];

        foreach ($r as $item)
        {
            $results[] = $item->rt_value;
        }

        return $results;

    }

    function setToStorage($tag, $key, $value)
    {
        // check i table exista
        $tname = $this->setupStorage($tag);
        
        if (config('FieldsEncryptedIndex.encrypt'))
        {
          $key = FieldsEncryptedIndexEncrypter::hash($key);
        }

        Log::channel('stderr')->debug('FEIS!setToStorage!', [$tname, $tag, $key, $value] );

        DB::table($tname)->insertOrIgnore([
            [
                // 'rt_tag' => $tag,
                'rt_key' => $key,
                'rt_value' => $value,
            ]
        ]);
        return $tname . ":" . $key . ":" . $value;

    }

    function deleteFromStorage($tag, $value)
    {
      Log::channel('stderr')->debug('FEIS!deleteFromStorage!', [$tag, $value] );
      $tname = $this->setupStorage($tag);

      DB::table($tname)
      // ->where('rt_tag', $tag)
      ->where('rt_value', $value)
      ->delete();
    }


    function resetIndexFromStorage($tag)
    {
      Log::channel('stderr')->debug('FEIS!resetIndexFromStorage!', [$tag] );
      $tname = $this->setupStorage($tag);
      DB::table($tname)
      // ->where('rt_tag', $tag)
      ->delete();
    }


	// Controlla che eista la tabella per contenere l'indice
	// $tag deve essere nella forma TABLENAME:FIELDNAME
    function setupStorage($tag)
    {


		if (config('FieldsEncryptedIndex.prefix'))
		{
			$prefix = config('FieldsEncryptedIndex.prefix');
		}
		else 
		{
			die('FieldsEncryptedIndexService:setupStorage prefix not set!');
		}

		$tname = $this->slugify($prefix . "-" . $tag);

		Log::channel('stderr')->debug('FEIS!setupStorage!', [$tname] );
		
		if (config('FieldsEncryptedIndex.encrypt'))
		{
			$tname = FieldsEncryptedIndexEncrypter::hash_md5($tname);
		}      

      	
		$cacheKey = 'setupStorage:' . $tname;

		// $value = Cache::store('file')->get('foo');

		if ( Cache::store('file')->has($cacheKey) )
		{
			Log::channel('stderr')->debug('FEIS!setupStorage!CACHE', [$tname] );
			return $tname;
		} 
		else 
		{
			
			if ( !Schema::hasTable($tname)) {

				Log::channel('stderr')->debug('FEIS!setupStorage!CREATE TABLE', [$tname] );
			
				Schema::create($tname, function(Blueprint $table)
				{
					// $table->increments('id');
					// $table->string('rt_tag');
					$table->text('rt_key');
					$table->bigInteger('rt_value');
					// $table->unique(['rt_tag','rt_key','rt_value']);
					// $table->index(['rt_tag','rt_value']);
				});

			}

			// Cache::store('redis')->put('bar', 'baz', 600); // 10 Minutes

			Cache::store('file')->put($cacheKey, true, 600); // 10 Minutes
			return $tname;
		}

    }

	// --------------------------------------------------------------------------------------------------------------
	// 											NUOVE IMPLEMENTAZIONI
	// --------------------------------------------------------------------------------------------------------------
	// --------------------------------------------------------------------------------------------------------------
	// --------------------------------------------------------------------------------------------------------------
	// --------------------------------------------------------------------------------------------------------------
	// --------------------------------------------------------------------------------------------------------------
	// --------------------------------------------------------------------------------------------------------------
	// --------------------------------------------------------------------------------------------------------------
	// --------------------------------------------------------------------------------------------------------------
	// --------------------------------------------------------------------------------------------------------------
	// --------------------------------------------------------------------------------------------------------------
	// --------------------------------------------------------------------------------------------------------------


	// Salva i valori dell'indice ...
	function FEI_set_old($tableName, $fieldName, $fieldValue, $value)
	{
		Log::channel('stderr')->debug('FEIS!FEI_set:', [$tableName, $fieldName, $fieldValue, $value] );


		// $data = self::rtiSanitize($fValue, $fSafeChars, $fTransform);

		// Tokenize ... su
		// $keyList = $this->feiTokenize($fieldValue, $fMinTokenLen);
		$keyList = $this->feiTokenize($fieldValue, 3);

		// TODO function makeTag TAG!
		$tag = $tableName . ":" . $fieldName;

		foreach( $keyList as $tokenValue )
		{

			// $this->setRT($tag, $tokenValue, $value);
			$this->setToStorage($tag, $tokenValue, $value);
		    // $rtService->setRT($item['tag'],$item['key'],$item['value']);
		}

		

		// crea i token
		// rimuove gli spazi
		// rimuove i duplicati
		// inserisce

	}




	// Salva i valori dell'indice ...
	function FEI_set($tableName, $fieldName, $fieldValue, $value)
	{
		Log::channel('stderr')->debug('FEIS!FEI_set:', [$tableName, $fieldName, $fieldValue, $value] );


		// $data = self::rtiSanitize($fValue, $fSafeChars, $fTransform);

		// Tokenize ... su
		// $keyList = $this->feiTokenize($fieldValue, $fMinTokenLen);
		$keyList = $this->feiTokenize($fieldValue, 3);

		// TODO function makeTag TAG!
		$tag = $tableName . ":" . $fieldName;

		$dataList = [];

		foreach( $keyList as $tokenValue )
		{

			$dataList[] = [
				// 'rt_tag' => $tag,
				'rt_key' => $tokenValue,
				'rt_value' => $value,
			];

			
			// $this->setToStorage($tag, $tokenValue, $value);
		    
		}

		// check i table exists
		$tname = $this->setupStorage($tag);
     
		Log::channel('stderr')->debug('FEIS!FEI_set:', [$tname] );

		// dd($dataList);

		DB::table($tname)->insertOrIgnore(	$dataList );

		// return $tname . ":" . $key . ":" . $value;

		Log::channel('stderr')->debug('FEIS!FEI_set:DONE!', [$tname, count($dataList)] );

		/*
		DB::table('users')->insertOrIgnore([
			['id' => 1, 'email' => 'sisko@example.com'],
			['id' => 2, 'email' => 'archer@example.com'],
		]);
		*/

		// dd('STOP : FEI_set');
		

		// crea i token
		// rimuove gli spazi
		// rimuove i duplicati
		// inserisce

	}


	// Recupera i valori dell'indice ...
	function FEI_get($tableName, $fieldName, $fieldValue)
	{
		Log::channel('stderr')->debug('FEIS!FEI_get:', [$tableName, $fieldName, $fieldValue] );

		// $s2 = str_replace("%", "", $s);
        // Log::channel('stderr')->debug('FEIS!getRT*!:', [$tag, $s, $s2] );

		// TODO function makeTag TAG!
		$tag = $tableName . ":" . $fieldName;


        $r = $this->getFromStorage($tag, $fieldValue);
        // print_r($pieces);

		// print_r($r);

        $u = array_unique($r, SORT_STRING);

		// dd(json_encode($u));

        Log::channel('stderr')->debug("FEIS!getRT!:" . json_encode($u), []);
        // echo "RainbowTable search result for :" . $multiple_token_string . "\n";
        // print_r($u);
        return $u;

	}

	// ELIMINA LA VOCE DALL'INDICE opportuno
	public function FEI_del($tableName, $fieldName, $index)
    {
        Log::channel('stderr')->debug('FEIS!FEI_del:', [$tableName, $fieldName, $index] );
		// TODO function makeTag TAG!
		$tag = $tableName . ":" . $fieldName;

        $this->deleteFromStorage($tag, $index);
        return true;
    }


	// DROP DELL'INDICE
	function FEI_drop($tableName, $fieldName)
	{
		Log::channel('stderr')->debug('FEIS!FEI_drop:', [$tableName, $fieldName] );
		// TODO function makeTag TAG!
		$tag = $tableName . ":" . $fieldName;


        $r = $this->resetIndexFromStorage($tag);
        // print_r($pieces);
        
        return true;

	}


	static function feiSanitize($s, $safeChars, $fTransform)
    {

      // SANITIZE and UPCASE...

      $SAFE_CHARS=" àèéìòùqwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM.";
      $SAFE_CHARS = $safeChars;

      Log::channel('stderr')->debug('FEIS!rtiSanitize!', [$s, $safeChars, $fTransform] );


	  if (str_contains($fTransform, "UPPER_CASE")) 
	  {
		$s = strtoupper($s); 
	  }


	  /*
      //echo "SAFE:$SAFE_CHARS\n";
      $ret = "";
      $sl = mb_strlen($s);

      for($i=0;$i<$sl;$i++)
      {
        $needle = mb_substr($s, $i, 1);
        // echo $needle . " - " . mb_strrpos( $SAFE_CHARS, $needle) . "\n";
        if ( mb_strrpos( $SAFE_CHARS, $needle) !== false  ) // mb_strstr
        {
          $ret = $ret . $needle;
        }
      }
	  */

      return $s;
    }

	
	function feiTokenize($s, $minTokenLen, $optimizations = [])
    {
      // Divide la stringa in token di lunghezza e successivi
	  // Rimuove il carattere spazio dai token
	  // Elimina i token doppi

	  // $optimizations TODO REMOVE_BLANK | UPPER_CASE	

      // per ogni item vengono se >= della lunghezza minima vengono genarati i token

      // if $minTokenLen == 0 get all $s

	  // divide la stringa in token
	  $tokens = $this->rolling_window_string($s, $minTokenLen);

	
	  return $tokens;
	  


	  // per ogni token rimuove gli spazi


	  // riverifica i token


	  // elimina i token doppi


	  // fine

	 /*	

      if ( $minTokenLen <> 0 )
      {
        $pieces = explode(" ", $s);
        $pieces2 = [];
        foreach($pieces as $it)
        {
          if ( strlen($it) >= $minTokenLen )
          {
            $pieces2[] = $it;
          }
        }
      }
      else
      {
        $pieces2[] = $s;
      }     

      Log::channel('stderr')->debug('FEIS!rtiTokenize!', [$s, $minTokenLen, $pieces2] );

      $toReturn = [];
      foreach ($pieces2 as $key=>$value)
      {
        $tokens = $this->rolling_window_string($value, $minTokenLen);
        foreach($tokens as $t)
        {
          $toReturn[] = $t;
        }
      }
      return $toReturn;

	  */

    }

    static function rolling_window_string($s, $token_size)
    {

      $tokens = [];
      if ($token_size == 0)
      {
        $tokens[] = $s;
        return $tokens;
      }
      
      $str_len = strlen($s);
      if( $str_len > $token_size )
      {
        for($token_len = $token_size; $token_len <= $str_len; $token_len++)
        {
          for($start = 0; $start <= ($str_len - $token_len); $start++)
            {
              // echo "p tl:$token_len strlen:$str_len start:$start\n";
              $t = mb_substr($s, $start, $token_len);
              $tokens[] = $t;
            }
          }
      }
      else
      {
        $tokens[] = $s;
      }
      return $tokens;
    }

	/*

	// Da implementare 


	// Partendo dal model e dalla configurazione costruisce l'elenco dei dati
    // da indicizzare per una riga
    // estrae tutti i campi che devono essere indicizzati

    public static function buildDataToIndex($model)
    {
      $conf = self::$FieldsEncryptedIndexConfig;
      Log::channel('stderr')->debug('FieldsEncryptedIndexTrait!buildDataToIndex!', [$model, $conf] );

      $toIndex = [];
      $toIndex['data'] = [];
      $toIndex['fields'] = [];

      $index = -1;
      $table = "";
      $primaryKey = "";

      // $conf is ok already checked!
      $table = $conf['table']['tableName'];
      $primaryKey = $conf['table']['primaryKey'];

      // controllare se esiste il campo primaryKey in model
      if ( !array_key_exists($primaryKey, $model) )
      {
        Log::error('FieldsEncryptedIndexTrait!buildDataToIndex!NO table primaryKey in model!',[$primaryKey]);
        exit(3);
      }
      else
      {
        $index = $model[$primaryKey];
      }

      // per ogni campo in configurazione

      foreach($conf['fields'] as $item)
      {
        if($item['fType'] == 'ENCRYPTED_FULL_TEXT')
        {
          // prendo il valore dal model / data
          Log::channel('stderr')->debug('FieldsEncryptedIndexTrait!buildDataToIndex!FULLTEXT', [$item]);

          $fName =  $item['fName'];
          $fValue = $model[$fName];
          $fSafeChars = $item['fSafeChars'];
          $fTransform = $item['fTransform'];
          $fMinTokenLen = $item['fMinTokenLen'];

          $toIndex['fields'][] = [
            'tag' => $table . ":" . $fName,
            'key' => "*",
            'value' => $index
          ];

          // Sanitizza i dati
          Log::channel('stderr')->debug('FieldsEncryptedIndexTrait!buildDataToIndex!stiSanitize', [$item]);
          $data = self::rtiSanitize($fValue, $fSafeChars, $fTransform);

          // Tokenize ...
          $keyList = self::rtiTokenize($data, $fMinTokenLen);

          foreach($keyList as $t)
          {
            $toIndex['data'][] = [
              'tag' => $table . ":" . $fName,
              'key' => $t, // Decrypt
              'value' => $index,
            ];
          }
        }
      }

      Log::channel('stderr')->debug('FieldsEncryptedIndexTrait!buildDataToIndex!', [$toIndex]);

      return $toIndex;
    }

	// rigenera il rainbow Index per il $model istanziato  
    public function rebuildRainbowIndex()
    {
      $output = [];
      Log::channel('stderr')->debug('FieldsEncryptedIndexTrait!rebuildRainbowIndex', [] );
      // static::query()->save();

      $rtService = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexService();

      // dal modello usando la configurazione prepara un elenco di campi che devono essere indicizzati poichè in una tabella i campi da
      // fulltext protrebbero essere più di uno

      $data2index = self::buildDataToIndex($this->toArray());

      // reset index data
      foreach( $data2index['fields'] as $item )
      {
        $rtService->delRT($item['tag'],$item['value']);
      }

      // set index
      foreach( $data2index['data'] as $item )
      {
        $rtService->setRT($item['tag'],$item['key'],$item['value']);
      }

      Log::channel('stderr')->debug('FieldsEncryptedIndexTrait!rebuildRainbowIndex', ['OK'] );
      $output[] = 'rebuildRainbowIndex:OK!';
      return $output;
    }



	*/


}
