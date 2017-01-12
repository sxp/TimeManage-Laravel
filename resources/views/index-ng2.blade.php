<!DOCTYPE html>
<html lang="en">
<head>
    <base href="/">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    {{--<link href="/css/app.css" rel="stylesheet">--}}
    <link href="/css/styles.bundle.css" rel="stylesheet">
    {{--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">--}}
</head>

<!-- Scripts -->
<script>
    window.Laravel = <?php echo json_encode([
    'csrfToken' => csrf_token(),
  ]); ?>
</script>
</head>
<body>
<app-root>正在加载...</app-root>

<!-- Scripts -->
<script src="/js/app.js"></script>
<script type="text/javascript" src="/js/inline.bundle.js"></script>
<script type="text/javascript" src="/js/vendor.bundle.js"></script>
<script type="text/javascript" src="/js/main.bundle.js"></script>
</body>
</html>
