<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\{
    HomeController,
    WasteController,
    WasteItemController,
    MixAndMatchController,
    DispatchInventoryController,
    DispatchInventory1Controller,
    OpicController,
    OpicManageController,
    SpecialOrdersController,
    PickListController,
    PickListManageController,
    ReportController,
    ItemsController,
    ItemsManageController,
    ItemOrderController,
    StoreController,
    AnnouncementController,
    PartyCakesController,
    PickListCakesController,
    PartyCakesManageController,
    POSController,
    UpdateOrderController,
    PostingController,
    RegisterController,
    BarcodesController,
    BarcodesetupsController,
    CustomersController,
    DiscblankOpController,
    InventitemBarcodeController,
    InventTransacController,
    JournaltransController,
    InventJournalTablesController,
    InventtransreasonController,
    LedgerController,
    NumbersequenceContoller,
    NumbervaluesController,
    POSdiscountlinesController,
    posmmlinegroupsController,
    POSperiodicdiscountController,
    POSvalidController,
    RBOInverntoryController,
    RBOSpecialgroupController,
    RBOStoretableController,
    RboTransacdisController,
    TaxController,
    UnitTableController,
    OrderController,
    MobileOrderController,
    CashFundController,
    ReceivedController,
    ReceivedItemsController,
    StockCountingController,
    StockCountingLineController,
    ECReportController,
    ReceivedOrderController,
    UpdateReceivedOrderController,
    StockTransferLineController,
    LoyaltyCardController,
    ItemLinkController,
    StockTransferController,
    AttendanceController,
    ImportProductsController
};

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

Route::get('cls', function(){
    Artisan::call('clear-compiled');
    echo "clear-compiled: complete<br>";
    Artisan::call('cache:clear');
    echo "cache:clear: complete<br>";
    Artisan::call('config:clear');
    echo "config:clear: complete<br>";
    Artisan::call('view:clear');
    echo "view:clear: complete<br>";
    Artisan::call('optimize:clear');
    echo "optimize:clear: complete<br>";
    Artisan::call('config:cache');
    echo "config:cache: complete<br>";
    Artisan::call('view:cache');
    echo "view:cache: complete<br>";
  
  });

  Route::get('inventory-backfill/date-range/{from}/{to}', function($from, $to) {
    Artisan::call('inventory:backfill', [
        '--from' => $from,
        '--to' => $to
    ]);
    return "Inventory backfill completed for date range: $from to $to";
})->middleware(['auth', 'role:SUPERADMIN,ADMIN']);

Route::get('inventory-backfill/days/{days}', function($days) {
    Artisan::call('inventory:backfill', [
        '--days' => $days
    ]);
    return "Inventory backfill completed for the past $days days";
})->middleware(['auth', 'role:SUPERADMIN,ADMIN']);

Route::get('inventory-backfill/yesterday', function() {
    Artisan::call('inventory:backfill', [
        '--yesterday' => true
    ]);
    return "Inventory backfill completed for yesterday";
})->middleware(['auth', 'role:SUPERADMIN,ADMIN']);

Route::get('/offline', [HomeController::class, 'offline']);

Route::resource('items', ItemsController::class);
Route::get('warehouse', [ItemsManageController::class, 'warehouse'])->name('warehouse');
Route::resource('announcement', AnnouncementController::class);
Route::get('/announcements/{id}', [HomeController::class, 'downloadFile'])->name('announcements.download');
Route::resource('partycakes', PartyCakesController::class);

