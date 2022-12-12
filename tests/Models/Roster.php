<?php
namespace Paulodiff\RainbowTableIndex\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Paulodiff\RainbowTableIndex\RainbowTableIndexTrait;

class Roster extends Model
{
    use HasFactory;
    use RainbowTableIndexTrait;

    protected $table = 'rosters';
    protected $primaryKey = 'roster_id';

    protected $fillable = [
        'roster_description', 
        'roster_player_id',
        'roster_team_id',
        'roster_player_role_id', 
        'roster_amount', 
    ];

    protected $with = [
        'team',
        'player',
        'player_role'
    ];

    public static $rainbowTableIndexConfig = [
          'table' => [
            'primaryKey' => 'roster_id',
            'tableName' => 'rosters',
        ],
        'fields' => [
            [
              'fName' => 'roster_description',
              'fType' => 'ENCRYPTED_FULL_TEXT',
              'fSafeChars' => " 'àèéìòùqwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM.",
              'fTransform' => 'UPPER_CASE',
              'fMinTokenLen' => 3,
            ],
            [
                'fName' => 'roster_amount',
                'fType' => 'ENCRYPTED_FULL_TEXT',
                'fSafeChars' => " 0123456789.",
                'fTransform' => 'UPPER_CASE',
                'fMinTokenLen' => 3,
            ],
        ]
    ];
    
    public function team()
    {
        return $this->hasOne(Team::class, 'team_id', 'roster_team_id');
        // return $this->hasOne(Phone::class, 'foreign_key', 'local_key');
    }

    public function player()
    {
        return $this->hasOne(Player::class, 'player_id', 'roster_player_id');
    }

    public function player_role()
    {
        return $this->hasOne(PlayerRole::class, 'player_role_id', 'roster_player_role_id');
    }

}
