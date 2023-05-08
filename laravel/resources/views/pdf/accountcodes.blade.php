<!DOCTYPE html>
<html>
<head>
    <title>Account Codes</title>
    <link href='{{public_path()."/css/app.css"}}' rel="stylesheet">
</head>
<body>
  <style>
    #header,
    #footer {
      position: fixed;
      left: 0;
      right: 0;
      color: #aaa;
      font-size: 0.9em;
    }
    #header {
      top: 0;
      border-bottom: 0.1pt solid #aaa;
    }
    #footer {
      bottom: 0;
      border-top: 0.1pt solid #aaa;
    }
    .page-number:before {
      content: "Page " counter(page);
    }
  </style>
  <div id="footer">
    <div class="page-number"></div>
  </div>

  <div class = "header">
      <h1>{{ $heading}}</h1>
  </div>
  <div>
  <table class="table">
    <thead class="thead-default">
      <tr>
        <th>#</th>
        <th>Account Code</th>
        <th>Account Title</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($accountcodes as $codes)
        <tr>
          <th>{{$loop->iteration}}</th>
          <td>{{$codes->AccountCode}}</td>
          <td>{{$codes->AccountTitle}}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
  </div>
  <script src="/js/app.js"></script>
</body>
</html>