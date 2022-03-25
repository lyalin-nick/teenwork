@extends('adminlte::page')

@section('title', 'Faq')

@section('content_header')
    <h1>Создание вопрос-ответа</h1>
@endsection
@section('plugins.Summernote', true)
@section('content')
    <div class="card card-primary">
        <form action="{{route('admin.faqs.store')}}" method="POST">
            @csrf
            <div class="card-body">
                <x-adminlte-input name="question" label="Question" placeholder="question" enable-old-support/>
            </div>
            <div class="card-body">
                <label for="answer">Answer</label>
                <textarea name="answer" id="answer" cols="30" rows="10"></textarea>
            </div>
            <div class="card-footer">
                <x-adminlte-button label="Save" theme="primary" type="submit"/>
            </div>
        </form>
    </div>
@endsection

@section('js')
    <script src="/vendor/unisharp/laravel-ckeditor/ckeditor.js"></script>
    <script src="/vendor/unisharp/laravel-ckeditor/adapters/jquery.js"></script>
    <script>
        var options = {
            filebrowserImageBrowseUrl: '/laravel-filemanager?type=Images',
            filebrowserImageUploadUrl: '/laravel-filemanager/upload?type=Images&_token=',
            filebrowserBrowseUrl: '/laravel-filemanager?type=Files',
            filebrowserUploadUrl: '/laravel-filemanager/upload?type=Files&_token='
        };
        $('textarea').ckeditor(options);
    </script>
@endsection
{{--заполнение формы задачи (по шагам или полностью???)--}}
{{--автокомплит на геопозицию--}}
{{--Доки--}}
