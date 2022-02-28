@extends('adminlte::page')

@section('title', 'Категории')

@section('content_header')
    <h1>Создание категории</h1>
@endsection

@section('content')
    <div class="card card-primary">
        <form action="{{route('admin.languages.store')}}" method="POST">
            @csrf
            <div class="card-body">
                <x-adminlte-input name="name" label="Name" placeholder="name" enable-old-support/>
            </div>
            <div class="card-footer">
                <x-adminlte-button label="Save" theme="primary" type="submit"/>
            </div>
        </form>
    </div>
@endsection

{{--заполнение формы задачи (по шагам или полностью???)--}}
{{--автокомплит на геопозицию--}}
{{--Доки--}}
