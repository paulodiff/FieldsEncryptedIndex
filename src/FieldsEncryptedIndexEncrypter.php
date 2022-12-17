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

    
    public function encrypt_sodium($s)
    {
		Log::channel('stderr')->error('encrypt_sodium', [$s] );   

		$sc = $this->FEI_config->getSecurityConfig($s['fieldName']);

		// dd($sc);

        // $k = self::getKey($s['fie']);
        // $nonce = self::getNonce($o);
        $enc_result = sodium_crypto_secretbox( $s['fieldValue'], hex2bin($sc['nonce']), hex2bin($sc['key']));
        $encoded = bin2hex( $enc_result );
        sodium_memzero($sc['nonce']);
        sodium_memzero($sc['key']);
        return $encoded;
    }

    public function decrypt_sodium($s)
    {
        // Log::debug('Encrypter:decrypt ', [] );

		Log::channel('stderr')->error('decrypt_sodium', [$s] );   

		$sc = $this->FEI_config->getSecurityConfig($s['fieldName']);

        // $k = self::getKey();
        // $nonce = self::getNonce();
        $decoded = hex2bin($s['fieldValue']);
        $o = sodium_crypto_secretbox_open($decoded, hex2bin($sc['nonce']), hex2bin($sc['key']) );
        // $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        // $encrypted_result = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
        // $o = sodium_crypto_secretbox_open($encrypted_result, $nonce, $k);
        //$o = gzinflate($o);
		sodium_memzero($sc['nonce']);
        sodium_memzero($sc['key']);
        return $o;
    }

    public function hash_sodium($s)
    {
        return sodium_bin2hex(sodium_crypto_generichash($s));
    }

  

    public  function short_hash($s)
    {
        return sodium_crypto_shorthash($s, self::getNonce());
    }

    protected  function getKey($o)
    {
        // $key = config('rainbowtable.key');
        // Log::debug('Encrypter:getKey ... from config', [$key] );
        return  sodium_base642bin(config('FieldsEncryptedIndex.key') , SODIUM_BASE64_VARIANT_ORIGINAL);
    }

    protected  function getNonce($o)
    {
        // $nonce = config('rainbowtable.nonce');
        // Log::debug('Encrypter:getNonce ... from config', [$nonce] );
        return  sodium_base642bin(config('FieldsEncryptedIndex.nonce') , SODIUM_BASE64_VARIANT_ORIGINAL);
    }
    


}
