@extends('layouts.app')

@section('content')

    <chat-room :chat-id="{{$chatId}}" :user-id="{{0}}"></chat-room>

@endsection