// SUPERADMIN routes
Route::middleware(['auth', 'role:SUPERADMIN'])->group(function () {
    Route::post('/ImportProducts', [ItemsManageController::class, 'store'])->name('import.products');
    Route::get('/retailprice', function () {
        return Inertia::render('retail/salesprice');
    });
    Route::patch('/terminal', [ItemsManageController::class, 'terminal'])->name('retail.terminal');
    Route::resource('rboinventitemretailgroups', RBOInverntoryController::class);

    /*<==================DISCOUNT====================>*/
    Route::resource('isdiscblankoperations',DiscblankOpController::class);
    Route::resource('rbotransactiondiscounttrans',RboTransacdisController::class);
    Route::resource('posperiodicdiscounts',POSperiodicdiscountController::class);
    Route::get('MNM/{offerid}/{discountType}', [POSdiscountlinesController::class, 'MNM'])->name('MNM.show');
    Route::resource('posperiodicdiscountlines',POSdiscountlinesController::class);
    Route::resource('posdiscvalidationperiods',POSvalidController::class);
    Route::resource('posmmlinegroups',posmmlinegroupsController::class);
    Route::get('POSMMMLINEGROUPS/{offerid}', [posmmlinegroupsController::class, 'POSMMMLINEGROUPS'])->name('POSMMMLINEGROUPS');

    /* Route::get('/items/{itemid}/links', [ItemLinkController::class, 'index'])->name('item-links.index');
    Route::post('/item-links', [ItemLinkController::class, 'store'])->name('item-links.store');
    Route::put('/item-links/{itemLink}', [ItemLinkController::class, 'update'])->name('item-links.update');
    Route::delete('/item-links/{itemLink}', [ItemLinkController::class, 'destroy'])->name('item-links.destroy'); */

    Route::get('/items/{itemid}/links', [ItemLinkController::class, 'index'])->name('item-links.index');
    Route::post('/item-links', [ItemLinkController::class, 'store'])->name('item-links.store');
    Route::put('/item-links/{itemLink}', [ItemLinkController::class, 'update'])->name('item-links.update');
    Route::delete('/item-links/{itemLink}', [ItemLinkController::class, 'destroy'])->name('item-links.destroy');

    // Show import form
    Route::get('/products/import', [ImportProductsController::class, 'showImportForm'])
        ->name('products.import.form');
    
    // Handle import form submission
    Route::post('/products/import', [ImportProductsController::class, 'importExcel'])
        ->name('products.import');
});

// ADMIN && SUPERADMIN routes
Route::middleware(['auth', 'role:ADMIN,SUPERADMIN'])->group(function () {
    Route::post('reset-order', [ReportController::class, 'resetorder'])->name('resetorder');
    Route::resource('signup', RegisterController::class);
    Route::get('/getcurrentstocks', [ItemOrderController::class, 'fgsync'])->name('fgsync');
    Route::get('/autopost', [ItemOrderController::class, 'autopost'])->name('autopost');

    Route::get('/get-stores', [HomeController::class, 'getStores'])->name('get.stores');
    Route::post('/get-metrics', [HomeController::class, 'getMetrics'])->name('get.metrics');
    Route::post('/get-top-bottom-products', [HomeController::class, 'getTopBottomProducts'])->name('get.top.bottom.products');
    Route::post('/get-monthly-sales', [HomeController::class, 'getMonthlySales'])->name('get.monthly.sales');
    Route::post('/get-top-wastes', [HomeController::class, 'getTopWastes'])->name('get.top.wastes');

    Route::resource('attendance', AttendanceController::class);

});

// ADMIN && SUPERADMIN && OPIC routes
Route::middleware(['auth', 'role:ADMIN,SUPERADMIN,OPIC'])->group(function () {
    Route::resource('store', StoreController::class);
    Route::post('EnableOrder', [ItemOrderController::class, 'EnableOrder'])->name('EnableOrder');
});

// SUPERADMIN && DISPATCH && OPIC && PLANNING routes
Route::middleware(['auth', 'role:SUPERADMIN,DISPATCH,OPIC,PLANNING'])->group(function () {
    Route::get('Dispatch/Items/{journalid}', [DispatchInventoryController::class, 'items'])->name('dispatch-items');
    Route::patch('/Dispatch/getbwproducts', [DispatchInventory1Controller::class, 'getbwproducts']);
    Route::get('/managefg', [DispatchInventoryController::class, 'managefg'])->name('managefg');
    Route::post('/update-counted-value', [DispatchInventory1Controller::class, 'updateCountedValue']);
    Route::get('/dispatch-inventory', [DispatchInventoryController::class, 'index']);
    Route::resource('d-inventory', DispatchInventoryController::class);
    Route::get('/test', [DispatchInventoryController::class, 'getSheetData']);   
});

