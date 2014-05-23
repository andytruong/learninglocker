{{ Form::model($lrs, array('route' => array('lrs.authService', $lrs->_id), 'method' => 'PUT', 'class' => 'form-horizontal lrs-edit')) }}


<div class="form-group">
    {{ Form::label('token', Lang::get('site.token'), array('class' => 'col-sm-2 control-label' )) }}
    <div class="col-sm-10">
        {{ Form::text('token', Input::old('token') ? Input::old('token') : $lrs->token, array('class' => 'form-control')) }}
        <span class="help-block">similar to the token configured in authentication service</span>
    </div>
</div>

<div class="form-group">
    {{ Form::label('auth_cache_time', Lang::get('lrs.auth_cache_time'), array('class' => 'col-sm-2 control-label' )) }}
    <div class="col-sm-10">
        {{ Form::text('auth_cache_time', Input::old('auth_cache_time') ? Input::old('auth_cache_time') : $lrs->auth_cache_time, array('class' => 'form-control')) }}
        <span class="help-block">In minute, default to 15</span>
    </div>
</div>
<div class="form-group">
    {{ Form::label('auth_service_url', Lang::get('lrs.auth_service_url'), array('class' => 'col-sm-2 control-label' )) }}
    <div class="col-sm-10">
        {{ Form::text('auth_service_url', Input::old('auth_service_url') ? Input::old('auth_service_url') : $lrs->auth_service_url, array('class' => 'form-control')) }}
        <span class="help-block">Full URL to auth service. E.g.: http://auth.services.adurolms.com</span>
    </div>
</div>

<hr>
<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        <p>{{ Form::submit(Lang::get('site.submit'), array('class'=>'btn btn-primary')) }}</p>
    </div>
</div>

{{ Form::close() }}