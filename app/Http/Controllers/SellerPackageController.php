<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SellerPackage;
use App\Models\SellerPackageTranslation;
use App\Models\SellerPackagePayment;
use App\Models\Shop;
use Artisan;
use Auth;
use Session;
use Carbon\Carbon;

class SellerPackageController extends Controller
{
    public function __construct() {
        // Staff Permission Check
        $this->middleware(['permission:view_all_seller_packages'])->only('index');
        $this->middleware(['permission:add_seller_package'])->only('create');
        $this->middleware(['permission:edit_seller_package'])->only('edit');
        $this->middleware(['permission:delete_seller_package'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $seller_packages = SellerPackage::all();
        return view('seller_packages.index', compact('seller_packages'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('seller_packages.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $seller_package = new SellerPackage;
        $seller_package->name = $request->name;
        $seller_package->amount = $request->amount;
        $seller_package->product_upload_limit = $request->product_upload_limit;
        if(addon_is_activated('preorder')){
            $seller_package->preorder_product_upload_limit = $request->preorder_product_upload_limit;
        }
        $seller_package->duration = $request->duration;
        $seller_package->logo = $request->logo;
        if ($seller_package->save()) {

            $seller_package_translation = SellerPackageTranslation::firstOrNew(['lang' => env('DEFAULT_LANGUAGE'), 'seller_package_id' => $seller_package->id]);
            $seller_package_translation->name = $request->name;
            $seller_package_translation->save();

            flash(translate('Package has been inserted successfully'))->success();
            return redirect()->route('seller_packages.index');
        } else {
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $lang   = $request->lang;
        $seller_package = SellerPackage::findOrFail($id);
        return view('seller_packages.edit', compact('seller_package', 'lang'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $seller_package = SellerPackage::findOrFail($id);
        if ($request->lang == env("DEFAULT_LANGUAGE")) {
            $seller_package->name = $request->name;
        }
        $seller_package->amount = $request->amount;
        $seller_package->product_upload_limit = $request->product_upload_limit;
        if(addon_is_activated('preorder')){
            $seller_package->preorder_product_upload_limit = $request->preorder_product_upload_limit;
        }

        $seller_package->duration = $request->duration;
        $seller_package->logo = $request->logo;
        if ($seller_package->save()) {

            $seller_package_translation = SellerPackageTranslation::firstOrNew(['lang' => $request->lang, 'seller_package_id' => $seller_package->id]);
            $seller_package_translation->name = $request->name;
            $seller_package_translation->save();
            flash(translate('Package has been inserted successfully'))->success();
            return redirect()->route('seller_packages.index');
        } else {
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $seller_package = SellerPackage::findOrFail($id);
        foreach ($seller_package->seller_package_translations as $key => $seller_package_translation) {
            $seller_package_translation->delete();
        }
        SellerPackage::destroy($id);
        flash(translate('Package has been deleted successfully'))->success();
        return redirect()->route('seller_packages.index');
    }


    //FrontEnd
    //@index
    public function packages_payment_list()
    {
        $seller_packages_payment = SellerPackagePayment::with('seller_package')->where('user_id', Auth::user()->id)->paginate(15);
        return view('seller_packages.seller.packages_payment_list', compact('seller_packages_payment'));
    }

    public function seller_packages_list()
    {
        $seller_packages = SellerPackage::all();
        return view('seller_packages.seller.seller_packages_list', compact('seller_packages'));
    }

    public function purchase_package(Request $request)
    {
        $seller_purchased_package = auth()->user()->shop->seller_package;
        $data['seller_package_id'] = $request->seller_package_id;
        $data['payment_method'] = $request->payment_option;

        $request->session()->put('payment_type', 'seller_package_payment');
        $request->session()->put('payment_data', $data);

        $seller_package = SellerPackage::findOrFail(Session::get('payment_data')['seller_package_id']);

        if ($seller_package->amount == 0) {
            return $this->purchase_payment_done(Session::get('payment_data'), null);
        }
        elseif ($seller_purchased_package != null) 
        {
            $can_purchase = $seller_package->product_upload_limit < $seller_purchased_package->product_upload_limit ? false : true;
            if($can_purchase && addon_is_activated('preorder')) {
                $can_purchase = $seller_package->preorder_product_upload_limit < $seller_purchased_package->preorder_product_upload_limit ? false : true;
            }

            if(!$can_purchase){
                flash(translate('You can not downgrade the package.'))->warning();
                return back();
            }
        }
        $decorator = __NAMESPACE__ . '\\Payment\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $request->payment_option))) . "Controller";
        if (class_exists($decorator)) {
            return (new $decorator)->pay($request);
        }
    }

    public function purchase_payment_done($payment_data, $payment)
    {
        $user = auth()->user();
        $seller = $user->shop;
        $seller->seller_package_id = Session::get('payment_data')['seller_package_id'];
        $seller_package = SellerPackage::findOrFail(Session::get('payment_data')['seller_package_id']);
        $seller->product_upload_limit = $seller_package->product_upload_limit;
        if(addon_is_activated('preorder')){
            $seller->preorder_product_upload_limit = $seller_package->preorder_product_upload_limit;
        }
        $seller->package_invalid_at = date('Y-m-d', strtotime($seller->package_invalid_at . ' +' . $seller_package->duration . 'days'));
        $seller->save();

        $seller_package_payment = new SellerPackagePayment;
        $seller_package_payment->user_id = $user->id;
        $seller_package_payment->seller_package_id = $seller_package->id;
        $seller_package_payment->amount = $seller_package->amount;
        $seller_package_payment->payment_method = Session::get('payment_data')['payment_method'];
        $seller_package_payment->payment_details = $payment;
        $seller_package_payment->approval = 1;
        $seller_package_payment->offline_payment = 2;
        $seller_package_payment->save();

        flash(translate('Package purchasing successful'))->success();
        return redirect()->route('seller.dashboard');
    }

    public function unpublish_products(Request $request)
    {
        foreach (Shop::all() as $shop) {
            if ($shop->package_invalid_at != null && Carbon::now()->diffInDays(Carbon::parse($shop->package_invalid_at), false) <= 0) {
                foreach ($shop->user->products as $product) {
                    $product->published = 0;
                    $product->save();
                }
                foreach ($shop->user->preorderProducts as $preorderProduct) {
                    $preorderProduct->is_published = 0;
                    $preorderProduct->save();
                }
                $shop->seller_package_id = null;
                $shop->package_invalid_at = null;
                $shop->save();
            }
        }
        Artisan::call('cache:clear');
    }

    public function purchase_package_offline(Request $request)
    {
        $seller_package = SellerPackage::findOrFail($request->package_id);
        $user = auth()->user();
        $seller_purchased_package = $user->shop->seller_package;
        if ($user->shop->seller_package != null) {
            $can_purchase = $seller_package->product_upload_limit < $seller_purchased_package->product_upload_limit ? false : true;
            if($can_purchase && addon_is_activated('preorder')) {
                $can_purchase = $seller_package->preorder_product_upload_limit < $seller_purchased_package->preorder_product_upload_limit ? false : true;
            }

            if(!$can_purchase){
                flash(translate('You can not downgrade the package.'))->warning();
                return back();
            }
            flash(translate('You can not downgrade the package'))->warning();
            return redirect()->route('seller.seller_packages_list');
        }
        $seller_package_payment = new SellerPackagePayment;
        $seller_package_payment->user_id = $user->id;
        $seller_package_payment->seller_package_id = $request->package_id;
        $seller_package_payment->amount = $seller_package->amount;
        $seller_package_payment->payment_method = $request->payment_option;
        $seller_package_payment->payment_details = $request->trx_id;
        $seller_package_payment->approval = 0;
        $seller_package_payment->offline_payment = 1;
        $seller_package_payment->reciept = $request->photo;
        $seller_package_payment->save();

        flash(translate('Offline payment has been done. Please wait for response.'))->success();
        return redirect()->route('seller.products');
    }
}
