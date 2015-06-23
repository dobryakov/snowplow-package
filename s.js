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
