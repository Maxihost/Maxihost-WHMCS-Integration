<?php
namespace App\Libs;

class MaxiHostAPI {
    
    protected $url;
    protected $token;
    private $curl_info = null;
    private $response = null;
    private $request = null;
    private $responseAll = null;


    public function __construct($params) { 
        $this->url = $params['serversecure'] == '1' ? 'https://'.$params['serverhostname'].'/' : 'http://'.$params['serverhostname'].'/';
        $this->token = $params['serveraccesshash'];
    }
    
    public function sendGET($action)
    {
        $this->callAPI('GET', $action);
    }
    
    public function sendPOST($action, $data)
    {
        $this->callAPI('POST', $action, $data);
    }
    
    public function sendPUT($action, $data)
    {
        $this->callAPI('PUT', $action, $data);
    }

    public function sendDELETE($action)
    {
        $this->callAPI('DELETE', $action);
    }
    
    public function testConnection()
    {
        $this->callAPI('GET');
        $this->isSuccess();
    }
    
    private function callAPI($method = 'POST', $action = 'devices', $data = [])
    {
        $setup = [
            'token' => $this->token,
            'base_uri' => $this->url.$action
        ];
        
        $headers = [
            'Authorization: Bearer '.$setup['token'],
            'Accept:application/json'
        ];
        
        $url = $setup['base_uri'];
        $ch = curl_init($url);
        
        $jsondata = json_encode($data);
        
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,   $method);
        if(in_array($method, ['POST', 'PUT']) &&  strlen($jsondata) > 0)
        {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);
            $headers[]    = 'Content-Length:    '.strlen($jsondata);
        }
        
        $headers[] = 'Content-Type: application/json';
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $resp = curl_exec($ch);
        
        $this->responseAll = $resp;
        
        $curl_info = $this->curl_info = curl_getinfo($ch);
        $result = substr($resp, $curl_info['header_size']);
        
        $this->request = $curl_info['request_header'].$jsondata;
        
        logModuleCall('MaxiHost', $action, $curl_info['request_header'].$jsondata, $resp);
          
        if (curl_errno($ch) > 0) 
        { 
            throw new \Exception(curl_error($ch));
        } 
        curl_close($ch);
        if ($result === FALSE) {
            throw new \Exception("cURL call failed");
        } else {
            $this->response = $result;
        }
    }
    
    public function isSuccess()
    {
        if($this->curl_info['http_code'] == '204')
        {
            return true;
        }
        
        $response = $this->getResponse();
        if($response == 'true')
        {
            return true;
        }
        $arrayJSON = json_decode($response, true);
        if($arrayJSON === NULL)
        {
            throw new \Exception('Invalid response format');
        }
        
        if(isset($arrayJSON['status']) && $arrayJSON['status'] === false)
        {
            $errorMSG = '';
            if(isset($arrayJSON['error_messages']) && !empty($arrayJSON['error_messages']))
            {
                foreach($arrayJSON['error_messages'] as $errorType)
                {
                    foreach($errorType as $error)
                    {
                        $errorMSG .= $error.' ';
                    }
                }
            }
            
            if(empty($errorMSG))
            {
                throw new \Exception('Invalid response format');
            }
            
            throw new \Exception($errorMSG);
        }
        return true;
    }
    
    public function getResponse()
    {
        return $this->response;
    }
    
    public function getResponseArray()
    {
        $response = $this->getResponse();
        $arrayJSON = json_decode($response, true);
        return $arrayJSON;
    }
    
    public function getRequestResponse()
    {
        return array(
            'request' => $this->request,
            'response' => $this->responseAll
        );
    }
    
    public function getAllPlans()
    {
        $plans = [];
        $page = 1;
        $limit = 10;
        
        do{
            
            $this->sendGET('plans?page='.$page.'&limit='.$limit);
            $lists = $this->getResponseArray();
            $this->isSuccess();
       
            foreach($lists['servers'] as $plan)
            {
                $plans[$plan['slug']] = $plan['slug'];
            }
            
            $page++;
            
            
        } while ($lists['meta']['pages']['total'] >= $page);
        
        return $plans;        
    }
    
    public function getAllPlansType()
    {
        $plans = [];
        $page = 1;
        $limit = 10;
        
        do{
            
            $this->sendGET('plans?page='.$page.'&limit='.$limit);
            $lists = $this->getResponseArray();
            $this->isSuccess();
            
            foreach($lists['servers'] as $plan)
            {
                if (strpos($plan['slug'], 'type') !== false) {
                    $plans[$plan['slug']] = $plan['slug'];
                }
            }
            
            $page++;

        } while ($lists['meta']['pages']['total'] >= $page);
        
        return $plans;        
    }
    
    public function searchPlan($slug)
    {
        $page = 1;
        $limit = 10;
        
        do{
            
            $this->sendGET('plans?page='.$page.'&limit='.$limit);
            $lists = $this->getResponseArray();
            $this->isSuccess();
       
            foreach($lists['servers'] as $plan)
            {
                if($plan['slug'] == $slug)
                {
                    return $plan;
                }
            }
            
            $page++;
            
            
        } while ($lists['meta']['pages']['total'] >= $page);
        
        return false;        
    }
    
    public function getAllOS()
    {
        $os = [];
        $page = 1;
        $limit = 10;
        
        do{
            
            $this->sendGET('plans/operating-systems?page='.$page.'&limit='.$limit);
            $lists = $this->getResponseArray();
            $this->isSuccess();
       
            foreach($lists['operating-systems'] as $system)
            {
                $os[] = $system['slug'].'|'.$system['name'];
            }
            
            $page++;
            
            
        } while ($lists['meta']['pages']['total'] >= $page);
        
        return $os;        
    }
    
    public function getAllIPs($deviceid)
    {
        $ips = [];
        $page = 1;
        $limit = 10;
        
        do{
            
            $this->sendGET('devices/'.$deviceid.'/ips?page='.$page.'&limit='.$limit);
            $lists = $this->getResponseArray();
            $this->isSuccess();
       
            foreach($lists['ips'] as $ip)
            {
                $ips[] = $ip;
            }
            
            $page++;
            
            
        } while ($lists['meta']['pages']['total'] >= $page);
        
        return $ips;        
    }
    
    public function searchInAllDevice($domain)
    {
        $page = 1;
        $limit = 10;
        
        do{
            
            $this->sendGET('devices?page='.$page.'&limit='.$limit);
            $lists = $this->getResponseArray();
            $this->isSuccess();
       
            foreach($lists['devices'] as $device)
            {
                
                if($device['description'] == $domain)
                {
                    return $device;
                }
                
            }
            
            $page++;
            
            
        } while ($lists['meta']['pages']['total'] >= $page);
        
        return false;        
    }

    
}