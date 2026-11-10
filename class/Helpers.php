<?php
/** Fungsi bantu global: escape output, CSRF, redirect, dan pembuat elemen form. */

// Escape output agar aman dari XSS
function e($value): string {
    return is_scalar($value) ? htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') : '';
}

// ---------- CSRF ----------
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify($token): bool {
    return is_string($token) && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

// ---------- View ----------
function render_view(string $file, array $vars = []): void {
    extract($vars, EXTR_SKIP);
    include dirname(__DIR__) . '/view/' . $file . '.php';
}

// ---------- Elemen form (menghapus duplikasi di tiap view) ----------
function html_attrs(array $attrs): string {
    $out = '';
    foreach ($attrs as $name => $value) {
        if ($value === false || $value === null) continue;
        $out .= $value === true ? ' ' . e($name) : ' ' . e($name) . '="' . e($value) . '"';
    }
    return $out;
}

function form_open(string $slug, bool $isEdit, int $id = 0): string {
    $html  = '<form id="' . e($slug) . '-form" method="POST" action="index.php?page=' . e($slug) . '">';
    $html .= csrf_field();
    $html .= '<input type="hidden" name="op" value="' . ($isEdit ? 'update' : 'create') . '">';
    if ($isEdit) {
        $html .= '<input type="hidden" name="id" value="' . e($id) . '">';
    }
    return $html;
}

function form_close(string $slug, bool $isEdit): string {
    $html = '<button type="submit">' . ($isEdit ? 'Simpan Perubahan' : 'Tambah') . '</button>';
    if ($isEdit) {
        $html .= '<a class="btn btn-danger" href="?page=' . e($slug) . '">Batal</a>';
    }
    return $html . '</form>';
}

function input_field(string $name, string $label, $value, array $attrs = [], string $type = 'text'): string {
    $attrs = ['type' => $type, 'id' => $name, 'name' => $name, 'value' => $value] + $attrs;
    return '<label for="' . e($name) . '">' . e($label) . '</label><input' . html_attrs($attrs) . '>';
}

/** $options = [nilai => label] */
function select_field(string $name, string $label, array $options, $selected, string $placeholder = ''): string {
    $html = '<label for="' . e($name) . '">' . e($label) . '</label>';
    $html .= '<select id="' . e($name) . '" name="' . e($name) . '" required>';
    if ($placeholder !== '') {
        $html .= '<option value="">' . e($placeholder) . '</option>';
    }
    foreach ($options as $value => $text) {
        $sel = (string)$selected === (string)$value ? ' selected' : '';
        $html .= '<option value="' . e($value) . '"' . $sel . '>' . e($text) . '</option>';
    }
    return $html . '</select>';
}
