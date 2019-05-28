<?php

use Illuminate\Database\Capsule\Manager as DB;

require_once dirname(dirname(dirname(dirname(__DIR__)))).DIRECTORY_SEPARATOR.'init.php';
require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'Loader.php';

$services = DB::table('tblhosting')->where('domainstatus', 'Active')->get();

foreach($services as $service)
{
    try {
    
        $serviceId = $service->id;
        $productId = $service->packageid;
        $serverId = $service->server;

        $customfieldId = App\Libs\CustomFields::getCustomFieldId('device_id', $productId);
        $customfieldValue = App\Libs\CustomFields::getValueCustomField($serviceId, $customfieldId);
        
        $customfieldHostname = App\Libs\CustomFields::getCustomFieldId('hostname', $productId);
        $customfieldHostnameValue = App\Libs\CustomFields::getValueCustomField($serviceId, $customfieldHostname);

        if(empty($customfieldValue) && !empty($customfieldHostnameValue) && !empty($serverId))
        {

            $server = DB::table('tblservers')->where('id', $serverId)->first();

            $params['serversecure'] = isset($server->secure) ? '1' : '';
            $params['serverhostname'] = isset($server->hostname) ? $server->hostname : '';
            $params['serveraccesshash'] = isset($server->accesshash) ? $server->accesshash : '';


            $api = new App\Libs\MaxiHostAPI($params);
            $api->testConnection();
            
            $device = $api->searchInAllDevice($customfieldHostnameValue);
            
            if(isset($device['id']) && !empty($device['id']))
            {
                App\Libs\CustomFields::saveValeuCustomField($serviceId, $customfieldId, $device['id']);
                logActivity('MaxiHost Info: Device #'.$device['id'].' has been assigned to Service #'.$serviceId, 0);
            }
            
        }
    
    } catch (\Exception $e) {
        
        logActivity('Error MaxiHost: '.$e->getMessage(), 0);
        continue;
        
    }
}


