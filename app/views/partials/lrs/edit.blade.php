@extends('layouts.master')

@section('footer')
  @parent
  <!-- load in one page application with requirejs -->
  <script data-main="{{ URL() }}/assets/js/lrs/aduro/lrs.js" src="{{ URL() }}/assets/js/lrs/aduro/lrs.js"></script>
  {{ HTML::style('assets/css/typeahead.css')}}
@stop

@section('sidebar')
  @include('partials.lrs.sidebars.lrs')
@stop


@section('content')

  @if($errors->any())
  <ul class="alert alert-danger">
    {{ implode('', $errors->all('<li>:message</li>'))}}
  </ul>
  @endif

  @include('partials.site.elements.page_title', array('page' => ucfirst(Lang::get('site.edit'))))
  
  <div class="row">
    <div class="col-xs-12 col-sm-8 col-lg-8">
      @include('partials.lrs.forms.edit')
    </div>
  </div>

@stop