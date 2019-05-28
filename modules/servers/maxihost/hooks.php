<?php

use Illuminate\Database\Capsule\Manager as DB;

require_once 'Loader.php';

add_hook('AdminAreaPage', 1, function($vars) {
    if(isset($_POST['ajax']) && $_POST['ajax'] == 'generateCF')
    {
        try{
            
            $api = new App\Libs\MaxiHostAPI($_POST);
            $response = $api->getAllOS();

            $customfield_hostname = DB::table('tblcustomfields')->where('type', 'product')->where('relid', $_POST['pid'])->where('fieldname', 'like', 'hostname|%')->first();
            
            if(empty($customfield_hostname))
            {
                DB::table('tblcustomfields')->insert([
                    'type' => 'product',
                    'relid' => $_POST['pid'],
                    'fieldname' => 'hostname|Hostname',
                    'fieldtype' => 'text',
                    'required' => 'on',
                    'showorder' => 'on'
                ]);
            }
            else
            {
                DB::table('tblcustomfields')->where('type', 'product')->where('relid', $_POST['pid'])->where('fieldname', 'like', 'hostname|%')->update([
                    'type' => 'product',
                    'relid' => $_POST['pid'],
                    'fieldname' => 'hostname|Hostname',
                    'fieldtype' => 'text',
                    'required' => 'on',
                    'showorder' => 'on'
                ]);
            }
 
            $customfield_os_maxihost = DB::table('tblcustomfields')->where('type', 'product')->where('relid', $_POST['pid'])->where('fieldname', 'like', 'os_maxihost|%')->first();
            
            if(empty($customfield_os_maxihost))
            {
                DB::table('tblcustomfields')->insert([
                    'type' => 'product',
                    'relid' => $_POST['pid'],
                    'fieldname' => 'os_maxihost|Operating System',
                    'fieldtype' => 'dropdown',
                    'fieldoptions' => implode(',', $response),
                    'required' => 'on',
                    'showorder' => 'on'
                ]);
            }
            else
            {
                DB::table('tblcustomfields')->where('type', 'product')->where('relid', $_POST['pid'])->where('fieldname', 'like', 'os_maxihost|%')->update([
                    'type' => 'product',
                    'relid' => $_POST['pid'],
                    'fieldname' => 'os_maxihost|Operating System',
                    'fieldtype' => 'dropdown',
                    'fieldoptions' => implode(',', $response),
                    'required' => 'on',
                    'showorder' => 'on'
                ]);
            }
            
            $customfield_deviceID = DB::table('tblcustomfields')->where('type', 'product')->where('relid', $_POST['pid'])->where('fieldname', 'like', 'device_id|%')->first();
            
            if(empty($customfield_deviceID))
            {
                DB::table('tblcustomfields')->insert([
                    'type' => 'product',
                    'relid' => $_POST['pid'],
                    'fieldname' => 'device_id|Device Id',
                    'fieldtype' => 'text',
                    'required' => '',
                    'showorder' => ''
                ]);
            }
            else
            {
                DB::table('tblcustomfields')->where('type', 'product')->where('relid', $_POST['pid'])->where('fieldname', 'like', 'device_id|%')->update([
                    'type' => 'product',
                    'relid' => $_POST['pid'],
                    'fieldname' => 'device_id|Device Id',
                    'fieldtype' => 'text',
                    'required' => '',
                    'showorder' => ''
                ]);
            }

            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success']);
            die();
            
        } catch (\Exception $e) {
            
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
            die();
            
        }
    }
});

