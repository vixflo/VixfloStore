<?php

namespace App\Models;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use App\Traits\PreventDemoModeChanges;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Str;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Storage;

class ProductsImport implements ToCollection, WithHeadingRow
{
    use PreventDemoModeChanges;

    protected function preventDemoMode()
    {
        // Verifică dacă aplicația rulează în mod demo
        // Poți seta această variabilă în fișierul de configurare sau altundeva în aplicație
        return config('app.demo_mode', false);
    }

    public function canImport(Collection $rows)
    {
        $demoMode = $this->preventDemoMode();
        return !$demoMode;
    }

    public function collection(Collection $rows)
    {
        $mapping = json_decode(file_get_contents(config_path('xml_mapping.json')), true)['product'];
        $canImport = $this->canImport($rows);

        if ($canImport) {
            foreach ($rows as $row) {
                $productData = $this->mapProductData($row, $mapping);
                $this->insertProduct($productData);
            }

            flash(translate('Products imported successfully'))->success();
        }
    }

    // protected function mapProductData($data, $mapping)
    // {
    //     $productData = [];

    //     foreach ($mapping as $field => $aliases) {
    //         foreach ($aliases as $alias) {
    //             if (isset($data[$alias])) {
    //                 $productData[$field] = $data[$alias];
    //                 break;
    //             }
    //         }

    //         // Atribuire valoare dacă a fost găsită
    //         Log::info('Mapping product data:', $productData);

    //         // Atribuie valoare null dacă nu s-a găsit nimic
    //         if (!isset($productData[$field])) {
    //             $productData[$field] = null;
    //         }
    //     }

    //     // Verifică dacă `name` a fost setat, altfel aruncă o eroare explicită
    // if (!isset($productData['name'])) {
    //     Log::error('Error during Excel import: Missing required field "name".');
    //     throw new \Exception('Missing required field "name".');
    // }

    //     return $productData;
    // }





    private function getMappedValue($data, $fields, $isXML = false)
    {
        foreach ($fields as $field) {
            // Pentru XML, caută direct în array-ul asociativ
            if ($isXML) {
                if (isset($data[$field])) {
                    return $data[$field];
                }
            } else {
                // Pentru Excel/CSV, folosește $data ca array sau obiect
                if (isset($data[$field])) {
                    return $data[$field];
                } elseif (isset($data->$field)) {
                    return $data->$field;
                }
            }
        }
        return null;
    }



    public function importData($row, $isXML = false)
    {
        $mapping = config('xml_mapping.product');
        $productData = [];

        foreach ($mapping as $key => $fields) {
            $productData[$key] = $this->getMappedValue($isXML ? $row : $row->toArray(), $fields, $isXML);
        }

        if (empty($productData['name'])) {
            throw new \Exception('Missing required field "name"');
        }

        // Logica de inserare în baza de date
        $this->insertProduct($productData);
    }

    public function mapProductData($data, $isXML = false)
    {
        $mapping = config('xml_mapping.product');
        $productData = [];

        foreach ($mapping as $key => $fields) {
            $productData[$key] = $this->getMappedValue($data, $fields, $isXML);
        }

        if (empty($productData['name'])) {
            throw new \Exception('Missing required field "name"');
        }

        // Returnează datele procesate
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
