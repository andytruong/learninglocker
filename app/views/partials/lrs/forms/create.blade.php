{{ Form::open(array('route' => 'lrs.store', 'class' => 'form-horizontal')) }}

<div class="form-group">
    {{ Form::label('title', Lang::get('site.title'), array('class' => 'col-sm-2 control-label' )) }}
    <div class="col-sm-10">
        {{ Form::text('title', '',array('class' => 'form-control')) }}
        <span class="help-block">Machine name (Only lowercase letter, number and hyphen are allowed. E.g.: lms1, lms2)</span>
    </div>
</div>

<div class="form-group">
    {{ Form::label('description', Lang::get('site.description'), array('class' => 'col-sm-2 control-label' )) }}
    <div class="col-sm-10">
        {{ Form::text('description', '',array('class' => 'form-control')) }}
    </div>
</div>

<div class="form-group">
    {{ Form::label('subdomain', Lang::get('lrs.subdomain'), array('class' => 'col-sm-2 control-label' )) }}
    <div class="col-sm-5">
        {{ Form::text('subdomain', '',array('class' => 'form-control')) }}
        <span class="help-block">Similar to title. E.g.: lms1, lms2. If there's no subdomain, the LRS will use machine name as its domain.</span>
    </div>
    <span class="form-suffix" style="display: inline-block;vertical-align: middle;margin-top: 5px;">.{{ Config::get('app.domain')}}</span>
</div>

<div class="panel panel-default col-sm-offset-2 ">
    <div class="panel-heading">Authentication</div>
    <div class="panel-body">

        <div class="col-sm-10 col-sm-offset-2 ">
            <div class="form-group">
                <div class="radio">
                    <label>
                        {{ Form::radio('auth_service', Lrs::INTERNAL_LRS, false ,array('id'=>'internallrs-authentication')) }}
                        {{ Lang::get('lrs.api.label.internal') }}
                    </label>
                </div>
            </div>
            <div class="form-group">
                <div class="radio">
                    <label>
                        {{ Form::radio('auth_service', Lrs::ADURO_AUTH_SERVICE, true, array('id'=>'aduro-auth-service')) }}
                        {{ Lang::get('lrs.api.label.auth_service') }}
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('token', Lang::get('site.token'), array('class' => 'col-sm-2 control-label' )) }}
            <div class="col-sm-10">
                {{ Form::text('token', '',array('class' => 'form-control')) }}
                <span class="help-block">similar to the token configured in authentication service</span>
            </div>
        </div>

        <div class="form-group">
            {{ Form::label('auth_cache_time', Lang::get('lrs.auth_cache_time'), array('class' => 'col-sm-2 control-label' )) }}
            <div class="col-sm-10">
                {{ Form::text('auth_cache_time', '',array('class' => 'form-control')) }}
                <span class="help-block">In minute, default to 15</span>
            </div>
        </div>
        <div class="form-group">
            {{ Form::label('auth_service_url', Lang::get('lrs.auth_service_url'), array('class' => 'col-sm-2 control-label' )) }}
            <div class="col-sm-10">
                {{ Form::text('auth_service_url', '',array('class' => 'form-control')) }}
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
