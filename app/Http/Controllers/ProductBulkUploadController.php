<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Models\ProductsImport;
use App\Models\ProductsExport;
use App\Models\ProductStock;
use App\Models\Upload;

// use Barryvdh\DomPDF\Facade as PDF;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleXMLElement;

class ProductBulkUploadController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:product_bulk_import'])->only('index');
        $this->middleware(['permission:product_bulk_export'])->only('export');
    }

    public function index()
    {
        if (Auth::user()->user_type == 'seller') {
            if (Auth::user()->shop->verification_status) {
                return view('seller.product_bulk_upload.index');
            } else {
                flash(translate('Your shop is not verified yet!'))->warning();
                return back();
            }
        } elseif (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff') {
            return view('backend.product.bulk_upload.index');
        }
    }

    public function export()
    {
        return Excel::download(new ProductsExport, 'products.xlsx');
    }

    public function pdf_download_category()
    {
        $categories = Category::all();

        return PDF::loadView('backend.downloads.category', [
            'categories' => $categories,
        ], [], [])->download('category.pdf');
    }

    public function pdf_download_brand()
    {
        $brands = Brand::all();

        return PDF::loadView('backend.downloads.brand', [
            'brands' => $brands,
        ], [], [])->download('brands.pdf');
    }

    public function pdf_download_seller()
    {
        $users = User::where('user_type', 'seller')->get();

        return PDF::loadView('backend.downloads.user', [
            'users' => $users,
        ], [], [])->download('user.pdf');
    }

    public function bulk_upload(Request $request)
    {
        if ($request->hasFile('bulk_file')) {
            $import = new ProductsImport;
            Excel::import($import, request()->file('bulk_file'));
        }

        return back();
    }

    // Funcția de import a produselor în baza de date din fișierul XML
    public function importXmlFeed(Request $request)
    {
        $request->validate([
            'xml_url' => 'required|url'
        ]);

        $url = $request->input('xml_url');

        try {
            $response = Http::get($url);

            if (!$response->successful()) {
                return back()->with('error', 'Failed to fetch the XML feed. Please check the URL and try again.');
            }

            $xml = @simplexml_load_string($response->body());
            if ($xml === false) {
                return back()->with('error', 'The provided URL does not return a valid XML file.');
            }

            Log::info('Starting XML import from URL: ' . $url);

            // Încarcă mapping-ul din fișierul JSON
            $mapping = json_decode(file_get_contents(config_path('xml_mapping.json')), true)['product'];

            // Asigurăm că $xml->post este întotdeauna un array
            $posts = is_array($xml->post) ? $xml->post : [$xml->post];

            foreach ($posts as $post) {
                Log::info('Processing product with raw data: ' . json_encode($post));

                // Apelăm metoda pentru maparea datelor
                $productData = $this->mapProductData($post, $mapping);

                Log::info('Processed product data: ' . json_encode($productData));

                // Adaptează datele produsului pentru inserare
                $this->insertProduct($productData);
            }

            Log::info('XML import completed successfully.');

            return back()->with('success', 'XML feed imported successfully');
        } catch (\Exception $e) {
            Log::error('Error during XML import: ' . $e->getMessage());
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }







    // Metodă pentru maparea datelor
    protected function mapProductData($data, $mapping)
    {
        $productData = [];

        foreach ($mapping as $field => $aliases) {
            foreach ($aliases as $alias) {
                if (isset($data[$alias])) {
                    $productData[$field] = $data[$alias];
                    Log::info("Găsit valoarea pentru $field: " . $data[$alias]);
                    break;
                }
            }

            // Atribuire valoare dacă a fost găsită
            Log::info('Mapping product data:', $productData);

            // Atribuie valoare null dacă nu s-a găsit nimic
            if (!isset($productData[$field])) {
                $productData[$field] = null;
            }
        }

        // Verifică dacă `name` a fost setat, altfel aruncă o eroare explicită
        if (!isset($productData['name'])) {
            Log::error('Error during XML import: Missing required field "name".');
            throw new \Exception('Missing required field "name".');
        }

        // Verificare pentru `category_id`
        if (empty($productData['category_id']) || !is_numeric($productData['category_id'])) {
            $productData['category_id'] = 51; // ID-ul categoriei "Diverse"
        }

        return $productData;
    }

    protected function insertProduct(array $productData)
    {
        $user = Auth::user();
        $approved = ($user->user_type == 'seller' && get_setting('product_approve_by_admin') == 1) ? 0 : 1;

        // Verificare și atribuire `category_id` dacă este gol sau nevalid
        if (empty($productData['category_id']) || !is_numeric($productData['category_id'])) {
        $productData['category_id'] = 51; // ID-ul categoriei "Diverse"
    }

        $product = Product::create([
            'name' => $productData['name'],
            'description' => $productData['description'],
            'added_by' => $user->user_type == 'seller' ? 'seller' : 'admin',
            'user_id' => $user->user_type == 'seller' ? $user->id : User::where('user_type', 'admin')->first()->id,
            'approved' => $approved,
            'category_id' => $productData['category_id'],
            'brand_id' => $productData['brand_id'],
            'video_provider' => $productData['video_provider'],
            'video_link' => $productData['video_link'],
            'tags' => $productData['tags'],
            'unit_price' => $productData['unit_price'],
            'unit' => $productData['unit'],
            'meta_title' => $productData['meta_title'],
            'meta_description' => $productData['meta_description'],
            'est_shipping_days' => $productData['est_shipping_days'],
            'colors' => json_encode([]),
            'choice_options' => json_encode([]),
            'variations' => json_encode([]),
            'slug' => preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', strtolower($productData['slug']))) . '-' . Str::random(5),
            'thumbnail_img' => $this->downloadThumbnail($productData['thumbnail_img']),
            'photos' => $this->downloadGalleryImages($productData['photos']),
        ]);

        ProductStock::create([
            'product_id' => $product->id,
            'qty' => $productData['current_stock'],
            'price' => $productData['unit_price'],
            'sku' => $productData['sku'],
            'variant' => $productData['variant'] ?? '',
        ]);

        if ($productData['multi_categories'] != null) {
            foreach (explode(',', $productData['multi_categories']) as $category_id) {
                ProductCategory::insert([
                    "product_id" => $product->id,
                    "category_id" => $category_id
                ]);
            }
        }
    }

    public function downloadThumbnail($url)
    {
        try {
            $upload = new Upload;
            $upload->external_link = $url;
            $upload->type = 'image';
            $upload->save();

            return $upload->id;
        } catch (\Exception $e) {
            Log::error('Failed to download thumbnail: ' . $e->getMessage());
        }
        return null;
    }

    public function downloadGalleryImages($urls)
    {
        $data = [];
        foreach (explode(',', str_replace(' ', '', $urls)) as $url) {
            $data[] = $this->downloadThumbnail($url);
        }
        return implode(',', $data);
    }
}
