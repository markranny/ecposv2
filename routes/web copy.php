<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\DispatchInventoryController;
use App\Http\Controllers\DispatchInventory1Controller;
use App\Http\Controllers\OpicController;
use App\Http\Controllers\OpicManageController;
use App\Http\Controllers\SpecialOrdersController;
use App\Http\Controllers\PickListController;
use App\Http\Controllers\PickListManageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\ItemsManageController;
use App\Http\Controllers\ItemOrderController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\PartyCakesController;
use App\Http\Controllers\PickListCakesController;
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


/* MOBILE */
use App\Http\Controllers\MobileOrderController;

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

    /*<==================STORE====================>*/
    Route::resource('store', StoreController::class);
});


    /*<==================ROLE:ADMIN====================>*/
    Route::middleware(['auth', 'role:ADMIN,SUPERADMIN'])->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/admin', [HomeController::class, 'admin']);
    
    

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

    /*<==================SIGNUP====================>*/
    Route::resource('signup', RegisterController::class);

    /*<==================STORE====================>*/
    Route::resource('store', StoreController::class);

});

    /*<==================ROLE:OPIC====================>*/
    Route::middleware(['auth', 'role:OPIC,SUPERADMIN'])->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/admin', [HomeController::class, 'admin']);
    
    

    /*<==================RETAIL====================>*/
    Route::resource('items', ItemsController::class);
    Route::post('/ImportProducts', [ItemsManageController::class, 'store'])->name('import.products');

    Route::get('/retailprice', function () {
        return Inertia::render('retail/salesprice');
    });

    Route::patch('/terminal', [ItemsManageController::class, 'terminal'])->name('retail.terminal');

    /*<==================ORDER====================>*/
    Route::resource('orderingconso', ReportController::class);
    Route::get('/orderingconso2', [UpdateOrderController::class, 'getrange'])->name('orderingconso.getrange');
    Route::get('orderingconsoexport', [ReportController::class, 'export'])->name('export');
    Route::post('EnableOrder', [ItemOrderController::class, 'EnableOrder'])->name('EnableOrder');

    Route::resource('opic', OpicController::class);
    Route::get('/opic-2', [OpicManageController::class, 'getrange'])->name('opic.getrange');
    Route::get('opicexport', [OpicController::class, 'export'])->name('opic.export');

    /*<==================PARTYCAKES====================>*/
    Route::resource('partycakes', PartyCakesController::class);
    Route::get('pc-process/{id}', [PartyCakesManageController::class, 'process'])->name('processpartycakes');

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

    /*<==================INVENTORY COUNT====================>*/
    Route::get('mgcount', [ReportController::class, 'mgcount'])->name('mgcount');
    Route::get('/south1', [ReportController::class, 'south1'])->name('south1');
    Route::get('/south2', [ReportController::class, 'south2'])->name('south2');
    Route::get('/south3', [ReportController::class, 'south3'])->name('south3');
    Route::get('/north1', [ReportController::class, 'north1'])->name('north1');
    Route::get('/north2', [ReportController::class, 'north2'])->name('north2');
    Route::get('/central', [ReportController::class, 'central'])->name('central');
    Route::get('/east', [ReportController::class, 'east'])->name('east');
    
    /*<==================BARCODES====================>*/
    Route::resource('barcodesetups',BarcodesetupsController::class);
    Route::resource('barcodes',BarcodesController::class);

    /*<==================ID SERIES====================>*/
    Route::resource('nubersequencetables',NumbersequenceContoller::class);
    Route::resource('nubersequencevalues',NumbervaluesController::class);

    /*<==================PICKLIST====================>*/
    Route::resource('picklist', PickListController::class);
    Route::get('/picklist2', [PickListManageController::class, 'getrange'])->name('picklist.getrange');
    Route::get('/PickListInputData', [PickListController::class, 'PickListInputData'])->name('picklist.PickListInputData');
    Route::get('/cakepicklist', [PickListCakesController::class, 'cakepicklist'])->name('cakepicklist');

    Route::get('/pl-south1', [PickListController::class, 'south1'])->name('pl.south1');
    Route::get('/pl-south2', [PickListController::class, 'south2'])->name('pl.south2');
    Route::get('/pl-south3', [PickListController::class, 'south3'])->name('pl.south3');
    Route::get('/pl-north1', [PickListController::class, 'north1'])->name('pl.north1');
    Route::get('/pl-north2', [PickListController::class, 'north2'])->name('pl.north2');
    Route::get('/pl-central', [PickListController::class, 'central'])->name('pl.central');
    Route::get('/pl-east', [PickListController::class, 'east'])->name('east');
    Route::get('/pl-get-store', [PickListController::class, 'getstore'])->name('picklist.getstore');

    Route::get('/plc-south1', [PickListCakesController::class, 'south1'])->name('plc.south1');
    Route::get('/plc-south2', [PickListCakesController::class, 'south2'])->name('plc.south2');
    Route::get('/plc-south3', [PickListCakesController::class, 'south3'])->name('plc.south3');
    Route::get('/plc-north1', [PickListCakesController::class, 'north1'])->name('plc.north1');
    Route::get('/plc-north2', [PickListCakesController::class, 'north2'])->name('plc.north2');
    Route::get('/plc-central', [PickListCakesController::class, 'central'])->name('plc.central');
    Route::get('/plc-east', [PickListCakesController::class, 'east'])->name('east');
    Route::get('/plc-get-store', [PickListCakesController::class, 'getstore'])->name('cakepicklist.getstore');

    Route::get('/special-orders', [SpecialOrdersController::class, 'specialorders'])->name('specialorders');
    Route::patch('/special-orders/getbwproducts', [SpecialOrdersController::class, 'update']);
    Route::get('SP-ViewOrders/', [SpecialOrdersController::class, 'ViewOrders'])->name('ViewOrders');
    Route::get('sp-postedorders/', [SpecialOrdersController::class, 'postedorders'])->name('postedorders');
    Route::get('/SP-DeleteOrders', [SpecialOrdersController::class, 'DeleteOrders']);
    Route::patch('/special-orders/post', [SpecialOrdersController::class, 'post'])->name('sp-orders.update');
    /* Route::resource('/special-orders/post', SpecialOrdersController::class); */
    Route::get('/specialorders/vieworders', [SpecialOrdersController::class, 'ViewOrders'])->name('sp-vieworders');
    /* Route::resource('SpecialOrders', SpecialOrdersController::class); */

    /*<==================STORE====================>*/
    Route::resource('store', StoreController::class);

    /*<==================FINAL INVENTORY====================>*/
    Route::get('/f-mgcount', [OpicController::class, 'mgcount'])->name('f-mgcount');
    Route::get('/f-south1', [OpicController::class, 'south1'])->name('f-south1');
    Route::get('/f-south2', [OpicController::class, 'south2'])->name('f-south2');
    Route::get('/f-south3', [OpicController::class, 'south3'])->name('f-south3');
    Route::get('/f-north1', [OpicController::class, 'north1'])->name('f-north1');
    Route::get('/f-north2', [OpicController::class, 'north2'])->name('f-north2');
    Route::get('/f-central', [OpicController::class, 'central'])->name('f-central');
    Route::get('/f-east', [OpicController::class, 'east'])->name('f-east');

    Route::resource('f-picklist', OpicController::class);
    Route::get('/f-cakepicklist', [OpicController::class, 'cakepicklist'])->name('cakepicklist');
    Route::get('/finalDR', [OpicController::class, 'finaldr'])->name('finaldr');

    Route::patch('/dr-process/post', [OpicController::class, 'post'])->name('dr-process.update');
});


