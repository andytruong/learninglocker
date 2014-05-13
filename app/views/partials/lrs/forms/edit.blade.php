{{ Form::model($lrs, array('route' => array('lrs.update', $lrs->_id), 
      'method' => 'PUT', 'class' => 'form-horizontal')) }}

  <div class="form-group">
    {{ Form::label('title', Lang::get('site.title'), array('class' => 'col-sm-2 control-label' )) }}
    <div class="col-sm-10">
      {{ Form::text('title', $lrs->title,array('class' => 'form-control')) }}
    </div>
  </div>

  <div class="form-group">
    {{ Form::label('description', Lang::get('site.description'), array('class' => 'col-sm-2 control-label' )) }}
    <div class="col-sm-10">
      {{ Form::text('description', $lrs->description,array('class' => 'form-control')) }}
    </div>
  </div>
  <div class="col-sm-offset-2 col-sm-10">
    <div class="form-group">
      {{ Form::radio('auth_service', Lrs::ADURO_AUTH_SERVICE, $lrs->auth_service == Lrs::ADURO_AUTH_SERVICE, array('id'=>'aduro-auth-service')) }}
      {{ Form::label('auth-service', Lang::get('lrs.api.label.auth_service')) }}
    </div>
    <div class="form-group">
      {{ Form::radio('auth_service', Lrs::INTERNAL_LRS, $lrs->auth_service == Lrs::INTERNAL_LRS ,array('id'=>'internal-lrs-authentication')) }}
      {{ Form::label('internal', Lang::get('lrs.api.label.internal')) }}
    </div>
  </div>
  <hr>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <p>{{ Form::submit(Lang::get('site.submit'), array('class'=>'btn btn-primary')) }}</p>
    </div>
  </div>

{{ Form::close() }}