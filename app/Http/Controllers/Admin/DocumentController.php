<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::with('uploader')->latest()->paginate(10);
        return view('admin.documents.index', compact('documents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,png|max:5120', // Max 5MB
        ]);

        $path = $request->file('document')->store('documents', 'local'); // Stored in storage/app/documents (Secure)

        Document::create([
            'title' => $request->title,
            'file_path' => $path,
            'uploaded_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Document uploaded securely.');
    }

    public function download($id)
    {
        $document = Document::findOrFail($id);

        if (!Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('local')->download($document->file_path, $document->title . '.' . pathinfo($document->file_path, PATHINFO_EXTENSION));
    }

    public function destroy($id)
    {
        $document = Document::findOrFail($id);

        if (Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->back()->with('success', 'Document deleted.');
    }
}
