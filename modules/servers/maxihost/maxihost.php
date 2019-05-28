<?php

if (!defined("WHMCS"))
{
    die("This file cannot be accessed directly");
}
if (!defined('DS'))
{
    define('DS', DIRECTORY_SEPARATOR);
}

use Illuminate\Database\Capsule\Manager as DB;

function maxihost_MetaData()
{
    return array(
        'DisplayName' => 'Maxihost',
    );
}

function maxihost_ConfigOptions($params)
{   
    //if($_REQUEST['action'] == 'save') return true;
    require_once 'Loader.php';
    
    try{
    
        $pid = $_REQUEST['id'];
        $servergroupId = $_REQUEST['servergroup'];
        $servergroup = DB::table('tblservergroupsrel')->where('groupid', $servergroupId)->first();
        $server = (array)DB::table('tblservers')->where('id', $servergroup->serverid)->first();

        $vars = [
            'serverhostname' => $server['hostname'],
            'serveraccesshash' => $server['accesshash'],
            'serversecure' => $server['secure'] == 'on' ? '1' : ''
        ];

        $api = new App\Libs\MaxiHostAPI($vars);
        $response = $api->getAllPlansType();

        $script = "<script>jQuery(document).ready(function(){
                    jQuery('table.module-settings #custom-alert-module-settings').remove();
                    jQuery('body').on('click', '#generateCF', function(){
                        
                        jQuery.ajax({
                            type:'POST',
                            data: {
                                ajax:'generateCF',
                                serverhostname: '{$vars['serverhostname']}',
                                serveraccesshash: '{$vars['serveraccesshash']}',
                                serversecure: '{$vars['serversecure']}',
                                pid: '$pid'
                            },
                            beforeSend: function() {
                                jQuery('#generateCF').text('Loading...');
                                jQuery('#generateCF').attr('disabled', true);
                            },
                            complete: function(data) {
                                jQuery('#generateCF').text('Generate custom fields');
                                jQuery('#generateCF').attr('disabled', false);
                                var res = JSON.parse(data.responseText);
                                if(res.status == 'success')
                                {
                                    alert('Custom fields was generated successfully.');
                                    window.location.href = 'configproducts.php?action=edit&id=$pid&tab=4';
                                }
                                else
                                {
                                    alert('Error: '+res.msg);
                                }
                            },
                            dataType: 'jsonp',
                        });

                    });

                   });</script>";        
        return [

            "facility" => [
                "FriendlyName" => "Facility",
                "Type" => "dropdown",
                "Options" => ['mh1' => 'mh1'],
                "Description" => "Select a Facility"
            ],

            "plan" => [
                "FriendlyName" => "Plan",
                "Type" => "dropdown",
                "Options" => $response,
                "Description" => "Select a Plan"
            ],
            
            "customfields" => [
                "FriendlyName" => "Custom Fields",
                "Description" => "<button id='generateCF' type='button' class='btn btn-success'>Generate custom fields</button>".$script
            ]
                
        ];
        
    } catch (\Exception $e) {
        
        $data['content'].= '<div id="custom-alert-module-settings" class="alert alert-danger"><strong><span class="title">' . $e->getMessage() . '</span></strong></div>';
        $data['ex'] = print_r($e, true);
           
        ob_clean();
        header('Content-Type: application/json');

        echo json_encode($data);
        die();
        
    }
}

function maxihost_CreateAccount(array $params)
{
    require_once 'Loader.php';
    $module = new \App\Controllers\Admin\ModuleActions();
    return $module->create($params);
}

function maxihost_SuspendAccount(array $params)
{
    require_once 'Loader.php';
    $module = new \App\Controllers\Admin\ModuleActions();
    return $module->suspend($params);
}

function maxihost_UnsuspendAccount(array $params)
{
    require_once 'Loader.php';
    $module = new \App\Controllers\Admin\ModuleActions();
    return $module->unsuspend($params);
}

function maxihost_TestConnection(array $params)
{
    require_once 'Loader.php';
    $module = new \App\Controllers\Admin\ModuleActions();
    return $module->testConnection($params);
}

function maxihost_ClientArea(array $params)
{
    require_once 'Loader.php';
    
    global $smarty;
    
    $lang      = App\Libs\Lang::getLang();
    $moduledir = substr(dirname(__FILE__), strlen(ROOTDIR) + 1);
    
    $smarty->assign('lang', $lang['mainsite']);
    $smarty->assign('dir', $moduledir);
    $smarty->assign('params', $params);
    
    try {

        if($params['status'] != 'Active')
        {
            return true;
        }
        
        $startdate_enddate = '';
        
        if(isset($_GET['startdate']) && !empty($_GET['startdate']) && isset($_GET['enddate']) && !empty($_GET['enddate']))
        {
            $startdate_enddate = '?period=custom&start_time='.$_GET['startdate'].'&end_time='.$_GET['enddate'];
        }
        
        $api = new \App\Libs\MaxiHostAPI($params);
        
        $api->sendGET('devices/'.$params['customfields']['device_id']);
        $api->isSuccess();
        $deviceParams = $api->getResponseArray();
        
        $ipsList = $api->getAllIPs($params['customfields']['device_id']);
        
        $api->sendGET('devices/'.$params['customfields']['device_id'].'/bandwidth'.$startdate_enddate);
        $api->isSuccess();
        $bandwidth = $api->getResponseArray();

        $smarty->assign('ipsList', $ipsList);
        $smarty->assign('deviceParams', $deviceParams);
        $smarty->assign('bandwidth', $bandwidth['bandwidth']);

        $smarty->assign('serviceId', $params['serviceid']);

    } catch (Exception $ex) {
        
        $smarty->assign('error_msg', $ex->getMessage());
        
    }
}

