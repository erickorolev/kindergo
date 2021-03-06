{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{strip}
<!DOCTYPE html>
<html>
	<head>
		<title>{vtranslate($PAGETITLE, $QUALIFIED_MODULE)}</title>
        <link rel="SHORTCUT ICON" href="layouts/v7/skins/images/favicon.ico">
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

		<link type='text/css' rel='stylesheet' href='layouts/v7/lib/todc/css/bootstrap.min.css'>
		<link type='text/css' rel='stylesheet' href='layouts/v7/lib/todc/css/docs.min.css'>
		<link type='text/css' rel='stylesheet' href='layouts/v7/lib/todc/css/todc-bootstrap.min.css'>
		<link type='text/css' rel='stylesheet' href='layouts/v7/lib/font-awesome/css/font-awesome.min.css'>
        <link type='text/css' rel='stylesheet' href='layouts/v7/lib/jquery/select2/select2.css'>
        <link type='text/css' rel='stylesheet' href='layouts/v7/lib/select2-bootstrap/select2-bootstrap.css'>
        <link type='text/css' rel='stylesheet' href='libraries/bootstrap/js/eternicode-bootstrap-datepicker/css/datepicker3.css'>
        <link type='text/css' rel='stylesheet' href='layouts/v7/lib/jquery/jquery-ui-1.11.3.custom/jquery-ui.css'>
        <link type='text/css' rel='stylesheet' href='layouts/v7/lib/vt-icons/style.css'>
        <link type='text/css' rel='stylesheet' href='layouts/v7/lib/animate/animate.min.css'>
        <link type='text/css' rel='stylesheet' href='layouts/v7/lib/jquery/malihu-custom-scrollbar/jquery.mCustomScrollbar.css'>
        <link type='text/css' rel='stylesheet' href='layouts/v7/lib/jquery/jquery.qtip.custom/jquery.qtip.css'>
        <link type='text/css' rel='stylesheet' href='layouts/v7/lib/jquery/daterangepicker/daterangepicker.css'>
        {*Salesplatform.ru begin PBXManager porting*}
        <link type='text/css' rel='stylesheet' href='libraries/jquery/pnotify/jquery.pnotify.default.css'>
        {*Salesplatform.ru end PBXManager porting*}
        <input type="hidden" id="inventoryModules" value={ZEND_JSON::encode($INVENTORY_MODULES)}>
        
        {assign var=V7_THEME_PATH value=Vtiger_Theme::getv7AppStylePath($SELECTED_MENU_CATEGORY)}
        {if strpos($V7_THEME_PATH,".less")!== false}
            <link type="text/css" rel="stylesheet/less" href="{vresource_url($V7_THEME_PATH)}" media="screen" />
        {else}
            <link type="text/css" rel="stylesheet" href="{vresource_url($V7_THEME_PATH)}" media="screen" />
        {/if}
        
        {foreach key=index item=cssModel from=$STYLES}
			<link type="text/css" rel="{$cssModel->getRel()}" href="{vresource_url($cssModel->getHref())}" media="{$cssModel->getMedia()}" />
		{/foreach}
		
		<link type='text/css' rel='stylesheet' href='resources/drive/form.css'>

		{* For making pages - print friendly *}
		<style type="text/css">
            @media print {
            .noprint { display:none; }
		}
		</style>
		<script type="text/javascript">var __pageCreationTime = (new Date()).getTime();</script>
		<script src="{vresource_url('layouts/v7/lib/jquery/jquery.min.js')}"></script>
		<script src="{vresource_url('layouts/v7/lib/jquery/jquery-migrate-1.0.0.js')}"></script>
        
		<script type="text/javascript">
			var _META = { 'module': "{$MODULE}", view: "{$VIEW}", 'parent': "{$PARENT_MODULE}", 'notifier':"{$NOTIFIER_URL}", 'app':"{$SELECTED_MENU_CATEGORY}" };
            {if $EXTENSION_MODULE}
                var _EXTENSIONMETA = { 'module': "{$EXTENSION_MODULE}", view: "{$EXTENSION_VIEW}"};
            {/if}
            var _USERMETA;
            {if $CURRENT_USER_MODEL}
               _USERMETA =  { 'id' : "{$CURRENT_USER_MODEL->get('id')}", 'menustatus' : "{$CURRENT_USER_MODEL->get('leftpanelhide')}", 
                              'currency' : "{$USER_CURRENCY_SYMBOL}", 'currencySymbolPlacement' : "{$CURRENT_USER_MODEL->get('currency_symbol_placement')}",
                          'currencyGroupingPattern' : "{$CURRENT_USER_MODEL->get('currency_grouping_pattern')}", 'truncateTrailingZeros' : "{$CURRENT_USER_MODEL->get('truncate_trailing_zeros')}"};
            {/if}
		</script>
	</head>
	 {assign var=CURRENT_USER_MODEL value=Users_Record_Model::getCurrentUserModel()}
	<body data-skinpath="{Vtiger_Theme::getBaseThemePath()}" data-language="{$LANGUAGE}" data-user-decimalseparator="{$CURRENT_USER_MODEL->get('currency_decimal_separator')}" data-user-dateformat="{$CURRENT_USER_MODEL->get('date_format')}"
          data-user-groupingseparator="{$CURRENT_USER_MODEL->get('currency_grouping_separator')}" data-user-numberofdecimals="{$CURRENT_USER_MODEL->get('no_of_currency_decimals')}" data-user-hourformat="{$CURRENT_USER_MODEL->get('hour_format')}"
          data-user-calendar-reminder-interval="{$CURRENT_USER_MODEL->getCurrentUserActivityReminderInSeconds()}">
            <input type="hidden" id="start_day" value="{$CURRENT_USER_MODEL->get('dayoftheweek')}" />
            {* SalesPlatform.ru begin #5116 fixed localization *} 
            <input type="hidden" name="locale" value='{json_encode($LOCALE)}'> 
            {* SalesPlatform.ru end *} 
		<div id="page">
            <div id="pjaxContainer" class="hide noprint"></div>
            <div id="messageBar" class="hide"></div>
			
			
		<input type="hidden" value="{$CURRENT_DATE}" id="currentDate" />	
		<input type="hidden" value="0" id="countCalendar" />	
		<input type="hidden" value="" id="recordFieldName" />
		<input type="hidden" value="" id="yearTemp" />
		<input type="hidden" value="" id="monthTemp" />
		
		<div id="openCalendar"> 
		<div class="closePanelOpenCalendar"><a href="javascript:closePanel('openCalendar')">??????????????</a></div>
		<div class="containerCalendar"></div>
		<div class="actionButtonCalendar">
			<span id="button1">
			<input type="button" class="actionButton" onclick="saveCalendar()" value="??????????????????" /> 
			</span>
			<span id="button2">
			<input type="button" class="actionButton" onclick="addCalendar(0,'')" id="addNewCalendar" value="???????????????? ??????????????????" /> 
			</span>
			<span id="button3">
			</span>
			{*<a href="javascript:addCalendar()" id="addNewCalendar">????????????????</a>*} 
			
			</div>	
		<div></div>
		
		
		</div>
		
		
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<div id="mapBlock">
			<input type="hidden" name="" id="duration_id" />
			<input type="hidden" name="" id="distance_id" />
			
			<div class="containerForTest">
			????????????: <input type="text" id="idblock" value="" />
			??????????????????: <input type="text" id="distance" value="" />
			????????????????????????: <input type="text" id="duration" value="" />
		
			????????????: <input type="text" id="startpoint" value="" />
			????????: <input type="text" id="endpoint" value="" />
			
			????????????????????1: <input type="text" id="XY1" value="" />
			????????????????????2: <input type="text" id="XY2" value="" />
			</div>
			{*<input type="button" value="save" onclick="setparam()" /> <input type="button" value="cencel" onclick="cancel()" />*}
			<div id="mapcontainer" style="width: 100%; height: 100%">
			<div id="map" style="width: 100%; height: 100%"></div>
			</div>
		</div>
		
		<div id="mapBlockGetCoordinat">
			<input type="button" value="cencel" onclick="cancel()" />
			<div id="mapCoordinat" style="width: 100%; height: 100%"></div>
		</div>
		
		<div id="messageTrip"><div class="mescontainer">?????????????????? ???????????????? ??????????????</div></div>
		
		<script src="https://api-maps.yandex.ru/2.1/?apikey=90c1cdc7-f43b-4d10-b2d6-039db209370e&lang=ru-RU" type="text/javascript">
		</script>
			