/*<==================ROLE:PLANNING====================>*/
Route::middleware(['auth', 'role:PLANNING,OPIC,SUPERADMIN'])->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/admin', [HomeController::class, 'admin']);
    
    

    /*<==================ORDER====================>*/
    Route::resource('orderingconso', ReportController::class);
    Route::get('/orderingconso2', [UpdateOrderController::class, 'getrange'])->name('orderingconso.getrange');
    Route::get('orderingconsoexport', [ReportController::class, 'export'])->name('export');

    Route::resource('opic', OpicController::class);
    Route::get('/opic-2', [OpicManageController::class, 'getrange'])->name('opic.getrange');
    Route::get('opicexport', [OpicController::class, 'export'])->name('opic.export');

    /*<==================PARTYCAKES====================>*/
    Route::resource('partycakes', PartyCakesController::class);
    Route::get('pc-process/{id}', [PartyCakesManageController::class, 'process'])->name('processpartycakes');

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

    /*<==================INVENTORY COUNT====================>*/
    Route::get('mgcount', [ReportController::class, 'mgcount'])->name('mgcount');
    Route::get('/south1', [ReportController::class, 'south1'])->name('south1');
    Route::get('/south2', [ReportController::class, 'south2'])->name('south2');
    Route::get('/south3', [ReportController::class, 'south3'])->name('south3');
    Route::get('/north1', [ReportController::class, 'north1'])->name('north1');
    Route::get('/north2', [ReportController::class, 'north2'])->name('north2');
    Route::get('/central', [ReportController::class, 'central'])->name('central');
    Route::get('/east', [ReportController::class, 'east'])->name('east');
    
    /*<==================BARCODES====================>*/
    Route::resource('barcodesetups',BarcodesetupsController::class);
    Route::resource('barcodes',BarcodesController::class);

    /*<==================ID SERIES====================>*/
    Route::resource('nubersequencetables',NumbersequenceContoller::class);
    Route::resource('nubersequencevalues',NumbervaluesController::class);

    /*<==================PICKLIST====================>*/
    Route::resource('picklist', PickListController::class);
    Route::get('/picklist2', [PickListManageController::class, 'getrange'])->name('picklist.getrange');
    Route::get('/PickListInputData', [PickListController::class, 'PickListInputData'])->name('picklist.PickListInputData');
    Route::get('/cakepicklist', [PickListCakesController::class, 'cakepicklist'])->name('cakepicklist');

    Route::get('/pl-south1', [PickListController::class, 'south1'])->name('pl.south1');
    Route::get('/pl-south2', [PickListController::class, 'south2'])->name('pl.south2');
    Route::get('/pl-south3', [PickListController::class, 'south3'])->name('pl.south3');
    Route::get('/pl-north1', [PickListController::class, 'north1'])->name('pl.north1');
    Route::get('/pl-north2', [PickListController::class, 'north2'])->name('pl.north2');
    Route::get('/pl-central', [PickListController::class, 'central'])->name('pl.central');
    Route::get('/pl-east', [PickListController::class, 'east'])->name('east');
    Route::get('/pl-get-store', [PickListController::class, 'getstore'])->name('picklist.getstore');

    Route::get('/plc-south1', [PickListCakesController::class, 'south1'])->name('plc.south1');
    Route::get('/plc-south2', [PickListCakesController::class, 'south2'])->name('plc.south2');
    Route::get('/plc-south3', [PickListCakesController::class, 'south3'])->name('plc.south3');
    Route::get('/plc-north1', [PickListCakesController::class, 'north1'])->name('plc.north1');
    Route::get('/plc-north2', [PickListCakesController::class, 'north2'])->name('plc.north2');
    Route::get('/plc-central', [PickListCakesController::class, 'central'])->name('plc.central');
    Route::get('/plc-east', [PickListCakesController::class, 'east'])->name('east');
    Route::get('/plc-get-store', [PickListCakesController::class, 'getstore'])->name('cakepicklist.getstore');

    Route::get('/special-orders', [SpecialOrdersController::class, 'specialorders'])->name('specialorders');
    Route::patch('/special-orders/getbwproducts', [SpecialOrdersController::class, 'update']);
    Route::get('SP-ViewOrders/', [SpecialOrdersController::class, 'ViewOrders'])->name('ViewOrders');
    Route::get('sp-postedorders/', [SpecialOrdersController::class, 'postedorders'])->name('postedorders');
    Route::get('/SP-DeleteOrders', [SpecialOrdersController::class, 'DeleteOrders']);
    Route::patch('/special-orders/post', [SpecialOrdersController::class, 'post'])->name('sp-orders.update');
    Route::get('/specialorders/vieworders', [SpecialOrdersController::class, 'ViewOrders'])->name('sp-vieworders');


    /*<==================FINAL INVENTORY====================>*/
    Route::get('/f-mgcount', [OpicController::class, 'mgcount'])->name('f-mgcount');
    Route::get('/f-south1', [OpicController::class, 'south1'])->name('f-south1');
    Route::get('/f-south2', [OpicController::class, 'south2'])->name('f-south2');
    Route::get('/f-south3', [OpicController::class, 'south3'])->name('f-south3');
    Route::get('/f-north1', [OpicController::class, 'north1'])->name('f-north1');
    Route::get('/f-north2', [OpicController::class, 'north2'])->name('f-north2');
    Route::get('/f-central', [OpicController::class, 'central'])->name('f-central');
    Route::get('/f-east', [OpicController::class, 'east'])->name('f-east');

    Route::resource('f-picklist', OpicController::class);
    Route::get('/f-cakepicklist', [OpicController::class, 'cakepicklist'])->name('cakepicklist');
    Route::get('/finalDR', [OpicController::class, 'finaldr'])->name('finaldr');

    Route::patch('/dr-process/post', [OpicController::class, 'post'])->name('dr-process.update');

    /*<==================FINAL DR====================>*/
    Route::get('/fdr-south1', [OpicController::class, 'fdrsouth1'])->name('fdr-south1');
    Route::get('/fdr-south2', [OpicController::class, 'fdrsouth2'])->name('fdr-south2');
    Route::get('/fdr-south3', [OpicController::class, 'fdrsouth3'])->name('fdr-south3');
    Route::get('/fdr-north1', [OpicController::class, 'fdrnorth1'])->name('fdr-north1');
    Route::get('/fdr-north2', [OpicController::class, 'fdrnorth2'])->name('fdr-north2');
    Route::get('/fdr-central', [OpicController::class, 'fdrcentral'])->name('fdr-central');
    Route::get('/fdr-east', [OpicController::class, 'fdreast'])->name('fdr-east');
    Route::get('/fdr-daterange', [OpicController::class, 'getrange'])->name('fdr.getrange');

    /*<==================ADD DETAILS====================>*/
    Route::get('/dispatch/add-details', [OpicController::class, 'adddetails'])->name('add-details');
    Route::patch('/dispatch/generate', [OpicController::class, 'generate'])->name('dispatch-generate');
    /* Route::patch('updatedetails', [OpicManageController::class, 'generate'])->name('dispatch-generate'); */
    Route::resource('updatedetails', OpicManageController::class);
    Route::get('/details/sync', [OpicManageController::class, 'sync'])->name('details.sync');


    /*<==================MANAGE FGCOUNT====================>*/
    Route::get('/inventory', [OpicManageController::class, 'inventory'])->name('details.inventory');
    Route::get('/finish-goods/sync', [OpicManageController::class, 'fgsync'])->name('fgsync');
});



