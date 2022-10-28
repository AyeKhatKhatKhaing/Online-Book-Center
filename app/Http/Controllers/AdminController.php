<?php

namespace App\Http\Controllers;

use App\Book;
use App\Buy;
use App\Category;
use App\Chapter;
use App\Payment;
use App\Reader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /////////////////////////////////////Reader Functions ///////////////////////////////////////////////
    public function reader(){
        $readers=Reader::all();
        return view('main.reader.index',compact('readers'));
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Reader  $reader
     * @return \Illuminate\Http\Response
     */
    public function redit(Reader $reader){
        return view('main.reader.edit',compact('reader'));
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Reader  $reader
     * @return \Illuminate\Http\Response
     */
    public function rupdate(Request $request, Reader $reader)
    {
        $request->validate([
            "coin" => "required|integer",
        ]);
        $reader->coin=$reader->coin+ $request->coin;
        $reader->update();
        return redirect()->route('admin.rindex')->with("toast"," ပိုက်ဆံထည့်ပီးပါပီ...");

    }

    public function rbook($id){
        $books=Buy::where('reader_id',$id)->with('getBook')->latest()->get();
        return view('main.reader.bought-books',compact('books'));
    }


    /////////////////////////////////////Book Functions ///////////////////////////////////////////////

    public function bshow($book)
    {
        $books=Category::where([['book_id',$book]])->latest()->limit(10)->get();
        $fbooks=Category::where([['book_id',$book],['ads','free']])->latest()->limit(10)->get();
        return view('main.book.show',compact('books','fbooks'));
    }


    /////////////////////////////////////Chapter Functions ///////////////////////////////////////////////

    public function chindex($id)
    {
        $chapters=Chapter::where([['category_id',$id]])->get();
        return view('main.chapter.index', compact('chapters'));
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Chapter  $chapter
     * @return \Illuminate\Http\Response
     */
    public function chdestroy(Chapter $chapter)
    {
        $chapter->delete();
        return redirect()->back()->with("toast"," Chapter Delete Successfully");;
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Chapter  $chapter
     * @return \Illuminate\Http\Response
     */
    public function chshow(Chapter $chapter)
    {
        return view('main.chapter.show', compact('chapter'));
    }


    /////////////////////////////////////Category Functions ///////////////////////////////////////////////

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function cedit(Category $category)
    {
        return view('main.category.edit', compact('category'));
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function cupdate(Request $request, Category $category)
    {
        $request->validate([
            "title" => "required|max:255",
            "chapter" => "required|max:255",
            "price" => "required|integer",
            "cover" => "mimetypes:image/jpeg,image/png|file|max:3500"
        ]);
        $category->title=$request->title;
        $category->chapter=$request->chapter;
        $category->price=$request->price;
        if ($request->file("cover")){
            $dir="public/book/cover";
            $newName = uniqid()."_cover.".$request->file("cover")->getClientOriginalExtension();
            $request->file("cover")->storeAs($dir,$newName);
            $category->cover = $newName;
        }

        $category->update();
        return redirect()->route('admin.bshow', $category->book_id)->with("toast"," Category Update Successfully");

    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function cdestroy(Category $category)
    {
        Chapter::where([['category_id',$category->id]])->delete();
        $category->delete();
        return redirect()->back()->with("toast"," Category Delete Successfully");
    }
/////////////////////////////////////Payment Functions ///////////////////////////////////////////////
    public function pmindex()
    {
        $payment=Payment::all();
        return view('main.payment.index', compact('payment'));
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function pmdestroy(Payment $payment)
    {
        $payment->delete();
        return redirect()->back()->with("toast"," Payment Delete Successfully");;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function pmshow(Payment $payment)
    {
        $reader=Reader::find($payment->reader_id);
        $reader->coin=$reader->coin+$payment->amount;
        $reader->update();
        $payment->status='done';
        $payment->update();
        return redirect()->route('admin.pmindex')->with("toast"," ပိုက်ဆံထည့်ပီးပါပီ...");
    }

    public function bookList(){
        $books=Book::all();
        return view('main.book.index',compact('books'));
    }


}
