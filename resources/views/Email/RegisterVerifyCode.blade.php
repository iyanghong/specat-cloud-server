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

<p>尊敬的用户：</p><br>
<p>我们收到了一项请求，要求通过您的电子邮件地址在{{$site}}注册账户,您的验证码为：</p>
<p style="text-align: center;line-height: 50px"><h1>{{$code}}</h1></p>
<p>如果你未申请{{$site}}新账户服务，请忽略该邮件。</p>
<p></p>
<p>此致</p>
<p></p>
<p>{{$site}}</p>
</body>
</html>