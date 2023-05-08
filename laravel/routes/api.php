<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'api'], function ($router) {
    Route::get('menu', 'MenuController@index');

    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('register', 'AuthController@register'); 

    Route::resource('notes', 'NotesController');

    Route::resource('resource/{table}/resource', 'ResourceController');
    
    Route::group(['middleware' => 'admin'], function ($router) {

        Route::resource('mail',        'MailController');
        Route::get('prepareSend/{id}', 'MailController@prepareSend')->name('prepareSend');
        Route::post('mailSend/{id}',   'MailController@send')->name('mailSend');

        Route::resource('bread',  'BreadController');   //create BREAD (resource)

        Route::resource('users', 'UsersController')->except( ['create', 'store'] );

        Route::prefix('menu/menu')->group(function () { 
            Route::get('/',         'MenuEditController@index')->name('menu.menu.index');
            Route::get('/create',   'MenuEditController@create')->name('menu.menu.create');
            Route::post('/store',   'MenuEditController@store')->name('menu.menu.store');
            Route::get('/edit',     'MenuEditController@edit')->name('menu.menu.edit');
            Route::post('/update',  'MenuEditController@update')->name('menu.menu.update');
            Route::get('/delete',   'MenuEditController@delete')->name('menu.menu.delete');
        });
        Route::prefix('menu/element')->group(function () { 
            Route::get('/',             'MenuElementController@index')->name('menu.index');
            Route::get('/move-up',      'MenuElementController@moveUp')->name('menu.up');
            Route::get('/move-down',    'MenuElementController@moveDown')->name('menu.down');
            Route::get('/create',       'MenuElementController@create')->name('menu.create');
            Route::post('/store',       'MenuElementController@store')->name('menu.store');
            Route::get('/get-parents',  'MenuElementController@getParents');
            Route::get('/edit',         'MenuElementController@edit')->name('menu.edit');
            Route::post('/update',      'MenuElementController@update')->name('menu.update');
            Route::get('/show',         'MenuElementController@show')->name('menu.show');
            Route::get('/delete',       'MenuElementController@delete')->name('menu.delete');
        });
        Route::prefix('media')->group(function ($router) {
            Route::get('/',                 'MediaController@index')->name('media.folder.index');
            Route::get('/folder/store',     'MediaController@folderAdd')->name('media.folder.add');
            Route::post('/folder/update',   'MediaController@folderUpdate')->name('media.folder.update');
            Route::get('/folder',           'MediaController@folder')->name('media.folder');
            Route::post('/folder/move',     'MediaController@folderMove')->name('media.folder.move');
            Route::post('/folder/delete',   'MediaController@folderDelete')->name('media.folder.delete');;

            Route::post('/file/store',      'MediaController@fileAdd')->name('media.file.add');
            Route::get('/file',             'MediaController@file');
            Route::post('/file/delete',     'MediaController@fileDelete')->name('media.file.delete');
            Route::post('/file/update',     'MediaController@fileUpdate')->name('media.file.update');
            Route::post('/file/move',       'MediaController@fileMove')->name('media.file.move');
            Route::post('/file/cropp',      'MediaController@cropp');
            Route::get('/file/copy',        'MediaController@fileCopy')->name('media.file.copy');

            Route::get('/file/download',    'MediaController@fileDownload');
        });

        Route::resource('roles',        'RolesController');
        Route::get('/roles/move/move-up',      'RolesController@moveUp')->name('roles.up');
        Route::get('/roles/move/move-down',    'RolesController@moveDown')->name('roles.down');

    });
    
    Route::group(['middleware' => 'role:admin|user'], function ($router) {
        Route::prefix('accounts')->group(function () { 
            Route::get('/',         'AccountsController@index')->name('accounts.index');
            Route::post('/store',   'AccountsController@store')->name('accounts.store');
            Route::post('/update',   'AccountsController@update')->name('accounts.update');
            Route::get('/delete/{id}',   'AccountsController@destroy')->name('accounts.delete');
            Route::get('/generate-pdf','AccountsController@generatePDF')->name('accounts.generatePDF');
        });
        
        Route::prefix('allocations')->group(function () { 
            Route::get('/',         'AllocationsController@index')->name('allocations.index');
            Route::post('/store',   'AllocationsController@store')->name('allocations.store');
            Route::post('/update',   'AllocationsController@update')->name('allocations.update');
            Route::get('/delete/{id}',   'AllocationsController@destroy')->name('allocations.delete');
            Route::get('/generate-pdf','AllocationsController@generatePDF')->name('allocations.generatePDF');
        });

        Route::prefix('accountcodes')->group(function () { 
            Route::get('/',         'AccountCodesController@index')->name('accountcodes.index');
            Route::post('/store',   'AccountCodesController@store')->name('accountcodes.store');
            Route::post('/update',   'AccountCodesController@update')->name('accountcodes.update');
            Route::get('/delete/{id}',   'AccountCodesController@destroy')->name('accountcodes.delete');
            Route::get('/generate-pdf','AccountCodesController@generatePDF')->name('accountcodes.generatePDF');
        });
        Route::prefix('papcodes')->group(function () { 
            Route::get('/',         'PapCodesController@index')->name('papcodes.index');
            Route::post('/store',   'PapCodesController@store')->name('papcodes.store');
            Route::post('/update',   'PapCodesController@update')->name('papcodes.update');
            Route::get('/delete/{id}',   'PapCodesController@destroy')->name('papcodes.delete');
            Route::get('/generate-pdf','PapCodesController@generatePDF')->name('papcodes.generatePDF');
        });
        
        Route::prefix('vouchers')->group(function () { 
            Route::get('/', 'VoucherController@index')->name('voucher.getdata');
            Route::post('/store',   'VoucherController@store')->name('voucher.store');
            Route::post('/edit',   'VoucherController@edit')->name('voucher.edit');
            Route::post('/update',   'VoucherController@update')->name('voucher.update');
            Route::get('/delete',   'VoucherController@destroy')->name('voucher.delete');
            Route::get('/deleteObligation',   'VoucherController@deleteObligation')->name('voucher.deleteObligation');
            Route::get('/deleteTran',   'VoucherController@deleteTran')->name('voucher.deleteTran');
            Route::get('/get-lib', 'VoucherController@libraries')->name('voucher.getlib');
        });

    });
    
    Route::prefix('reports')->group(function () { 
        Route::get('get-status', 'ReportController@statusreport');
        Route::get('get-rci', 'ReportController@rci');
        Route::get('get-cdr', 'ReportController@cdr');
        Route::get('get-pap', 'ReportController@pap');
        Route::get('get-summary','ReportController@allocationsummary')->name('allocations.summary');
        Route::get('get-papStatement','ReportController@papStatement')->name('export.pap.statement');

        Route::post('export-summary','ReportController@exportAllocationSummary')->name('export.allocations.summary');
        Route::post('export-status','ReportController@exportStatusReport')->name('export.status.report');
        Route::post('export-pap','ReportController@exportPAPReport')->name('export.pap.report');
        Route::post('export-cdr','ReportController@exportCDRReport')->name('export.cdr.report');
        Route::post('export-rci','ReportController@exportRCIReport')->name('export.rci.report');
        Route::post('export-pap-statement','ReportController@exportPAPStatement')->name('export.rci.report');
    });
    Route::prefix('library')->group(function () { 
        Route::get('/papcodes',   'LibraryController@papcodes')->name('papcodes.list');
        Route::get('/acountcodes',   'LibraryController@acountcodes')->name('acountcodes.list');
        Route::get('/accounts',   'LibraryController@accounts')->name('accounts.list');
        Route::get('/allocations',   'LibraryController@allocations')->name('allocations.list');
    });

    
});


