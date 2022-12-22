<?php


/**
 * FieldsEncryptedIndexEncrypter
 * Funzioni di cifratura/decifratura
 * 
 */


namespace Paulodiff\FieldsEncryptedIndex;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

use Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexConfig;

// $h1 = Hash::make('test');
// $cr = Crypt::encryptString('test');
// $cr = Crypt::decryptString('test');

class FieldsEncryptedIndexEncrypter
{

	/*
    public static function encrypt($s)
    {
        $enc_result = Crypt::encryptString($s);
        $encoded = base64_encode($enc_result);
        return $encoded;
    }

    public static function decrypt($s)
    {
        $decoded = base64_decode($s);
        $o = Crypt::decryptString($decoded);
        return $o;
    }

    public static function hash($s)
    {
        //$enc_result = Hash::make($s);
        //$encoded = base64_encode($enc_result);
        //return $encoded;
        return hash("sha256", $s);
    }

    public static function hash_md5($s)
    {
        return hash("md5", $s);
    }
	*/

	public $FEI_service;


	public function __construct()
    {
        Log::channel('stderr')->debug('FieldsEncryptedIndexEncrypter __construct WITH SODIUM!', [] );        
		$this->FEI_config = new \Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexConfig();
		
    }

    

	/**
     * Cifra un valore con la libreria PHP SODIUM ricavando la key e nonce dal file di configurazione
     *
     * @param array $s parametro
	 * @var $s[fiedlName]  string, il nome del campo da codificare nel formato "tableName.fieldName"
 	 * @var $s[fieldValue] string, il valore da codificare
 	 *
     * @return string la codifica di $[fiedlValue]
     *             
     */

    public function encrypt_sodium($s)
    {
		Log::channel('stderr')->debug('encrypt_sodium', [$s] );   

		$sc = $this->FEI_config->getFieldSecurityConfig($s['fieldName']);

		// dd($sc);

        // $k = self::getKey($s['fie']);
        // $nonce = self::getNonce($o);
        $enc_result = sodium_crypto_secretbox( $s['fieldValue'], sodium_hex2bin($sc['nonce']), sodium_hex2bin($sc['key']));
        $encoded = sodium_bin2hex( $enc_result );
        sodium_memzero($sc['nonce']);
        sodium_memzero($sc['key']);
        return $encoded;
    }

    public function decrypt_sodium($s)
    {
        // Log::debug('Encrypter:decrypt ', [] );

		Log::channel('stderr')->debug('decrypt_sodium', [$s] );   

		$sc = $this->FEI_config->getFieldSecurityConfig($s['fieldName']);

        // $k = self::getKey();
        // $nonce = self::getNonce();
        $decoded = sodium_hex2bin($s['fieldValue']);
        $o = sodium_crypto_secretbox_open($decoded, sodium_hex2bin($sc['nonce']), sodium_hex2bin($sc['key']) );
        // $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        // $encrypted_result = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
        // $o = sodium_crypto_secretbox_open($encrypted_result, $nonce, $k);
        //$o = gzinflate($o);
		sodium_memzero($sc['nonce']);
        sodium_memzero($sc['key']);
        return $o;
    }


  
	/**
     * Ricava il short_hash di un fieldName
     *
     * @param string $s string, il nome del campo da codificare nel formato "tableName.fieldName"
 	 *
     * @return string lo short hash di $s
     *             
     */
    public  function hash_sodium($s)
    {

		die('DO NOT USE! : hash_sodium');
        
		Log::channel('stderr')->debug('hash_sodium', [$s] );   

		$sc = $this->FEI_config->getTableSecurityConfig($s);
		
		// dd($sc);

		Log::channel('stderr')->debug('hash_sodium:key', [$sc] );
		Log::channel('stderr')->debug('hash_sodium:val', [$s] );

		$o = sodium_crypto_generichash($s, sodium_hex2bin($sc));
		
		sodium_memzero($sc);
        // sodium_memzero($sc['key']);
        return 'r' . sodium_bin2hex($o);
    }

	public function keygen_sodium()
	{

	// Generate a secret key. This value must be stored securely.
		return sodium_bin2hex(sodium_crypto_aead_xchacha20poly1305_ietf_keygen());
	}

	public function noncegen_sodium()
	{
		return  sodium_bin2hex(\random_bytes(\SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES));
	}
	
	public function keygen_hash_sodium()
	{
		die('DO NOT USE : keygen_hash_sodium');
		return sodium_bin2hex(sodium_crypto_generichash_keygen());
	}

  

	public function keygen_short_hash_sodium()
	{
		return sodium_bin2hex(sodium_crypto_shorthash_keygen());
	}


/**
     * Ricava il short_hash di un fieldName
     *
     * @param string $s string, il nome del campo da codificare nel formato "tableName.fieldName" 
	 *                          viene usata per compatibilità solo la parte tableName
 	 *
     * @return string lo short hash di $s
     *             
     */
    public  function short_hash_sodium($s)
    {

        
		Log::channel('stderr')->debug('short_hash_sodium', [$s] );   

		$sc = $this->FEI_config->getTableSecurityConfig($s);
		
		// dd($sc);

		Log::channel('stderr')->debug('short_hash_sodium:key', [$sc] );
		Log::channel('stderr')->debug('short_hash_sodium:val', [$s] );

		// $o = sodium_crypto_generichash($s, sodium_hex2bin($sc));

		$o = sodium_crypto_shorthash($s, sodium_hex2bin($sc));
		
		sodium_memzero($sc);
        // sodium_memzero($sc['key']);
        return 'r' . sodium_bin2hex($o);
    }

	

}