/*<==================ROLE:DISPATCH====================>*/
Route::middleware(['auth', 'role:DISPATCH,PLANNING,OPIC,SUPERADMIN'])->group(function () {

    /*<==================DISPATCH INVENTORY====================>*/
    Route::get('Dispatch/Items/{journalid}', [DispatchInventoryController::class, 'items'])->name('dispatch-items');
    Route::patch('/Dispatch/getbwproducts', [DispatchInventory1Controller::class, 'getbwproducts']);
    Route::get('/managefg', [DispatchInventoryController::class, 'managefg'])->name('managefg');
    Route::post('/update-counted-value', [DispatchInventory1Controller::class, 'updateCountedValue']);
    Route::get('/dispatch-inventory', [DispatchInventoryController::class, 'index']);
    Route::resource('d-inventory', DispatchInventoryController::class);
    Route::resource('test', DispatchInventoryController::class);
    Route::get('/test', [DispatchInventoryController::class, 'getSheetData']);
    /* Route::post('/update-counted-value', [DispatchInventory1Controller::class, 'updateCountedValue']); */


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

    /*<==================MOBILE - ORDER====================>*/
    Route::resource('m-order', MobileOrderController::class);
    Route::get('m-ItemOrders/{journalid}', [MobileOrderController::class, 'ItemOrders'])->name('m-ItemOrders');

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