// SUPERADMIN && OPIC && PLANNING routes
Route::middleware(['auth', 'role:SUPERADMIN,OPIC,PLANNING'])->group(function () {
    // Report routes
    Route::get('mgcount', [ReportController::class, 'mgcount'])->name('mgcount');
    Route::get('/south1', [ReportController::class, 'south1'])->name('south1');
    Route::get('/south2', [ReportController::class, 'south2'])->name('south2');
    Route::get('/south3', [ReportController::class, 'south3'])->name('south3');
    Route::get('/north1', [ReportController::class, 'north1'])->name('north1');
    Route::get('/north2', [ReportController::class, 'north2'])->name('north2');
    Route::get('/central', [ReportController::class, 'central'])->name('central');
    Route::get('/east', [ReportController::class, 'east'])->name('east');

    // Picklist routes
    Route::resource('picklist', PickListController::class);
    Route::get('/picklist2', [PickListManageController::class, 'getrange'])->name('picklist.getrange');
    Route::get('/PickListInputData', [PickListController::class, 'PickListInputData'])->name('picklist.PickListInputData');
    Route::get('/cakepicklist', [PickListCakesController::class, 'cakepicklist'])->name('cakepicklist');

    // PL routes
    Route::get('/pl-south1', [PickListController::class, 'south1'])->name('pl.south1');
    Route::get('/pl-south2', [PickListController::class, 'south2'])->name('pl.south2');
    Route::get('/pl-south3', [PickListController::class, 'south3'])->name('pl.south3');
    Route::get('/pl-north1', [PickListController::class, 'north1'])->name('pl.north1');
    Route::get('/pl-north2', [PickListController::class, 'north2'])->name('pl.north2');
    Route::get('/pl-central', [PickListController::class, 'central'])->name('pl.central');
    Route::get('/pl-east', [PickListController::class, 'east'])->name('east');
    Route::get('/pl-get-store', [PickListController::class, 'getstore'])->name('picklist.getstore');

    // PLC routes
    Route::get('/plc-south1', [PickListCakesController::class, 'south1'])->name('plc.south1');
    Route::get('/plc-south2', [PickListCakesController::class, 'south2'])->name('plc.south2');
    Route::get('/plc-south3', [PickListCakesController::class, 'south3'])->name('plc.south3');
    Route::get('/plc-north1', [PickListCakesController::class, 'north1'])->name('plc.north1');
    Route::get('/plc-north2', [PickListCakesController::class, 'north2'])->name('plc.north2');
    Route::get('/plc-central', [PickListCakesController::class, 'central'])->name('plc.central');
    Route::get('/plc-east', [PickListCakesController::class, 'east'])->name('east');
    Route::get('/plc-get-store', [PickListCakesController::class, 'getstore'])->name('cakepicklist.getstore');

    // Special orders routes
    Route::get('/special-orders', [SpecialOrdersController::class, 'specialorders'])->name('specialorders');
    Route::patch('/special-orders/getbwproducts', [SpecialOrdersController::class, 'update']);
    Route::get('SP-ViewOrders/', [SpecialOrdersController::class, 'ViewOrders'])->name('ViewOrders');
    Route::get('sp-postedorders/', [SpecialOrdersController::class, 'postedorders'])->name('postedorders');
    Route::get('/SP-DeleteOrders', [SpecialOrdersController::class, 'DeleteOrders']);
    Route::patch('/special-orders/post', [SpecialOrdersController::class, 'post'])->name('sp-orders.update');
    Route::get('/specialorders/vieworders', [SpecialOrdersController::class, 'ViewOrders'])->name('sp-vieworders');

    // OPIC routes
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

    Route::get('pc-process/{id}', [PartyCakesManageController::class, 'process'])->name('processpartycakes');
    Route::resource('opic', OpicController::class);
    Route::get('/opic-2', [OpicManageController::class, 'getrange'])->name('opic.getrange');
    Route::get('opicexport', [OpicController::class, 'export'])->name('opic.export');

    // FDR routes
    Route::get('/fdr-south1', [OpicController::class, 'fdrsouth1'])->name('fdr-south1');
    Route::get('/fdr-south2', [OpicController::class, 'fdrsouth2'])->name('fdr-south2');
    Route::get('/fdr-south3', [OpicController::class, 'fdrsouth3'])->name('fdr-south3');
    Route::get('/fdr-north1', [OpicController::class, 'fdrnorth1'])->name('fdr-north1');
    Route::get('/fdr-north2', [OpicController::class, 'fdrnorth2'])->name('fdr-north2');
    Route::get('/fdr-central', [OpicController::class, 'fdrcentral'])->name('fdr-central');
    Route::get('/fdr-east', [OpicController::class, 'fdreast'])->name('fdr-east');
    Route::get('/fdr-daterange', [OpicController::class, 'getrange'])->name('fdr.getrange');
    Route::get('/dispatch/add-details', [OpicController::class, 'adddetails'])->name('add-details');
    Route::patch('/dispatch/generate', [OpicController::class, 'generate'])->name('dispatch-generate');
    Route::resource('updatedetails', OpicManageController::class);
    Route::get('/details/sync', [OpicManageController::class, 'sync'])->name('details.sync');
    Route::get('/inventory', [OpicManageController::class, 'inventory'])->name('details.inventory');
    Route::get('/finish-goods/sync', [OpicManageController::class, 'fgsync'])->name('fgsync');
});

