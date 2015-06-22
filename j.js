
/* SnowPlow jQuery additional functions */
/* make sure to include jQuery in your HTML code! */

jQuery(document).ready(function() {

  $('a.trackable-link').click(function(event){

    var target  = $(event.target).closest('a');
    console.log(target);

    var data = {
      'category' : typeof target.attr('snowplow-category') == 'undefined' ? 'link'              : target.attr('snowplow-category'),
      'action'   : typeof target.attr('snowplow-action')   == 'undefined' ? 'click'             : target.attr('snowplow-action'),
      'label'    : typeof target.attr('snowplow-label')    == 'undefined' ? target.attr('id')   : target.attr('snowplow-label'),
      'property' : typeof target.attr('snowplow-property') == 'undefined' ? 'url'               : target.attr('snowplow-property'),
      'value'    : typeof target.attr('snowplow-value')    == 'undefined' ? target.attr('href') : target.attr('snowplow-value')
    };

    console.log(data);

    if ( typeof snowplow !== 'undefined' ) {
      snowplow('trackStructEvent', data.category, data.action, data.label, data.property, data.value);
    }

  });

});
