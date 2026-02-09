<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Documents;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentRequest;
use App\Models\Document;
use Exception;
use Symfony\Component\HttpFoundation\Response;

final class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::all();
        if ($documents->isEmpty()) {
            return response()->json(['message' => 'No documents found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => $documents,
            'message' => 'Documents retrieved successfully',
            'success' => true,
        ], Response::HTTP_OK);
    }

    public function store(DocumentRequest $request)
    {
        try {
            $document = Document::create($request->validated());

            return response()->json([
                'data' => $document,
                'message' => 'Document created successfully',
                'success' => true,
            ], Response::HTTP_CREATED);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
    }

    public function show(int $id)
    {
        try {
            $document = Document::find($id);
            if (! $document) {
                return response()->json([
                    'message' => 'No document found with this ID',
                    'success' => false,
                ], Response::HTTP_NOT_FOUND);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while retrieving the document: '.$e->getMessage()]);
        }
        $document = Document::findOrFail($id);

        return response()->json([
            'data' => $document,
            'message' => 'Document retrieved successfully',
            'success' => true,
        ], Response::HTTP_OK);
    }

    public function update(DocumentRequest $request, $id)
    {
        try {
            $document = Document::find($id);
            if (! $document) {
                return response()->json([
                    'message' => 'No document found with this ID',
                    'success' => false,
                ], Response::HTTP_NOT_FOUND);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while retrieving the document: '.$e->getMessage()]);
        }
        $document = Document::findOrFail($id);
        $document->update($request->validated());

        return response()->json([
            'data' => $document,
            'message' => 'Document updated successfully',
            'success' => true,
        ], Response::HTTP_OK);
    }

    public function destroy($id)
    {
        try {
            $document = Document::find($id);
            if (! $document) {
                return response()->json([
                    'message' => 'No document found with this ID',
                    'success' => false,
                ], Response::HTTP_NOT_FOUND);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while retrieving the document: '.$e->getMessage()]);
        }
        $document->delete();

        return response()->json([
            'message' => 'Document deleted successfully',
            'success' => true,
        ], Response::HTTP_NO_CONTENT);
    }
}
