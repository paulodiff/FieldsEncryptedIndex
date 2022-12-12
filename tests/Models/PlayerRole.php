<?php
namespace Paulodiff\RainbowTableIndex\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Paulodiff\RainbowTableIndex\RainbowTableIndexTrait;

class PlayerRole extends Model
{
    use HasFactory;
    use RainbowTableIndexTrait;

    protected $table = 'player_roles';
    protected $primaryKey = 'player_role_id';

    protected $fillable = [
        'player_role_description', 
        'player_role_salary', 
    ];

    public static $rainbowTableIndexConfig = [
          'table' => [
            'primaryKey' => 'player_role_id',
            'tableName' => 'player_roles',
        ],
        'fields' => [
            [
              'fName' => 'player_role_description',
              'fType' => 'ENCRYPTED_FULL_TEXT',
              'fSafeChars' => " 'àèéìòùqwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM.",
              'fTransform' => 'UPPER_CASE',
              'fMinTokenLen' => 3,
            ]
        ]
    ];
    

}
