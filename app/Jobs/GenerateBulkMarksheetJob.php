<?php

namespace App\Jobs;

use App\Http\Controllers\Backend\Result\ResultController;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateBulkMarksheetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $exportId;

    public int $tries = 3;

    public int $timeout = 0;

    public function __construct(int $exportId)
    {
        $this->exportId = $exportId;
    }

    public function handle(): void
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $export = DB::table('bulk_marksheet_exports')->where('id', $this->exportId)->first();
        if (!$export) {
            return;
        }

        DB::table('bulk_marksheet_exports')->where('id', $this->exportId)->update([
            'status' => 'processing',
            'started_at' => now(),
            'error_message' => null,
            'updated_at' => now(),
        ]);

        try {
            $search = [];
            if (!empty($export->search_params)) {
                $decoded = json_decode((string) $export->search_params, true);
                if (is_array($decoded)) {
                    $search = $decoded;
                }
            }

            $resultId = (int) ($search['result_id'] ?? 0);
            if ($resultId <= 0) {
                $resultId = (int) DB::table('results')
                    ->where('academic_session_id', $search['academic_session_id'] ?? null)
                    ->where('academic_qualification_id', $search['academic_qualification_id'] ?? null)
                    ->where('department_id', $search['department_id'] ?? null)
                    ->where('academic_class_id', $search['academic_class_id'] ?? null)
                    ->where('exam_id', $search['exam_id'] ?? null)
                    ->orderByDesc('id')
                    ->value('id');
            }

            if ($resultId <= 0) {
                throw new \RuntimeException('Result not found');
            }

            $detailIds = DB::table('result_details')->where('result_id', $resultId)->orderBy('id')->pluck('id')->map(fn($v) => (int) $v)->values()->all();
            $items = [];

            $controller = app(ResultController::class);
            foreach ($detailIds as $detailId) {
                $payload = $controller->marksheet($detailId)->getData(true);
                if (is_array($payload) && !empty($payload['id'] ?? null)) {
                    $items[] = $payload;
                }
            }

            $config = app()->make('siteSettingObj');

            $pdf = Pdf::loadView('pdf.result_marksheet_bulk', ['items' => $items, 'config' => $config])
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                ]);

            $out = $pdf->output();
            $path = 'bulk_marksheet_exports/bulk_marksheet_' . $this->exportId . '.pdf';
            Storage::put($path, $out);

            DB::table('bulk_marksheet_exports')->where('id', $this->exportId)->update([
                'status' => 'done',
                'file_path' => $path,
                'finished_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Throwable $e) {
            DB::table('bulk_marksheet_exports')->where('id', $this->exportId)->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
                'updated_at' => now(),
            ]);

            throw $e;
        }
    }
}
