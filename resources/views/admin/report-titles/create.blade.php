@extends('adminlte::page')

@section('title', 'Создание заголовка')

@section('content_header')
    <h1>Создание заголовка</h1>
@endsection
@section('plugins.Summernote', true)
@section('content')
    <div class="card card-primary">
        <form action="{{route('admin.report-titles.store')}}" method="POST">
            @csrf
            <div class="card-body">
                <x-adminlte-input name="name" label="Name" placeholder="name" enable-old-support/>
                <x-adminlte-select name="flag" label="Flag" enable-old-support>
                    @foreach(\App\Models\ReportTitle::getFlagLabels() as $id => $flag_label)
                        <option value="{{$id}}">{{$flag_label}}</option>
                    @endforeach
                </x-adminlte-select>
            </div>
            <div class="card-footer">
                <x-adminlte-button label="Save" theme="primary" type="submit"/>
            </div>
        </form>
    </div>
@endsection
