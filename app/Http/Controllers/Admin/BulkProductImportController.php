<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\Category;
use Illuminate\Support\Str;

class BulkProductImportController extends Controller
{
    /**
     * Show the bulk import form.
     */
    public function index()
    {
        $categories = Category::all();
        return view('admin.modules.products.bulk_import', compact('categories'));
    }

    /**
     * Download CSV template.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product_import_template.csv"',
        ];

        $columns = [
            'name', 'slug', 'description', 'category_id',
            'gender', 'best_seller', 'is_featured', 'customizable'
        ];

        $sampleRows = [
            [
                'Silver Bracelet for Women',
                'silver-bracelet-for-women',
                'Beautiful handcrafted silver bracelet',
                '2',
                'female',
                '1',
                '0',
                '0'
            ],
            [
                "Men's Classic Ring",
                'mens-classic-ring',
                'Timeless ring for men',
                '1',
                'male',
                '0',
                '1',
                '0'
            ],
        ];

        $callback = function () use ($columns, $sampleRows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($sampleRows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Process the uploaded CSV and import products.
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        // Read headers
        $headers = fgetcsv($handle);
        $headers = array_map('trim', $headers);

        $requiredCols = ['name', 'slug', 'category_id', 'gender'];
        foreach ($requiredCols as $col) {
            if (!in_array($col, $headers)) {
                fclose($handle);
                return back()->with('error', "Missing required column: {$col}. Please use the template.");
            }
        }

        $successCount = 0;
        $errors = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            if (count($row) !== count($headers)) {
                $errors[] = "Row {$rowNumber}: Column count mismatch.";
                continue;
            }

            $data = array_combine($headers, $row);
            $data = array_map('trim', $data);

            // Validate required fields
            if (empty($data['name']) || empty($data['category_id']) || empty($data['gender'])) {
                $errors[] = "Row {$rowNumber}: Missing required field (name, category_id, or gender).";
                continue;
            }

            // Validate gender
            if (!in_array($data['gender'], ['male', 'female', 'unisex'])) {
                $errors[] = "Row {$rowNumber}: Invalid gender '{$data['gender']}'. Use male, female, or unisex.";
                continue;
            }

            // Validate category exists
            if (!Category::find($data['category_id'])) {
                $errors[] = "Row {$rowNumber}: Category ID {$data['category_id']} not found.";
                continue;
            }

            // Auto-generate slug
            $baseSlug = !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);
            $slug = $baseSlug;
            $counter = 1;
            while (Products::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            try {
                Products::create([
                    'name'         => $data['name'],
                    'slug'         => $slug,
                    'description'  => $data['description'] ?? null,
                    'category_id'  => (int) $data['category_id'],
                    'gender'       => $data['gender'],
                    'best_seller'  => isset($data['best_seller']) ? (int) $data['best_seller'] : 0,
                    'is_featured'  => isset($data['is_featured']) ? (int) $data['is_featured'] : 0,
                    'customizable' => isset($data['customizable']) ? (int) $data['customizable'] : 0,
                ]);
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNumber}: Failed to save — " . $e->getMessage();
            }
        }

        fclose($handle);

        $message = "{$successCount} product(s) imported successfully.";
        if (!empty($errors)) {
            $message .= ' ' . count($errors) . ' row(s) had errors.';
        }

        return back()->with('import_success', $message)->with('import_errors', $errors);
    }
}