// SUPERADMIN && ADMIN && OPIC && PLANNING && STORE routes
Route::middleware(['auth', 'role:SUPERADMIN,STORE,ADMIN,OPIC,PLANNING'])->group(function () {
    Route::resource('order', OrderController::class);
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/admin', [HomeController::class, 'admin']);
    
    Route::resource('orderingconso', ReportController::class);
    Route::get('/orderingconso2', [UpdateOrderController::class, 'getrange'])->name('orderingconso.getrange');
    Route::get('orderingconsoexport', [ReportController::class, 'export'])->name('export');

    Route::get('/warehouse-reports', [ReportController::class, 'warehouseconso'])->name('warehouseconso');
    Route::get('/warehouse-daterange', [UpdateOrderController::class, 'warehousegetrange'])->name('warehouse.getrange');
    Route::get('/warehouse-export', [ReportController::class, 'warehouseexport'])->name('warehouseexport');

    Route::get('receivedorderconso', [ECReportController::class, 'ro'])->name('receivedorderconso.ro');

    /* Route::resource('receivedorderconso', ReceivedOrderController::class);
    Route::get('/receivedorderconso2', [UpdateReceivedOrderController::class, 'receivedorderconsogetrange'])->name('receivedorderconso.getrange');
    Route::get('receivedorderconsoexport', [ReceivedOrderController::class, 'receivedorderconsoexport'])->name('receivedorderconsoexport');
    Route::get('/received-warehouse-reports', [ReceivedOrderController::class, 'receivedwarehouseconso'])->name('receivedwarehouseconso');
    Route::get('/received-warehouse-daterange', [UpdateReceivedOrderController::class, 'receivedwarehousegetrange'])->name('receivedwarehouse.getrange');
    Route::get('/received-warehouse-export', [ReceivedOrderController::class, 'receivedwarehouseexport'])->name('receivedwarehouseexport');
    Route::get('/received-lastmonth', [UpdateReceivedOrderController::class, 'lastmonth'])->name('receivedlastmonth');
    Route::get('/received-lastweek', [UpdateReceivedOrderController::class, 'lastweek'])->name('receivedlastweek');
    Route::get('/received-yesterday', [UpdateReceivedOrderController::class, 'yesterday'])->name('receivedyesterday'); */

    Route::get('/lastmonth', [UpdateOrderController::class, 'lastmonth'])->name('lastmonth');
    Route::get('/lastweek', [UpdateOrderController::class, 'lastweek'])->name('lastweek');
    Route::get('/yesterday', [UpdateOrderController::class, 'yesterday'])->name('yesterday');

    Route::get('reports', [ECReportController::class, 'index'])->name('reports.test');
    Route::get('account-receivable', [ECReportController::class, 'ar'])->name('reports.ar');
    Route::get('employee-charge', [ECReportController::class, 'ec'])->name('reports.ec');
    Route::get('bad-orders', [ECReportController::class, 'bo'])->name('reports.bo');
    Route::get('regular-discount', [ECReportController::class, 'rd'])->name('reports.rd');
    Route::get('marketing-discount', [ECReportController::class, 'md'])->name('reports.md');
    Route::get('variance', [ECReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('sales-report', [ECReportController::class, 'sales'])->name('reports.sales');
    Route::get('tsales-report', [ECReportController::class, 'tsales'])->name('reports.tsales');
    Route::get('itemsales-report', [ECReportController::class, 'itemsales'])->name('reports.itemsales');

    /*<==================CUSTOMERS====================>*/
    Route::resource('customers', CustomersController::class);
    Route::get('ledger/{accountnum}', [LedgerController::class, 'ledger'])->name('ledger');
    Route::resource('customerledgerentries', LedgerController::class);
});

// STORE routes
Route::middleware(['auth', 'role:STORE'])->group(function () {
    Route::get('pc-posted/{id}', [PartyCakesManageController::class, 'posted'])->name('postpartycakes');
    Route::get('pc-received/{id}', [PartyCakesManageController::class, 'received'])->name('receivedpartycakes');
    Route::post('/save-file', [ItemOrderController::class, 'saveFile']);
    Route::post('/post-order', [ItemOrderController::class, 'post']);
    Route::get('/pos', [HomeController::class, 'pos'])->name('pos');    
    Route::get('/menu', [POSController::class, 'index'])->name('menu');
    Route::get('/windows/menu/{id}', [POSController::class, 'menu'])->name('windows');
    Route::post('windows/menu/addtocart/{id}/{winid}/{ar}/{customers}', [POSController::class, 'addtocart'])->name('addtocart');
    Route::post('/submit-order', [POSController::class, 'submitorder']);
    Route::get('/generate-xread', [POSController::class, 'generateXRead']);
    Route::post('/print-xread', [POSController::class, 'printXRead']);
    Route::get('/generate-zread', [POSController::class, 'generateZRead']);
    Route::post('/print-zread', [POSController::class, 'printZRead']);
    Route::post('update-price-quantity', [POSController::class, 'updatePriceAndQuantity']);

    Route::get('/daily-journal', [POSController::class, 'getDailyJournal']);
    Route::get('/transaction-sales/{transactionid}', [POSController::class, 'getTransactionSales']);
    Route::post('/reprint-receipt', [POSController::class, 'reprintReceipt']);
    Route::post('/tender-declaration', [POSController::class, 'tender']);
    Route::post('/return-transaction', [POSController::class, 'returnTransaction']);

    Route::get('/mix-match/discounts', [MixAndMatchController::class, 'getDiscounts']);
    Route::post('/mix-match/submit-order', [MixAndMatchController::class, 'saveOnTheCarts']);
    
    Route::get('/DeleteOrders', [ItemOrderController::class, 'DeleteOrders']);
    Route::get('ItemOrders/{journalid}', [ItemOrderController::class, 'ItemOrders'])->name('ItemOrders');
    Route::patch('/ItemOrders/getbwproducts', [ItemOrderController::class, 'getbwproducts']);
    Route::patch('/ItemOrders/post', [ItemOrderController::class, 'post'])->name('item-orders.update');
    Route::post('/update-counted-value', [ItemOrderController::class, 'updateCountedValue']);
    Route::resource('ItemOrders', ItemOrderController::class);
    Route::get('/generatetxtfile', [ReportController::class, 'generatetxtfile'])->name('generatetxtfile');
    Route::get('ViewOrders/{journalid}', [ItemOrderController::class, 'ViewOrders'])->name('ViewOrders');

    Route::resource('m-order', MobileOrderController::class);
    Route::get('m-ItemOrders/{journalid}', [MobileOrderController::class, 'ItemOrders'])->name('m-ItemOrders');

    Route::get('/warehouse/orders/{journalid}', [ItemOrderController::class, 'warehouseorders'])->name('warehouseorders');
    Route::get('/warehouse/ViewOrders/{journalid}', [ItemOrderController::class, 'wViewOrders'])->name('wViewOrders');

    Route::resource('waste', WasteController::class);
    Route::get('WasteItem/{journalid}', [WasteItemController::class, 'WasteItem'])->name('WasteItem');
    Route::patch('/WasteItem/getbwproducts', [WasteItemController::class, 'getbwproducts']);
    Route::patch('/WasteItem/post', [WasteItemController::class, 'post'])->name('item-orders.update');
    Route::get('/waste/print/{journalid}', [WasteItemController::class, 'printReceipt'])->name('waste.print');
    Route::resource('WasteItem', WasteItemController::class);
    Route::get('ViewWasteItem/{journalid}', [WasteItemController::class, 'ViewWasteItem'])->name('ViewWasteItem');

    /* Route::resource('/Received', ReceivedController::class);
    Route::get('ReceivedItems/{journalid}', [ReceivedItemsController::class, 'ReceivedOrders'])->name('ReceivedOrders');
    Route::patch('/ReceivedItems/getbwproducts', [ReceivedItemsController::class, 'getbwproducts']);
    Route::patch('/ReceivedItems/post', [ReceivedItemsController::class, 'post'])->name('item-orders.update');
    Route::post('/received-update-counted-value', [ReceivedItemsController::class, 'updateCountedValue']);
    Route::resource('ReceivedItems', ReceivedItemsController::class);
    Route::get('ReceivedViewOrders/{journalid}', [ReceivedItemsController::class, 'ViewOrders'])->name('ViewOrders');
    Route::get('/Receivedwarehouse/orders/{journalid}', [ReceivedItemsController::class, 'warehouseorders'])->name('Receivedwarehouseorders');
    Route::get('/Receivedwarehouse/ViewOrders/{journalid}', [ReceivedItemsController::class, 'wViewOrders'])->name('ReceivedwViewOrders');
    Route::post('/post-receivedorder', [ReceivedItemsController::class, 'post']); */

    Route::resource('/StockCounting', StockCountingController::class);
    Route::get('StockCountingLine/{journalid}', [StockCountingLineController::class, 'StockCountingLine'])->name('StockCountingLine');
    Route::patch('/StockCountingLine/getbwproducts', [StockCountingLineController::class, 'getbwproducts']);
    Route::patch('/StockCountingLine/post', [StockCountingLineController::class, 'post'])->name('item-orders.update');
    Route::post('/StockCountingLine-update-counted-value', [StockCountingLineController::class, 'updateCountedValue']);
    Route::resource('StockCountingLine', StockCountingLineController::class);
    Route::get('ViewStockCountingLine/{journalid}', [StockCountingLineController::class, 'ViewOrders'])->name('ViewOrders');
    Route::post('/post-stockcounting', [StockCountingLineController::class, 'post']);

    Route::get('/StockTransfer', [StockTransferController::class, 'index'])->name('StockTransfer');
    Route::get('/StockTransfer/create', [StockTransferController::class, 'create'])->name('StockTransfer.create');
    Route::post('/StockTransfer', [StockTransferController::class, 'store']);
    Route::post('/post-stocktransfer', [StockTransferController::class, 'post']);
    Route::patch('/StockTransferLine/getbwproducts', [StockTransferLineController::class, 'getbwproducts']);

    Route::get('StockTransfer/{journalid}', [StockTransferLineController::class, 'index'])->name('StockTransferLine');
    Route::post('/receive-stocktransfer', [StockTransferController::class, 'receive'])->name('receive-stocktransfer');
    Route::post('/posted-stocktransfer', [StockTransferController::class, 'posted'])->name('posted-stocktransfer');

    Route::get('/loyalty-cards', [LoyaltyCardController::class, 'index'])->name('loyalty-cards.index');
    Route::get('/loyalty-cards/{customer}', [LoyaltyCardController::class, 'show'])->name('loyalty-cards.show');
    Route::post('/loyalty-cards', [LoyaltyCardController::class, 'store'])->name('loyalty-cards.store');
    Route::put('/loyalty-cards/{loyaltyCard}', [LoyaltyCardController::class, 'update'])->name('loyalty-cards.update');
    Route::delete('/loyalty-cards/{loyaltyCard}', [LoyaltyCardController::class, 'destroy'])->name('loyalty-cards.destroy');
    Route::post('/loyalty-cards/add-points', [LoyaltyCardController::class, 'addPoints'])->name('loyalty-cards.add-points');
    Route::post('/loyalty-cards/redeem-points', [LoyaltyCardController::class, 'redeemPoints'])->name('loyalty-cards.redeem-points');

    Route::get('/stock-transfer', [StockTransferController::class, 'index'])
        ->name('stock-transfer.index');
    
    Route::post('/stock-transfer', [StockTransferController::class, 'store'])
        ->name('stock-transfer.store');
    
    Route::post('/stock-transfer/{stockTransfer}/receive', [StockTransferController::class, 'receive'])
        ->name('stock-transfer.receive');
    
    Route::post('/stock-transfer/{stockTransfer}/cancel', [StockTransferController::class, 'cancel'])
        ->name('stock-transfer.cancel');

    Route::patch('/stock-transfer/{transfer}/status', [StockTransferController::class, 'updateStatus'])
        ->name('stock-transfer.updateStatus');

    Route::get('/getstocktransfer/{journalid}', [StockCountingLineController::class, 'getstocktransfer']);

    Route::post('/postbatch', [StockCountingLineController::class, 'postbatch'])->name('postbatch');

    Route::post('/stock-counting-line/update-all-counted-values ', [StockCountingLineController::class, 'updateAllCountedValues']); 

    
});