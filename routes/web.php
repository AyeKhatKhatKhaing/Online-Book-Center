<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::group(['middleware' => ['isAdmin']], function () {
    Route::get('/edit','ProfileController@edit')->name('profile.edit');
    Route::post('/change-password','ProfileController@changePassword')->name('profile.changePassword');
    Route::post('/change-name','ProfileController@changeName')->name('profile.changeName');
    Route::post('/change-email','ProfileController@changeEmail')->name('profile.changeEmail');
    Route::post('/change-photo','ProfileController@changePhoto')->name('profile.changePhoto');
    Route::get('/sample', 'HomeController@sample')->name('sample');
    Route::resource('/author','AuthorController');
    Route::resource('/group','GroupController');
    Route::get('/admin-home','HomeController@admin')->name('adminHome');

    Route::get('/admin/reader','AdminController@reader')->name('admin.rindex');
    Route::get('/admin/reader/{reader}/redit','AdminController@redit')->name('admin.redit');
    Route::put('/admin/reader/{reader}','AdminController@rupdate')->name('admin.rupdate');
    Route::get('/admin/reader/{reader}','AdminController@rshow')->name('admin.rshow');
    Route::get('/admin/reader/{reader}/books','AdminController@rbook')->name('admin.rbook');

    Route::put('/book/{book}','BookController@update')->name('book.update');
    Route::get('/book/{book}/edit','BookController@edit')->name('book.edit');
    Route::get('/admin/book/{id}','AdminController@bshow')->name('admin.bshow');

    Route::get('/admin/chapter/{id}','AdminController@chindex')->name('admin.chindex');
    Route::get('/admin/see-chapter/{chapter}','AdminController@chshow')->name('admin.chshow');
    Route::delete('/admin/chapter/{chapter}','AdminController@chdestroy')->name('admin.chdestroy');

    Route::delete('/admin/category/{category}','AdminController@cdestroy')->name('admin.cdestroy');
    Route::get('/admin/category/{category}/cedit','AdminController@cedit')->name('admin.cedit');
    Route::put('/admin/category/{category}','AdminController@cupdate')->name('admin.cupdate');

    Route::get('/admin/payment','AdminController@pmindex')->name('admin.pmindex');
    Route::get('/admin/see-payment/{payment}','AdminController@pmshow')->name('admin.pmshow');
    Route::delete('/admin/payment/{payment}','AdminController@pmdestroy')->name('admin.pmdestroy');

    Route::get('/book-list','AdminController@bookList')->name('admin.bookList');
    Route::get('/popular-book', 'BookController@popularBook');
    Route::get('/book-status', 'BookController@bookStatus');
});


Route::group(['middleware' => ['isAuthor']], function () {
    Route::get('/author-home', 'HomeController@author')->name('authorHome');
    Route::post('/book','BookController@store')->name('book.store');
    Route::get('/book/create','BookController@create')->name('book.create');
    Route::get('/book','BookController@index')->name('book.index');


    Route::get('/category/create/{id}','CategoryController@create')->name('category.create');
    Route::get('/book-insert/{id}','CategoryController@category')->name('book-insert');
    Route::post('/category','CategoryController@store')->name('category.store');
    Route::get('/category/{id}/edit','CategoryController@edit')->name('category.edit');
    Route::put('/category/{id}','CategoryController@update')->name('category.update');
    Route::put('/category/{id}/done','CategoryController@done')->name('category.done');


    Route::get('/chapter-insert/{id}','ChapterController@chapter')->name('chapter-insert');
    Route::get('/chapter/create/{id}','ChapterController@create')->name('chapter.create');
    Route::post('/chapter','ChapterController@store')->name('chapter.store');
    Route::get('/chapter/{id}','ChapterController@show')->name('chapter.show');
    Route::put('/chapter/{id}','ChapterController@update')->name('chapter.update');
    Route::delete('/chapter/{id}','ChapterController@delete')->name('chapter.destroy');
});
//Route::post('/login/reader', 'Auth\LoginController@readerLogin');
//Route::post('/register/reader', 'Auth\RegisterController@createReader');
