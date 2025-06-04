<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PickListController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\ItemsManageController;
use App\Http\Controllers\ItemOrderController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\PartyCakesController;
use App\Http\Controllers\PartyCakesManageController;
use App\Http\Controllers\POSController;
use App\Http\Middleware\CheckUserRole;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\UpdateOrderController;
use App\Http\Controllers\PostingController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\BarcodesController;
use App\Http\Controllers\BarcodesetupsController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\DiscblankOpController;
use App\Http\Controllers\InventitemBarcodeController;
use App\Http\Controllers\InventTransacController;
use App\Http\Controllers\JournaltransController;
use App\Http\Controllers\InventJournalTablesController;
use App\Http\Controllers\InventtransreasonController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\NumbersequenceContoller;
use App\Http\Controllers\NumbervaluesController;
use App\Http\Controllers\POSdiscountlinesController;
use App\Http\Controllers\posmmlinegroupsController;
use App\Http\Controllers\POSperiodicdiscountController;
use App\Http\Controllers\POSvalidController;
use App\Http\Controllers\RBOInverntoryController;
use App\Http\Controllers\RBOSpecialgroupController;
use App\Http\Controllers\RBOStoretableController;
use App\Http\Controllers\RboTransacdisController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\UnitTableController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Storage;

/* Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
}); */

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/csrf-token', function () {
    return response()->json(['csrfToken' => csrf_token()]);
});

Route::get('/offline', [HomeController::class, 'offline']);

/* Route::middleware([CheckUserRole::class, 'auth', 'role:ADMIN'])->group(function () { */


/*<==================ROLE:SUPERADMIN====================>*/
Route::middleware(['auth', 'role:SUPERADMIN'])->group(function () {
    
    /*<==================RETAIL====================>*/
    Route::resource('items', ItemsController::class);
    Route::post('/ImportProducts', [ItemsManageController::class, 'store'])->name('import.products');

    Route::get('/retailprice', function () {
        return Inertia::render('retail/salesprice');
    });

    Route::patch('/terminal', [ItemsManageController::class, 'terminal'])->name('retail.terminal');

    /*<===========INVENTORY/REPLENISHMENT==============>*/
    Route::resource('inventtrans', InventTransacController::class);
    Route::resource('inventjournaltrans',JournaltransController::class);
    Route::resource('inventjournaltables',InventJournalTablesController::class);
    Route::resource('inventtransreasons',InventtransreasonController::class);

    Route::resource('inventitembarcodes',InventitemBarcodeController::class);
    Route::resource('taxdatas',TaxController::class);
    Route::resource('rboinventitemretailgroups', RBOInverntoryController::class);
    Route::resource('rbospecialgroups',RBOSpecialgroupController::class);
    Route::resource('units',UnitTableController::class);
    Route::resource('rbostoretables',RBOStoretableController::class);

    /*<==================BARCODES====================>*/
    Route::resource('barcodesetups',BarcodesetupsController::class);
    Route::resource('barcodes',BarcodesController::class);

    /*<==================ID SERIES====================>*/
    Route::resource('nubersequencetables',NumbersequenceContoller::class);
    Route::resource('nubersequencevalues',NumbervaluesController::class);

});


