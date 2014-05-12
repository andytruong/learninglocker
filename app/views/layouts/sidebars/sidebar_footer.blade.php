
  <!-- footer -->
  <?php $site = Site::first(); ?>
  <div id="footer">
    Powered by
    @if( isset($site) && ($site->footer_sitename === '' || $site->footer_url === '' ))
    	<a href="http://adurolms.com" target='_blank'>Aduro LRS</a>
    @else
   		<a href="{{ $site->footer_url }}" target='_blank'>{{ $site->footer_sitename }}</a>
    @endif
  </div>