function maxihost_ClientAreaCustomButtonArray($params)
{
    if($params['status'] == 'Active' && !empty($params['customfields']['device_id']))
    {
        $buttonarray = [
            "Power Management" => "management",
        ];

        return $buttonarray;
    }
}

function maxihost_management($params)
{
    require_once 'Loader.php';
    $lang      = App\Libs\Lang::getLang();
    $moduledir = substr(dirname(__FILE__), strlen(ROOTDIR) + 1);
    
    if($params['status'] != 'Active')
    {
        return true;
    }
    
    $page = (isset($_GET['page']) ? preg_replace('/[^A-Za-z0-9]/', '', $_GET['page']) : 'powermanagement');

    $vars['main_lang'] = $lang['mainsite'];
    $vars['lang']      = $lang[(empty($page) ? 'mainsite' : $page)];
    $vars['dir']       = $moduledir;
    $vars['hostname']  = (!empty($params['serverhostname']) ? $params['serverhostname'] : $params['serverip']);
    $vars['params']    = $params;
    $vars['main_dir']  = dirname(__FILE__);

    $vars['powersession'] = [];
    if(isset($_SESSION['powermanagement']))
    {
        $vars['powersession'] = $_SESSION['powermanagement'];
        unset($_SESSION['powermanagement']);
    }
    
    $api = new \App\Libs\MaxiHostAPI($params);
    
    try {
        
        $deviceid = isset($params['customfields']['device_id']) && !empty($params['customfields']['device_id']) ? $params['customfields']['device_id'] : 'null';
        
        $api->sendGET('devices/'.$deviceid);
        $api->isSuccess();
        $vars['deviceParams'] = $api->getResponseArray();
        
    } catch (\Exception $ex) {
        
        $vars['error'] = $ex->getMessage();
        
    }
    
    if(isset($_GET['powermanagement']) && $_GET['powermanagement'] == '1')
    {
        try{
            
            $data = [
                'type' => $_GET['actionpm']
            ];
            
            $api->sendPUT('devices/'.$params['customfields']['device_id'].'/actions', $data);
            $api->isSuccess();
            
            sleep(10);
            
            if($_GET['actionpm'] == 'power_on')
            {
                $msglang = $vars['lang']['alert_power_on'];
            }
            
            if($_GET['actionpm'] == 'power_off')
            {
                $msglang = $vars['lang']['alert_power_off'];
            }
            
            if($_GET['actionpm'] == 'power_cycle')
            {
                $msglang = $vars['lang']['alert_power_cycle'];
            }
            
            $_SESSION['powermanagement'] = [
                'status' => 'success',
                'msg' => $msglang
            ];
            
        
        } catch (\Exception $e) {
            
            $_SESSION['powermanagement'] = [
                'status' => 'error',
                'msg' => $e->getMessage()
            ];
            
        }
        
        redir("action=productdetails&id={$params['serviceid']}&modop=custom&a=management", "clientarea.php");
    }

    return [
        'templatefile' => 'templates/' . $page,
        'breadcrumb'   => '<a href="#">Server Details</a>',
        'vars'         => $vars,
    ];
}

