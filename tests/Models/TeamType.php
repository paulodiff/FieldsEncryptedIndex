<?php
namespace Paulodiff\RainbowTableIndex\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Paulodiff\RainbowTableIndex\RainbowTableIndexTrait;

class TeamType extends Model
{
    use HasFactory;
    use RainbowTableIndexTrait;

    protected $table = 'team_types';
    protected $primaryKey = 'team_type_id';

    protected $fillable = [
        'team_type_description', 
        'team_type_rules', 
    ];

    public static $rainbowTableIndexConfig = [
          'table' => [
            'primaryKey' => 'team_type_id',
            'tableName' => 'team_types',
        ],
        'fields' => [
            [
              'fName' => 'team_type_description',
              'fType' => 'ENCRYPTED_FULL_TEXT',
              'fSafeChars' => " _'àèéìòùqwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM.",
              'fTransform' => 'UPPER_CASE',
              'fMinTokenLen' => 3,
            ]
        ]
    ];
    
}
