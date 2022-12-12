<?php


namespace Paulodiff\FieldsEncryptedIndex\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Paulodiff\FieldsEncryptedIndex\FieldsEncryptedIndexTrait;
use Illuminate\Support\Facades\Log;

use Exception;
use Illuminate\Support\Facades\Schema;

class DynamicModel extends Model
{

    use HasFactory;
    use FieldsEncryptedIndexTrait;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    protected $routeKeyName = 'id';

    public function getRouteKeyName()
    {
        return $this->routeKeyName;
    }

    protected $guarded = [];


    public static $FieldsEncryptedIndexConfig = [];
    public static $modelAttributes = [];

    /**
     * important! - attributes need to be passed,
     * cause of new instance generation inside laravel
     *
     * @param $attributes
     * @throws Exception
     */
    public function __construct($attributes = [])
    {
        self::$modelAttributes =  $attributes;
        self::$FieldsEncryptedIndexConfig =  [
  

            'table' => [
                'primaryKey' => 'id',
                'tableName' => 'authors',
            ],
    
            'fields' => [
                
                [
                  'fName' => 'name_enc',
                  'fType' => 'ENCRYPTED_FULL_TEXT',
                  'fSafeChars' => " 'àèéìòùqwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM.",
                  'fTransform' => 'UPPER_CASE',
                  'fMinTokenLen' => 3,
                ],
                [
                    'fName' => 'address_enc',
                    'fType' => 'ENCRYPTED_FULL_TEXT',
                    'fSafeChars' => " 'àèéìòùqwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM.",
                    'fTransform' => 'UPPER_CASE',
                    'fMinTokenLen' => 4,
                ],
                [
                    'fName' => 'card_number_enc',
                    'fType' => 'ENCRYPTED_FULL_TEXT',
                    'fSafeChars' => '1234567890',
                    'fTransform' => 'NONE',
                    'fMinTokenLen' => 4,
                ],
                
                [
                    'fName' => 'role_enc',
                    'fType' => 'ENCRYPTED_FULL_TEXT',
                    'fSafeChars' => ' àèéìòùqwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM.',
                    'fTransform' => 'UPPER_CASE',
                    'fMinTokenLen' => 0,
                ],
    
            ]
        ];
 

        Log::channel('stderr')->debug('DynamicModel: ... __contructors ... ', [$attributes, self::$modelAttributes] );

        parent::__construct($attributes);

        Log::channel('stderr')->debug('DynamicModel:', [$attributes] );

        /*
        if (!$table = config('dynamic-model.current_table')) {
            throw new Exception("Seems like you called DynamicModel directly,
            please use service container: App::make(DynamicModel::class, ['table_name' => 'foo'])");
        }
        */

        // dd($attributes['table_name']);
        
        Log::channel('stderr')->debug('DynamicModel:', [$attributes['table_name']] );

        $this->table = $attributes['table_name'];

        // dd($attributes[0]);
       

        if (!Schema::hasTable($this->table)) {
            throw new Exception("The table you provided to the DynamicModel does not exists! Please create it first!");
        }

        $connection = Schema::getConnection();

        // $table = $connection->getDoctrineSchemaManager()->listTableDetails($this->table);
        
        // $primaryKeyName = $table->getPrimaryKey()->getColumns()[0];
        
        // $primaryColumn = $connection->getDoctrineColumn($this->table, $primaryKeyName);
       
        $this->primaryKey = 'id';
        
        // $this->incrementing = $primaryColumn->getAutoincrement();
        
        // $this->keyType = ($primaryColumn->getType()->getName() === 'string') ? 'string' : 'integer';
        
        // $this->routeKeyName = $primaryColumn->getName();

        Log::channel('stderr')->debug('DynamicModel:', [$this->primaryKey] );
        Log::channel('stderr')->debug('DynamicModel:', [$this->incrementing] );
        Log::channel('stderr')->debug('DynamicModel:', [$this->keyType] );
        Log::channel('stderr')->debug('DynamicModel:', [$this->routeKeyName] );

        $this->fillable = [
            'name', 
            'name_enc',
            'card_number', 
            'card_number_enc', 
            'address', 
            'address_enc', 
            'role', 
            'role_enc'
        ];

           


    }
}