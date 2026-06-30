<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CertificateController extends Controller
{
    // ── Validation Rules ──────────────────────────────────────────────────
    protected function validationRules($id = null)
    {
        return [
            'title' => 'required|string|min:3|max:255',
            'institution' => 'required|string|min:2|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'type' => 'required|string|in:CSEE,ACSEE,Degree,Diploma,Certificate,Certification,Professional',
            'level' => 'required|string|in:secondary,tertiary,professional,certificate',
            'file_path' => 'required|string|max:500',
            'file_type' => 'required|string|in:pdf,image,doc,excel',
            'thumbnail_path' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ];
    }

    // ── Custom Error Messages ─────────────────────────────────────────────
    protected function validationMessages()
    {
        return [
            'title.required' => 'Certificate title is required.',
            'title.min' => 'Title must be at least 3 characters.',
            'title.max' => 'Title cannot exceed 255 characters.',
            'institution.required' => 'Institution name is required.',
            'institution.min' => 'Institution must be at least 2 characters.',
            'institution.max' => 'Institution cannot exceed 255 characters.',
            'year.required' => 'Year is required.',
            'year.integer' => 'Year must be a valid number.',
            'year.min' => 'Year must be 1900 or later.',
            'year.max' => 'Year cannot be in the future.',
            'type.required' => 'Certificate type is required.',
            'type.in' => 'Type must be CSEE, ACSEE, Degree, Diploma, Certificate, Certification, or Professional.',
            'level.required' => 'Education level is required.',
            'level.in' => 'Level must be secondary, tertiary, professional, or certificate.',
            'file_path.required' => 'Certificate file is required.',
            'file_type.required' => 'File type is required.',
            'file_type.in' => 'File type must be PDF, Image, DOC, or Excel.',
            'order.integer' => 'Order must be a number.',
            'order.min' => 'Order cannot be negative.',
        ];
    }

    // ── Index ──────────────────────────────────────────────────────────────
    public function index()
    {
        try {
            $certificates = Certificate::where('is_active', true)
                ->orderBy('order')
                ->orderBy('year', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $certificates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch certificates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ── Store ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                $this->validationRules(),
                $this->validationMessages()
            );

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();
            
            // Clean the file_path if it's a full URL
            if (isset($validated['file_path']) && filter_var($validated['file_path'], FILTER_VALIDATE_URL)) {
                $parsedUrl = parse_url($validated['file_path']);
                if (isset($parsedUrl['path'])) {
                    $validated['file_path'] = ltrim($parsedUrl['path'], '/');
                    $validated['file_path'] = str_replace('storage/', '', $validated['file_path']);
                }
            }

            $certificate = Certificate::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Certificate created successfully',
                'data' => $certificate
            ], 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create certificate: ' . $e->getMessage()
            ], 500);
        }
    }

    // ── Update ─────────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        try {
            $certificate = Certificate::findOrFail($id);

            $validator = Validator::make(
                $request->all(),
                $this->validationRules($id),
                $this->validationMessages()
            );

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();
            
            // Clean the file_path if it's a full URL
            if (isset($validated['file_path']) && filter_var($validated['file_path'], FILTER_VALIDATE_URL)) {
                $parsedUrl = parse_url($validated['file_path']);
                if (isset($parsedUrl['path'])) {
                    $validated['file_path'] = ltrim($parsedUrl['path'], '/');
                    $validated['file_path'] = str_replace('storage/', '', $validated['file_path']);
                }
            }

            $certificate->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Certificate updated successfully',
                'data' => $certificate
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update certificate: ' . $e->getMessage()
            ], 500);
        }
    }

    // ── Show ───────────────────────────────────────────────────────────────
    public function show($id)
    {
        try {
            $certificate = Certificate::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $certificate
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch certificate: ' . $e->getMessage()
            ], 500);
        }
    }

    // ── Delete ─────────────────────────────────────────────────────────────
    public function destroy($id)
    {
        try {
            $certificate = Certificate::findOrFail($id);
            
            // Delete file if exists
            if ($certificate->file_path && Storage::disk('public')->exists($certificate->file_path)) {
                Storage::disk('public')->delete($certificate->file_path);
            }
            
            $certificate->delete();

            return response()->json([
                'success' => true,
                'message' => 'Certificate deleted successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete certificate: ' . $e->getMessage()
            ], 500);
        }
    }

    // ── Toggle Active ─────────────────────────────────────────────────────
    public function toggleActive($id)
    {
        try {
            $certificate = Certificate::findOrFail($id);
            $certificate->is_active = !$certificate->is_active;
            $certificate->save();

            return response()->json([
                'success' => true,
                'message' => $certificate->is_active ? 'Certificate activated' : 'Certificate deactivated',
                'data' => $certificate
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle certificate status: ' . $e->getMessage()
            ], 500);
        }
    }

    // ── Reorder ────────────────────────────────────────────────────────────
    public function reorder(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'orders' => 'required|array',
                'orders.*.id' => 'required|integer|exists:certificates,id',
                'orders.*.order' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            foreach ($request->orders as $item) {
                Certificate::where('id', $item['id'])->update(['order' => $item['order']]);
            }

            $certificates = Certificate::orderBy('order')->get();

            return response()->json([
                'success' => true,
                'message' => 'Certificates reordered successfully',
                'data' => $certificates
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder certificates: ' . $e->getMessage()
            ], 500);
        }
    }

    // ── Upload File ────────────────────────────────────────────────────────
    public function upload(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // ✅ Create directory if it doesn't exist
            if (!Storage::disk('public')->exists('certificates')) {
                Storage::disk('public')->makeDirectory('certificates', 0755, true);
            }

            $file = $request->file('file');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            $path = $file->storeAs('certificates', $filename, 'public');

            // Determine file type
            $extension = $file->getClientOriginalExtension();
            $fileType = match(strtolower($extension)) {
                'pdf' => 'pdf',
                'jpg', 'jpeg', 'png', 'gif', 'webp' => 'image',
                'doc', 'docx' => 'doc',
                'xls', 'xlsx' => 'excel',
                default => 'pdf',
            };

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'path' => Storage::url($path),
                'filename' => $filename,
                'file_type' => $fileType,
            ]);

        } catch (\Exception $e) {
            Log::error('Certificate upload error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file: ' . $e->getMessage()
            ], 500);
        }
    }
}