function maxihost_AdminServicesTabFields($params) {
    require_once 'Loader.php';
    $lang      = App\Libs\Lang::getLang();
        
    if($params['status'] == 'Active' && empty($params['customfields']['device_id']))
    {
        return [
            'Status' => '<span class="label label-info">'.$lang['admin']['in_progress'].'</span>'
        ];
    }

    if($params['status'] != 'Active')
    {
        return true;
    }
    
    try{
        
        $api = new \App\Libs\MaxiHostAPI($params);
        
        $startdate_enddate = '';
        
        if(isset($_GET['startdate']) && !empty($_GET['startdate']) && isset($_GET['enddate']) && !empty($_GET['enddate']) && $_GET['enddate'] != 'undefined' && $_GET['startdate'] != 'undefined')
        {
            $startdate_enddate = '?period=custom&start_time='.$_GET['startdate'].'&end_time='.$_GET['enddate'];
        }
        
        $api->sendGET('devices/'.$params['customfields']['device_id']);
        $api->isSuccess();
        $deviceParams = $api->getResponseArray();
        
        $powerstatus =  '';
        
        if($deviceParams['power_status'])
        {
            $powerstatus =  '<span class="label label-success">'.$lang['admin']['on'].'</span>';
        }
        else
        {
            $powerstatus =  '<span class="label label-danger">'.$lang['admin']['off'].'</span>';
        }
        
        
        $ipsList = $api->getAllIPs($params['customfields']['device_id']);
        $ipsHTML = '';
        if(!empty($ipsList))
        {
            foreach($ipsList as $ip)
            {
                $ipsHTML .= '<tr>
                                <td>'.$ip['ip_address'].'</td>
                                <td>'.$ip['ip_description'].'</td>
                                <td>'.$ip['device_description'].'</td>
                                <td>'.$ip['device_label'].'</td>
                            </tr>';
            }
        }
        else
        {
            $ipsHTML = '<tr><td colspan="4">'.$lang['admin']['noipsfound'].'</td></tr>';
        }
            
        
        
        $api->sendGET('devices/'.$params['customfields']['device_id'].'/bandwidth'.$startdate_enddate);
        $api->isSuccess();
        $bandwidth = $api->getResponseArray();
        

        return [
            
            $lang['admin']['serverlocationdetails'] => '<p>'.$lang['admin']['facilityname'].': <b>'.$deviceParams['location']['facility_name'].'</b></p>
                                        <p>'.$lang['admin']['facilitycode'].': <b>'.$deviceParams['location']['facility_code'].'</b></p>
                                        <p>'.$lang['admin']['facilitycountry'].': <b>'.$deviceParams['location']['facility_country'].'</b></p>
                                        <p>'.$lang['admin']['rowname'].': <b>'.$deviceParams['location']['row_name'].'</b></p>
                                        <p>'.$lang['admin']['rackname'].': <b>'.$deviceParams['location']['rack_name'].'</b></p>
                                        <p>'.$lang['admin']['rackposition'].': <b>'.$deviceParams['location']['rack_position'].'</b></p>',
            
            $lang['admin']['label'] => '<span class="label label-info">'.$deviceParams['label'].'</span>',
            
            $lang['admin']['powerstatus'] => $powerstatus,
            
            $lang['admin']['specs'] => '<p>'.$lang['admin']['cpu'].': <b>'.$deviceParams['specs']['cpu'].'</b></p>
                        <p>'.$lang['admin']['disk'].': <b>'.$deviceParams['specs']['disk'].'</b></p>
                        <p>'.$lang['admin']['ram'].': <b>'.$deviceParams['specs']['ram'].'</b></p>',
            
            $lang['admin']['bandwidthusage'] => '<script>jQuery(document).ready(function(){
                                jQuery(".datepickerModule").datepicker({
                                    dateFormat: "yy-mm-dd"
                                });
                                jQuery("body").on("click", "#getBandwidth", function(){
                                    var startdate = jQuery(this).parent(".form-group").parent(".row").find("input[name=\"startdate\"]").val();
                                    var enddate = jQuery(this).parent(".form-group").parent(".row").find("input[name=\"enddate\"]").val();

                                    var url = window.location.href+"&startdate="+startdate+"&enddate="+enddate;
                                    
                                    window.location.href = url;

                                });
                            });</script>
                            <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
                            <form method="GET" class="pull-left" style="margin-right: 10px; width: 100%;">
                                <div class="row">
                                    <div class="form-group col-sm-4 col-xs-12">
                                        <label for="startdate">'.$lang['admin']['startdate'].'</label>
                                        <input type="text" autocomplete="off" name="startdate" id="startdate" placeholder="Select start date" class="form-control datepickerModule" value="'.$_GET['startdate'].'">
                                    </div>
                                    <div class="form-group col-sm-4 col-xs-12">
                                        <label for="enddate">'.$lang['admin']['enddate'].'</label>
                                        <input type="text" autocomplete="off" name="enddate" id="enddate" placeholder="Select end date" class="form-control datepickerModule" value="'.$_GET['enddate'].'">
                                    </div>
                                    <div class="form-group col-sm-3 col-xs-12">
                                        <div style="margin-bottom:25px;"></div>
                                        <button id="getBandwidth" type="button" class="btn btn-success">'.$lang['admin']['show'].'</button>
                                    </div>
                                </div>
                            </form><img src="data:image/gif;base64,' . $bandwidth['bandwidth'] . '" class="img-responsive" />',
            $lang['admin']['ipslist'] => '<table  class="datatable" width="100%" border="0">
                                <tbody>
                                    <tr>
                                        <th>'.$lang['admin']['ipaddress'].'</th>
                                        <th>'.$lang['admin']['ipdescription'].'</th>
                                        <th>'.$lang['admin']['devicedescription'].'</th>
                                        <th>'.$lang['admin']['devicelabel'].'</th>
                                    </tr>
                                    '.$ipsHTML.'
                                </tbody>
                            </table>',
        ];
        
    } catch(\Exception $e){
        return [
            'Error' => '<span class="label label-danger">'.$e->getMessage().'</span>'
        ];
    } 
    
}

