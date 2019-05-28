<link rel="stylesheet" href="http://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<div id ="ca" style="text-align: left;">
    {if $error_msg}
        {if $params.status == 'Active' && empty($params.customfields.device_id)}
            <div class="alert alert-info">
                Status: {$lang.in_progress}
            </div>
        {else}
            <div id="vm_alerts" class="well mx-1 alert alert-danger">
                <div class="alert alert-danger">
                    {$error_msg}
                </div>
            </div>
        {/if}
    {else}
        <div class="power-info" style="margin-bottom: 30px;">
            <h4>{$lang.powerstatus} 
                {if $deviceParams.power_status}
                    <span class="label label-success">{$lang.on}</span>
                {else}
                    <span class="label label-danger">{$lang.off}</span>
                {/if}
            </h4>
        </div>
        
        <div class="specification-info" style="margin-bottom: 30px;">
            <h4>{$lang.specs}</h4>
            <p>{$lang.cpu}: <b>{$deviceParams.specs.cpu}</b></p>
            <p>{$lang.disk}: <b>{$deviceParams.specs.disk}</b></p>
            <p>{$lang.ram}: <b>{$deviceParams.specs.ram}</b></p>
        </div>
        
        <div class="bandwidth-usage-info" style="margin-bottom: 30px; overflow: hidden;">
            <h4>{$lang.bandwidthusage}</h4>
            <form method="GET" class="pull-left" style="margin-right: 10px; width: 100%;">
                <input type="hidden" name="action" value="productdetails">
                <input type="hidden" name="id" value="{$params.serviceid}">
                <div class="row">
                    <div class="form-group col-sm-4 col-xs-12">
                        <label for="startdate">{$lang.startdate}</label>
                        <input type="text" name="startdate" id="startdate" autocomplete="off" placeholder="Select start date" class="form-control datepickerModule" value="{$smarty.get.startdate}">
                    </div>
                    <div class="form-group col-sm-4 col-xs-12">
                        <label for="enddate">{$lang.enddate}</label>
                        <input type="text" name="enddate" id="enddate" autocomplete="off" placeholder="Select end date" class="form-control datepickerModule" value="{$smarty.get.enddate}">
                    </div>
                    <div class="form-group col-sm-3 col-xs-12">
                        <div style="margin-bottom:25px;"></div>
                        <button class="btn btn-success">{$lang.show}</button>
                    </div>
                </div>
            </form>     
            <img src="data:image/gif;base64,{$bandwidth}" class="img-responsive" />
        </div>
        
        <div class="ip-list-info" style="margin-bottom: 30px;">
            <h4>{$lang.ipslist}</h4>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>{$lang.ipaddress}</th>
                        <th>{$lang.ipdescription}</th>
                        <th>{$lang.devicedescription}</th>
                        <th>{$lang.devicelabel}</th>
                    </tr>
                </thead>
                <tbody>
                    {if $ipsList}
                        {foreach $ipsList as $ip}
                            <tr>
                                <td>{$ip.ip_address}</td>
                                <td>{$ip.ip_description}</td>
                                <td>{$ip.device_description}</td>
                                <td>{$ip.device_label}</td>
                            </tr>
                        {/foreach}
                    {else}
                        <td colspan="4">{$lang.noipsfound}</td>
                    {/if}
                </tbody>
            </table>
        </div>
    {/if}           
</div>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
    jQuery(document).ready(function(){
        jQuery(".datepickerModule").datepicker({
            dateFormat: "yy-mm-dd"
        });
    });
</script>
