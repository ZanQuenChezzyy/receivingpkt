<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ChezzyResource extends Command
{
    protected $signature = 'chezzy:resource';

    protected $description = 'Generate Filament resource for all models except specified ones';

    public function handle()
    {
        // 1. Tanya jenis resource
        $selectedOption = $this->choice(
            'Resource jenis apa yang ingin anda buat?',
            ['default' => 'Default (dengan Pages)', 'simple' => 'Simple (Modal/Modal-based)'],
            'default'
        );

        $modelsPath = app_path('Models');
        if (! File::isDirectory($modelsPath)) {
            $this->error("Folder 'app/Models' tidak ditemukan.");

            return;
        }

        $modelFiles = File::files($modelsPath);
        $models = collect($modelFiles)
            ->map(fn ($file) => pathinfo($file, PATHINFO_FILENAME))
            ->toArray();

        $alwaysExcludedModels = ['User', 'Role', 'Permission'];
        // RESET INDEX agar 0, 1, 2...
        $modelsToProcess = array_values(array_diff($models, $alwaysExcludedModels));

        if (empty($modelsToProcess)) {
            $this->warn('Tidak ada model yang tersedia.');

            return;
        }

        $excludedModels = [];
        if ($this->confirm("\033[36mApakah ada model yang ingin dikecualikan?\033[0m", false)) {
            $excludedModels = $this->askExcludedModels($modelsToProcess);
        }

        // Filter ulang dan RESET INDEX lagi
        $modelsToProcess = array_values(array_diff($modelsToProcess, $excludedModels));

        $simpleModels = [];
        if (! empty($excludedModels) && $this->confirm("\033[36mApakah model yang dikecualikan ingin dibuat sebagai resource\033[0m [\033[33msimple\033[0m] ?", false)) {
            $simpleModels = array_values($excludedModels);
        }

        // Tampilkan Resume
        $this->renderTableSummary($modelsToProcess, $simpleModels);

        if (! $this->confirm("\033[36mLanjutkan proses pembuatan resource?\033[0m", true)) {
            $this->error('Proses dibatalkan.');

            return;
        }

        $totalModels = count($modelsToProcess) + count($simpleModels);
        $progress = 0;

        foreach ($modelsToProcess as $model) {
            $progress++;
            $this->displayProgress($progress, $totalModels, $model);
            $this->generateResource($model, 'default');
        }

        foreach ($simpleModels as $model) {
            $progress++;
            $this->displayProgress($progress, $totalModels, $model);
            $this->generateResource($model, 'simple');
        }

        $this->output->write("\033[?25h"); // Munculkan kembali cursor
        $this->newLine(2);
        $this->info('✅ Semua resource telah berhasil dibuat.');
    }

    private function renderTableSummary($default, $simple)
    {
        $this->info("\nResume Pembuatan Resource:");
        $data = [];
        foreach ($default as $m) {
            $data[] = [$m, 'Default'];
        }
        foreach ($simple as $m) {
            $data[] = [$m, 'Simple'];
        }

        $this->table(['Model', 'Tipe'], $data);
    }

    private function askExcludedModels(array $models): array
    {
        $excluded = [];
        $this->info("\nDaftar Model:");
        foreach ($models as $index => $model) {
            $this->line(" [\033[33m$index\033[0m] $model");
        }

        $input = $this->ask('Masukkan nomor model (pisahkan dengan koma, misal: 0,2)');
        if ($input !== null) {
            $indexes = explode(',', $input);
            foreach ($indexes as $index) {
                $index = trim($index);
                if (isset($models[$index])) {
                    $excluded[] = $models[$index];
                }
            }
        }

        return $excluded;
    }

    private function generateResource(string $model, string $type)
    {
        // Menggunakan flag --quiet agar tidak banyak noise,
        // atau sediakan input otomatis yang lebih aman.
        $command = "php artisan make:filament-resource $model ".($type === 'simple' ? '--simple ' : '').'--generate --view';

        $process = proc_open($command, [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes);

        if (is_resource($process)) {
            // Kita coba tebak kolom identitas (name atau purchase_order_no dsb)
            // Atau cukup kirim enter kosong untuk default Filament
            fwrite($pipes[0], "\nno\n");
            fclose($pipes[0]);
            proc_close($process);
        }
    }

    private function displayProgress(int $current, int $total, string $model)
    {
        $progressWidth = 30; // Panjang progress bar
        $progress = ($current / $total) * 100;
        $filledBars = round(($progress / 100) * $progressWidth);
        $emptyBars = $progressWidth - $filledBars;
        $progressBar = '['.str_repeat('=', $filledBars).'>'.str_repeat('-', $emptyBars).']';

        if ($current === 1) {
            // Sembunyikan cursor pertama kali sebelum progress dimulai
            $this->output->write("\033[?25l");
        }

        if ($current === $total) {
            // Model terakhir, tampilkan status "Berhasil" dalam warna cyan
            $progressText = " $current/$total $progressBar ".intval($progress)."% \033[36mBerhasil\033[0m";
        } else {
            // Model masih dalam proses, tampilkan status "Membuat resource" dalam warna kuning
            $progressText = " $current/$total $progressBar ".intval($progress)."% \033[33mMembuat resource $model...\033[0m";
        }

        // Hapus baris sebelumnya dan tampilkan progress baru
        $this->output->write("\033[2K\r".str_pad($progressText, 100));

        if ($current === $total) {
            $this->output->write("\033[?25h");
        }

        usleep(500000); // Delay untuk efek progress bar
    }
}
