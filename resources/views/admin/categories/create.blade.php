@extends('adminlte::page')

@section('title', 'Категории')

@section('content_header')
    <h1>Создание категории</h1>
@endsection

@section('content')
    <div class="card card-primary">
        <form action="{{route('admin.categories.store')}}" method="POST">
            @csrf
            <div class="card-body">
                <x-adminlte-input name="name" label="Name" placeholder="name" enable-old-support/>
                @if($category_id !== 0)
                    <x-adminlte-select name="flag" label="Flag" enable-old-support>
                        @foreach(\App\Models\Category::getFlags() as $id => $flag_label)
                            <option value="{{$id}}">{{$flag_label}}</option>
                        @endforeach
                    </x-adminlte-select>
                @else
                    <x-adminlte-input name="icon_name" label="IconName" placeholder="icon_name" enable-old-support/>
                @endif
                <input type="hidden" name="category_id" value="{{$category_id}}">
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
