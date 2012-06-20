<script type="text/javascript" >
jQuery(document).ready(function($) {
  var data = {
    action: 'load_sidebar_info'
  };
  // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
  jQuery.post(ajaxurl, data, function(response) {
    $.each(response.info, function(index, value) {
      var html = '<div class="postbox"><div title="Click to toggle" class="handlediv"><br></div>';
      html += '<h3 class="hndle"><span>'+value.title+'</span></h3>';
      html += '<div class="inside">';
      html += '<p>'+value.description+'</p>';
      html += '</div></div>';
      $("#wpsecure-info").append(html);
    });
  }, 'json');
});
</script>