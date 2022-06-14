<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" href="favicon.ico" type="image/vnd.microsoft.icon" />
    <title>LoRaWAN Dashboard - Login</title>
    <link type="text/css" rel="stylesheet" href="/css/bootstrap.min.css">
    <link type="text/css" rel="stylesheet" href="/css/floating-labels.css">
    <link type="text/css" rel="stylesheet" href="/css/login.css">
</head>
<body class="align-items-center">
<form class="form-signin" id="loginForm" action="/login" method="post">
    <h1 class="h3 mb-4 font-weight-normal logo"><a href="/">LoRaWAN Dashboard</a></h1>
    <h1 class="h5 mb-3 font-weight-normal text-light text-center">Please sign in</h1>
    <h1 class="h5 mb-3 text-secondary text-center" id="login-message">
        <?php
            if (isset($_SESSION['LOGIN_ERROR']) && !is_null($_SESSION['LOGIN_ERROR'])) {
                echo $_SESSION['LOGIN_ERROR'];
                unset($_SESSION['LOGIN_ERROR']);
            }
        ?>
    </h1>
    <div class="row">
        <div class="col-sm-12 mb-3 form-floating">
            <input type="text" name="user" id="inputEmail" style="pointer-events: auto;" class="form-control" placeholder="Linkblue" required autofocus />
            <label for="inputEmail">Linkblue</label>
        </div>
        <div class="col-sm-12 mb-3 form-floating">
            <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required>
            <label for="inputPassword">Password</label>
        </div>
    </div>
    <!--<label for="remember" class="sr-only">Remember me</label>
    <input class="form-control" type="checkbox" name="remember" id="remember" value="remember-me">-->
    <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
    <div class="row">
        <div class="col-sm-12 mt-5 mb-3 text-center">
            <p class="text-light"><a class="text-light" href="https://ukhealthcare.uky.edu/" target="_blank">UK Healthcare</a> &copy; <?php echo date('Y'); ?></p>
        </div>
    </div>
</form>
<script src="/js/jquery.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/popper.min.js"></script>
</body>
</html>