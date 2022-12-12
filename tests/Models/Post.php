<?php
namespace Paulodiff\RainbowTableIndex\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Paulodiff\RainbowTableIndex\RainbowTableIndexTrait;

class Post extends Model
{
    use HasFactory;
    use RainbowTableIndexTrait;

    // title, title_enc, author_id

    public static $rainbowTableIndexConfig = [

        'table' => [
            'primaryKey' => 'id',
            'tableName' => 'posts',
        ],
        'fields' => [
            [
              'fName' => 'title_enc',
              'fType' => 'ENCRYPTED_FULL_TEXT',
              // aggiungere anche l'apice per indicizzare O'Briant Malago'
              'fSafeChars' => " 'àèéìòùqwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM.",
              'fTransform' => 'UPPER_CASE',
              'fMinTokenLen' => 3,
            ],

        ]

    ];


}
