{{ Form::model($lrs, array('route' => array('lrs.update', $lrs->_id),
      'method' => 'PUT', 'class' => 'form-horizontal')) }}

<div class="form-group">
    {{ Form::label('title', Lang::get('lrs.machine_name'), array('class' => 'col-sm-2 control-label' )) }}
    <div class="col-sm-10">
        {{ Form::text('title', $lrs->title, array('class' => 'form-control')) }}
        <span class="help-block">Only lowercase letter, number and hyphen are allowed. E.g.: lms1, lms2</span>
    </div>
</div>

<div class="form-group">
    {{ Form::label('description', Lang::get('site.description'), array('class' => 'col-sm-2 control-label' )) }}
    <div class="col-sm-10">
        {{ Form::text('description', $lrs->description,array('class' => 'form-control')) }}
    </div>
</div>
<div class="form-group">
    {{ Form::label('subdomain', Lang::get('lrs.subdomain'), array('class' => 'col-sm-2 control-label' )) }}
    <div class="col-sm-5">
        {{ Form::text('subdomain', $lrs->subdomain, array('class' => 'form-control')) }}
        <span class="help-block">Similar to title. E.g.: lms1, lms2. If there's no subdomain, the LRS will use machine name as its domain.</span>
    </div>
    <span class="form-suffix" style="display: inline-block;vertical-align: middle;margin-top: 5px;">.{{ Config::get('app.domain')}}</span>
</div>

<div class="panel panel-default col-sm-offset-2 ">
    <div class="panel-heading">Authentication</div>
    <div class="panel-body">

        <div class="col-sm-offset-2 col-sm-10">
            <div class="form-group">
                <div class="radio">
                    <label>
                        {{ Form::radio('auth_service', Lrs::INTERNAL_LRS, $lrs->auth_service == Lrs::INTERNAL_LRS ,array('id'=>'internal-lrs-authentication')) }}
                        {{ Lang::get('lrs.api.label.internal') }}
                    </label>
                </div>
            </div>
            <div class="form-group">
                <div class="radio">
                    <label>
                        {{ Form::radio('auth_service', Lrs::ADURO_AUTH_SERVICE, $lrs->auth_service == Lrs::ADURO_AUTH_SERVICE, array('id'=>'aduro-auth-service')) }}
                        {{ Lang::get('lrs.api.label.auth_service') }}
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('token', Lang::get('site.token'), array('class' => 'col-sm-2 control-label' )) }}
            <div class="col-sm-10">
                {{ Form::text('token', $lrs->token,array('class' => 'form-control')) }}
                <span class="help-block">similar to the token configured in authentication service</span>
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('auth_cache_time', Lang::get('lrs.auth_cache_time'), array('class' => 'col-sm-2 control-label' )) }}
            <div class="col-sm-10">
                {{ Form::text('auth_cache_time', $lrs->auth_cache_time,array('class' => 'form-control')) }}
                <span class="help-block">In minute, default to 15</span>
            </div>
        </div>
        <div class="form-group">
            {{ Form::label('auth_service_url', Lang::get('lrs.auth_service_url'), array('class' => 'col-sm-2 control-label' )) }}
            <div class="col-sm-10">
                {{ Form::text('auth_service_url', $lrs->auth_service_url ,array('class' => 'form-control')) }}
                <span class="help-block">Full URL to auth service. E.g.: http://auth.services.adurolms.com</span>
            </div>
        </div>
    </div>
</div>

<hr>
<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        <p>{{ Form::submit(Lang::get('site.submit'), array('class'=>'btn btn-primary')) }}</p>
    </div>
</div>

{{ Form::close() }}