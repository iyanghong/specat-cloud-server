<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>验证码</title>
</head>
<body>

<p>尊敬的{{$name}}：</p><br>
<p>我们收到了一项请求，要求通过您的电子邮件地址访问您的帐号{{$account}}您的验证码为：</p>
<p style="text-align: center;line-height: 50px"><h1>{{$code}}</h1></p>
<p>如果您并未请求此验证码，则可能是他人正在尝试访问以下{{$site}}帐号：{{$account}}。请勿将此验证码转发给或提供给任何人。</p>
<p>您之所以会收到此邮件，是因为此电子邮件地址已被设为 {{$site}} 帐号 {{$account}} 的辅助邮箱。如果您认为这项设置有误，请点击<a href="{{$localUrl}}">此处</a>从该 {{$site}} 帐号中移除您的电子邮件地址。</p>
<p></p>
<p>此致</p>
<p></p>
<p>{{$site}}</p>
</body>
</html>