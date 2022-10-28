<?php

namespace App\Http\Controllers;

use App\Book;
use App\Category;
use App\Chapter;
use App\Http\Middleware\Reader;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class BookController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $all=Book::where([['type','all'],['name',auth()->user()->name ]])->latest()->limit(10)->get();
        $one=Book::where([['type','one'],['name',auth()->user()->name ]])->latest()->limit(10)->get();
        return view('book.index',compact('all','one'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('book.create');
    }
    public function getUser()
    {
       return Auth::guard('api')->user();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            "title" => "required|max:255",
            "chapter" => "required|max:255",
            "price" => "required|integer",
            "type" => "required|in:all,one",
            "group_id" => "required|integer",
            "cover" => "required|mimetypes:image/jpeg,image/png|file|max:3500"
        ]);
        $book=new Book();
        $book->user_id=auth()->user()->id;
        $book->name= auth()->user()->name ;
        $book->author=auth()->user()->author ;
        $book->title=$request->title;
        $book->group_id=$request->group_id;
        $book->chapter=$request->chapter;
        $book->price=$request->price;
        $book->type=$request->type;
        $dir="public/book/cover";
        $newName = uniqid()."_cover.".$request->file("cover")->getClientOriginalExtension();
        $request->file("cover")->storeAs($dir,$newName);
        $book->cover = $newName;
        $book->save();

        $category=new Category();
        $category->user_id=auth()->user()->id;
        $category->book_id=$book->id;
        $category->name= auth()->user()->name ;
        $category->author=auth()->user()->author ;
        $category->main_title=$request->title;
        $category->title=$request->title;
        $category->chapter=$request->chapter;
        $category->price=0;
        $category->ads='free';
        $category->type='done';
        $dir="public/book/cover";
        $newName = uniqid()."_cover.".$request->file("cover")->getClientOriginalExtension();
        $request->file("cover")->storeAs($dir,$newName);
        $category->cover = $newName;
        $category->save();

        return redirect()->route("book.index")->with("toast","New Book Add Successfully");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Book  $book
     * @return \Illuminate\Http\Response
     */

    /**
     * Show the form for editing the specified resource.
     * @param  \App\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function edit(Book $book)
    {
        return view('book.edit',compact('book'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Book  $book
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request,Book $book)
    {
        $request->validate([
            "title" => "required|max:255",
            "chapter" => "required|max:255",
            "cover" => "mimetypes:image/jpeg,image/png|file|max:3500",
            "price" => "required|integer",
            "group_id" => "required|integer",
            "type" => "required|in:all,one",
                ]);
                $book->title=$request->title;
                $book->chapter=$request->chapter;
        $book->price=$request->price;
        $book->group_id=$request->group_id;
        $book->type=$request->type;
                if ($request->file("cover")){
                    $dir="public/book/cover";
                    $size=$request->file("cover")->getSize();
                    $newName = uniqid()."_cover.".$request->file("cover")->getClientOriginalExtension();
                    $request->file("cover")->storeAs($dir,$newName);
                    $book->cover= $newName;
                }
                $book->update();
                return redirect()->route('author.show', $book->user_id)->with("toast","Book Updated Successfully");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function destroy(Book $book)
    {
        //
    }

    public function popularBook(Request $request){
        $book = Book::find($request->id);
        $book->popular=$request->popular;
        $book->update();
        return redirect()->route('admin.bookList')->with("toast","Popular book added.");
    }

    public function bookStatus(Request $request){
        $book = Book::find($request->id);
        $book->status=$request->status;
        $book->update();
        return redirect()->route('admin.bookList')->with("toast","This book is finished.");

    }
}
