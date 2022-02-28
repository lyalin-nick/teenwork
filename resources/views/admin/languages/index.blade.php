@extends('adminlte::page')

@section('title', 'Языки')

@section('content_header')
    @if(\Illuminate\Support\Facades\Session::has('success'))
        <x-adminlte-alert theme="success" title="Success" dismissable>
            {{ \Illuminate\Support\Facades\Session::get('success') }}
        </x-adminlte-alert>
    @endif
    @if(\Illuminate\Support\Facades\Session::has('error'))
        <x-adminlte-alert theme="danger" title="Error" dismissable>
            {{ \Illuminate\Support\Facades\Session::get('error') }}
        </x-adminlte-alert>
    @endif

    <h1>Языки</h1>
    <a href="{{route('admin.languages.create')}}">
        <x-adminlte-button label="Create" theme="primary" class="mt-2"/>
    </a>
@endsection

@section('plugins.Datatables', true)
@section('content')
    {{-- Setup data for datatables --}}
    @php
        $heads = [
            'Name',
            ['label' => 'Actions', 'no-export' => true, 'width' => 5],
        ];

        $data = [];

        foreach ($languages as $language) {
            $data[] = [
                $language->name,
                '<nobr>
                    <a href="'.route('admin.languages.edit', ['language' => $language->id]).'" class="btn btn-xs btn-default text-primary mx-1 shadow" title="Edit">
                        <i class="fa fa-lg fa-fw fa-pen"></i>
                    </a>
                    <x-adminlte-button data-href="'.route('admin.languages.destroy', ['language' => $language->id]).'" label="Delete" data-toggle="modal" data-target="#modalDelete" class="btn btn-xs btn-default text-danger mx-1 shadow js-delete">
                        <i class="fa fa-lg fa-fw fa-trash"></i>
                    </x-adminlte-button>
                </nobr>'
            ];
        }
        $config = [
            'data' => $data,
            'order' => [[1, 'asc']],
            'columns' => [null, ['orderable' => false]],
        ];
    @endphp

    {{-- Compressed with style options / fill data using the plugin config --}}
    <x-adminlte-datatable id="table" :heads="$heads" head-theme="dark" :config="$config" striped hoverable bordered
                          compressed/>

    <x-adminlte-modal id="modalDelete" title="Confirm action" theme="danger"
                      icon="fas fa-bell" v-centered static-backdrop scrollable>
        <div>Are you sure you want to delete this item?</div>
        <x-slot name="footerSlot">
            <x-adminlte-button class="mr-auto" label="Cancel" data-dismiss="modal"/>
            <form action="" id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <x-adminlte-button theme="danger" type="submit" label="Yes, delete"/>
            </form>
        </x-slot>
    </x-adminlte-modal>

    @push('js')
        <script>
            $(document).ready(function () {
                $('.js-delete').on('click', (e) => {
                    const action = $(e.currentTarget).attr('data-href');
                    $('#deleteForm').attr('action', action);
                });
            })
        </script>
    @endpush
@endsection
