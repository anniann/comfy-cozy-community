<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Drive Files</title>
</head>
<body>
    <h1>Google Drive Event Folders</h1>
    @if(isset($error))
        <p>{{ $error }}</p>
    @else
        <ul>
            @foreach($fileObjects as $fileObject)
                <li>
                    Folder Name: {{ $fileObject->name }}<br>
                    Folder ID: {{ $fileObject->id }}
                </li>
            @endforeach
        </ul>
    @endif
</body>
</html>