/*<==================ROLE:ADMIN====================>*/
Route::middleware(['auth', 'role:ADMIN,SUPERADMIN'])->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/admin', [HomeController::class, 'admin']);
    
    /*<==================SIGNUP====================>*/
    Route::resource('signup', RegisterController::class);

    /*<==================RETAIL====================>*/
    Route::resource('items', ItemsController::class);
    Route::post('/ImportProducts', [ItemsManageController::class, 'store'])->name('import.products');

    Route::get('/retailprice', function () {
        return Inertia::render('retail/salesprice');
    });

    Route::patch('/terminal', [ItemsManageController::class, 'terminal'])->name('retail.terminal');

    /*<==================ORDER====================>*/
    Route::resource('order', OrderController::class);
    Route::post('EnableOrder', [ItemOrderController::class, 'EnableOrder'])->name('EnableOrder');
    Route::get('ItemOrders/{journalid}', [ItemOrderController::class, 'ItemOrders'])->name('ItemOrders');
    Route::patch('/ItemOrders/post', [ItemOrderController::class, 'post'])->name('item-orders.update');
    Route::resource('ItemOrders', ItemOrderController::class);
    Route::resource('orderingconso', ReportController::class);
    Route::get('/orderingconso2', [UpdateOrderController::class, 'getrange'])->name('orderingconso.getrange');
    Route::get('orderingconsoexport', [ReportController::class, 'export'])->name('export');
    

    /*<==================STORE====================>*/
    Route::resource('store', StoreController::class);

    /*<==================ANNOUNCEMENT====================>*/
    Route::resource('announcement', AnnouncementController::class);
    Route::get('/announcements/{id}', [HomeController::class, 'downloadFile'])->name('announcements.download');

    /*<==================PARTYCAKES====================>*/
    Route::resource('partycakes', PartyCakesController::class);

    /*<===========INVENTORY/REPLENISHMENT==============>*/
    Route::resource('inventtrans', InventTransacController::class);
    Route::resource('inventjournaltrans',JournaltransController::class);
    Route::resource('inventjournaltables',InventJournalTablesController::class);
    Route::resource('inventtransreasons',InventtransreasonController::class);

    Route::resource('inventitembarcodes',InventitemBarcodeController::class);
    Route::resource('taxdatas',TaxController::class);
    Route::resource('rbospecialgroups',RBOSpecialgroupController::class);
    Route::resource('units',UnitTableController::class);
    Route::resource('rbostoretables',RBOStoretableController::class);

    /*<==================BARCODES====================>*/
    Route::resource('barcodesetups',BarcodesetupsController::class);
    Route::resource('barcodes',BarcodesController::class);

    /*<==================ID SERIES====================>*/
    Route::resource('nubersequencetables',NumbersequenceContoller::class);
    Route::resource('nubersequencevalues',NumbervaluesController::class);

    /*<==================PICKLIST====================>*/
    Route::resource('picklist', PickListController::class);

});


/*<==================ROLE:STORE====================>*/
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/admin', [HomeController::class, 'admin']);
    Route::get('/announcements/{id}', [HomeController::class, 'downloadFile'])->name('announcements.download');

    /*<==================RETAIL====================>*/
    Route::resource('items', ItemsController::class);
    Route::post('/ImportProducts', [ItemsManageController::class, 'store'])->name('import.products');

    /*<==================PARTYCAKES====================>*/
    Route::resource('partycakes', PartyCakesController::class);
    Route::get('pc-posted/{id}', [PartyCakesManageController::class, 'posted'])->name('postpartycakes');

    /*<===========INVENTORY/REPLENISHMENT==============>*/
    Route::resource('inventtrans', InventTransacController::class);
    Route::resource('inventjournaltrans',JournaltransController::class);
    Route::resource('inventjournaltables',InventJournalTablesController::class);
    Route::resource('inventtransreasons',InventtransreasonController::class);

    Route::resource('inventitembarcodes',InventitemBarcodeController::class);
    Route::resource('taxdatas',TaxController::class);
    Route::resource('rbospecialgroups',RBOSpecialgroupController::class);
    Route::resource('units',UnitTableController::class);
    Route::resource('rbostoretables',RBOStoretableController::class);

    /*<==================ORDER====================>*/
    Route::resource('order', OrderController::class);
    Route::get('/DeleteOrders', [ItemOrderController::class, 'DeleteOrders']);
    Route::get('ItemOrders/{journalid}', [ItemOrderController::class, 'ItemOrders'])->name('ItemOrders');
    Route::patch('/ItemOrders/getbwproducts', [ItemOrderController::class, 'getbwproducts']);
    Route::patch('/ItemOrders/post', [ItemOrderController::class, 'post'])->name('item-orders.update');
    Route::post('/update-counted-value', [ItemOrderController::class, 'updateCountedValue']);
    Route::resource('ItemOrders', ItemOrderController::class);
    Route::resource('orderingconso', ReportController::class);
    Route::get('/generatetxtfile', [ReportController::class, 'generatetxtfile'])->name('generatetxtfile');
     Route::get('ViewOrders/{journalid}', [ItemOrderController::class, 'ViewOrders'])->name('ViewOrders');

    /*<==================REPORTS====================>*/
    Route::get('orderingconsoexport', [ReportController::class, 'export'])->name('export');
    Route::get('/orderingconso2', [UpdateOrderController::class, 'getrange'])->name('orderingconso.getrange');
    Route::get('/lastmonth', [UpdateOrderController::class, 'lastmonth'])->name('lastmonth');
    Route::get('/lastweek', [UpdateOrderController::class, 'lastweek'])->name('lastweek');
    Route::get('/yesterday', [UpdateOrderController::class, 'yesterday'])->name('yesterday');

    Route::post('/save-file', [PostingController::class, 'saveFile']);


});

Route::get('/table', function () {
    return Inertia::render('VueTable');
})->name('table');

/* Route::get('/menu', function () {
    return Inertia::render('Menu/Index');
}); */

Route::get('/menu', [POSController::class, 'index'])->name('menu');
