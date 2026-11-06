<?php

it('respons publik membawa header keamanan dengan CSP ber-nonce tanpa wildcard', function () {
    $r = $this->get('/cari');

    $csp = $r->headers->get('Content-Security-Policy');
    expect($csp)->toContain("frame-ancestors 'self'")
        ->toContain("object-src 'none'")
        ->toMatch("/script-src 'self' 'nonce-[A-Za-z0-9+\/=_-]+'/")
        ->not->toContain('*');
    expect($csp)->not->toMatch('/script-src[^;]*unsafe-(inline|eval)/');
    $r->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $r->assertHeader('Permissions-Policy');
    $r->assertHeaderMissing('Strict-Transport-Security');
});

it('HSTS hanya dikirim lewat HTTPS', function () {
    $this->get('https://localhost/cari')->assertHeader('Strict-Transport-Security');
});

it('halaman panel memuat skrip dengan nonce yang sama dengan header', function () {
    $r = $this->get('/admin/masuk');
    preg_match("/'nonce-([^']+)'/", $r->headers->get('Content-Security-Policy'), $m);
    expect($m[1] ?? '')->not->toBe('');
});

it('tidak ada handler inline onclick/onsubmit di view', function () {
    $hasil = shell_exec('grep -rlE "onclick=|onsubmit=" '.escapeshellarg(resource_path('views')));
    expect(trim((string) $hasil))->toBe('');
});
