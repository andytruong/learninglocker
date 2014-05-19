@extends('layouts.master')

@section('footer')
  @parent
  <!-- load in one page application with requirejs -->
  <script data-main="{{ URL() }}/assets/js/lrs/aduro/lrs.js" src="{{ URL() }}/assets/js/lrs/aduro/lrs.js"></script>
  {{ HTML::style('assets/css/typeahead.css')}}
@stop
@section('sidebar')
  @if( app\locker\helpers\Access::isRole('super') )
    @include('partials.site.sidebars.admin')
  @else
    @include('layouts.sidebars.blank')
  @endif
@stop


@section('content')
  @if($errors->any())
  <ul class="alert alert-danger">
    {{ implode('', $errors->all('<li>:message</li>'))}}
  </ul>
  @endif

  <div class="page-header">
    <h1>{{ Lang::get('lrs.create') }}</h1>
  </div>
  {{ Breadcrumbs::render('create') }}

  <div class="row">
    <div class="col-xs-12 col-sm-8 col-lg-8">
      @if($verified == 'yes')
        @include('partials.lrs.forms.create')
      @else
        <?php $verify_link = URL() . '/users/' . Auth::user()->_id . '/edit'; ?>
        <p>{{ Lang::get('lrs.verify', array('verify_link' => $verify_link)) }}</p>
      @endif
    </div>
  </div>
@stop