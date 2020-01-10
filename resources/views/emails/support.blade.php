@component('mail::message')
<h1>Support | Messenger</h1>

@component('mail::panel')
<strong>Name: </strong>{{$data['name']}}

<strong>Email: </strong>{{$data['reply_email']}}

<strong>Message: </strong>{{$data['the_message']}}
@endcomponent
@endcomponent
