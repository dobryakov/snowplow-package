/* SnowPlow activity tracker setup */

;(function(p,l,o,w,i,n,g){if(!p[i]){p.GlobalSnowplowNamespace=p.GlobalSnowplowNamespace||[];
p.GlobalSnowplowNamespace.push(i);p[i]=function(){(p[i].q=p[i].q||[]).push(arguments)
};p[i].q=p[i].q||[];n=l.createElement(o);g=l.getElementsByTagName(o)[0];n.async=1;
n.src=w;g.parentNode.insertBefore(n,g)}}(window,document,"script","http://t.adaliska.com/s/sp.js","snowplow"));

window.snowplow('newTracker', 'cf', 't.adaliska.com/s', { // Initialise a tracker
  appId: window.location.hostname,
  cookieDomain: null
});

window.snowplow('enableActivityTracking', 15, 30);
window.snowplow('enableLinkClickTracking', {'whitelist': ['tracked', 'trackable']});
window.snowplow('trackPageView');

/*
* Function to extract the Snowplow user ID from the first-party cookie set by the Snowplow JavaScript Tracker
*
* @param string cookieName (optional) The value used for "cookieName" in the tracker constructor argmap
* (leave blank if you did not set a custom cookie name)
*
* @return string or bool The ID string if the cookie exists or false if the cookie has not been set yet
*/
function getSnowplowDuid(cookieName) {
    cookieName = cookieName || '_sp_';
    var matcher = new RegExp(cookieName + 'id\\.[a-f0-9]+=([^;]+);');
    var match = document.cookie.match(matcher);
    if (match && match[1]) {
        return match[1].split('.')[0];
    } else {
        return false;
    }
}

var SnowplowUser = {};

/*
 * Get full info about current snowplow user
 */
jQuery(document).ready(function(){

    $.ajax({
        url: 'http://t.adaliska.com/s/api/user/' + getSnowplowDuid() + '.json',
        type: 'GET',
        data: {},
        success: function (result) {
            //console.log(result);
            window.SnowplowUser = result;
            $(document).trigger({
                type: "SnowplowUserLoaded",
                user: result
            });
        }
    });

    /*
    // example usage:
    $(document).on("SnowplowUserLoaded", {}, function (data){
        console.log(data);
        console.log(data.user);
    });
    */

});
