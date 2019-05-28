<button class="btn" onclick="window.location='clientarea.php?action=productdetails&id={$id}'">{$lang.back}</button>
<div>
{if $error}
    <div class="alert alert-danger alert-dismissible fade in" role="alert" style="margin-top:30px;"> 
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button> 
        {$error}
    </div>
{else}    
<div style="overflow: auto; margin-top:30px;">
    
    {if $powersession.status == 'success'}
        <div class="alert alert-success alert-dismissible fade in" role="alert"> 
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button> 
            {$powersession.msg}
        </div>
    {/if}
    
    {if $powersession.status == 'error'}
        <div class="alert alert-danger alert-dismissible fade in" role="alert"> 
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button> 
            {$powersession.msg}
        </div>
    {/if}
    
    <h2 style="margin-bottom:35px;">{$lang.powermanagement}</h2>
    
    <div class="power-info" style="margin-bottom: 30px;">
        <h4>{$lang.powerstatus} {if $deviceParams.power_status}<span class="label label-success">{$lang.on}</span>{else}<span class="label label-danger">{$lang.off}</span>{/if}</h4>
    </div>
    
    <div class="power-button" style="margin-bottom: 100px;">
        <form method="GET" class="pull-left" style="margin-right: 10px;">
            <input type="hidden" name="action" value="productdetails">
            <input type="hidden" name="id" value="{$params.serviceid}">
            <input type="hidden" name="modop" value="custom">
            <input type="hidden" name="a" value="management">
            <input type="hidden" name="powermanagement" value="1">
            <input type="hidden" name="actionpm" value="power_on">
            <button {if $deviceParams.power_status}disabled{/if} class="btn btn-success"><span class="glyphicon glyphicon-off" aria-hidden="true"></span> {$lang.poweron}</button>
        </form>
        
        <form method="GET" class="pull-left" style="margin-right: 10px;">
            <input type="hidden" name="action" value="productdetails">
            <input type="hidden" name="id" value="{$params.serviceid}">
            <input type="hidden" name="modop" value="custom">
            <input type="hidden" name="a" value="management">
            <input type="hidden" name="powermanagement" value="1">
            <input type="hidden" name="actionpm" value="power_off">
            <button {if !$deviceParams.power_status}disabled{/if} class="btn btn-danger"><span class="glyphicon glyphicon-off" aria-hidden="true"></span> {$lang.poweroff}</button>
        </form>
        
        <form method="GET" class="pull-left" style="margin-right: 10px;">
            <input type="hidden" name="action" value="productdetails">
            <input type="hidden" name="id" value="{$params.serviceid}">
            <input type="hidden" name="modop" value="custom">
            <input type="hidden" name="a" value="management">
            <input type="hidden" name="powermanagement" value="1">
            <input type="hidden" name="actionpm" value="power_cycle">
            <button {if !$deviceParams.power_status}disabled{/if} class="btn btn-primary"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span> {$lang.powercycle}</button>
        </form>
    </div>
</div>
{/if}
