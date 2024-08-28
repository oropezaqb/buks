<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
{
    use HasFactory;
    protected $table = 'documents';
    protected $guarded = [];
    public function path()
    {
        return route('documents.show', $this);
    }
    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'document_type_id');
    }
}