add_hook('ClientAreaHeadOutput', 1, function($vars) {
    
    if($vars['filename'] == 'clientarea' && $_GET['action'] = 'productdetails' && isset($_GET['id']))
    {
        return <<<HTML
            <script type="text/javascript">
                $(document).ready(function(){
                   $('#domain > .row').remove();
                });
            </script>
HTML;
    }
    
    
    if($vars['filename'] == 'cart' && $_GET['a'] = 'confproduct' && isset($_GET['i']))
    {
        try{
        
        $productId = $_SESSION['cart']['products'][$_GET['i']]['pid'];
        
        if(empty($productId))
        {
            return;
        }
        
        $product = DB::table('tblproducts')->where('id', $productId)->where('servertype', 'maxihost')->first();
        
        if(!isset($product->configoption2) || empty($product->configoption2) || !isset($product->servergroup) || empty($product->servergroup))
        {
            return;
        } 
        
        $planSlug = $product->configoption2;
            
        $servergroup = DB::table('tblservergroupsrel')->where('groupid', $product->servergroup)->first();
        
        if(!isset($servergroup->serverid) || empty($servergroup->serverid))
        {
            return;
        } 
        
        $server = DB::table('tblservers')->where('id', $servergroup->serverid)->first();
        
        $params = [
            'serversecure' => isset($server->secure) && $server->secure == 'on' ? '1' : '',
            'serverhostname' => isset($server->hostname) ? $server->hostname : '',
            'serveraccesshash' => isset($server->accesshash) ? $server->accesshash : ''
        ];
        
        $api = new App\Libs\MaxiHostAPI($params);
        $planDetails = $api->searchPlan($planSlug);
        
        if($planDetails === false)
        {
            return;
        }
        
        $lang = App\Libs\Lang::getLang();
        
        $drivesHTML = '';
        if(isset($planDetails['specs']['drives']) && !empty($planDetails['specs']['drives']))
        {
            $drivesHTML = '<h4>'.$lang['configproduct']['drives'].'</h4>';
            foreach($planDetails['specs']['drives'] as $drives)
            {
                $drivesHTML .= '<p>'.$lang['configproduct']['count'].': '.$drives['count'].'</p>';
                $drivesHTML .= '<p>'.$lang['configproduct']['size'].': '.$drives['size'].'</p>';
                $drivesHTML .= '<p>'.$lang['configproduct']['type'].': '.$drives['type'].'</p>';
            }
        }
        
        $regionsHTML = '';
        if(isset($planDetails['specs']['drives']) && !empty($planDetails['specs']['drives']))
        {
            $regionsHTML = '<h4>'.$lang['configproduct']['regions'].'</h4>';
            foreach($planDetails['regions'] as $regions)
            {
                $stockText = $lang['configproduct']['outofstock'];
                if($regions['in_stock'] == '1')
                {
                    $stockText = $lang['configproduct']['instock'];
                }
                
                $regionsHTML .= '<p>'.$lang['configproduct']['name'].': '.$regions['name'].'</p>';
                $regionsHTML .= '<p>'.$lang['configproduct']['code'].': '.$regions['code'].'</p>';
                $regionsHTML .= '<p>'.$lang['configproduct']['city'].': '.$regions['city'].'</p>';
                $regionsHTML .= '<p>'.$lang['configproduct']['country'].': '.$regions['country'].'</p>';
                $regionsHTML .= '<p>'.$lang['configproduct']['stock'].': '.$stockText.'</p>';
            }
        }
        
        return <<<HTML
            <script type="text/javascript">
                $(document).ready(function(){
                    $('.product-info').append('<p>{$lang['configproduct']['cpucount']}: {$planDetails['specs']['cpus']['count']}</p><p>{$lang['configproduct']['cputype']}: {$planDetails['specs']['cpus']['type']}</p><p>CPU Cores: {$planDetails['specs']['cpus']['cores']}</p><p>{$lang['configproduct']['cpuclock']}: {$planDetails['specs']['cpus']['clock']}</p><p>{$lang['configproduct']['memory']}: {$planDetails['specs']['memory']['total']}</p>{$drivesHTML}{$regionsHTML}');
                });
            </script>
HTML;
                    
        } catch (\Exception $e) {
     
            return;
            
        }                       
    }
});