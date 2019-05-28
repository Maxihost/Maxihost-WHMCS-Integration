<?php
namespace App\Controllers\Admin;

use App\Libs\CustomFields;

class ModuleActions {
    
    public function create($params) 
    {
        try{
            
            $customfieldId = CustomFields::getCustomFieldId('device_id', $params['pid']);
            $customfieldValue = CustomFields::getValueCustomField($params['serviceid'], $customfieldId);
            
            if($customfieldValue != '') { return 'Field "Device Id" must be empty.'; }
            
            $api = new \App\Libs\MaxiHostAPI($params);
            
            $data = [
                'facility' => $params['configoption1'],
                'plan' => $params['configoption2'],
                'hostname' => $params['customfields']['hostname'],
                'operating_system' => $params['customfields']['os_maxihost'],
                'billing_cycle' => strtolower($params['model']->billingcycle),
                'backorder' => true
            ];
            
            $api->sendPOST('devices', $data);
            $api->isSuccess();
            
            $results = $api->getResponseArray();

            CustomFields::saveValeuCustomField($params['serviceid'], $customfieldId, $results['id']);
            
            return 'success';
            
            
        } catch (\Exception $e) {
            
            return $e->getMessage();
            
        }
    }
    
    public function suspend($params) 
    {
        try{
            
            $customfieldId = CustomFields::getCustomFieldId('device_id', $params['pid']);
            $customfieldValue = CustomFields::getValueCustomField($params['serviceid'], $customfieldId);
            
            if($customfieldValue == '') { return 'Field "Device Id" cannot be empty.'; }
            
            $api = new \App\Libs\MaxiHostAPI($params);
            
            $data = [
                'type' => 'power_off'
            ];
            
            $api->sendPUT('devices/'.$customfieldValue.'/actions', $data);
            $api->isSuccess();
            
            sleep(10);
            
            return 'success';
            
            
        } catch (\Exception $e) {
            
            return $e->getMessage();
            
        }
    }
    
    public function unsuspend($params) 
    {
        try{
            
            $customfieldId = CustomFields::getCustomFieldId('device_id', $params['pid']);
            $customfieldValue = CustomFields::getValueCustomField($params['serviceid'], $customfieldId);
            
            if($customfieldValue == '') { return 'Field "Device Id" cannot be empty.'; }
            
            $api = new \App\Libs\MaxiHostAPI($params);
            
            $data = [
                'type' => 'power_on'
            ];
            
            $api->sendPUT('devices/'.$customfieldValue.'/actions', $data);
            $api->isSuccess();
            
            sleep(10);
            
            return 'success';
            
            
        } catch (\Exception $e) {
            
            return $e->getMessage();
            
        }
    }
    
    public function testConnection($params)
    {
        try{
        
            $api = new \App\Libs\MaxiHostAPI($params);
            $api->testConnection();
            return ['success' => true];
            
        } catch (\Exception $e) {
            
            return ['error' => $e->getMessage()];
            
        }
        
    }

}