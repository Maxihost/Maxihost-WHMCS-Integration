<?php

namespace App\Libs;

use Illuminate\Database\Capsule\Manager as DB;

class CustomFields {

    public static function getCustomFieldId($name, $productId)
    {
        $customfield = DB::table('tblcustomfields')->where('type', 'product')->where('relid', $productId)->where('fieldname', 'like', $name.'|%')->first();
        
        if(isset($customfield->id) && !empty($customfield->id))
        {
            return $customfield->id;
        }
        
        return false;
    }
    
    public static function getValueCustomField($serviceId, $customfieldId)
    {
        
        $customfield = DB::table('tblcustomfieldsvalues')->where('fieldid', $customfieldId)->where('relid', $serviceId)->first();
        
        if(isset($customfield->value) && !empty($customfield->value))
        {
            return $customfield->value;
        }
        
        return '';
        
    }
    
    public static function saveValeuCustomField($serviceId, $customfieldId, $value)
    {
        $customfield = DB::table('tblcustomfieldsvalues')->where('fieldid', $customfieldId)->where('relid', $serviceId)->first();
        
        if(!empty($customfield))
        {
            DB::table('tblcustomfieldsvalues')->where('fieldid', $customfieldId)->where('relid', $serviceId)->update([
                'value' => $value,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            return true;
        }
        
        DB::table('tblcustomfieldsvalues')->insert([
            'fieldid' => $customfieldId,
            'relid' => $serviceId,
            'value' => $value,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return true;
    }
}

