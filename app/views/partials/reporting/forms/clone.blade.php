<div class="modal fade" id="cloneForm" tabindex="-1" role="dialog" aria-labelledby="smallModal" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">New report name</h4>
      </div>
      {{ Form::open(array('url' => 'lrs/'.$lrs.'/reporting/clone/'.$report,
          'method' => 'POST', 'class' => 'form-horizontal')) }}
        <div class="modal-body">
           {{ Form::text('reportname', '', array('class'=>'form-control', 'placeholder'=>'Report name')) }}
        </div>
        <div class="modal-footer">
          {{ Form::button('Close', array('class'=>'btn btn-default', 'data-dismiss'=>'modal')) }}
          {{ Form::submit('Save change', array('class'=>'btn btn-primary')) }}
        </div>
      {{ Form::close() }}
    </div>
  </div>
</div>

