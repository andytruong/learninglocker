{{ Form::open(array('route' => 'lrs.store', 'class' => 'form-horizontal lrs-create')) }}

<div class="form-group">
    {{ Form::label('title', Lang::get('lrs.machine_name'), array('class' => 'col-sm-2 control-label' )) }}
    <div class="col-sm-10">
        {{ Form::text('title', Input::old('title'), array('class' => 'form-control')) }}
        <span class="help-block">Only lowercase letter, number and hyphen are allowed. E.g.: lms1, lms2</span>
    </div>
</div>

<div class="form-group">
    {{ Form::label('description', Lang::get('site.description'), array('class' => 'col-sm-2 control-label' )) }}
    <div class="col-sm-10">
        {{ Form::text('description', Input::old('description'), array('class' => 'form-control')) }}
    </div>
</div>

<div class="form-group">
    {{ Form::label('subdomain', Lang::get('lrs.subdomain'), array('class' => 'col-sm-2 control-label' )) }}
    <div class="col-sm-5">
        {{ Form::text('subdomain', Input::old('subdomain'), array('class' => 'form-control')) }}
        <span class="help-block">Similar to title. E.g.: lms1, lms2. If there's no subdomain, the LRS will use machine name as its domain.</span>
    </div>
    <span class="form-suffix" style="display: inline-block;vertical-align: middle;margin-top: 5px;">.{{ Config::get('app.domain')}}</span>
</div>
<hr>
<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        <p>{{ Form::submit(Lang::get('site.submit'), array('class'=>'btn btn-primary')) }}</p>
    </div>
</div>

{{ Form::close() }}
