<?php

namespace App\Http\Controllers;

use App\Http\Requests\InsertCartItem;
use App\Http\Requests\RemoveCartItem;
use App\Http\Requests\UpdateShoppingCartItem;
use App\Repositories\Eloquent\ProductOptionRepository;
use App\Repositories\Eloquent\ProductRepository;
use Cart;
use DateTime;

class CartController extends Controller
{
    private $productRepository;
    private $productOptionRepository;

    public function __construct(ProductRepository $productRepository, ProductOptionRepository $productOptionRepository)
    {
        $this->productRepository = $productRepository;
        $this->productOptionRepository = $productOptionRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $carts = Cart::content();
        $total = Cart::total();
        $products = $this->productRepository->all(['id', 'image']);
        $count = Cart::count();

        return view('carts.index', compact('carts', 'total', 'products', 'count'));
    }

    public function InsertShoppingCartItem(InsertCartItem $request)
    {
        $productId = $request->input('product_id');
        $product = $this->productRepository->findOrFail($productId);
        $product_option = $this->productOptionRepository->where('id', $request->input('product_option_id'))->first(['value']);
        $size = [];
        if ($product_option) $size = ['size' => $product_option->value];
        Cart::add($productId, $product->name, $request->input('qty'), $product->price, $size);

        return response()->json(['count' => Cart::count()]);
    }

    public function UpdateShoppingCartItem(UpdateShoppingCartItem $request)
    {
        $rowId = $request->input('rowId');
        $qty = $request->input('qty');

        Cart::update($rowId, $qty); // Will update the quantity
        $carts = Cart::content();
        $cartTotal = Cart::total();
        $count = Cart::count();
        return response()->json([
            'carts' => $carts,
            'total' => $cartTotal,
            'count' => $count,
        ]);
    }

    public function RemoveShoppingCartItem(RemoveCartItem $request)
    {
        $rowId = $request->input('rowId');
        Cart::remove($rowId);
        $cartTotal = Cart::total();
        $count = Cart::count();
        return response()->json(['total' => $cartTotal, 'count' => $count]);
    }

    public function completion()
    {
        $carts = Cart::content();
        $total = Cart::total();
        $count = Cart::count();
        $halfHourIntervals = $this->createHalfHourIntervals(date('H'), 20, 0.5, 'H:i');
        return view('carts.completion', compact('carts', 'total', 'count', 'halfHourIntervals'));
    }

    /**
     * https://stackoverflow.com/a/3903320/5283067
     * @param int $lower
     * @param int $upper
     * @param int $step
     * @param null $format
     *
     * @return array
     */
    public function createHalfHourIntervals($lower = 0, $upper = 23, $step = 1, $format = null)
    {
        $curDate = new DateTime;
        if ($format === null) {
            $format = 'g:ia'; // 9:30pm
        }
        $times = [];
        $times[$curDate->format('d/m/Y H:i')] = 'Càng sớm càng tốt';
        foreach (range($lower, $upper, $step) as $increment) {
            $increment = number_format($increment, 2);
            list($hour, $minutes) = explode('.', $increment);
            $date = new DateTime($hour . ':' . $minutes * .6);
            if ($date > $curDate) $times[$date->format('d/m/Y H:i')] = $date->format($format);
        }
        return $times;
    }
}
