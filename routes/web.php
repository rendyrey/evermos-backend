<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('auth/login',['uses'=>'AuthController@authenticate']);

$router->group(
    ['middleware'=>'jwt.auth'],
    function() use ($router){
        
        // users
        $router->get('usersx', function(){
            $users = \App\Models\User::all();
            return response()->json($users);
        });

        $router->get('list-basket', 'ProductController@listOfBasket');

        $router->post('add-to-basket/{productId}[/{amount}]', 'TransactionController@addToBasket');

        $router->post('checkout','TransactionController@checkout');

        $router->post('pay/{transactionId}[/{paymentId}]','TransactionController@pay');

        $router->post('process-transaction/{transactionId}/{transactionStatus}','TransactionController@processTransaction');
    }
);

// list barang
$router->get('list-products', 'ProductController@listOfProduct');
// list transaction by the status
$router->get('list-transactions/{transactionStatus}','TransactionController@listOfTransaction');
