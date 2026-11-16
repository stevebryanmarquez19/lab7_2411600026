<?php

namespace App\Exports;

use App\Support\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Base class for report exports. A subclass only describes WHAT is exported (title, headings, rows);
 * this class knows HOW to turn that into a CSV download or a PDF.
 */
abstract class BaseExport
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    abstract public function title(): string;

    /** @return string[] */
    abstract public function headings(): array;

    /** @return array[] each row is a plain array in heading order */
    abstract public function rows(): array;

    public function filename(string $extension): string
    {
        return str_replace(' ', '-', strtolower($this->title())) . '-' . date('Y-m-d') . '.' . $extension;
    }

    public function toCsv(): StreamedResponse
    {
        $headings = $this->headings();
        $rows = $this->rows();

        return new StreamedResponse(function () use ($headings, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows the peso sign correctly
            fputcsv($out, $headings);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $this->filename('csv') . '"',
        ]);
    }

    public function toPdf()
    {
        return Pdf::make('exports.pdf', [
            'title' => $this->title(),
            'headings' => $this->headings(),
            'rows' => $this->rows(),
        ], count($this->headings()) > 6 ? 'landscape' : 'portrait')->download($this->filename('pdf'));
    }
}
