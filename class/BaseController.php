<?php
require_once __DIR__ . '/Helpers.php';

/**
 * Controller dasar: menangani alur POST (create/update/delete), memuat data untuk
 * form ubah, pagination, lalu mengembalikan array data siap tampil untuk view.
 * Subclass cukup menyebutkan model, slug, dan cara membuat entity.
 */
abstract class BaseController {
    protected BaseModel $model;
    protected string $error = '';

    abstract protected function slug(): string;
    abstract protected function view(): string;
    abstract protected function newEntity(): BaseEntity;
    abstract protected function entityFromInput(array $input): BaseEntity;

    /** Data tambahan untuk view (mis. pilihan dropdown). */
    protected function extraViewData(): array {
        return [];
    }

    public function handle(): array {
        $entity = $this->newEntity();

        // POST menjalankan aksi; GET dengan action=edit mengisi form dari data yang tersimpan.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $entity = $this->handlePost($entity);
        } elseif (($this->queryString('action') === 'edit')) {
            $found = $this->model->getById($_GET['id'] ?? 0);
            if ($found !== null) {
                $entity = $found;
            } else {
                $this->capture('Data tidak ditemukan.');
            }
        }

        $perPage = (int)(require __DIR__ . '/../config/config.php')['per_page'];
        $total   = $this->model->countAll();
        $this->capture();
        $pages   = max(1, (int)ceil($total / $perPage));
        // Pastikan nomor halaman selalu berada di rentang yang tersedia.
        $current = min(max(1, (int)($this->queryString('p') ?: 1)), $pages);
        $rows    = $this->model->getAll($perPage, ($current - 1) * $perPage);
        $this->capture();

        // Data umum dipakai semua view; subclass menambahkan kebutuhan khusus seperti opsi dropdown.
        return [
            'slug'       => $this->slug(),
            'rows'       => $rows,
            'entity'     => $entity,
            'error'      => $this->error,
            'pagination' => ['page' => $current, 'pages' => $pages, 'total' => $total],
        ] + $this->extraViewData();
    }

    public function viewFile(): string {
        return $this->view();
    }

    private function handlePost(BaseEntity $entity): BaseEntity {
        // Verifikasi sesi sebelum menerima permintaan yang dapat mengubah data.
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            $this->error = 'Sesi formulir tidak valid atau kedaluwarsa. Muat ulang halaman lalu coba lagi.';
            return $entity;
        }

        $op = is_string($_POST['op'] ?? null) ? $_POST['op'] : '';
        $submitted = $this->entityFromInput($_POST);

        switch ($op) {
            case 'create': $ok = $this->model->create($submitted); break;
            case 'update': $ok = $this->model->update($submitted); break;
            case 'delete': $ok = $this->model->delete($_POST['id'] ?? 0); break;
            default:
                $this->error = 'Aksi tidak dikenal.';
                return $entity;
        }

        if ($ok) {
            // Post/Redirect/Get: cegah submit ulang saat halaman di-refresh
            $codes = ['create' => 'created', 'update' => 'updated', 'delete' => 'deleted'];
            redirect('index.php?page=' . $this->slug() . '&msg=' . $codes[$op]);
        }

        $this->error = $this->model->getError();
        return $op === 'delete' ? $entity : $submitted; // isi ulang form agar input tidak hilang
    }

    private function queryString(string $key): string {
        return is_string($_GET[$key] ?? null) ? $_GET[$key] : '';
    }

    // Simpan pesan galat model (jika ada) tanpa menimpa galat yang sudah tercatat
    private function capture(string $fallback = ''): void {
        if ($this->error !== '') return;
        $this->error = $this->model->getError() ?: $fallback;
    }
}
