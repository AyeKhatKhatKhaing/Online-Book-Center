<?php

namespace App\Http\Controllers;

use App\Book;
use App\Buy;
use App\Category;
use App\Group;
use App\Reader;
use App\Payment;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('isReader');
    }
    public function getUser()
    {
        return response()->json(array('user'=> Auth::guard('api')->user()));
    }
    public function home(){
        try {
            $group=Group::latest()->get();
            foreach ($group as $g){
                $g->photos = array_map(function($book){
                    $book['cover']=asset("storage/book/cover/".$book["cover"]);
                    return $book;
                },Book::where('group_id',$g->id)->limit(3)->get()->toArray());
            }
            return response()->json([
                'result' => 1,
                'message' => 'success',
                'group' => $group
            ], 201);

        }catch(\Exception $e){
            return response()->json([
                'result' => 0,
                'message' => 'Fail to proceed!',
                'message_detail' => $e->getMessage(),
            ], 500);
        }

    }


    public function bookList($id)
    {
        try {
            $books=Book::where('group_id',$id)->latest()->paginate(12);
            foreach ($books as $g){
                $cover = $g->cover;
                $g->cover = asset("storage/book/cover/".$cover);
                $g->category = array_map(function($category){
                    $category['cover']=asset("storage/book/cover/".$category["cover"]);
                    return $category;
                },$g->getCategory()->get()->toArray());
            }
            return response()->json([
                'result' => 1,
                'message' => 'success',
                'books' => $books
            ], 201);

        }catch(\Exception $e){
            return response()->json([
                'result' => 0,
                'message' => 'Fail to get book list!',
                'message_detail' => $e->getMessage(),
            ], 500);
        }

    }
    public function categoryList($id)
    {
        try {
            $buy=Buy::where('category_id',$id)->where('reader_id',Auth::guard('api')->user()->id)->first();
            if($buy!=''){
                $categories=Category::where('id',$id)->with('getChapter')->latest()->get();
                foreach ($categories as $g){
                    $cover = $g->cover;
                    $g->cover = asset("storage/book/cover/".$cover);
                }
                return response()->json([
                    'result' => 1,
                    'message' => 'success',
                    'categories' => $categories
                ], 201);
            }else{
                return response()->json([
                    'result' => 1,
                    'message' => 'Buy this book first',
                ], 201);
            }

        }catch(\Exception $e){
            return response()->json([
                'result' => 0,
                'message' => 'Fail to read book!',
                'message_detail' => $e->getMessage(),
            ], 500);
        }
    }
    public function addPayment(Request $request){
        try {
            $request->validate([
                "amount" => "required|integer|max:100",
                "payment" => "required|mimetypes:image/jpeg,image/png|file|max:3500"
            ]);           
            $payment=new Payment();
            $payment->amount=$request->amount;
            $dir="public/payment";
            $newName = uniqid()."_payment.".$request->file("payment")->getClientOriginalExtension();
            $request->file("payment")->storeAs($dir,$newName);
            $payment->payment = $newName;
            $payment->reader_id=Auth::guard('api')->user()->id;
            $payment->save();
            $finish=' We will check your payment and reply soon.....';
            return response()->json([
                'result' => 1,
                'message' => $finish,
            ], 201);

        }catch(\Exception $e){
            return response()->json([
                'result' => 0,
                'message' => 'Fail to send payment!',
                'message_detail' => $e->validator->errors(),
            ], 500);
        }

    }

    public function buyBook(Request $request){
        try {
            $request->validate([
                "category_id" => "required|integer|max:100",
                "reader_id" => "required|integer|max:100",
            ]);
            if ($request->reader_id = Auth::guard('api')->user()->id){
                $reader=Reader::find(Auth::guard('api')->user()->id);
                $category=Category::find($request->category_id);
                if ($reader->coin >= $category->price){
                    $uprice=$reader->coin - $category->price;
                    Reader::where('id', Auth::guard('api')->user()->id)
                        ->update(['coin' => $uprice]);
                    if (Buy::where('category_id',$request->category_id)->where('reader_id', Auth::guard('api')->user()->id)->get()==''){
                        $buy=new Buy();
                        $buy->category_id=$request->category_id;
                        $buy->reader_id=Auth::guard('api')->user()->id;
                        $buy->price=$category->price;
                        $buy->save();
                    }
                    $finish='Thanks for buying this book.....';
                }else{
                    $finish='Not enough money for this books.....';
                }

            }else{
                $finish='Wrong User Login.....';
            }
            return response()->json([
                'result' => 1,
                'message' => $finish,
            ], 201);

        }catch(\Exception $e){
            return response()->json([
                'result' => 0,
                'message' => 'Fail to buy books!',
                'message_detail' => $e->validator->errors(),
            ], 500);
        }

    }

    //all books by reader
    public function boughtBooks(){
        try {
            $books=Buy::where('reader_id',Auth::guard('api')->user()->id)->with('getBooks')->latest()->paginate(12);
            foreach ($books as $b){
                $cover = $b->getBooks->cover;
                $b->getBooks->cover = asset("storage/book/cover/".$cover);
            }
            return response()->json([
                'result' => 1,
                'message' => 'success',
                'b-books' => $books
            ], 201);

        }catch(\Exception $e){
            return response()->json([
                'result' => 0,
                'message' => 'Fail to get bought books!',
                'message_detail' => $e->getMessage(),
            ], 500);
        }

    }


    //all popular books
    public function popularBooks(){
        try{
            $books=Book::where('popular','1')->latest()->with('getCategory')->paginate(12);
            foreach ($books as $b){
                $cover = $b->cover;
                $b->cover = asset('storage/book/cover/'.$cover);
                $b->category = array_map(function($category){
                    $category['cover']=asset("storage/book/cover/".$category["cover"]);
                    return $category;
                },$b->getCategory()->get()->toArray());
            }
            return response()->json([
                'result' => 1,
                'message' => 'success',
                'books' => $books
            ], 201);

        }catch(\Exception $e){
            return response()->json([
                'result' => 0,
                'message' => 'Fail to get popular books!',
                'message_detail' => $e->getMessage(),
            ], 500);
        }

    }

    //for show books
    public function forShowBooks(){
        try {
            $bbooks=Buy::where('reader_id',Auth::guard('api')->user()->id)->with('getBooks')->latest()->limit(3)->get();
            foreach ($bbooks as $b){
                $cover = $b->getBooks->cover;
                $b->getBooks->cover = asset("storage/book/cover/".$cover);
                $b->category = array_map(function($category){
                    $category['cover']=asset("storage/book/cover/".$category["cover"]);
                    return $category;
                },$b->getCategory()->get()->toArray());
            }

            $pbooks=Book::where('popular','1')->latest()->with('getCategory')->limit(3)->get();
            foreach ($pbooks as $b){
                $cover = $b->cover;
                $b->cover = asset('storage/book/cover/'.$cover);
                $b->category = array_map(function($category){
                    $category['cover']=asset("storage/book/cover/".$category["cover"]);
                    return $category;
                },$b->getCategory()->get()->toArray());
            }
            return response()->json([
                'result' => 1,
                'message' => 'success',
                'bought-books' => $bbooks,
                'popular-books' => $pbooks
            ], 201);

        }catch(\Exception $e){
            return response()->json([
                'result' => 0,
                'message' => 'Fail to proceed!',
                'message_detail' => $e->getMessage(),
            ], 500);
        }

    }




}
