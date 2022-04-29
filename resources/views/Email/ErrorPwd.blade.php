<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>账号冻结</title>
</head>
<body>

<p>尊敬的{{$name}}：</p><br>
<p>您的 {{$site}} 账号 {{$account}} 已被冻结，原因为：</p>
<p style="text-align: center;line-height: 50px"><h1>{{$content}}</h1></p>
<p>如果密码已忘记可以点击此处进行找回密码。如果不是本人操作可点击此处来屏蔽该用户恶意登录。</p>
<p></p>
<p>此致</p>
<p></p>
<p>{{$site}}</p>
</body>
</html>