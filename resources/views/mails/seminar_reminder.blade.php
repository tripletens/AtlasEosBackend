@extends('layouts.mail')

@section('content')

<div class="container-fluid">
    <span class="alert alert-info text-start">
        <h3> This is a reminder that the seminar {{$data['seminar_data']->topic}} is about to start </h3> <br/>

        <p><b>Seminar Name</b>: {{ ucwords($data['seminar_data']->topic)}}</p>
        <p><b>Seminar Date</b>: {{$data['seminar_data']->seminar_date}}</p>
        <p><b>Seminar Time</b>: {{$data['seminar_data']->start_time}}</p>
    </span>
</div>

@endsection
