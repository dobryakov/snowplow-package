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
