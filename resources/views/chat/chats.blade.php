@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row clearfix">
            <div class="col-lg-12">
                <div class="card">
                    <div id="plist" class="people-list">
                        <ul class="list-unstyled chat-list mt-2 mb-0">
                            @foreach($chats as $chat)
                                <li class="clearfix">
                                    <a href="{{route('chat.show', ['chatId' => $chat->id])}}">
                                        <img src="{{$chat->logo}}" alt="avatar">
                                        <div class="about">
                                            <div class="name">{{$chat->name}}</div>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
