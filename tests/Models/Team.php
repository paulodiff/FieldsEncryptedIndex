<?php
namespace Paulodiff\RainbowTableIndex\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Paulodiff\RainbowTableIndex\RainbowTableIndexTrait;

class Team extends Model
{
    use HasFactory;
    use RainbowTableIndexTrait;

    protected $table = 'teams';
    protected $primaryKey = 'team_id';

    protected $fillable = [
        'team_name', 
        'team_type_id', 
    ];

    protected $with = [
        'team_type'
    ];

    public static $rainbowTableIndexConfig = [
          'table' => [
            'primaryKey' => 'team_id',
            'tableName' => 'teams',
        ],
        'fields' => [
            [
              'fName' => 'team_name',
              'fType' => 'ENCRYPTED_FULL_TEXT',
              'fSafeChars' => " '_àèéìòùqwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM.",
              'fTransform' => 'UPPER_CASE',
              'fMinTokenLen' => 3,
            ]
        ]
    ];
    
    public function team_type()
    {
        return $this->hasOne(TeamType::class, 'team_type_id', 'team_type_id');
    }

}
