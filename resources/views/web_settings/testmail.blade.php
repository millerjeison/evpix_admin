<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>
<body>
<h3>Hello {{$first_name .' '. $last_name}}</h3>
<br>
Thank you for reaching out to us. We have received your query and would like to provide you with the following response:
<br>
---
<br>
{{$query}}
<br>
---
<br>
Our Response:
<br>
{{$reply_message}}
@if($attachments)
    <br>
    Attachments:
    <ul>
        @foreach($attachments as $attachment)
            <li><a href="{{ $attachment['path'] }}">{{ $attachment['name'] }}</a></li>
        @endforeach
    </ul>
@endif

<br>
---
<br>
If you have any further questions or concerns, feel free to contact us. We are here to assist you.
<br><br><br>
Best regards,
<br>
From {{$school_name}}
</body>
</html>
