<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Basket;
use App\Helper\UserHelper;

/**
 * ProductController class file
 * @author Rendy Reynaldy Anggradwiguna <rendyreynaldy@gmail.com>
 * @since 2021.01.01
 */
class ProductController extends Controller
{
    
    /**
     * Show all product that has quantity.
     * @return string
     */
    public function listOfProduct(){
        $data['products'] = Product::where('quantity','>',0)->get();
        
        return response()->json($data);
    }

    /**
     * Show all unpaid product on user's basket
     * @param Request The Request param on url
     * @return array
     */
    public function listOfBasket(Request $request){
        $user = UserHelper::getUser($request->token);
        $data['basket'] = Basket::where('user_id', $user->id)->where('is_checked_out', 0)->get();

        return $data;
    }
}