<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Variation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{

    public function syncProducts()
    {
        $response = Http::get('https://5fc7a13cf3c77600165d89a8.mockapi.io/api/v5/products');

        if ($response->successful()) {
            $products = $response->json();

            foreach ($products as $productData) {
                Product::updateOrCreate(
                    ['id' => $productData['id']],
                    [
                        'name' => $productData['name'],
                        'price' => $productData['price'],
                    ]
                );
            }

            return response()->json(['message' => 'Products synchronized successfully.']);
        } else {
            return response()->json(['message' => 'Failed to fetch products from API.'], 500);
        }
    }

    private function validateData(array $fields)
    {
        $data = [
            'id' => trim($fields[0]),
            'name' => $fields[1] ?? '',
            'sku' => $fields[2] ?? '',
            'status' => $fields[3] ?? '',
            // 'variations' => $fields[4] ?? [],
            'price' => $fields[5] ?? 0,00,
            'currency' => $fields[6] ?? ''
        ];

        // On vérifie l'existance de l'identifiant et que il est numérique
        if (empty($data['id'] || !is_numeric($data['id']))) {
            return false;
        }

        // On vérifie que le prix est existant et il est supérieur à 0
        if (!is_numeric($data['price']) || $data['price'] < 0) {
            return false;
        }

        return $data;
    }


    public function import(Request $request)
    {
        // On valide le fichie CSV
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ], [
            'file.required'=>'The file product.csv is required',
            'file.file'=>'The file imported have to be a CSV file',
            'file.mimes'=>'The file extention...',
        ]);
        
        $filePath = $request->file('file')->getRealPath();
        $contents = file_get_contents($filePath);

        // On divise le contenu du fichier CSV en un tableau de lignes, chaque ligne dans un élément du tableau
        $lines = explode("\n", $contents);

        // On supprime la première ligne du tableau (header du CSV)
        $lines = array_slice($lines, 1);

        // On récupér tous les identifiants des produits existants dans la base de données
        $existingProductIds = Product::pluck('id')->all();

        $importedIds = [];

        foreach ($lines as $line) {

            // On utilise la fonction suivante pour convertir la ligne CSV en un tableau
            // , comme séparateur par defaut
            $fields = str_getcsv($line, ','); 


            // On enlève les espaces si le cas
            $productId = trim($fields[0]); 

            // On vérifie : si l'identifiant n'existe pas, on continue vers la ligne suivante
            if (empty($productId)) {
                continue;
            }

            // On supprime les produits existants pour les mettre à jour 
            // Comme il était mentionné dans le fichier ImportProducts.php l'énoncé
            if(in_array($productId, $existingProductIds)){
                Product::where('id', $productId)->delete();
            }

            // On valide les données avant de les insérer à la base de données
            $validatedData = $this->validateData($fields);

            if ($validatedData) {
                Product::create(
                    [
                        'id' => $productId,
                        'name' => $validatedData['name'],
                        'sku' => $validatedData['sku'],
                        'status' => $validatedData['status'],
                        // 'variations' => $validatedData['variations'],
                        'price' => $validatedData['price'],
                        'currency' => $validatedData['currency']
                    ]
                );

                // On gére les variations si existantes
                if (!empty($fields[4])) {
                    foreach (explode('],[', trim($fields[4], '[]')) as $variation) {
                        $variationFields = explode(';', trim($variation));
                        
                        // On vérifie que le tableau des variations a 4 éléments
                        if (count($variationFields) == 4) {
                            list($color, $size, $quantity, $isAvailable) = $variationFields;
                            Variation::create([
                                'product_id' => $productId,
                                'color' => $color,
                                'size' => $size,
                                'quantity' => $quantity,
                                'is_available' => filter_var($isAvailable, FILTER_VALIDATE_BOOLEAN)
                            ]);
                        } else {
                            // On gére les cas où les variations ne sont pas correctement formatées
                            return response()->json(['error' => "Invalid variation format for product : $productId"], 400);
                        }
                    }
                }

                // On enregistre les identifiants des nouveaux produits ajoutés
                $importedIds[] = $productId;
            } else {

                // On gére les erreurs si la validation échoue
                return response()->json(['error' => "Invalid data for line : $line"], 400);
            }
        }

        // On supprime les produits qui ne sont pas présents dans le fichier CSV importé
        Product::whereNotIn('id', $importedIds)->update(['deleted_at' => now()]);

        return response()->json(['message' => 'Products imported successfully', 'updated' => count($importedIds)]);
    }
}