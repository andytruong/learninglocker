
  <!-- footer -->
  <?php $site = Site::first(); ?>
  <div id="footer">
    Powered by
    @if( empty($site->footer_sitename) || empty($site->footer_url))
    	<a href="http://adurolms.com" target='_blank'>Aduro LRS</a>
    @else
   		<a href="{{ $site->footer_url }}" target='_blank'>{{ $site->footer_sitename }}</a>
    @endif
  </div>
