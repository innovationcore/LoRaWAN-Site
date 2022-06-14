<h4>Attempting MSSQL connection from PHP...</h4>
<?php
  require __DIR__ . '/vendor/autoload.php';
  
  $router = new AltoRouter();
  $router->map('GET', '/', function() {
    echo "Root";
  }, 'home');
  $router->map('GET', '/home', function() {
    echo "Home";
  });
  $router->map('GET', '/db', function() {
	  try {
		  $conn = new PDO("sqlsrv:Server=mssql;Database=orders", "sa", "u@mssql01");
		  $conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	  } catch (Exception $e) {
		  die( print_r( $e->getMessage() ) );
	  }  
  });
  $router->map('GET', '/info', function() {
	 phpinfo(); 
  });
  

  // match current request url
$match = $router->match();

// call closure or throw 404 status
if( is_array($match) && is_callable( $match['target'] ) ) {
	call_user_func_array( $match['target'], $match['params'] );
} else {
	// no route was matched
	header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
}
?>
