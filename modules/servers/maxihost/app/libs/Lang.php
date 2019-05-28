<?php

namespace App\Libs;

use Illuminate\Database\Capsule\Manager as DB;

class Lang {
    
    public static function getLang()
    {
        global $CONFIG;

        if (!empty($_SESSION['Language']))
        {
            $language = strtolower($_SESSION['Language']);
        }
        else
        {
            if(isset($_SESSION['uid']) && !empty($_SESSION['uid']))
            {
                $client = DB::table('tblclients')->where('id', $_SESSION['uid'])->first();
                if(isset($client->language) && !empty($client->language))
                {
                    $language = $client->language;
                }
                else 
                {
                    $language = $CONFIG['Language'];
                }
            }
            else
            {
                $language = $CONFIG['Language'];
            }
        }

        $langfilename = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $language . '.php';

        if (file_exists($langfilename))
        {
            require_once($langfilename);
        }
        else
        {
            require_once(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'english.php');
        }

        if (isset($lang))
        {
            return $lang;
        }
    }
    
    
}