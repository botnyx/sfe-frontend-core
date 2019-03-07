<?php




$app->get('/robots.txt',  function ( $request,  $response, array $args){
  $res = "User-agent: *".PHP_EOL."Disallow: /";
  return $response->write($res);

});
