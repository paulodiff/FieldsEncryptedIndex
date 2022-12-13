// https://dbdiagram.io/d/61d3251f3205b45b73d51c25

// https://github.com/austinheap/laravel-database-encryption/blob/master/tests/TestCase.php

// https://www.youtube.com/watch?v=H-euNqEKACA&t=1045s

// https://laravel-news.com/building-your-own-laravel-packages

// https://github.com/JustSteveKing/laravel-data-object-tools

// https://laravelpackage.com/02-development-environment.html#psr-4-autoloading 

NO Pest 
TODO Pint 
TODO PHPStan  
TODO distanza levinstein/humming altre per eventuale ordinamento
TODO cache

PHP_CodeSniffer

https://cylab.be/blog/22/using-php-codesniffer-in-a-laravel-project


.\vendor\bin\phpcs ./app

By default, PHP_CodeSniffer validates and reports violations to PEAR coding standards. For Laravel, you would instead use the option --standard=PSR12 along with the run command.


FieldsEncryptedIndexSqlQuery (Crea ed esegue la query)
FieldsEncryptedIndexConfig (Gestisce il caricamento/verifica/recupero delle configurazioni)

## Sviluppato con test e avviati con PHPUnit

-> go.bat 
-> .\vendor\bin\phpunit.bat


CREATE TEST package on Artisan Console Commands..


CREATE CONFIG 


NO Using Pest


NO create packages/username/package_name
NO cd packages/username/package_name
NO composer init
NO mkdir src
ONmkdir tests


create package_nameServiceProvider.php

add autoload to packages/username/package_name/composer.json
"autoload": {
    "psr-4": {
        "RainbowTableIndex\\": "src/"
    }
}

add autoload-dev to /roo/composer.json
"autoload-dev": {
    "psr-4": {
        "Tests\\": "tests/",
        "RainbowTableIndex\\": "packages/paulodiff/rainbow-table-index/src"
    }
}

cd /root project
composer dump-autoload

"autoload": {
    "psr-4": {
        "RainbowTableIndex\\": "src/"
    }
},


//// FULL TEST -------------------------------------------------------------------------------------


```

Table player as A {
  player_id int [pk] // auto-increment
  player_full_name varchar
  player_address varchar
  player_credit varchar
  player_phone varchar
}

Table team as B {
  team_id int [pk]
  team_name varchar
  team_type_id int
}

Table team_type as C {
  team_type_id int [pk]
  team_type_description varchar
  team_type_rules varchar
}

Table roster as D {
  roster_id int [pk]
  roster_description varchar
  roster_player_id int
  roster_team_id int
  roster_player_role_id int
  roster_amount varchar 
}

Table player_role as E {
  player_role_id int [pk]
  player_role_description varchar
  player_role_fee varchar
}


// Creating references
// You can also define relaionship separately
// > many-to-one; < one-to-many; - one-to-one
// Ref: merchants.country_code > countries.code
Ref: D.roster_team_id - B.team_id  
Ref: D.roster_player_id - A.player_id  
Ref: D.roster_player_role_id - E.player_role_id  
Ref: B.team_type_id - C.team_type_id  

//----------------------------------------------//

//// -- LEVEL 2
//// -- Adding column settings

//Table order_items {
//  order_id int [ref: > orders.id] // inline relationship (many-to-one)
//  product_id int
//  quantity int [default: 1] // default value
//}

//Ref: order_items.product_id > products.id


//----------------------------------------------//

//// -- Level 3 
//// -- Enum, Indexes

// Enum for 'products' table below
Enum products_status {
  out_of_stock
  in_stock
  running_low [note: 'less than 20'] // add column note
}



// Ref: products.merchant_id > merchants.id // many-to-one
//composite foreign key
// Ref: merchant_periods.(merchant_id, country_code) > merchants.(id, country_code)
```












-- Parse della richiesta
-- Creazione della query

--- select campi cifrati ? --> decodifica
--- where campi cifrati ? --> modifica della query



{
  //  SELECT, INSERT, UPDATE or DELETE.
  "action" : "SELECT",

  "tables" : [
    {
      "tname" : "t1",
      "as" : "t1a"
    },
  ],

  "fieldsToGet" : [
    {
      "fielName": "as.mario"
    }
  ],

  "whereConditions" : [

  ],

  "order" : [

  ],

  "limit" : [


  ]
}


verifica nelle configazioni JSON


// 


TODO ESCAPE VALUES













