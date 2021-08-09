/**
 * Created by Stefan on 08.08.2016.
 */
var OAuthHandler = {
    moduleName:'Workflow2',
    interval:null,
    currentKey:null,
    reloadAfterConnect:false,
    start:function(key, reloadAfterConnect) {
        OAuthHandler.reloadAfterConnect = reloadAfterConnect;
        OAuthHandler.currentKey = key;
        jQuery('#oauth_' + key).html(jQuery('#oauth_' + key).data('text1') + '.');
        jQuery('#oauth_' + key).show();

        OAuthHandler.interval = window.setInterval(OAuthHandler.checkStatus, 2000);

        jQuery.post('index.php', {
            module:OAuthHandler.moduleName,
            parent:"Settings",
            action:'OAuthHandler',
            mode:'GetAuthUrl',
            oauth_key:key
        }, function(response) {
            window.open(response.url);
        }, 'json');
    },
    checkStatus:function() {
        jQuery.post('index.php', {
            module:OAuthHandler.moduleName,
            parent:"Settings",
            action:'OAuthHandler',
            mode:'CheckStatus',
            oauth_key:OAuthHandler.currentKey
        }, function(response) {
            if(response == 'true') {
                window.clearInterval(OAuthHandler.interval);
                jQuery('#oauthbtn_' + OAuthHandler.currentKey).hide();

                jQuery('#oauth_' + OAuthHandler.currentKey).html(jQuery('#oauth_' + OAuthHandler.currentKey).data('text2') + '.').css('color', '#094F00');

                if(OAuthHandler.reloadAfterConnect == true) {
                    window.location.href = window.location.pathname + window.location.search + '&oauth=ok';
                }
                return;
            }
            jQuery('#oauth_' + OAuthHandler.currentKey).html(jQuery('#oauth_' + OAuthHandler.currentKey).html() + '.');
        });
    }
};